<?php
defined( 'ABSPATH' ) || exit;

/** Hardening discovered by the third fresh 20-round File 11 review cycle. */
final class RSV_Third_Review_Hardening {
	const MIN_CREATOR_RESEARCH_SAMPLE = 5;

	public static function register() {
		add_filter( 'rsv_future30_creator_research_aggregates', array( __CLASS__, 'creator_research_aggregates' ), PHP_INT_MAX, 3 );
	}

	/**
	 * Fail closed unless every exposed aggregate carries verifiable sample-size proof.
	 * Supported provider shapes:
	 * - metric => array( 'value' => numeric, 'aggregate_count' => >= 5 )
	 * - metric => numeric plus _sample_sizes[metric] => >= 5
	 */
	public static function creator_research_aggregates( $data, $user_id, $request ) {
		unset( $user_id );
		if ( ! is_array( $data ) ) return array();
		$required = max( self::MIN_CREATOR_RESEARCH_SAMPLE, absint( $request['minimum_viewers'] ?? 0 ) );
		$allowed = array_map( 'sanitize_key', (array) ( $request['allowed_metrics'] ?? array() ) );
		$sample_sizes = is_array( $data['_sample_sizes'] ?? null ) ? $data['_sample_sizes'] : array();
		$safe = array();
		foreach ( $allowed as $metric ) {
			if ( ! array_key_exists( $metric, $data ) ) continue;
			$value = $data[ $metric ];
			$count = absint( $sample_sizes[ $metric ] ?? 0 );
			if ( is_array( $value ) ) {
				$count = absint( $value['aggregate_count'] ?? $value['sample_size'] ?? $count );
				$value = $value['value'] ?? null;
			}
			if ( $count < $required || ! is_numeric( $value ) ) continue;
			$safe[ $metric ] = 0 + $value;
		}
		return $safe;
	}
}

RSV_Third_Review_Hardening::register();
