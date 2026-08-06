<?php
defined( 'ABSPATH' ) || exit;

final class RSV_File10 {
	const MIN_VERSION = '1.0.0-rc1';

	public static function ready() {
		$ready = defined( 'VWLB_VERSION' ) && version_compare( VWLB_VERSION, self::MIN_VERSION, '>=' )
			&& class_exists( 'VWLB_Repository' ) && class_exists( 'VWLB_Videos' ) && class_exists( 'VWLB_Contracts' );
		return (bool) apply_filters( 'rsv_file10_ready', $ready, self::MIN_VERSION );
	}
	public static function version() { return defined( 'VWLB_VERSION' ) ? VWLB_VERSION : ''; }
	public static function video( $id ) {
		if ( ! self::ready() || ! absint( $id ) ) return null;
		$video = VWLB_Repository::find( 'videos', absint( $id ) );
		return is_array( $video ) ? $video : null;
	}
	public static function public_id( $id ) {
		$video = self::video( $id );
		return $video && ! empty( $video['public_id'] ) ? (string) $video['public_id'] : 'video_' . substr( hash_hmac( 'sha256', (string) absint( $id ), wp_salt( 'auth' ) ), 0, 20 );
	}
	public static function captions_ready( $id ) {
		if ( ! self::ready() || ! class_exists( 'VWLB_Helpers' ) ) return false;
		global $wpdb;
		$table = VWLB_Helpers::table( 'captions' );
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE video_id=%d AND status=%s", absint( $id ), 'published' ) );
		return $count > 0 || (bool) apply_filters( 'rsv_caption_exception_approved', false, absint( $id ) );
	}
	public static function eligible( $id, $allow_unlisted = false ) {
		$video = self::video( $id );
		if ( ! $video ) return false;
		$duration = (int) ( $video['duration_seconds'] ?? 0 );
		$visibility = (string) ( $video['visibility'] ?? '' );
		$allowed_visibility = $allow_unlisted ? array( 'public', 'unlisted', 'member', 'entitled' ) : array( 'public' );
		return 'published' === ( $video['status'] ?? '' )
			&& in_array( $visibility, $allowed_visibility, true )
			&& $duration >= 60 && $duration <= 600
			&& in_array( $video['rights_status'] ?? '', array( 'declared', 'verified' ), true )
			&& in_array( $video['consent_status'] ?? '', array( 'not_patient_case', 'documented', 'anonymized', 'approved' ), true );
	}
	public static function validate_for_reel( $id, $owner_id = 0 ) {
		if ( ! self::ready() ) return RSV_Helpers::error( 'rsv_file10_unavailable', __( 'File 10 media services are unavailable.', RSV_TEXT_DOMAIN ), 503 );
		$video = self::video( $id );
		if ( ! $video ) return RSV_Helpers::error( 'rsv_video_missing', __( 'The selected video does not exist.', RSV_TEXT_DOMAIN ), 404 );
		if ( $owner_id && ! current_user_can( 'manage_options' ) && (int) ( $video['owner_id'] ?? 0 ) !== (int) $owner_id ) return RSV_Helpers::error( 'rsv_video_forbidden', __( 'You cannot link this video.', RSV_TEXT_DOMAIN ), 403 );
		$duration = (int) ( $video['duration_seconds'] ?? 0 );
		if ( $duration < 60 || $duration > 600 ) return RSV_Helpers::error( 'rsv_duration_invalid', __( 'Reels must be between 60 and 600 seconds using File 10 verified duration.', RSV_TEXT_DOMAIN ), 422 );
		if ( ! in_array( $video['status'] ?? '', array( 'review', 'scheduled', 'published' ), true ) ) return RSV_Helpers::error( 'rsv_video_not_ready', __( 'File 10 media is still processing or restricted.', RSV_TEXT_DOMAIN ), 422 );
		if ( ! in_array( $video['rights_status'] ?? '', array( 'declared', 'verified' ), true ) ) return RSV_Helpers::error( 'rsv_rights_incomplete', __( 'Media rights review is incomplete.', RSV_TEXT_DOMAIN ), 422 );
		if ( ! in_array( $video['consent_status'] ?? '', array( 'not_patient_case', 'documented', 'anonymized', 'approved' ), true ) ) return RSV_Helpers::error( 'rsv_consent_incomplete', __( 'Patient privacy review is incomplete.', RSV_TEXT_DOMAIN ), 422 );
		return $video;
	}
	public static function publication_gate( $video ) {
		if ( ! self::ready() ) return RSV_Helpers::error( 'rsv_file10_unavailable', __( 'File 10 media services are unavailable.', RSV_TEXT_DOMAIN ), 503 );
		if ( method_exists( 'VWLB_Videos', 'publication_gate' ) ) {
			$gate = VWLB_Videos::publication_gate( $video );
			if ( is_wp_error( $gate ) ) { $data = $gate->get_error_data(); return RSV_Helpers::error( 'rsv_file10_gate', $gate->get_error_message(), (int) ( is_array( $data ) ? ( $data['status'] ?? 422 ) : 422 ) ); }
		}
		if ( ! self::captions_ready( (int) $video['id'] ) ) return RSV_Helpers::error( 'rsv_captions_required', __( 'A reviewed caption track or approved accessibility exception is required.', RSV_TEXT_DOMAIN ), 422 );
		return true;
	}
	public static function playback( $video_id ) {
		if ( ! self::ready() ) return RSV_Helpers::error( 'rsv_file10_unavailable', __( 'Playback is temporarily unavailable.', RSV_TEXT_DOMAIN ), 503 );
		return VWLB_Videos::playback( absint( $video_id ) );
	}
	public static function progress( $video_id, $seconds, $duration ) { return self::ready() ? VWLB_Videos::progress( absint( $video_id ), absint( $seconds ), absint( $duration ) ) : null; }
	public static function interact( $video_id, $type ) {
		$type = RSV_Helpers::enum( $type, RSV_Contracts::INTERACTIONS, '' );
		if ( ! $type ) return RSV_Helpers::error( 'rsv_bad_interaction', __( 'Invalid interaction.', RSV_TEXT_DOMAIN ), 422 );
		return self::ready() ? VWLB_Videos::interact( absint( $video_id ), $type ) : RSV_Helpers::error( 'rsv_interactions_unavailable', __( 'Interactions are temporarily unavailable.', RSV_TEXT_DOMAIN ), 503 );
	}
	public static function safe_playback( $video_id ) {
		$result = self::playback( $video_id );
		if ( is_wp_error( $result ) || ! is_array( $result ) ) return $result;
		$payload = is_array( $result['playback'] ?? null ) ? $result['playback'] : array();
		$allowed = array_intersect_key( $payload, array_flip( array( 'type','url','poster','mime','provider','sandbox','allow','captions_url','transcript_url' ) ) );
		if ( ! empty( $allowed['url'] ) ) {
			$url = RSV_Helpers::safe_url( $allowed['url'] );
			if ( ! $url ) return RSV_Helpers::error( 'rsv_playback_url_invalid', __( 'Playback URL is invalid.', RSV_TEXT_DOMAIN ), 503 );
			$allowed['url'] = $url;
		}
		$allowed['sandbox'] = 'allow-scripts allow-same-origin allow-presentation';
		$allowed['allow'] = 'autoplay; fullscreen; picture-in-picture; encrypted-media';
		return array( 'playback' => $allowed, 'session' => is_array( $result['session'] ?? null ) ? array_intersect_key( $result['session'], array_flip( array( 'resume_seconds' ) ) ) : array() );
	}
}
