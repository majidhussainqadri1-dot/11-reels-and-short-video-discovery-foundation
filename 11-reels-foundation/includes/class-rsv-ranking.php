<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Ranking {
	public static function score( $reel, $video = array(), $metrics = array() ) {
		$score = 20.0;
		if ( 'verified' === ( $reel['rights_status'] ?? '' ) ) $score += 12;
		elseif ( 'declared' === ( $reel['rights_status'] ?? '' ) ) $score += 5;
		if ( in_array( $reel['consent_status'] ?? '', array( 'not_patient_case','approved','anonymized' ), true ) ) $score += 10;
		if ( 'ready' === ( $reel['captions_status'] ?? '' ) ) $score += 10;
		if ( ! empty( $reel['cover_id'] ) ) $score += 4;
		if ( ! empty( $reel['caption'] ) ) $score += 4;
		$age_days = ! empty( $reel['published_at'] ) ? max( 0, ( time() - strtotime( $reel['published_at'] . ' UTC' ) ) / DAY_IN_SECONDS ) : 365;
		$score += max( 0, 12 - min( 12, $age_days / 3 ) );
		$completion = max( 0, min( 1, (float) ( $metrics['completion_rate'] ?? 0 ) ) );
		$score += 16 * $completion;
		$quality = max( 0, min( 1, (float) ( $metrics['positive_rate'] ?? 0 ) ) );
		$score += 8 * $quality;
		$score -= min( 25, 5 * (int) ( $metrics['open_reports'] ?? 0 ) );
		$labels = RSV_Helpers::json_decode( $reel['safety_labels_json'] ?? '[]' );
		if ( in_array( 'urgent-safety', $labels, true ) || in_array( 'minor-sensitive', $labels, true ) ) $score -= 5;
		return round( max( -100, min( 100, $score ) ), 4 );
	}
}
