<?php
defined( 'ABSPATH' ) || exit;

trait RSV_Top20_Privacy_Integration_Trait {
	public static function privacy_exporters( $exporters ) {
		$exporters['rsv-top20'] = array(
			'exporter_friendly_name' => __( 'Reel source context, Stories, Highlights, responses and well-being preferences', RSV_TEXT_DOMAIN ),
			'callback'               => array( __CLASS__, 'privacy_export' ),
		);
		return $exporters;
	}

	public static function privacy_erasers( $erasers ) {
		$erasers['rsv-top20'] = array(
			'eraser_friendly_name' => __( 'Private Reel well-being preferences and unpublished temporal items', RSV_TEXT_DOMAIN ),
			'callback'             => array( __CLASS__, 'privacy_erase' ),
		);
		return $erasers;
	}

	private static function export_item( $group_id, $group_label, $item_id, $fields ) {
		$data = array();
		foreach ( $fields as $name => $value ) $data[] = array( 'name' => $name, 'value' => is_scalar( $value ) || null === $value ? (string) $value : wp_json_encode( $value ) );
		return array( 'group_id' => $group_id, 'group_label' => $group_label, 'item_id' => $item_id, 'data' => $data );
	}

	public static function privacy_export( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		if ( ! $user ) return array( 'data' => array(), 'done' => true );
		$page   = max( 1, absint( $page ) );
		$limit  = 50;
		$offset = ( $page - 1 ) * $limit;
		$data   = array();
		if ( 1 === $page ) {
			$data[] = self::export_item(
				'rsv-wellbeing',
				__( 'Reel well-being preferences', RSV_TEXT_DOMAIN ),
				'preferences-' . $user->ID,
				array( __( 'Preferences', RSV_TEXT_DOMAIN ) => self::preferences( $user->ID ) )
			);
		}

		global $wpdb;
		$stories = (array) $wpdb->get_results( $wpdb->prepare(
			'SELECT public_id,title,body,status,visibility,destination_url,expires_at,published_at,created_at,updated_at FROM ' . RSV_Helpers::table( 'stories' ) . ' WHERE owner_id=%d ORDER BY id ASC LIMIT %d OFFSET %d',
			$user->ID, $limit, $offset
		), ARRAY_A );
		foreach ( $stories as $row ) {
			$data[] = self::export_item( 'rsv-stories', __( 'Reel Stories', RSV_TEXT_DOMAIN ), $row['public_id'], array(
				__( 'Title', RSV_TEXT_DOMAIN ) => $row['title'], __( 'Body', RSV_TEXT_DOMAIN ) => $row['body'], __( 'Status', RSV_TEXT_DOMAIN ) => $row['status'],
				__( 'Visibility', RSV_TEXT_DOMAIN ) => $row['visibility'], __( 'Destination', RSV_TEXT_DOMAIN ) => $row['destination_url'], __( 'Expires', RSV_TEXT_DOMAIN ) => $row['expires_at'],
				__( 'Published', RSV_TEXT_DOMAIN ) => $row['published_at'], __( 'Created', RSV_TEXT_DOMAIN ) => $row['created_at'], __( 'Updated', RSV_TEXT_DOMAIN ) => $row['updated_at'],
			) );
		}

		$responses = (array) $wpdb->get_results( $wpdb->prepare(
			'SELECT public_id,attribution_text,patient_reuse_consent,status,published_at,created_at,updated_at FROM ' . RSV_Helpers::table( 'responses' ) . ' WHERE owner_id=%d ORDER BY id ASC LIMIT %d OFFSET %d',
			$user->ID, $limit, $offset
		), ARRAY_A );
		foreach ( $responses as $row ) {
			$data[] = self::export_item( 'rsv-responses', __( 'Attributed Reel responses', RSV_TEXT_DOMAIN ), $row['public_id'], array(
				__( 'Attribution', RSV_TEXT_DOMAIN ) => $row['attribution_text'], __( 'Patient reuse consent declared', RSV_TEXT_DOMAIN ) => ! empty( $row['patient_reuse_consent'] ) ? 'yes' : 'no',
				__( 'Status', RSV_TEXT_DOMAIN ) => $row['status'], __( 'Published', RSV_TEXT_DOMAIN ) => $row['published_at'], __( 'Created', RSV_TEXT_DOMAIN ) => $row['created_at'], __( 'Updated', RSV_TEXT_DOMAIN ) => $row['updated_at'],
			) );
		}

		$highlights = (array) $wpdb->get_results( $wpdb->prepare(
			'SELECT public_id,title,slug,status,created_at,updated_at FROM ' . RSV_Helpers::table( 'highlights' ) . ' WHERE owner_id=%d ORDER BY id ASC LIMIT %d OFFSET %d',
			$user->ID, $limit, $offset
		), ARRAY_A );
		foreach ( $highlights as $row ) {
			$data[] = self::export_item( 'rsv-highlights', __( 'Reel Highlights', RSV_TEXT_DOMAIN ), $row['public_id'], array(
				__( 'Title', RSV_TEXT_DOMAIN ) => $row['title'], __( 'Slug', RSV_TEXT_DOMAIN ) => $row['slug'], __( 'Status', RSV_TEXT_DOMAIN ) => $row['status'],
				__( 'Created', RSV_TEXT_DOMAIN ) => $row['created_at'], __( 'Updated', RSV_TEXT_DOMAIN ) => $row['updated_at'],
			) );
		}

		$contexts = (array) $wpdb->get_results( $wpdb->prepare(
			'SELECT r.public_id,c.source_title,c.source_url,c.transcript_url,c.safety_summary,c.allow_response,c.patient_reuse_policy,c.updated_at FROM ' . RSV_Helpers::table( 'reel_context' ) . ' c JOIN ' . RSV_Helpers::table( 'reels' ) . ' r ON r.id=c.reel_id WHERE r.owner_id=%d ORDER BY c.reel_id ASC LIMIT %d OFFSET %d',
			$user->ID, $limit, $offset
		), ARRAY_A );
		foreach ( $contexts as $row ) {
			$data[] = self::export_item( 'rsv-source-context', __( 'Reel source and safety context', RSV_TEXT_DOMAIN ), 'context-' . $row['public_id'], array(
				__( 'Reel', RSV_TEXT_DOMAIN ) => $row['public_id'], __( 'Source label', RSV_TEXT_DOMAIN ) => $row['source_title'], __( 'Source URL', RSV_TEXT_DOMAIN ) => $row['source_url'],
				__( 'Transcript URL', RSV_TEXT_DOMAIN ) => $row['transcript_url'], __( 'Safety summary', RSV_TEXT_DOMAIN ) => $row['safety_summary'],
				__( 'Responses allowed', RSV_TEXT_DOMAIN ) => ! empty( $row['allow_response'] ) ? 'yes' : 'no', __( 'Patient reuse policy', RSV_TEXT_DOMAIN ) => $row['patient_reuse_policy'], __( 'Updated', RSV_TEXT_DOMAIN ) => $row['updated_at'],
			) );
		}

		$done = count( $stories ) < $limit && count( $responses ) < $limit && count( $highlights ) < $limit && count( $contexts ) < $limit;
		return array( 'data' => $data, 'done' => $done );
	}

