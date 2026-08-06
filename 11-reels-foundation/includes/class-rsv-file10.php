<?php
defined( 'ABSPATH' ) || exit;

final class RSV_File10 {
	const MIN_VERSION = '1.0.0-rc1';

	public static function ready() {
		return defined( 'VWLB_VERSION' )
			&& version_compare( VWLB_VERSION, self::MIN_VERSION, '>=' )
			&& class_exists( 'VWLB_Repository' )
			&& class_exists( 'VWLB_Videos' )
			&& class_exists( 'VWLB_Contracts' );
	}

	public static function video( $id ) {
		if ( ! self::ready() ) {
			return null;
		}
		return VWLB_Repository::find( 'videos', absint( $id ) );
	}

	public static function publicly_eligible( $id ) {
		$video = self::video( $id );
		if ( ! $video ) {
			return false;
		}
		$duration = (int) ( $video['duration_seconds'] ?? 0 );
		return 'published' === ( $video['status'] ?? '' )
			&& in_array( $video['visibility'] ?? '', array( 'public', 'unlisted' ), true )
			&& $duration >= 60
			&& $duration <= 600
			&& in_array( $video['rights_status'] ?? '', array( 'declared', 'verified' ), true )
			&& in_array( $video['consent_status'] ?? '', array( 'not_patient_case', 'documented', 'anonymized', 'approved' ), true );
	}

	public static function validate_for_reel( $id, $owner_id = 0 ) {
		if ( ! self::ready() ) {
			return RSV_Helpers::error( 'rsv_file10_unavailable', __( 'File 10 media services are unavailable.', RSV_TEXT_DOMAIN ), 503 );
		}
		$video = self::video( $id );
		if ( ! $video ) {
			return RSV_Helpers::error( 'rsv_video_missing', __( 'The selected video does not exist.', RSV_TEXT_DOMAIN ), 404 );
		}
		if ( $owner_id && ! current_user_can( 'manage_options' ) && (int) ( $video['owner_id'] ?? 0 ) !== (int) $owner_id ) {
			return RSV_Helpers::error( 'rsv_video_forbidden', __( 'You cannot link this video.', RSV_TEXT_DOMAIN ), 403 );
		}
		$duration = (int) ( $video['duration_seconds'] ?? 0 );
		if ( $duration < 60 || $duration > 600 ) {
			return RSV_Helpers::error( 'rsv_duration_invalid', __( 'Reels must be between 60 and 600 seconds using File 10 verified duration.', RSV_TEXT_DOMAIN ), 422 );
		}
		if ( ! in_array( $video['status'] ?? '', array( 'review', 'scheduled', 'published' ), true ) ) {
			return RSV_Helpers::error( 'rsv_video_not_ready', __( 'File 10 media is still processing or restricted.', RSV_TEXT_DOMAIN ), 422 );
		}
		if ( ! in_array( $video['rights_status'] ?? '', array( 'declared', 'verified' ), true ) ) {
			return RSV_Helpers::error( 'rsv_rights_incomplete', __( 'Media rights review is incomplete.', RSV_TEXT_DOMAIN ), 422 );
		}
		if ( ! in_array( $video['consent_status'] ?? '', array( 'not_patient_case', 'documented', 'anonymized', 'approved' ), true ) ) {
			return RSV_Helpers::error( 'rsv_consent_incomplete', __( 'Patient privacy review is incomplete.', RSV_TEXT_DOMAIN ), 422 );
		}
		return $video;
	}

	public static function playback( $video_id ) {
		if ( ! self::ready() ) {
			return RSV_Helpers::error( 'rsv_file10_unavailable', __( 'Playback is temporarily unavailable.', RSV_TEXT_DOMAIN ), 503 );
		}
		return VWLB_Videos::playback( absint( $video_id ) );
	}

	public static function progress( $video_id, $seconds, $duration ) {
		return self::ready() ? VWLB_Videos::progress( absint( $video_id ), absint( $seconds ), absint( $duration ) ) : null;
	}

	public static function interact( $video_id, $type ) {
		return self::ready() ? VWLB_Videos::interact( absint( $video_id ), sanitize_key( $type ) ) : RSV_Helpers::error( 'rsv_interactions_unavailable', __( 'Interactions are temporarily unavailable.', RSV_TEXT_DOMAIN ), 503 );
	}
}
