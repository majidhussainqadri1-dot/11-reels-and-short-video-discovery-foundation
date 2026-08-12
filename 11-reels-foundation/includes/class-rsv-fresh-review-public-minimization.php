<?php
defined( 'ABSPATH' ) || exit;

/** Public DTO minimization found during the second fresh 20-round review. */
final class RSV_Fresh_Review_Public_Minimization {
	public static function register() {
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'filter_response' ), 110, 3 );
	}

	public static function filter_response( $response, $server, $request ) {
		unset( $server );
		if ( ! ( $response instanceof WP_REST_Response ) || 'GET' !== strtoupper( $request->get_method() ) ) return $response;
		if ( '/rsv/v1/future30/learning-paths' !== $request->get_route() ) return $response;
		$data = $response->get_data();
		if ( ! is_array( $data ) ) return $response;
		$safe = array();
		foreach ( $data as $row ) {
			if ( ! is_array( $row ) ) continue;
			unset( $row['owner_id'], $row['reel_id'], $row['id'] );
			$payload = is_array( $row['payload'] ?? null ) ? $row['payload'] : array();
			$steps = array();
			foreach ( (array) ( $payload['steps'] ?? array() ) as $step ) {
				if ( ! is_array( $step ) ) continue;
				$ref = RSV_Helpers::text( $step['file05_ref'] ?? '', 255 );
				if ( ! $ref || true !== apply_filters( 'rsv_future30_file05_public_ref_valid', false, $ref ) ) continue;
				$level = RSV_Helpers::enum( $step['level'] ?? '', array( 'beginner','intermediate','advanced' ), '' );
				$steps[] = array( 'file05_ref'=>$ref, 'level'=>$level );
			}
			if ( ! $steps ) continue;
			$course_refs = array();
			foreach ( (array) ( $payload['file05_course_refs'] ?? array() ) as $ref ) {
				$ref = RSV_Helpers::text( $ref, 255 );
				if ( $ref && true === apply_filters( 'rsv_future30_file05_public_ref_valid', false, $ref ) ) $course_refs[] = $ref;
			}
			$row['payload'] = array(
				'level' => RSV_Helpers::enum( $payload['level'] ?? '', array( 'beginner','intermediate','advanced' ), 'beginner' ),
				'description' => RSV_Helpers::textarea( $payload['description'] ?? '', 2000 ),
				'steps' => array_slice( $steps, 0, 100 ),
				'file05_course_refs' => array_values( array_unique( $course_refs ) ),
			);
			$safe[] = $row;
		}
		$response->set_data( $safe );
		return $response;
	}
}

RSV_Fresh_Review_Public_Minimization::register();
