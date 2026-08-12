<?php
defined( 'ABSPATH' ) || exit;

final class RSV_File10 {
	const MIN_VERSION = '1.0.0-rc1';

	public static function ready() {
		return self::contract_compatible();
	}

	public static function contract_compatible() {
		$base = defined( 'VWLB_VERSION' )
			&& version_compare( VWLB_VERSION, self::MIN_VERSION, '>=' )
			&& class_exists( 'VWLB_Repository' )
			&& class_exists( 'VWLB_Videos' )
			&& class_exists( 'VWLB_Contracts' )
			&& is_callable( array( 'VWLB_Repository', 'find' ) )
			&& is_callable( array( 'VWLB_Videos', 'playback' ) )
			&& is_callable( array( 'VWLB_Videos', 'progress' ) )
			&& is_callable( array( 'VWLB_Videos', 'interact' ) );
		return $base && (bool) apply_filters( 'rsv_file10_contract_compatible', true, self::version(), self::contract_version(), RSV_CONTRACT_VERSION );
	}

	public static function version() { return defined( 'VWLB_VERSION' ) ? (string) VWLB_VERSION : ''; }
	public static function contract_version() {
		if ( defined( 'VWLB_CONTRACT_VERSION' ) ) return (string) VWLB_CONTRACT_VERSION;
		if ( defined( 'VWLB_Contracts::EVENT_VERSION' ) ) return 'event-v' . (string) constant( 'VWLB_Contracts::EVENT_VERSION' );
		return '';
	}

	public static function video( $id ) {
		if ( ! self::ready() ) return null;
		$video = VWLB_Repository::find( 'videos', absint( $id ) );
		return is_array( $video ) ? $video : null;
	}

	public static function eligible_for_reel( $id ) {
		$video = self::video( $id );
		if ( ! $video ) return false;
		$duration = absint( $video['duration_seconds'] ?? 0 );
		return 'published' === ( $video['status'] ?? '' )
			&& $duration >= 60 && $duration <= 600
			&& in_array( $video['rights_status'] ?? '', array( 'declared', 'verified' ), true )
			&& in_array( $video['consent_status'] ?? '', array( 'not_patient_case', 'documented', 'anonymized', 'approved' ), true );
	}

	public static function publicly_eligible( $id ) {
		$video = self::video( $id );
		return self::eligible_for_reel( $id ) && in_array( $video['visibility'] ?? '', array( 'public', 'unlisted' ), true );
	}

	public static function validate_for_reel( $id, $owner_id = 0 ) {
		if ( ! self::ready() ) return RSV_Helpers::error( 'rsv_file10_unavailable', __( 'File 10 media services are unavailable.', RSV_TEXT_DOMAIN ), 503 );
		$video = self::video( $id );
		if ( ! $video ) return RSV_Helpers::error( 'rsv_video_missing', __( 'The selected video does not exist.', RSV_TEXT_DOMAIN ), 404 );
		if ( $owner_id && ! current_user_can( 'manage_options' ) && absint( $video['owner_id'] ?? 0 ) !== absint( $owner_id ) ) return RSV_Helpers::error( 'rsv_video_forbidden', __( 'You cannot link this video.', RSV_TEXT_DOMAIN ), 403 );
		$duration = absint( $video['duration_seconds'] ?? 0 );
		if ( $duration < 60 || $duration > 600 ) return RSV_Helpers::error( 'rsv_duration_invalid', __( 'Reels must be between 60 and 600 seconds using File 10 verified duration.', RSV_TEXT_DOMAIN ), 422 );
		if ( ! in_array( $video['status'] ?? '', array( 'review', 'scheduled', 'published' ), true ) ) return RSV_Helpers::error( 'rsv_video_not_ready', __( 'File 10 media is still processing or restricted.', RSV_TEXT_DOMAIN ), 422 );
		if ( ! in_array( $video['rights_status'] ?? '', array( 'declared', 'verified' ), true ) ) return RSV_Helpers::error( 'rsv_rights_incomplete', __( 'Media rights review is incomplete.', RSV_TEXT_DOMAIN ), 422 );
		if ( ! in_array( $video['consent_status'] ?? '', array( 'not_patient_case', 'documented', 'anonymized', 'approved' ), true ) ) return RSV_Helpers::error( 'rsv_consent_incomplete', __( 'Patient privacy review is incomplete.', RSV_TEXT_DOMAIN ), 422 );
		return $video;
	}

