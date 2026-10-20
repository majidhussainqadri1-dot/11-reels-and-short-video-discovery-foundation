<?php
defined( 'ABSPATH' ) || exit;

trait RSV_Future30_User_Features_Trait {
	/** Grade against server-side answers without PHP array-to-string coercion. */
	private static function quiz_answer_matches( $question, $answer ) {
		if ( ! is_array( $question ) || ! array_key_exists( 'correct', $question ) ) return false;
		$type = sanitize_key( $question['type'] ?? 'single-choice' );
		if ( 'true-false' === $type ) {
			$normalize = static function ( $value ) {
				if ( is_bool( $value ) ) return $value ? 'true' : 'false';
				if ( ! is_scalar( $value ) ) return '';
				$value = strtolower( trim( (string) $value ) );
				if ( in_array( $value, array( 'true', '1' ), true ) ) return 'true';
				if ( in_array( $value, array( 'false', '0' ), true ) ) return 'false';
				return '';
			};
			$expected = $normalize( $question['correct'] );
			return '' !== $expected && $expected === $normalize( $answer );
		}
		if ( ! in_array( $type, array( 'single-choice', 'multiple-choice' ), true ) ) return false;
		$expected = (array) $question['correct'];
		$given = is_array( $answer ) ? $answer : array( $answer );
		if ( ! $expected || ! $given || ( 'single-choice' === $type && ( 1 !== count( $expected ) || 1 !== count( $given ) ) ) ) return false;
		$normalize = static function ( $values ) {
			$out = array();
			foreach ( $values as $value ) {
				if ( ! is_scalar( $value ) ) return array();
				$value = trim( (string) $value );
				if ( '' === $value ) return array();
				$out[] = $value;
			}
			return count( $out ) === count( array_unique( $out ) ) ? $out : array();
		};
		$expected = $normalize( $expected );
		$given = $normalize( $given );
		if ( ! $expected || count( $expected ) !== count( $given ) ) return false;
		sort( $expected, SORT_STRING );
		sort( $given, SORT_STRING );
		return $expected === $given;
	}

