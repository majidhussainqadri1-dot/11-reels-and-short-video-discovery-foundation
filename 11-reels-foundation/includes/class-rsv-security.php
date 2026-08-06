<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Security {
	public static function claims( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$claims  = apply_filters( 'rsv_identity_claims', null, $user_id, RSV_CONTRACT_VERSION );
		if ( is_array( $claims ) ) {
			$normalized = self::normalize_claims( $claims, $user_id, 'filter' );
			return self::contract_compatible( $normalized, 'filter' ) ? $normalized : self::unavailable_claims( $user_id, 'filter-incompatible' );
		}

		if ( function_exists( 'smc_membership_assertions' ) ) {
			$membership = smc_membership_assertions( $user_id );
			$publishing = function_exists( 'smc_publishing_assertions' ) ? smc_publishing_assertions( $user_id ) : array();
			if ( is_array( $membership ) ) {
				$approved_types = (array) ( $membership['approved_membership_types'] ?? array() );
				$capabilities   = array();
				if ( ! empty( $publishing['can_open_composer'] ) ) {
					$capabilities[] = RSV_Contracts::CAP_SUBMIT;
				}
				if ( ! empty( $publishing['can_direct_publish'] ) ) {
					$capabilities[] = RSV_Contracts::CAP_PUBLISH;
				}
				$mapped = array(
					'user_id'             => $user_id,
					'contract_version'     => (string) ( $membership['contract_version'] ?? '' ),
					'source'               => 'file00',
					'status'               => ! empty( $membership['approved'] ) && empty( $membership['suspended'] ) ? 'active' : sanitize_key( $membership['status'] ?? 'unavailable' ),
					'is_founder'           => 'founder' === ( $membership['account_class'] ?? '' ),
					'is_verified_doctor'   => in_array( 'doctor', $approved_types, true ) && ! empty( $membership['professional_verified'] ),
					'is_suspended'         => ! empty( $membership['suspended'] ),
					'is_minor'             => ! empty( $membership['is_minor'] ) || 'minor' === sanitize_key( $membership['age_band'] ?? '' ),
					'age_band'             => sanitize_key( $membership['age_band'] ?? '' ),
					'guardian_required'    => ! empty( $membership['guardian_required'] ),
					'guardian_ok'          => ! array_key_exists( 'guardian_verified', $membership ) || ! empty( $membership['guardian_verified'] ),
					'session_two_factor'   => ! empty( $membership['session_two_factor'] ),
					'membership_approved'  => ! empty( $membership['approved'] ),
					'entitlements'         => (array) ( $membership['entitlements'] ?? array() ),
					'capabilities'         => $capabilities,
				);
				$normalized = self::normalize_claims( $mapped, $user_id, 'file00' );
				return self::contract_compatible( $normalized, 'file00' ) ? $normalized : self::unavailable_claims( $user_id, 'file00-incompatible' );
			}
		}

		$is_admin = $user_id && user_can( $user_id, 'manage_options' );
		return self::normalize_claims(
			array(
				'user_id'            => $user_id,
				'contract_version'    => 'wordpress-recovery-v1',
				'source'              => 'recovery',
				'status'              => $is_admin ? 'active' : 'unavailable',
				'is_founder'          => false,
				'is_verified_doctor'  => false,
				'is_suspended'        => ! $is_admin,
				'is_minor'            => false,
				'guardian_required'   => false,
				'guardian_ok'         => $is_admin,
				'session_two_factor'  => $is_admin,
				'membership_approved' => $is_admin,
				'capabilities'        => array(),
			),
			$user_id,
			'recovery'
		);
	}

	private static function unavailable_claims( $user_id, $source ) {
		return self::normalize_claims(
			array(
				'user_id' => $user_id,
				'source' => $source,
				'status' => 'unavailable',
				'is_suspended' => true,
			),
			$user_id,
			$source
		);
	}

	private static function contract_compatible( $claims, $source ) {
		$declared = trim( (string) ( $claims['contract_version'] ?? '' ) );
		$default  = 'recovery' === $source ? true : '' !== $declared;
		return (bool) apply_filters( 'rsv_identity_contract_compatible', $default, $declared, RSV_CONTRACT_VERSION, $source, $claims );
	}

	private static function normalize_claims( $claims, $user_id, $source ) {
		$defaults = array(
			'user_id'            => $user_id,
			'contract_version'    => '',
			'source'              => $source,
			'status'              => 'unknown',
			'is_founder'          => false,
			'is_verified_doctor'  => false,
			'is_suspended'        => true,
			'is_minor'            => false,
			'age_band'            => '',
			'guardian_required'   => false,
			'guardian_ok'         => false,
			'session_two_factor'  => false,
			'membership_approved' => false,
			'entitlements'        => array(),
			'capabilities'        => array(),
		);
		$claims = wp_parse_args( $claims, $defaults );
		$claims['user_id']          = absint( $claims['user_id'] );
		$claims['contract_version'] = RSV_Helpers::text( $claims['contract_version'], 80 );
		$claims['source']           = sanitize_key( $claims['source'] );
		$claims['status']           = sanitize_key( $claims['status'] );
		$claims['age_band']         = sanitize_key( $claims['age_band'] );
		$claims['capabilities']     = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $claims['capabilities'] ) ) ) );
		$claims['entitlements']     = is_array( $claims['entitlements'] ) ? $claims['entitlements'] : array();
		foreach ( array( 'is_founder', 'is_verified_doctor', 'is_suspended', 'is_minor', 'guardian_required', 'guardian_ok', 'session_two_factor', 'membership_approved' ) as $flag ) {
			$claims[ $flag ] = ! empty( $claims[ $flag ] );
		}
		return $claims;
	}

	public static function can( $capability, $object = null, $purpose = '' ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$capability = sanitize_key( $capability );
		$purpose    = sanitize_key( $purpose );
		$claims     = self::claims();
		if ( ! empty( $claims['is_suspended'] ) || 'active' !== ( $claims['status'] ?? '' ) || empty( $claims['membership_approved'] ) || empty( $claims['guardian_ok'] ) ) {
			return false;
		}

		$sensitive = in_array( $capability, array( RSV_Contracts::CAP_SUBMIT, RSV_Contracts::CAP_PUBLISH, RSV_Contracts::CAP_MODERATE, RSV_Contracts::CAP_MANAGE ), true );
		if ( $sensitive && empty( $claims['session_two_factor'] ) && ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$native   = current_user_can( $capability ) || current_user_can( 'manage_options' );
		$asserted = in_array( $capability, (array) $claims['capabilities'], true );
		if ( RSV_Contracts::CAP_SUBMIT === $capability ) {
			$identity = ! empty( $claims['is_founder'] ) || ! empty( $claims['is_verified_doctor'] ) || current_user_can( 'manage_options' );
			if ( ! $identity ) {
				return false;
			}
		}
		if ( $object && ! current_user_can( 'manage_options' ) && (int) ( $object['owner_id'] ?? 0 ) !== get_current_user_id() ) {
			if ( ! in_array( $capability, array( RSV_Contracts::CAP_PUBLISH, RSV_Contracts::CAP_MODERATE, RSV_Contracts::CAP_MANAGE ), true ) ) {
				return false;
			}
		}

		$base_allowed = $native || $asserted;
		// Integration filters may narrow authorization, never manufacture it.
		$filtered = (bool) apply_filters( 'rsv_authorize', $base_allowed, $capability, $object, $purpose, $claims );
		return $base_allowed && $filtered;
	}

	public static function can_view_reel( $reel, $user_id = null ) {
		if ( ! is_array( $reel ) || 'published' !== ( $reel['status'] ?? '' ) || ! RSV_File10::eligible_for_reel( absint( $reel['video_id'] ?? 0 ) ) ) {
			return false;
		}
		$explicit_user = null !== $user_id;
		$visibility = RSV_Helpers::enum( $reel['visibility'] ?? '', RSV_Contracts::VISIBILITIES, '' );
		$allowed    = false;
		if ( in_array( $visibility, array( 'public', 'unlisted' ), true ) ) {
			$allowed = RSV_File10::publicly_eligible( absint( $reel['video_id'] ?? 0 ) );
		} else {
			$user_id = $explicit_user ? absint( $user_id ) : get_current_user_id();
			if ( $user_id && ( user_can( $user_id, 'manage_options' ) || absint( $reel['owner_id'] ?? 0 ) === $user_id ) ) {
				$allowed = true;
			} elseif ( $user_id ) {
				$claims = self::claims( $user_id );
				if ( 'active' === $claims['status'] && empty( $claims['is_suspended'] ) && ! empty( $claims['membership_approved'] ) && ! empty( $claims['guardian_ok'] ) ) {
					if ( 'member' === $visibility ) {
						$allowed = true;
					} else {
						$entitlements = (array) $claims['entitlements'];
						$explicit = ! empty( $entitlements['reels'] ) || ! empty( $entitlements['base_services']['reels'] );
						$allowed = (bool) apply_filters( 'rsv_reel_entitled', $explicit, $user_id, $reel, $claims );
					}
				}
			}
		}
		if ( ! $allowed ) {
			return false;
		}
		$user_id  = $explicit_user ? absint( $user_id ) : get_current_user_id();
		$filtered = (bool) apply_filters( 'rsv_can_view_reel', true, $reel, $user_id );
		return $allowed && $filtered;
	}

	public static function publisher_label( $user_id ) {
		$claims = self::claims( $user_id );
		if ( ! empty( $claims['is_founder'] ) ) {
			return __( 'Verified Founder', RSV_TEXT_DOMAIN );
		}
		if ( ! empty( $claims['is_verified_doctor'] ) && empty( $claims['is_suspended'] ) ) {
			return __( 'Verified Doctor', RSV_TEXT_DOMAIN );
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return __( 'Authorized Administrator', RSV_TEXT_DOMAIN );
		}
		return __( 'Authorized Publisher', RSV_TEXT_DOMAIN );
	}

	public static function rate_limit( $scope, $limit, $window ) {
		global $wpdb;
		$scope  = RSV_Helpers::text( sanitize_key( $scope ), 80 );
		$limit  = max( 1, absint( $limit ) );
		$window = max( 60, absint( $window ) );
		$actor  = get_current_user_id();
		$ip     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key    = hash_hmac( 'sha256', $actor . '|' . $ip, wp_salt( 'auth' ) );
		$bucket = (int) floor( time() / $window ) * $window;
		$table  = RSV_Helpers::table( 'rate_limits' );
		$now   = RSV_Helpers::now();
		$sql    = $wpdb->prepare(
			"INSERT INTO $table (actor_key,scope_key,window_start,request_count,expires_at,updated_at)
			 VALUES (%s,%s,%d,1,%s,%s)
			 ON DUPLICATE KEY UPDATE request_count=request_count+1,updated_at=VALUES(updated_at)",
			$key,
			$scope,
			$bucket,
			gmdate( 'Y-m-d H:i:s', $bucket + $window + 60 ),
			$now
		);
		if ( false === $wpdb->query( $sql ) ) {
			return self::fail_closed_rate_limit();
		}
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT request_count FROM $table WHERE actor_key=%s AND scope_key=%s AND window_start=%d", $key, $scope, $bucket ) );
		if ( $count > $limit ) {
			return RSV_Helpers::error( 'rsv_rate_limited', __( 'Too many requests. Please wait and try again.', RSV_TEXT_DOMAIN ), 429, array( 'retry_after' => max( 1, $bucket + $window - time() ) ) );
		}
		return true;
	}

	private static function fail_closed_rate_limit() {
		return RSV_Helpers::error( 'rsv_rate_limit_unavailable', __( 'The action is temporarily unavailable. Please retry shortly.', RSV_TEXT_DOMAIN ), 503 );
	}

	public static function idempotency_begin( $scope, $key, $payload ) {
		$key   = RSV_Helpers::text( $key, 120 );
		$scope = RSV_Helpers::text( sanitize_key( $scope ), 80 );
		if ( ! $key ) {
			return RSV_Helpers::error( 'rsv_idempotency_required', __( 'An idempotency key is required.', RSV_TEXT_DOMAIN ), 400 );
		}
		global $wpdb;
		$table = RSV_Helpers::table( 'idempotency' );
		$actor = get_current_user_id();
		$hash  = hash( 'sha256', RSV_Helpers::json_encode( $payload ) );
		$now   = RSV_Helpers::now();
		$result = $wpdb->insert(
			$table,
			array(
				'actor_id'      => $actor,
				'scope_key'     => $scope,
				'idem_key'      => $key,
				'payload_hash'  => $hash,
				'status'        => 'started',
				'response_json' => '{}',
				'expires_at'    => gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS ),
				'created_at'    => $now,
				'updated_at'    => $now,
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		if ( 1 === $result ) {
			return array( 'replay' => false );
		}
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE actor_id=%d AND scope_key=%s AND idem_key=%s", $actor, $scope, $key ), ARRAY_A );
		if ( ! $row ) {
			return RSV_Helpers::error( 'rsv_idempotency_unavailable', __( 'The request could not be safely deduplicated.', RSV_TEXT_DOMAIN ), 503 );
		}
		if ( ! hash_equals( (string) $row['payload_hash'], $hash ) ) {
			return RSV_Helpers::error( 'rsv_idempotency_conflict', __( 'The idempotency key was reused with different data.', RSV_TEXT_DOMAIN ), 409 );
		}
		if ( 'complete' === $row['status'] ) {
			return array( 'replay' => true, 'response' => RSV_Helpers::json_decode( $row['response_json'] ) );
		}
		if ( 'failed' === $row['status'] ) {
			$deleted = $wpdb->delete( $table, array( 'id' => absint( $row['id'] ) ), array( '%d' ) );
			return $deleted ? self::idempotency_begin( $scope, $key, $payload ) : RSV_Helpers::error( 'rsv_request_in_progress', __( 'The request is already in progress.', RSV_TEXT_DOMAIN ), 409 );
		}
		return RSV_Helpers::error( 'rsv_request_in_progress', __( 'The request is already in progress.', RSV_TEXT_DOMAIN ), 409 );
	}

	public static function idempotency_finish( $scope, $key, $response ) {
		global $wpdb;
		$result = $wpdb->update(
			RSV_Helpers::table( 'idempotency' ),
			array( 'status' => 'complete', 'response_json' => RSV_Helpers::json_encode( $response ), 'updated_at' => RSV_Helpers::now() ),
			array( 'actor_id' => get_current_user_id(), 'scope_key' => sanitize_key( $scope ), 'idem_key' => RSV_Helpers::text( $key, 120 ), 'status' => 'started' ),
			array( '%s', '%s', '%s' ),
			array( '%d', '%s', '%s', '%s' )
		);
		return 1 === $result;
	}

	public static function idempotency_fail( $scope, $key ) {
		global $wpdb;
		return false !== $wpdb->update(
			RSV_Helpers::table( 'idempotency' ),
			array( 'status' => 'failed', 'updated_at' => RSV_Helpers::now() ),
			array( 'actor_id' => get_current_user_id(), 'scope_key' => sanitize_key( $scope ), 'idem_key' => RSV_Helpers::text( $key, 120 ), 'status' => 'started' ),
			array( '%s', '%s' ),
			array( '%d', '%s', '%s', '%s' )
		);
	}
}