	public static function privacy_erase( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		if ( ! $user ) return array( 'items_removed' => false, 'items_retained' => false, 'messages' => array(), 'done' => true );
		global $wpdb;
		$batch = 100;
		$result = RSV_DB::transaction(
			static function () use ( $wpdb, $user, $batch, $page ) {
				$story_rows = (array) $wpdb->get_results( $wpdb->prepare( "SELECT id,version FROM " . RSV_Helpers::table( 'stories' ) . " WHERE owner_id=%d AND status IN ('draft','review') ORDER BY id ASC LIMIT %d", $user->ID, $batch ), ARRAY_A );
				$response_rows = (array) $wpdb->get_results( $wpdb->prepare( "SELECT id,version FROM " . RSV_Helpers::table( 'responses' ) . " WHERE owner_id=%d AND status='review' ORDER BY id ASC LIMIT %d", $user->ID, $batch ), ARRAY_A );
				$removed = 0;
				if ( 1 === max( 1, absint( $page ) ) ) {
					$deleted = $wpdb->delete( RSV_Helpers::table( 'preferences' ), array( 'user_id' => $user->ID ), array( '%d' ) );
					if ( false === $deleted ) return RSV_Helpers::error( 'rsv_privacy_preferences_erase_failed', __( 'Private Reel preferences could not be erased safely.', RSV_TEXT_DOMAIN ), 500 );
					$removed += (int) $deleted;
				}
				foreach ( $story_rows as $row ) {
					$changed = $wpdb->update( RSV_Helpers::table( 'stories' ), array( 'status' => 'removed', 'body' => '', 'destination_url' => '', 'version' => absint( $row['version'] ) + 1, 'updated_at' => RSV_Helpers::now() ), array( 'id' => absint( $row['id'] ), 'owner_id' => $user->ID, 'version' => absint( $row['version'] ) ), array( '%s','%s','%s','%d','%s' ), array( '%d','%d','%d' ) );
					if ( false === $changed ) return RSV_Helpers::error( 'rsv_privacy_story_erase_failed', __( 'An unpublished Story could not be minimized safely.', RSV_TEXT_DOMAIN ), 500 );
					$removed += (int) $changed;
				}
				foreach ( $response_rows as $row ) {
					$changed = $wpdb->update( RSV_Helpers::table( 'responses' ), array( 'status' => 'removed', 'attribution_text' => 'Removed unpublished response', 'version' => absint( $row['version'] ) + 1, 'updated_at' => RSV_Helpers::now() ), array( 'id' => absint( $row['id'] ), 'owner_id' => $user->ID, 'version' => absint( $row['version'] ) ), array( '%s','%s','%d','%s' ), array( '%d','%d','%d' ) );
					if ( false === $changed ) return RSV_Helpers::error( 'rsv_privacy_response_erase_failed', __( 'An unpublished response could not be minimized safely.', RSV_TEXT_DOMAIN ), 500 );
					$removed += (int) $changed;
				}
				$retained = (int) $wpdb->get_var( $wpdb->prepare(
					"SELECT (SELECT COUNT(*) FROM " . RSV_Helpers::table( 'stories' ) . " WHERE owner_id=%d AND status IN ('published','expired')) + (SELECT COUNT(*) FROM " . RSV_Helpers::table( 'responses' ) . " WHERE owner_id=%d AND status='published') + (SELECT COUNT(*) FROM " . RSV_Helpers::table( 'highlights' ) . " WHERE owner_id=%d AND status='published')",
					$user->ID, $user->ID, $user->ID
				) );
				if ( $removed > 0 && ! RSV_Helpers::audit( 'privacy_erasure', $user->ID, 'erase_private_reel_data', '', '', 'Preferences and unpublished temporal items removed or minimized', array( 'removed_count' => $removed, 'retained_governed_count' => $retained ), 0 ) ) {
					return RSV_Helpers::error( 'rsv_privacy_erasure_evidence_failed', __( 'Private Reel data could not be erased with complete audit evidence.', RSV_TEXT_DOMAIN ), 500 );
				}
				return array( 'removed' => $removed, 'retained' => $retained, 'done' => count( $story_rows ) < $batch && count( $response_rows ) < $batch );
			}
		);
		if ( is_wp_error( $result ) ) return array( 'items_removed' => false, 'items_retained' => false, 'messages' => array( $result->get_error_message() ), 'done' => false );
		$messages = array();
		if ( $result['retained'] > 0 ) $messages[] = __( 'Published Stories, Highlights and responses are retained for governed correction or takedown rather than silently erased.', RSV_TEXT_DOMAIN );
		return array( 'items_removed' => $result['removed'] > 0, 'items_retained' => $result['retained'] > 0, 'messages' => $messages, 'done' => (bool) $result['done'] );
	}