	public function quiz_attempt( $request ) {
		RSV_Helpers::no_cache_private();
		$rate = RSV_Security::rate_limit( 'future30_quiz_attempt', 60, HOUR_IN_SECONDS );
		if ( is_wp_error( $rate ) ) return $rate;
		$reel = self::reel( $request['id'] );
		if ( is_wp_error( $reel ) || ! RSV_Security::can_view_reel( $reel ) )
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		$p = (array) $request->get_json_params();
		$answers = (array) ( $p['answers'] ?? array() );
		$quiz = self::object_rows( 'F11-FUT-019', $reel['id'], 0, 1 );
		if ( ! $quiz ) return RSV_Helpers::error( 'rsv_quiz_unavailable', __( 'No quiz is available for this Reel.', RSV_TEXT_DOMAIN ), 404 );
		$questions = (array) ( $quiz[0]['payload']['questions'] ?? array() );
		$total = count( $questions );
		if ( $total < 1 || $total > 5 ) return RSV_Helpers::error( 'rsv_quiz_invalid', __( 'The quiz is not in a valid reviewable state.', RSV_TEXT_DOMAIN ), 422 );
		$score = 0;
		foreach ( $questions as $i => $question ) {
			if ( array_key_exists( $i, $answers ) && self::quiz_answer_matches( $question, $answers[ $i ] ) ) ++$score;
		}
		$user_id = get_current_user_id();
		$result = RSV_DB::transaction( static function () use ( $reel, $score, $total, $user_id ) {
			$state = self::state_put( 'F11-FUT-019', $reel['public_id'], 'quiz_attempt', array( 'score'=>$score, 'total'=>$total, 'completed_at'=>RSV_Helpers::now() ) );
			if ( is_wp_error( $state ) ) return $state;
			if ( ! RSV_Helpers::audit( 'reel', absint( $reel['id'] ), 'quiz_attempt', '', 'complete', 'F11-FUT-019', array( 'learning_only'=>true ), $user_id ) ||
				! RSV_Helpers::outbox( 'ReelLearningQuizAttempted', 'reel', absint( $reel['id'] ), array( 'reel_public_id'=>$reel['public_id'], 'user_ref'=>RSV_Helpers::opaque_user_ref( $user_id, 'future30-quiz' ), 'learning_only'=>true ) ) )
				return RSV_Helpers::error( 'rsv_quiz_evidence_failed', __( 'Quiz progress could not be committed with audit evidence.', RSV_TEXT_DOMAIN ), 500 );
			return array( 'score'=>$score, 'total'=>$total, 'learning_only'=>true, 'ranking_effect'=>false );
		} );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	/** Saved references are not proof that a Reel is still public. */
	private static function current_study_collections( $state ) {
		$state = is_array( $state ) ? $state : array();
		$payload = (array) ( $state['payload'] ?? array() );
		$collections = (array) ( $payload['collections'] ?? array() );
		$safe = array();
		foreach ( $collections as $key => $collection ) {
			if ( ! is_array( $collection ) || ! is_scalar( $key ) ) continue;
			$key = sanitize_key( (string) $key );
			$name = RSV_Helpers::text( $collection['name'] ?? '', 80 );
			if ( ! $key || ! $name ) continue;
			$refs = (array) ( $collection['reels'] ?? array() );
			$valid = array();
			foreach ( array_slice( $refs, 0, 500 ) as $ref ) {
				if ( ! is_scalar( $ref ) ) continue;
				$ref = RSV_Helpers::text( $ref, 255 );
				if ( $ref && ! in_array( $ref, $valid, true ) &&
					self::canonical_ref_public_valid( 'File 11', $ref, 'study-collection-current-read' ) ) $valid[] = $ref;
			}
			$safe[ $key ] = array(
				'name' => $name,
				'reels' => $valid,
				'private' => true,
				'updated_at' => RSV_Helpers::text( $collection['updated_at'] ?? '', 40 ),
			);
		}
		return array(
			'payload' => array( 'collections' => $safe ),
			'version' => absint( $state['version'] ?? 0 ),
			'updated_at' => RSV_Helpers::text( $state['updated_at'] ?? '', 40 ),
		);
	}
	public function collections() {
		RSV_Helpers::no_cache_private();
		$stored = self::state_get( 'F11-FUT-020', 'user', 'collections',
			array( 'payload' => array( 'collections' => array() ), 'version' => 0 ) );
		return rest_ensure_response( self::current_study_collections( $stored ) );
	}
	public function collection_write( $request ) { RSV_Helpers::no_cache_private();$rate=RSV_Security::rate_limit('future30_collections',60,HOUR_IN_SECONDS);if(is_wp_error($rate))return $rate;$p=(array)$request->get_json_params();$current=self::state_get('F11-FUT-020','user','collections',array('payload'=>array('collections'=>array()),'version'=>0));$collections=(array)($current['payload']['collections']??array());$name=RSV_Helpers::text($p['name']??'',80);if(!$name)return RSV_Helpers::error('rsv_collection_name_required',__('Collection name is required.',RSV_TEXT_DOMAIN),422);$key=sanitize_key($p['key']??$name);$refs=array_values(array_unique(array_map('sanitize_text_field',(array)($p['reels']??($collections[$key]['reels']??array())))));if(count($refs)>500)return RSV_Helpers::error('rsv_collection_too_large',__('A study collection may contain at most 500 Reels.',RSV_TEXT_DOMAIN),422);foreach($refs as $ref)if(!self::canonical_ref_public_valid('File 11',$ref,'study-collection'))return RSV_Helpers::error('rsv_collection_reel_invalid',__('Every study-collection item must be a current public Reel.',RSV_TEXT_DOMAIN),422);$collections[$key]=array('name'=>$name,'reels'=>$refs,'private'=>true,'updated_at'=>RSV_Helpers::now());return self::state_put('F11-FUT-020','user','collections',array('collections'=>$collections)); }
	public function notes( $request ) { RSV_Helpers::no_cache_private();$reel=self::reel($request['id']);if(is_wp_error($reel)||!RSV_Security::can_view_reel($reel))return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);return rest_ensure_response(self::state_get('F11-FUT-021',$reel['public_id'],'notes',array('payload'=>array('notes'=>array()),'version'=>0))); }
	public function note_write( $request ) { RSV_Helpers::no_cache_private();$reel=self::reel($request['id']);if(is_wp_error($reel)||!RSV_Security::can_view_reel($reel))return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);$rate=RSV_Security::rate_limit('future30_notes',120,HOUR_IN_SECONDS);if(is_wp_error($rate))return $rate;$p=(array)$request->get_json_params();$idem=$request->get_header('Idempotency-Key');$scope='future30_note_'.$reel['public_id'];$begin=RSV_Security::idempotency_begin($scope,$idem,$p);if(is_wp_error($begin))return $begin;if(!empty($begin['replay']))return rest_ensure_response($begin['response']);$time=self::validate_time_range($reel,$p['second']??0,0);if(is_wp_error($time)){RSV_Security::idempotency_fail($scope,$idem);return $time;}$text=RSV_Helpers::textarea($p['text']??'',4000);if(!$text){RSV_Security::idempotency_fail($scope,$idem);return RSV_Helpers::error('rsv_note_text_required',__('Note text is required.',RSV_TEXT_DOMAIN),422);}$current=self::state_get('F11-FUT-021',$reel['public_id'],'notes',array('payload'=>array('notes'=>array()),'version'=>0));$notes=(array)($current['payload']['notes']??array());$notes[]=array('second'=>$time['start'],'text'=>$text,'created_at'=>RSV_Helpers::now());$notes=array_slice($notes,-200);return self::idempotent_finish_result($scope,$idem,static function() use($reel,$notes){return self::state_put('F11-FUT-021',$reel['public_id'],'notes',array('notes'=>$notes,'private'=>true));},array('entity_type'=>'reel','entity_id'=>absint($reel['id']),'action'=>'private_note_update','reason'=>'F11-FUT-021','event'=>'ReelPrivateNoteUpdated','event_data'=>array('reel_public_id'=>$reel['public_id'],'feature_id'=>'F11-FUT-021'))); }

	public function ai_context( $request ) { RSV_Helpers::no_cache_private();$reel=self::reel($request['id']);if(is_wp_error($reel))return $reel;if(!RSV_Security::can_view_reel($reel))return RSV_Helpers::error('rsv_not_found',__('Reel not found.',RSV_TEXT_DOMAIN),404);$context=array('reel'=>RSV_Repository::public_dto($reel),'citations'=>self::edge_rows('F11-FUT-004',$reel['id'],100),'evidence'=>self::object_rows('F11-FUT-005',$reel['id'],0,20),'knowledge_card'=>self::object_rows('F11-FUT-018',$reel['id'],0,1),'chapters'=>self::edge_rows('F11-FUT-017',$reel['id'],100),'medical_authority'=>'education-only-no-diagnosis-prescription-dose-or-emergency-replacement');$context=apply_filters('rsv_future30_ai_grounded_context',$context,$reel,get_current_user_id());return rest_ensure_response(array('owner'=>'File 16','context'=>$context,'execute_ai'=>false)); }
	/** Source-verified privacy-thresholded File 26 aggregates only. */
	private static function public_search_opportunity_rows( $data, $limit ) {
		$items = array();
		foreach ( array_slice( (array) $data, 0, min( 50, max( 1, absint( $limit ) ) ) ) as $row ) {
			if ( ! is_array( $row ) || ! isset( $row['aggregate_count'] )
				|| ! is_scalar( $row['aggregate_count'] ) || ! is_numeric( $row['aggregate_count'] )
				|| (float) $row['aggregate_count'] < 5 ) continue;
			$count = absint( $row['aggregate_count'] );
			if ( $count < 5 ) continue;
			$topic = RSV_Helpers::text( $row['topic'] ?? '', 80 );
			$label = RSV_Helpers::text( $row['query_label'] ?? '', 160 );
			if ( ! $topic || ! $label ) continue;
			$ref = RSV_Helpers::text( $row['source_ref'] ?? '', 255 );
			if ( $ref && true !== apply_filters( 'rsv_future30_public_ref_valid', false, 'File 26', $ref, 'search-opportunity-current' ) ) continue;
			$items[] = array(
				'topic'=>$topic, 'query_label'=>$label,
				'language'=>RSV_Helpers::text( $row['language'] ?? '', 20 ),
				'region'=>RSV_Helpers::text( $row['region'] ?? '', 80 ),
				'demand_band'=>RSV_Helpers::text( $row['demand_band'] ?? '', 30 ),
				'content_gap'=>RSV_Helpers::text( $row['content_gap'] ?? '', 30 ),
				'freshness'=>RSV_Helpers::text( $row['freshness'] ?? '', 40 ),
				'source_ref'=>$ref, 'aggregate_count'=>$count,
			);
		}
		return $items;
	}
	public function search_opportunities( $request ) {
		RSV_Helpers::no_cache_private();
		$limit = min( 50, max( 1, absint( $request->get_param( 'limit' ) ?: 20 ) ) );
		$args = array( 'user_id'=>get_current_user_id(), 'limit'=>$limit, 'privacy'=>'aggregate-only', 'minimum_count'=>5, 'ranking_advantage'=>'none' );
		$data = apply_filters( 'rsv_future30_search_opportunities', null, $args );
		if ( ! is_array( $data ) ) return RSV_Helpers::error( 'rsv_search_intelligence_unavailable', __( 'Search opportunity intelligence is unavailable.', RSV_TEXT_DOMAIN ), 503 );
		return rest_ensure_response( array( 'owner'=>'File 26', 'privacy'=>'aggregate-only', 'items'=>self::public_search_opportunity_rows( $data, $limit ) ) );
	}
	public function why( $request ) {
		$reel = self::reel( $request['id'] );
		if ( is_wp_error( $reel ) || ! RSV_Security::can_view_reel( $reel ) )
			return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		$native = RSV_Integrations::recommendation_explanation( $reel, sanitize_key( $request->get_param( 'sort' ) ?: 'recommended' ) );
		$provider = apply_filters( 'rsv_future30_global_recommendation_reasons', array(), $reel, get_current_user_id() );
		$reasons = array();
		foreach ( array_merge( (array) $native, is_array( $provider ) ? $provider : array() ) as $reason ) {
			// A provider may return a malformed nested value; never forward unbounded internal data.
			if ( ! is_scalar( $reason ) ) continue;
			$reason = RSV_Helpers::text( $reason, 280 );
			if ( '' !== $reason && ! in_array( $reason, $reasons, true ) ) $reasons[] = $reason;
			if ( count( $reasons ) >= 12 ) break;
		}
		return rest_ensure_response( array(
			'reasons'=>$reasons,
			'editable_controls'=>array( 'more-like-this','less-like-this','not-interested','mute-topic','mute-doctor','keyword-filters','reset-recommendations','explore-more' ),
			'payment_or_donation_reason'=>false,
		) );
	}

	private static function default_feed_preferences(){return array('muted_topics'=>array(),'muted_doctors'=>array(),'keywords'=>array(),'topic_affinity'=>array(),'diversity'=>50,'explore_more'=>false,'autoplay'=>false,'history_paused'=>false);}
	public function feed_preferences(){RSV_Helpers::no_cache_private();return rest_ensure_response(self::state_get('F11-FUT-025','user','feed_preferences',array('payload'=>self::default_feed_preferences(),'version'=>0)));}
	public function feed_preferences_write($request){RSV_Helpers::no_cache_private();$rate=RSV_Security::rate_limit('future30_feed_preferences',60,HOUR_IN_SECONDS);if(is_wp_error($rate))return $rate;$p=(array)$request->get_json_params();$current=self::state_get('F11-FUT-025','user','feed_preferences',array('payload'=>self::default_feed_preferences(),'version'=>0));$data=array_merge(self::default_feed_preferences(),(array)($current['payload']??array()));foreach(array('muted_topics','muted_doctors','keywords','topic_affinity') as $key)if(array_key_exists($key,$p))$data[$key]=array_slice(array_values(array_unique(array_map('sanitize_text_field',(array)$p[$key]))),0,100);if(array_key_exists('diversity',$p))$data['diversity']=min(100,max(0,absint($p['diversity'])));foreach(array('explore_more','autoplay','history_paused') as $key)if(array_key_exists($key,$p))$data[$key]=!empty($p[$key]);$saved=self::state_put('F11-FUT-025','user','feed_preferences',$data);if(!is_wp_error($saved))do_action('rsv_future30_feed_preferences_changed',get_current_user_id(),$data);return $saved;}

	public static function feed_allows_row( $row ) {
		if ( ! is_user_logged_in() || ! is_array( $row ) ) return true;
		$state = self::state_get( 'F11-FUT-025', 'user', 'feed_preferences', array( 'payload'=>self::default_feed_preferences(), 'version'=>0 ) );
		$prefs = array_merge( self::default_feed_preferences(), (array) ( $state['payload'] ?? array() ) );
		$topic = sanitize_key( (string) ( $row['topic'] ?? '' ) );
		if ( $topic && in_array( $topic, array_map( 'sanitize_key', (array) $prefs['muted_topics'] ), true ) ) return false;
		if ( in_array( (string) absint( $row['owner_id'] ?? 0 ), array_map( 'strval', (array) $prefs['muted_doctors'] ), true ) ) return false;
		$haystack = strtolower( wp_strip_all_tags( (string) ( $row['title'] ?? '' ) . ' ' . (string) ( $row['caption'] ?? '' ) ) );
		foreach ( (array) $prefs['keywords'] as $keyword ) {
			$keyword = strtolower( trim( (string) $keyword ) );
			if ( '' !== $keyword && false !== strpos( $haystack, $keyword ) ) return false;
		}
		return true;
	}

}
