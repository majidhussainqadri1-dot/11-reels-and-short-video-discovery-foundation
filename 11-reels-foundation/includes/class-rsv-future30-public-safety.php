<?php
defined( 'ABSPATH' ) || exit;

/**
 * Final public-response guard for Future30 generic feature projections.
 * Write-time validation is not sufficient: external references, reviewer
 * attestations, translations and transcripts can become stale after storage.
 */
final class RSV_Future30_Public_Safety {
	public static function register() {
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'filter_response' ), 90, 3 );
	}

	public static function filter_response( $response, $server, $request ) {
		unset( $server );
		if ( ! ( $response instanceof WP_REST_Response ) || 'GET' !== $request->get_method() ) return $response;
		$route = $request->get_route();
		if ( ! preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/future30/(F11-FUT-[0-9]{3})$#', $route, $m ) ) return $response;
		$data = $response->get_data();
		if ( ! is_array( $data ) || empty( $data['feature_id'] ) ) return $response;
		$reel = RSV_Repository::find( $m[1], true );
		if ( ! $reel || ! RSV_Security::can_view_reel( $reel, 0 ) ) return self::not_found_response();
		$data['objects'] = self::safe_objects( $m[2], (array) ( $data['objects'] ?? array() ), $reel );
		$data['edges']   = self::safe_edges( $m[2], (array) ( $data['edges'] ?? array() ), $reel );
		$response->set_data( $data );
		return $response;
	}

	private static function safe_objects( $feature, $objects, $reel ) {
		$out = array();
		foreach ( $objects as $row ) {
			if ( ! is_array( $row ) ) continue;
			unset( $row['owner_id'], $row['reel_id'], $row['id'] );
			$p = (array) ( $row['payload'] ?? array() );
			if ( 'F11-FUT-005' === $feature ) {
				$owner = RSV_Helpers::text( $p['source_owner'] ?? 'File 06', 30 );
				$refs = (array) ( $p['source_refs'] ?? array() );
				if ( ! $refs || ! self::all_public_refs_valid( $owner, $refs, 'evidence-public-read' ) ) continue;
			}
			if ( 'F11-FUT-016' === $feature ) {
				$ref = RSV_Helpers::text( $p['transcript_ref'] ?? '', 255 );
				if ( empty( $p['reviewed'] ) || ! $ref || true !== apply_filters( 'rsv_future30_transcript_ref_public_valid', false, $ref, $reel ) ) continue;
			}
			if ( 'F11-FUT-018' === $feature ) {
				foreach ( array( 'remedy_refs', 'disease_refs', 'book_refs', 'source_refs' ) as $key ) {
					if ( ! self::all_public_refs_valid( 'File 06', (array) ( $p[ $key ] ?? array() ), 'knowledge-card-public-read' ) ) continue 2;
				}
			}
			if ( 'F11-FUT-019' === $feature && isset( $row['payload']['questions'] ) ) {
				$questions = array();
				foreach ( (array) $row['payload']['questions'] as $question ) {
					if ( ! is_array( $question ) ) continue;
					unset( $question['correct'], $question['answer'], $question['explanation_private'] );
					$questions[] = $question;
				}
				$row['payload']['questions'] = $questions;
			}
			$out[] = $row;
		}
		return $out;
	}

	private static function safe_edges( $feature, $edges, $reel ) {
		$out = array();
		foreach ( $edges as $row ) {
			if ( ! is_array( $row ) ) continue;
			unset( $row['source_reel_id'], $row['reel_id'], $row['owner_id'], $row['id'] );
			$owner = RSV_Helpers::text( $row['target_owner'] ?? '', 30 );
			$ref   = RSV_Helpers::text( $row['target_ref'] ?? '', 255 );
			$p     = (array) ( $row['payload'] ?? array() );
			if ( in_array( $feature, array( 'F11-FUT-003','F11-FUT-004','F11-FUT-006','F11-FUT-008','F11-FUT-011','F11-FUT-030' ), true ) && ! self::public_ref_valid( $owner, $ref, 'future30-public-read' ) ) continue;
			if ( 'F11-FUT-008' === $feature ) {
				$source = RSV_Repository::find( $ref, true );
				if ( ! $source || true !== apply_filters( 'rsv_future30_remix_allowed', false, $source, $reel, $row['edge_type'] ?? 'response', 0 ) ) continue;
				$row['payload'] = array( 'attribution_required'=>true, 'patient_media_default_denied'=>true, 'permission_rechecked'=>true );
			}
			if ( 'F11-FUT-012' === $feature ) {
				if ( empty( $p['accepted'] ) || true !== apply_filters( 'rsv_future30_identity_ref_valid', false, $ref, 'coauthor-public-read' ) ) continue;
				$row['payload'] = array( 'role'=>RSV_Helpers::text( $p['role'] ?? 'co-author', 80 ), 'accepted'=>true );
			}
			if ( 'F11-FUT-013' === $feature ) {
				$attestation = RSV_Helpers::text( $p['attestation_ref'] ?? '', 255 );
				if ( 'approved' !== ( $p['decision'] ?? '' ) || true !== apply_filters( 'rsv_future30_identity_ref_valid', false, $ref, 'peer-reviewer-public-read' ) || ! $attestation || true !== apply_filters( 'rsv_future30_peer_review_attestation_valid', false, $attestation, $ref, $reel, 'approved' ) ) continue;
				$row['payload'] = array( 'decision'=>'approved', 'reviewed_at'=>RSV_Helpers::text( $p['reviewed_at'] ?? '', 40 ), 'conflict_declared'=>false );
			}
			if ( 'F11-FUT-014' === $feature ) {
				$status = sanitize_key( $p['translation_status'] ?? '' );
				if ( empty( $p['human_reviewed'] ) || ! in_array( $status, array( 'approved','published' ), true ) ) continue;
				if ( preg_match( '/^reel_[a-f0-9-]{36}$/', $ref ) && ! self::public_ref_valid( 'File 11', $ref, 'translation-public-read' ) ) continue;
				if ( ! preg_match( '/^reel_[a-f0-9-]{36}$/', $ref ) && true !== apply_filters( 'rsv_future30_translation_projection_valid', false, $row ) ) continue;
			}
			$out[] = $row;
		}
		return $out;
	}

	private static function public_ref_valid( $owner, $ref, $purpose ) {
		if ( ! $owner || ! $ref ) return false;
		if ( 'File 11' === $owner ) {
			$target = RSV_Repository::find( $ref, true );
			return (bool) ( $target && RSV_Security::can_view_reel( $target, 0 ) );
		}
		return true === apply_filters( 'rsv_future30_public_ref_valid', false, $owner, $ref, sanitize_key( $purpose ) );
	}

	private static function all_public_refs_valid( $owner, $refs, $purpose ) {
		foreach ( (array) $refs as $ref ) if ( ! self::public_ref_valid( $owner, RSV_Helpers::text( $ref, 255 ), $purpose ) ) return false;
		return true;
	}

	private static function not_found_response() {
		$response = rest_ensure_response( array( 'code'=>'rsv_not_found', 'message'=>__( 'Reel not found.', RSV_TEXT_DOMAIN ) ) );
		$response->set_status( 404 );
		return $response;
	}
}

RSV_Future30_Public_Safety::register();