	public static function provider_manifest( $manifest ) {
		$manifest = is_array( $manifest ) ? $manifest : array();
		if ( isset( $manifest['provider_id'] ) ) {
			$manifest['entity_types'] = array_values( array_unique( array_merge( (array) ( $manifest['entity_types'] ?? array() ), array( 'reel','story','highlight','reel-response' ) ) ) );
			$manifest['capabilities'] = array_values( array_unique( array_merge( (array) ( $manifest['capabilities'] ?? array() ), array( 'stories-status','highlights','attributed-responses','source-safety','wellbeing','youth-safe','value-insights' ) ) ) );
			$manifest['provider_version'] = 3;
			return $manifest;
		}
		if ( isset( $manifest['file11-reels'] ) ) $manifest['file11-reels'] = self::provider_manifest( $manifest['file11-reels'] );
		return $manifest;
	}

	public static function search_documents( $documents, $query, $args = array() ) {
		$documents = is_array( $documents ) ? $documents : array();
		$data = self::public_stories( min( 20, max( 1, absint( $args['limit'] ?? 10 ) ) ) );
		if ( is_wp_error( $data ) ) return $documents;
		$q = function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $query ) : strtolower( (string) $query );
		foreach ( $data['items'] as $s ) {
			$hay = function_exists( 'mb_strtolower' ) ? mb_strtolower( $s['title'] . ' ' . $s['body'] ) : strtolower( $s['title'] . ' ' . $s['body'] );
			if ( $q && false === ( function_exists( 'mb_strpos' ) ? mb_strpos( $hay, $q ) : strpos( $hay, $q ) ) ) continue;
			$documents[] = array( 'provider_id'=>'file11-reels','provider_version'=>3,'native_id'=>$s['id'],'entity_type'=>'story','title'=>$s['title'],'excerpt'=>wp_trim_words($s['body'],24),'canonical_url'=>$s['url'],'published_at'=>$s['published_at'],'visibility'=>'public','safety'=>array('temporal','consent-current','source-reel-eligible') );
		}
		return $documents;
	}

	public static function author_items( $items, $author_id, $args = array() ) {
		$items = is_array( $items ) ? $items : array();
		foreach ( self::public_highlights( absint( $author_id ), min( 20, max( 1, absint( $args['limit'] ?? 10 ) ) ) ) as $h ) $items[] = array( 'provider_id'=>'file11-reels','provider_version'=>3,'native_object_type'=>'highlight','native_id'=>$h['id'],'author_id'=>absint($author_id),'title'=>$h['title'],'canonical_url'=>$h['url'],'content_type'=>'highlight','visibility_state'=>'public','available_actions'=>array('open'=>$h['url']) );
		return $items;
	}

	public static function story_cards( $cards, $args = array() ) {
		$cards = is_array( $cards ) ? $cards : array();
		$data = self::public_stories( min( 12, max( 1, absint( $args['limit'] ?? 6 ) ) ) );
		if ( is_wp_error( $data ) ) return $cards;
		foreach ( $data['items'] as $s ) $cards[] = array( 'provider_id'=>'file11-reels','provider_version'=>3,'native_id'=>$s['id'],'title'=>$s['title'],'excerpt'=>wp_trim_words($s['body'],18),'canonical_url'=>$s['url'],'expires_at'=>$s['expires_at'],'story_type'=>$s['type'] );
		return $cards;
	}

	public static function highlight_collections( $collections, $owner_id, $args = array() ) {
		$collections = is_array( $collections ) ? $collections : array();
		return array_merge( $collections, self::public_highlights( absint( $owner_id ), min( 30, max( 1, absint( $args['limit'] ?? 20 ) ) ) ) );
	}

	public static function composer_contracts( $contracts ) {
		$contracts = is_array( $contracts ) ? $contracts : array();
		$contracts['file11-reel'] = array( 'owner'=>'File 11','media_owner'=>'File 10','contract_version'=>RSV_CONTRACT_VERSION,'fields'=>array('video_id','title','topic','language','caption','cover_id','disclosure','visibility','source_title','source_url','transcript_url','safety_summary','allow_response','patient_reuse_policy'),'duration'=>array('min'=>60,'max'=>600,'authority'=>'File 10'),'write'=>'versioned File 11 command only' );
		$contracts['file11-story-status'] = array( 'owner'=>'File 11','source'=>'existing eligible Reel','max_hours'=>24,'types'=>RSV_Contracts::STORY_TYPES,'media_owner'=>'File 10','idempotency'=>'required' );
		$contracts['file11-response'] = array( 'owner'=>'File 11 relationship','source_and_response'=>'existing eligible Reels','attribution'=>'required','patient_reuse'=>'verified consent when applicable','idempotency'=>'required' );
		return $contracts;
	}
}
