<?php
defined( 'ABSPATH' ) || exit;

trait RSV_Top20_Context_Trait {
	public static function authorization_gate( $allowed, $capability, $object, $purpose, $claims ) {
		if ( ! $allowed ) return false;
		if ( RSV_Contracts::CAP_PUBLISH === $capability && 'publish_reel' === $purpose && is_array( $object ) ) {
			return ! is_wp_error( self::publication_gate( $object ) );
		}
		return true;
	}

	public static function publication_gate( $reel ) {
		$context = self::context_row( absint( $reel['id'] ?? 0 ) );
		if ( ! $context || '' === trim( (string) $context['source_title'] ) || '' === trim( (string) $context['safety_summary'] ) ) {
			return RSV_Helpers::error( 'rsv_source_safety_required', __( 'A reviewed source label and safety summary are required before publication.', RSV_TEXT_DOMAIN ), 422 );
		}
		$video       = RSV_File10::video( absint( $reel['video_id'] ?? 0 ) );
		$has_caption = ! empty( $video['captions'] ) || ! empty( $context['transcript_url'] ) || '' !== RSV_File10::transcript_url( absint( $reel['video_id'] ?? 0 ), $reel );
		$exception   = (bool) apply_filters( 'rsv_caption_exception_approved', false, $reel, $video, $context );
		if ( ! $has_caption && ! $exception ) {
			return RSV_Helpers::error( 'rsv_caption_track_required', __( 'A caption track, transcript link or explicitly approved accessibility exception is required.', RSV_TEXT_DOMAIN ), 422 );
		}
		return true;
	}

	public static function can_view_reel_filter( $allowed, $reel, $user_id ) {
		if ( ! $allowed ) return false;
		$user_id = absint( $user_id );
		$owner_or_operator = $user_id && ( absint( $reel['owner_id'] ?? 0 ) === $user_id || user_can( $user_id, 'manage_options' ) );
		if ( ! $owner_or_operator && is_wp_error( self::publication_gate( $reel ) ) ) return false;
		if ( self::youth_mode( $user_id ) && ! self::reel_is_youth_safe( $reel ) ) return false;
		return true;
	}

	public static function reel_is_youth_safe( $reel ) {
		$labels         = RSV_Helpers::json_decode( $reel['safety_labels_json'] ?? '[]' );
		$blocked_labels = (array) apply_filters( 'rsv_youth_blocked_labels', array( 'medical-claim-risk', 'patient-case', 'adult', 'graphic', 'emergency', 'private-contact' ) );
		if ( array_intersect( $blocked_labels, $labels ) ) return false;
		$blocked_topics = (array) apply_filters( 'rsv_youth_blocked_topics', array( 'case-taking' ) );
		return ! in_array( (string) ( $reel['topic'] ?? '' ), $blocked_topics, true );
	}

