<?php
defined( 'ABSPATH' ) || exit;

/** Additional fail-closed guards around Future30 write identities and payload integrity. */
final class RSV_Future30_Write_Integrity {
	public static function register() {
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'pre_dispatch' ), 15, 3 );
	}

	public static function pre_dispatch( $result, $server, $request ) {
		unset( $server );
		if ( null !== $result || 'POST' !== $request->get_method() ) return $result;
		$route = $request->get_route();
		$feature = '';
		$reel_id = 0;
		$reel = null;
		if ( preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/future30/(F11-FUT-[0-9]{3})$#', $route, $m ) ) {
			$feature = $m[2];
			$reel = RSV_Repository::find( $m[1], true );
			if ( ! $reel ) return $result;
			$reel_id = absint( $reel['id'] );
			$owns = absint( $reel['owner_id'] ?? 0 ) === get_current_user_id();
			$manage = RSV_Security::can( RSV_Contracts::CAP_MANAGE, $reel, 'future30_integrity' );
			if ( ! RSV_Security::can( RSV_Contracts::CAP_PUBLISH, $reel, 'future30_integrity' ) || ( ! $owns && ! $manage ) ) return $result;
		} elseif ( '/rsv/v1/future30/series' === $route ) {
			$feature = 'F11-FUT-001';
			if ( ! RSV_Security::can( RSV_Contracts::CAP_PUBLISH, null, 'future30_integrity' ) && ! RSV_Security::can( RSV_Contracts::CAP_MANAGE, null, 'future30_integrity' ) ) return $result;
		} elseif ( '/rsv/v1/future30/learning-paths' === $route ) {
			$feature = 'F11-FUT-002';
			if ( ! RSV_Security::can( RSV_Contracts::CAP_PUBLISH, null, 'future30_integrity' ) && ! RSV_Security::can( RSV_Contracts::CAP_MANAGE, null, 'future30_integrity' ) ) return $result;
		} else {
			return $result;
		}
		$params = (array) $request->get_json_params();

		if ( 'F11-FUT-019' === $feature ) {
			$quiz_error = self::validate_quiz( (array) ( $params['questions'] ?? array() ) );
			if ( is_wp_error( $quiz_error ) ) return $quiz_error;
		}
		if ( 'F11-FUT-018' === $feature ) {
			foreach ( (array) ( $params['source_refs'] ?? array() ) as $ref ) {
				if ( ! self::external_public_ref_valid( 'File 06', $ref, 'knowledge-card-source' ) ) return RSV_Helpers::error( 'rsv_knowledge_source_invalid', __( 'Every knowledge-card source must be a current public File 06 reference.', RSV_TEXT_DOMAIN ), 422 );
			}
		}
		if ( 'F11-FUT-030' === $feature && ! empty( $params['source_ref'] ) ) {
			$source_owner = RSV_Helpers::text( $params['source_owner'] ?? 'File 06', 30 );
			if ( ! self::external_public_ref_valid( $source_owner, $params['source_ref'], 'knowledge-graph-evidence' ) ) return RSV_Helpers::error( 'rsv_graph_source_invalid', __( 'The knowledge-graph evidence source is not current and public.', RSV_TEXT_DOMAIN ), 422 );
		}

		$public_id = RSV_Helpers::text( $params['public_id'] ?? '', 80 );
		if ( ! $public_id ) return $result;
		global $wpdb;
		$table = RSV_Helpers::table( 'future_objects' );
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT feature_id,reel_id,owner_id,version FROM $table WHERE public_id=%s LIMIT 1", $public_id ), ARRAY_A );
		if ( ! $existing ) return $result;
		if ( 'F11-FUT-007' === $feature ) {
			return RSV_Helpers::error( 'rsv_version_snapshot_immutable', __( 'A historical Reel version snapshot is immutable and cannot be overwritten.', RSV_TEXT_DOMAIN ), 409 );
		}
		if ( $feature !== (string) $existing['feature_id'] || $reel_id !== absint( $existing['reel_id'] ) ) {
			return RSV_Helpers::error( 'rsv_future_public_id_conflict', __( 'This Future30 public identity belongs to a different feature or Reel and cannot be reassigned.', RSV_TEXT_DOMAIN ), 409 );
		}
		return $result;
	}

	private static function validate_quiz( $questions ) {
		if ( count( $questions ) < 1 || count( $questions ) > 5 ) return RSV_Helpers::error( 'rsv_quiz_size_invalid', __( 'A Reel quiz must contain 1 to 5 questions.', RSV_TEXT_DOMAIN ), 422 );
		foreach ( $questions as $question ) {
			if ( ! is_array( $question ) ) return RSV_Helpers::error( 'rsv_quiz_question_invalid', __( 'Every quiz question must be structured data.', RSV_TEXT_DOMAIN ), 422 );
			$type = RSV_Helpers::enum( $question['type'] ?? 'single-choice', array( 'single-choice','multiple-choice','true-false' ), '' );
			$prompt = RSV_Helpers::text( $question['prompt'] ?? '', 1200 );
			if ( ! $type || '' === trim( $prompt ) || ! array_key_exists( 'correct', $question ) ) return RSV_Helpers::error( 'rsv_quiz_question_incomplete', __( 'Each quiz question needs a supported type, prompt and private correct answer.', RSV_TEXT_DOMAIN ), 422 );
			if ( 'true-false' === $type ) {
				$correct = strtolower( trim( (string) $question['correct'] ) );
				if ( ! in_array( $correct, array( 'true','false','1','0' ), true ) ) return RSV_Helpers::error( 'rsv_quiz_answer_invalid', __( 'A true/false question must have a valid true or false answer.', RSV_TEXT_DOMAIN ), 422 );
				continue;
			}
			$options = array_values( array_map( static function( $value ) { return RSV_Helpers::text( $value, 500 ); }, (array) ( $question['options'] ?? array() ) ) );
			if ( count( $options ) < 2 || count( $options ) > 8 || count( array_unique( $options ) ) !== count( $options ) || in_array( '', $options, true ) ) return RSV_Helpers::error( 'rsv_quiz_options_invalid', __( 'Choice questions require 2 to 8 distinct non-empty options.', RSV_TEXT_DOMAIN ), 422 );
			$correct = (array) $question['correct'];
			if ( 'single-choice' === $type && 1 !== count( $correct ) ) return RSV_Helpers::error( 'rsv_quiz_answer_invalid', __( 'A single-choice question must have exactly one correct option.', RSV_TEXT_DOMAIN ), 422 );
			if ( 'multiple-choice' === $type && count( $correct ) < 1 ) return RSV_Helpers::error( 'rsv_quiz_answer_invalid', __( 'A multiple-choice question must have at least one correct option.', RSV_TEXT_DOMAIN ), 422 );
			foreach ( $correct as $answer ) if ( ! in_array( RSV_Helpers::text( $answer, 500 ), $options, true ) ) return RSV_Helpers::error( 'rsv_quiz_answer_invalid', __( 'Every correct answer must match one of the declared options.', RSV_TEXT_DOMAIN ), 422 );
		}
		return true;
	}

	private static function external_public_ref_valid( $owner, $ref, $purpose ) {
		$owner = RSV_Helpers::text( $owner, 30 );
		$ref = RSV_Helpers::text( $ref, 255 );
		return $owner && $ref && true === apply_filters( 'rsv_future30_public_ref_valid', false, $owner, $ref, sanitize_key( $purpose ) );
	}
}

RSV_Future30_Write_Integrity::register();