	public static function playback( $video_id ) {
		if ( ! self::ready() ) return RSV_Helpers::error( 'rsv_file10_unavailable', __( 'Playback is temporarily unavailable.', RSV_TEXT_DOMAIN ), 503 );
		$result = VWLB_Videos::playback( absint( $video_id ) );
		return is_array( $result ) || is_wp_error( $result ) ? $result : RSV_Helpers::error( 'rsv_playback_malformed', __( 'Playback is temporarily unavailable.', RSV_TEXT_DOMAIN ), 503 );
	}

	public static function progress( $video_id, $seconds, $duration ) {
		if ( ! self::ready() ) return RSV_Helpers::error( 'rsv_file10_unavailable', __( 'Viewing progress is temporarily unavailable.', RSV_TEXT_DOMAIN ), 503 );
		return VWLB_Videos::progress( absint( $video_id ), absint( $seconds ), absint( $duration ) );
	}

	public static function interact( $video_id, $type ) {
		$type = RSV_Helpers::enum( $type, RSV_Contracts::INTERACTIONS, '' );
		if ( ! $type ) return RSV_Helpers::error( 'rsv_interaction_invalid', __( 'The requested interaction is not supported.', RSV_TEXT_DOMAIN ), 422 );
		return self::ready() ? VWLB_Videos::interact( absint( $video_id ), $type ) : RSV_Helpers::error( 'rsv_interactions_unavailable', __( 'Interactions are temporarily unavailable.', RSV_TEXT_DOMAIN ), 503 );
	}

	public static function download_url( $video_id, $reel = array() ) {
		$url = '';
		if ( self::ready() && is_callable( array( 'VWLB_Videos', 'download_url' ) ) ) {
			$result = VWLB_Videos::download_url( absint( $video_id ), array( 'consumer' => 'file11', 'contract_version' => RSV_CONTRACT_VERSION, 'reel_id' => $reel['public_id'] ?? '' ) );
			if ( is_string( $result ) ) $url = $result;
		}
		$url = apply_filters( 'rsv_file10_download_url', $url, absint( $video_id ), $reel, RSV_CONTRACT_VERSION );
		return is_string( $url ) ? esc_url_raw( $url, array( 'https' ) ) : '';
	}

	public static function transcript_url( $video_id, $reel = array() ) {
		$url = '';
		if ( self::ready() && is_callable( array( 'VWLB_Videos', 'transcript_url' ) ) ) {
			$result = VWLB_Videos::transcript_url( absint( $video_id ), array( 'consumer' => 'file11', 'contract_version' => RSV_CONTRACT_VERSION ) );
			if ( is_string( $result ) ) $url = $result;
		}
		$url = apply_filters( 'rsv_file10_transcript_url', $url, absint( $video_id ), $reel, RSV_CONTRACT_VERSION );
		return is_string( $url ) ? esc_url_raw( $url, array( 'http', 'https' ) ) : '';
	}

	public static function interaction_aggregate( $video_id ) {
		$result = null;
		if ( self::ready() && is_callable( array( 'VWLB_Videos', 'interaction_aggregate' ) ) ) {
			$result = VWLB_Videos::interaction_aggregate( absint( $video_id ), array( 'consumer' => 'file11', 'contract_version' => RSV_CONTRACT_VERSION ) );
		}
		$result = apply_filters( 'rsv_file10_interaction_aggregate', $result, absint( $video_id ), RSV_CONTRACT_VERSION );
		return is_array( $result ) ? $result : null;
	}

	public static function control_origin( $playback_url ) {
		$parts = wp_parse_url( (string) $playback_url );
		if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) || ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ) return '';
		$origin = strtolower( $parts['scheme'] ) . '://' . strtolower( $parts['host'] );
		if ( ! empty( $parts['port'] ) ) $origin .= ':' . absint( $parts['port'] );
		return $origin;
	}
}