	public static function mandatory_youth_mode( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) return false;
		$claims = RSV_Security::claims( $user_id );
		$minor  = ! empty( $claims['is_minor'] ) || in_array( $claims['age_band'] ?? '', array( 'minor', 'child', 'teen' ), true );
		return (bool) apply_filters( 'rsv_mandatory_youth_mode', $minor, $user_id, $claims );
	}

	public static function youth_mode( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( self::mandatory_youth_mode( $user_id ) ) return true;
		if ( ! $user_id ) return (bool) apply_filters( 'rsv_guest_youth_safe', false );
		$prefs = self::preferences( $user_id );
		return ! empty( $prefs['youth_safe'] );
	}

	public static function filter_youth_social_url( $url ) { return self::youth_mode() ? '' : $url; }
	public static function filter_youth_download( $url ) { return self::youth_mode() ? '' : $url; }

	public static function preferences( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$mandatory = self::mandatory_youth_mode( $user_id );
		$defaults = array(
			'user_id'             => $user_id,
			'youth_safe'          => $mandatory,
			'history_paused'      => false,
			'session_limit_minutes'=> 15,
			'natural_stop_every'  => 10,
			'late_night_reminder' => true,
			'version'             => 0,
			'mandatory_youth_safe'=> $mandatory,
		);
		if ( ! $user_id ) return $defaults;
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . RSV_Helpers::table( 'preferences' ) . ' WHERE user_id=%d', $user_id ), ARRAY_A );
		if ( ! $row ) return $defaults;
		return array(
			'user_id'              => $user_id,
			'youth_safe'           => $mandatory || ! empty( $row['youth_safe'] ),
			'history_paused'       => ! empty( $row['history_paused'] ),
			'session_limit_minutes'=> min( 60, max( 5, absint( $row['session_limit_minutes'] ) ) ),
			'natural_stop_every'   => min( 25, max( 5, absint( $row['natural_stop_every'] ) ) ),
			'late_night_reminder'  => ! empty( $row['late_night_reminder'] ),
			'version'              => absint( $row['version'] ),
			'mandatory_youth_safe' => $mandatory,
		);
	}

	public static function save_preferences( $data, $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) return RSV_Helpers::error( 'rsv_login_required', __( 'Sign in to save well-being preferences.', RSV_TEXT_DOMAIN ), 401 );
		$data    = is_array( $data ) ? $data : array();
		$current = self::preferences( $user_id );
		$expected = array_key_exists( 'version', $data ) ? absint( $data['version'] ) : absint( $current['version'] );
		$mandatory = self::mandatory_youth_mode( $user_id );

		$youth_safe = array_key_exists( 'youth_safe', $data ) ? ! empty( $data['youth_safe'] ) : ! empty( $current['youth_safe'] );
		$history_paused = array_key_exists( 'history_paused', $data ) ? ! empty( $data['history_paused'] ) : ! empty( $current['history_paused'] );
		$session_limit  = array_key_exists( 'session_limit_minutes', $data ) ? absint( $data['session_limit_minutes'] ) : absint( $current['session_limit_minutes'] );
		$natural_stop   = array_key_exists( 'natural_stop_every', $data ) ? absint( $data['natural_stop_every'] ) : absint( $current['natural_stop_every'] );
		$late_night     = array_key_exists( 'late_night_reminder', $data ) ? ! empty( $data['late_night_reminder'] ) : ! empty( $current['late_night_reminder'] );

		$values = array(
			'youth_safe'           => ( $mandatory || $youth_safe ) ? 1 : 0,
			'history_paused'       => $history_paused ? 1 : 0,
			'session_limit_minutes'=> min( 60, max( 5, $session_limit ) ),
			'natural_stop_every'   => min( 25, max( 5, $natural_stop ) ),
			'late_night_reminder'  => $late_night ? 1 : 0,
			'updated_at'           => RSV_Helpers::now(),
		);
		global $wpdb;
		$table = RSV_Helpers::table( 'preferences' );
		$result = RSV_DB::transaction(
			static function () use ( $wpdb, $table, $user_id, $current, $expected, $values ) {
				if ( 0 === absint( $current['version'] ) ) {
					$insert_values = $values;
					$insert_values['user_id'] = $user_id;
					$insert_values['version'] = 1;
					$ok = $wpdb->insert( $table, $insert_values, array( '%d','%d','%d','%d','%d','%s','%d','%d' ) );
				} else {
					if ( $expected !== absint( $current['version'] ) ) return RSV_Helpers::error( 'rsv_preferences_conflict', __( 'Preferences changed. Refresh and try again.', RSV_TEXT_DOMAIN ), 409 );
					$update_values = $values;
					$update_values['version'] = $expected + 1;
					$ok = $wpdb->update( $table, $update_values, array( 'user_id' => $user_id, 'version' => $expected ), array( '%d','%d','%d','%d','%d','%s','%d' ), array( '%d','%d' ) );
				}
				if ( 1 !== $ok ) return RSV_Helpers::error( 'rsv_preferences_write_failed', __( 'Preferences changed or could not be saved.', RSV_TEXT_DOMAIN ), 409 );
				if ( ! RSV_Helpers::audit( 'wellbeing_preferences', $user_id, 'update', (string) $current['version'], (string) ( absint( $current['version'] ) + 1 ), 'User-controlled privacy and well-being settings', array(), $user_id ) ) {
					return RSV_Helpers::error( 'rsv_preferences_evidence_failed', __( 'Preferences could not be saved with complete evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				return true;
			}
		);
		return is_wp_error( $result ) ? $result : self::preferences( $user_id );
	}

	private static function context_row( $reel_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . RSV_Helpers::table( 'reel_context' ) . ' WHERE reel_id=%d', absint( $reel_id ) ), ARRAY_A );
	}

	public static function public_context( $reel ) {
		$row = is_array( $reel ) ? self::context_row( absint( $reel['id'] ?? 0 ) ) : null;
		if ( ! $row ) return array( 'source_title'=>'','source_url'=>'','transcript_url'=>'','safety_summary'=>'','response_allowed'=>false,'patient_reuse_policy'=>'disabled','version'=>0 );
		$transcript = (string) $row['transcript_url'];
		if ( '' === $transcript && is_array( $reel ) ) $transcript = RSV_File10::transcript_url( absint( $reel['video_id'] ?? 0 ), $reel );
		return array(
			'source_title'         => (string) $row['source_title'],
			'source_url'           => (string) $row['source_url'],
			'transcript_url'       => $transcript,
			'safety_summary'       => (string) $row['safety_summary'],
			'response_allowed'     => ! empty( $row['allow_response'] ),
			'patient_reuse_policy' => (string) $row['patient_reuse_policy'],
			'version'              => absint( $row['version'] ),
		);
	}

	private static function safe_external_url( $value ) {
		$url = esc_url_raw( (string) $value, array( 'http', 'https' ) );
		return is_string( $url ) ? $url : '';
	}

	private static function safe_destination_url( $value ) {
		$url = esc_url_raw( (string) $value, array( 'http', 'https' ) );
		return $url ? wp_validate_redirect( $url, '' ) : '';
	}

	public static function save_context( $reel, $data ) {
		if ( ! is_array( $reel ) || ! RSV_Security::can( RSV_Contracts::CAP_SUBMIT, $reel, 'update_reel_context' ) ) return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		$data         = is_array( $data ) ? $data : array();
		$source_title = RSV_Helpers::text( $data['source_title'] ?? '', 255 );
		$safety       = RSV_Helpers::textarea( $data['safety_summary'] ?? '', 2000 );
		$policy       = RSV_Helpers::enum( $data['patient_reuse_policy'] ?? 'explicit-consent-required', array( 'not-applicable', 'explicit-consent-required', 'disabled' ), 'explicit-consent-required' );
		if ( '' === trim( $source_title ) || '' === trim( $safety ) ) return RSV_Helpers::error( 'rsv_source_safety_required', __( 'A reviewed source label and safety summary are required.', RSV_TEXT_DOMAIN ), 422 );
		global $wpdb;
		$table = RSV_Helpers::table( 'reel_context' );
		return RSV_DB::transaction(
			static function () use ( $wpdb, $table, $reel, $data, $source_title, $safety, $policy ) {
				$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE reel_id=%d FOR UPDATE", absint( $reel['id'] ) ), ARRAY_A );
				$expected = array_key_exists( 'version', $data ) ? absint( $data['version'] ) : 0;
				$values = array(
					'source_title'        => $source_title,
					'source_url'          => self::safe_external_url( $data['source_url'] ?? '' ),
					'transcript_url'      => self::safe_external_url( $data['transcript_url'] ?? '' ),
					'safety_summary'      => $safety,
					'allow_response'      => 'disabled' === $policy ? 0 : ( ! empty( $data['allow_response'] ) ? 1 : 0 ),
					'patient_reuse_policy'=> $policy,
					'version'             => $existing ? absint( $existing['version'] ) + 1 : 1,
					'updated_at'          => RSV_Helpers::now(),
				);
				if ( $existing && 0 === $expected ) {
					$same = (string) $existing['source_title'] === (string) $values['source_title'] && (string) $existing['source_url'] === (string) $values['source_url'] && (string) $existing['transcript_url'] === (string) $values['transcript_url'] && (string) $existing['safety_summary'] === (string) $values['safety_summary'] && absint( $existing['allow_response'] ) === absint( $values['allow_response'] ) && (string) $existing['patient_reuse_policy'] === (string) $values['patient_reuse_policy'];
					if ( $same ) return self::public_context( $reel );
					return RSV_Helpers::error( 'rsv_context_version_conflict', __( 'Source and safety context changed. Refresh and try again.', RSV_TEXT_DOMAIN ), 409 );
				}
				if ( $existing && $expected !== absint( $existing['version'] ) ) return RSV_Helpers::error( 'rsv_context_version_conflict', __( 'Source and safety context changed. Refresh and try again.', RSV_TEXT_DOMAIN ), 409 );
				$ok = $existing
					? $wpdb->update( $table, $values, array( 'reel_id' => absint( $reel['id'] ), 'version' => $expected ), array( '%s','%s','%s','%s','%d','%s','%d','%s' ), array( '%d','%d' ) )
					: $wpdb->insert( $table, array_merge( array( 'reel_id' => absint( $reel['id'] ) ), $values ), array( '%d','%s','%s','%s','%s','%d','%s','%d','%s' ) );
				if ( 1 !== $ok ) return RSV_Helpers::error( 'rsv_context_write_failed', __( 'Source and safety context could not be saved.', RSV_TEXT_DOMAIN ), 500 );
				if ( ! RSV_Helpers::audit( 'reel_context', absint( $reel['id'] ), 'update', $existing ? (string) $existing['version'] : '', (string) $values['version'], 'Source/safety context changed' ) ) return RSV_Helpers::error( 'rsv_context_evidence_failed', __( 'Source and safety context could not be saved with complete evidence.', RSV_TEXT_DOMAIN ), 500 );
				return self::public_context( $reel );
			}
		);
	}

	public static function rest_context( $request ) {
		$reel = RSV_Repository::find( sanitize_text_field( (string) $request['id'] ), true );
		if ( ! RSV_Security::can_view_reel( $reel ) ) return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		if ( 'public' !== (string) ( $reel['visibility'] ?? '' ) ) RSV_Helpers::no_cache_private();
		return rest_ensure_response( self::public_context( $reel ) );
	}

	public static function rest_context_update( $request ) {
		$reel   = RSV_Repository::find( sanitize_text_field( (string) $request['id'] ), true );
		$result = self::save_context( $reel, (array) $request->get_json_params() );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}
}
