<?php
defined( 'ABSPATH' ) || exit;

trait RSV_Future30_Storage_Trait {
	private static function feature( $id ) {
		$id = strtoupper( sanitize_text_field( (string) $id ) );
		$defs = self::definitions();
		return isset( $defs[ $id ] ) ? array( $id, $defs[ $id ] ) : array( '', null );
	}

	private static function reel( $public_id, $require_owner = false ) {
		$reel = RSV_Repository::find( sanitize_text_field( (string) $public_id ), true );
		if ( ! $reel ) return RSV_Helpers::error( 'rsv_not_found', __( 'Reel not found.', RSV_TEXT_DOMAIN ), 404 );
		if ( $require_owner && ! RSV_Security::can( RSV_Contracts::CAP_MANAGE ) && absint( $reel['owner_id'] ) !== get_current_user_id() ) return RSV_Helpers::error( 'rsv_forbidden', __( 'You cannot manage this Reel.', RSV_TEXT_DOMAIN ), 403 );
		return $reel;
	}

	private static function authoritative_duration( $reel ) {
		$dto = is_array( $reel ) ? RSV_Repository::public_dto( $reel ) : array();
		return absint( $dto['duration_seconds'] ?? 0 );
	}

	private static function validate_time_range( $reel, $start, $end = 0 ) {
		$duration = self::authoritative_duration( $reel );
		$start = absint( $start );
		$end = absint( $end );
		if ( $duration < 60 || $duration > 600 ) return RSV_Helpers::error( 'rsv_future_duration_unavailable', __( 'Authoritative Reel duration is unavailable or outside the 60–600 second rule.', RSV_TEXT_DOMAIN ), 503 );
		if ( $start > $duration || ( $end && ( $end < $start || $end > $duration ) ) ) return RSV_Helpers::error( 'rsv_future_time_invalid', __( 'The timestamp must be inside the authoritative Reel duration.', RSV_TEXT_DOMAIN ), 422 );
		return array( 'start'=>$start, 'end'=>$end, 'duration'=>$duration );
	}

	private static function identity_ref_valid( $ref, $purpose ) {
		$ref = RSV_Helpers::text( $ref, 255 );
		if ( ! $ref ) return false;
		return true === apply_filters( 'rsv_future30_identity_ref_valid', false, $ref, sanitize_key( $purpose ) );
	}

	private static function generic_public_allowed( $feature_id ) {
		return in_array( $feature_id, array( 'F11-FUT-003','F11-FUT-004','F11-FUT-005','F11-FUT-006','F11-FUT-007','F11-FUT-008','F11-FUT-009','F11-FUT-010','F11-FUT-011','F11-FUT-012','F11-FUT-013','F11-FUT-014','F11-FUT-016','F11-FUT-017','F11-FUT-018','F11-FUT-019','F11-FUT-024','F11-FUT-030' ), true );
	}

	private static function generic_write_allowed( $feature_id ) {
		return in_array( $feature_id, array( 'F11-FUT-003','F11-FUT-004','F11-FUT-005','F11-FUT-006','F11-FUT-007','F11-FUT-008','F11-FUT-009','F11-FUT-010','F11-FUT-011','F11-FUT-012','F11-FUT-013','F11-FUT-014','F11-FUT-015','F11-FUT-016','F11-FUT-017','F11-FUT-018','F11-FUT-019','F11-FUT-024','F11-FUT-028','F11-FUT-030' ), true );
	}

	private static function canonical_ref_public_valid( $owner, $ref, $purpose ) {
		$owner = RSV_Helpers::text( $owner, 30 );
		$ref = RSV_Helpers::text( $ref, 255 );
		if ( ! $owner || ! $ref ) return false;
		if ( 'File 11' === $owner ) {
			$target = RSV_Repository::find( $ref, true );
			return (bool) ( $target && RSV_Security::can_view_reel( $target, 0 ) );
		}
		return true === apply_filters( 'rsv_future30_public_ref_valid', false, $owner, $ref, sanitize_key( $purpose ) );
	}

	private static function public_projection( $feature_id, $reel ) {
		$objects = self::object_rows( $feature_id, $reel['id'], 0, 100 );
		$edges   = self::edge_rows( $feature_id, $reel['id'], 200 );
		$duration = self::authoritative_duration( $reel );
		foreach ( $objects as &$object ) unset( $object['owner_id'] );
		unset( $object );

		if ( in_array( $feature_id, array( 'F11-FUT-003','F11-FUT-004','F11-FUT-006','F11-FUT-011','F11-FUT-014','F11-FUT-030' ), true ) ) {
			$edges = array_values( array_filter( $edges, static function( $row ) use ( $feature_id, $reel, $duration ) {
				$owner = RSV_Helpers::text( $row['target_owner'] ?? '', 30 );
				$ref   = RSV_Helpers::text( $row['target_ref'] ?? '', 255 );
				if ( ! self::canonical_ref_public_valid( $owner, $ref, 'public-projection-' . strtolower( $feature_id ) ) ) return false;
				if ( 'F11-FUT-004' === $feature_id ) {
					$start = absint( $row['start_second'] ?? 0 );
					$end   = absint( $row['end_second'] ?? 0 );
					if ( $duration < 60 || $duration > 600 || $start > $duration || ( $end && ( $end < $start || $end > $duration ) ) ) return false;
				}
				if ( 'F11-FUT-014' === $feature_id ) {
					$target = RSV_Repository::find( $ref, true );
					$declared = strtolower( str_replace( '_', '-', RSV_Helpers::text( $row['payload']['language'] ?? '', 20 ) ) );
					$actual = $target ? strtolower( str_replace( '_', '-', RSV_Helpers::text( $target['language'] ?? '', 20 ) ) ) : '';
					if ( ! $declared || ! $actual || ! hash_equals( $actual, $declared ) ) return false;
				}
				if ( 'F11-FUT-030' === $feature_id ) {
					$source_ref = RSV_Helpers::text( $row['payload']['source_ref'] ?? '', 255 );
					if ( $source_ref && ! self::canonical_ref_public_valid( 'File 06', $source_ref, 'knowledge-graph-evidence-current' ) ) return false;
				}
				return true;
			} ) );
		}

		if ( 'F11-FUT-005' === $feature_id ) {
			$objects = array_values( array_filter( $objects, static function( $row ) {
				$payload = (array) ( $row['payload'] ?? array() );
				$owner = RSV_Helpers::text( $payload['source_owner'] ?? 'File 06', 30 );
				foreach ( (array) ( $payload['source_refs'] ?? array() ) as $ref ) {
					if ( ! self::canonical_ref_public_valid( $owner, RSV_Helpers::text( $ref, 255 ), 'evidence-layer-current' ) ) return false;
				}
				return true;
			} ) );
		}

		if ( 'F11-FUT-006' === $feature_id ) foreach ( $edges as &$row ) $row['payload'] = array( 'effective_at'=>$row['payload']['effective_at'] ?? '' );
		unset( $row );

		if ( 'F11-FUT-007' === $feature_id ) foreach ( $objects as &$row ) $row['payload'] = array( 'reel_version'=>absint($row['payload']['reel_version'] ?? 0), 'snapshot_hash'=>RSV_Helpers::text($row['payload']['snapshot_hash'] ?? '',64) );
		unset( $row );

		if ( 'F11-FUT-008' === $feature_id ) {
			$edges = array_values( array_filter( $edges, static function( $row ) use ( $reel ) {
				$ref = RSV_Helpers::text( $row['target_ref'] ?? '', 255 );
				$source = RSV_Repository::find( $ref, true );
				if ( ! $source || ! RSV_Security::can_view_reel( $source, 0 ) ) return false;
				$mode = sanitize_key( $row['edge_type'] ?? 'response' );
				if ( true !== apply_filters( 'rsv_future30_remix_allowed', false, $source, $reel, $mode, 0 ) ) return false;
				$derivative = RSV_Helpers::text( $row['payload']['file10_derivative_ref'] ?? '', 255 );
				return $derivative && true === apply_filters( 'rsv_future30_file10_derivative_valid', false, $derivative, $reel, $mode );
			} ) );
		}

		if ( 'F11-FUT-010' === $feature_id ) {
			$objects = array_values( array_filter( $objects, static function( $row ) use ( $reel ) {
				$payload = (array) ( $row['payload'] ?? array() );
				$recipe = RSV_Helpers::text( $payload['file10_media_recipe_ref'] ?? '', 255 );
				if ( ! $recipe ) return true;
				$type = sanitize_key( $payload['template_type'] ?? 'clinical-pearl' );
				return true === apply_filters( 'rsv_future30_file10_media_recipe_ref_valid', false, $recipe, $reel, $type );
			} ) );
		}

		if ( 'F11-FUT-012' === $feature_id ) {
			$edges = array_values( array_filter( $edges, static function( $row ) use ( $reel ) {
				$ref = RSV_Helpers::text( $row['target_ref'] ?? '', 255 );
				return ! empty( $row['payload']['accepted'] )
					&& self::identity_ref_valid( $ref, 'coauthor-public-current' )
					&& self::canonical_ref_public_valid( 'File 00', $ref, 'coauthor-public-current' )
					&& true === apply_filters( 'rsv_future30_coauthor_consent_valid', false, $ref, $reel, 0 );
			} ) );
		}

		if ( 'F11-FUT-013' === $feature_id ) {
			$edges = array_values( array_filter( $edges, static function( $row ) use ( $reel ) {
				$payload = (array) ( $row['payload'] ?? array() );
				$ref = RSV_Helpers::text( $row['target_ref'] ?? '', 255 );
				$attestation = RSV_Helpers::text( $payload['attestation_ref'] ?? '', 255 );
				$decision = sanitize_key( $payload['decision'] ?? '' );
				return 'approved' === $decision
					&& empty( $payload['conflict_declared'] )
					&& self::identity_ref_valid( $ref, 'peer-reviewer-public-current' )
					&& self::canonical_ref_public_valid( 'File 00', $ref, 'peer-reviewer-public-current' )
					&& $attestation
					&& true === apply_filters( 'rsv_future30_peer_review_attestation_valid', false, $attestation, $ref, $reel, $decision );
			} ) );
			foreach ( $edges as &$row ) {
				$row['payload'] = array( 'decision'=>'approved', 'reviewed_at'=>$row['payload']['reviewed_at'] ?? '', 'conflict_declared'=>false );
			}
			unset( $row );
		}

		if ( 'F11-FUT-016' === $feature_id ) {
			$objects = array_values( array_filter( $objects, static function( $row ) use ( $reel ) {
				$payload = (array) ( $row['payload'] ?? array() );
				$ref = RSV_Helpers::text( $payload['transcript_ref'] ?? '', 255 );
				return ! empty( $payload['reviewed'] ) && $ref && true === apply_filters( 'rsv_future30_transcript_ref_public_valid', false, $ref, $reel );
			} ) );
		}

		if ( 'F11-FUT-017' === $feature_id ) {
			$edges = array_values( array_filter( $edges, static function( $row ) use ( $duration ) {
				$start = absint( $row['start_second'] ?? 0 );
				$end = absint( $row['end_second'] ?? 0 );
				return $duration >= 60 && $duration <= 600 && $start <= $duration && ( ! $end || ( $end >= $start && $end <= $duration ) );
			} ) );
		}

		if ( 'F11-FUT-018' === $feature_id ) {
			$objects = array_values( array_filter( $objects, static function( $row ) {
				$payload = (array) ( $row['payload'] ?? array() );
				$refs = array_merge( (array) ( $payload['remedy_refs'] ?? array() ), (array) ( $payload['disease_refs'] ?? array() ), (array) ( $payload['book_refs'] ?? array() ), (array) ( $payload['source_refs'] ?? array() ) );
				foreach ( $refs as $ref ) if ( ! self::canonical_ref_public_valid( 'File 06', RSV_Helpers::text( $ref, 255 ), 'knowledge-card-current' ) ) return false;
				return true;
			} ) );
		}

		if ( 'F11-FUT-019' === $feature_id ) {
			foreach ( $objects as &$row ) {
				$questions = array();
				foreach ( (array) ( $row['payload']['questions'] ?? array() ) as $question ) {
					if ( ! is_array( $question ) ) continue;
					unset( $question['correct'], $question['answer'], $question['explanation_private'] );
					$questions[] = $question;
				}
				$row['payload']['questions'] = $questions;
			}
			unset( $row );
		}
		return array( 'objects'=>$objects, 'edges'=>$edges );
	}

	private static function sanitize_payload( $payload ) {
		$payload = is_array( $payload ) ? $payload : array();
		$out = array();
		foreach ( $payload as $key => $value ) {
			$key = sanitize_key( (string) $key );
			if ( is_array( $value ) ) $out[ $key ] = self::sanitize_payload( $value );
			elseif ( is_bool( $value ) ) $out[ $key ] = $value;
			elseif ( is_numeric( $value ) ) $out[ $key ] = 0 + $value;
			else $out[ $key ] = RSV_Helpers::text( (string) $value, 4000 );
		}
		return $out;
	}

	private static function put_object( $feature_id, $object_type, $reel_id, $title, $payload, $public_id = '' ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'future_objects' );
		$feature_id = strtoupper( sanitize_text_field( $feature_id ) );
		$object_type = sanitize_key( $object_type );
		$public_id = $public_id ? RSV_Helpers::text( $public_id, 80 ) : RSV_Helpers::public_id( 'f30' );
		$now = RSV_Helpers::now();
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE public_id=%s", $public_id ), ARRAY_A );
		$data = array( 'feature_id'=>$feature_id,'object_type'=>$object_type,'reel_id'=>absint($reel_id),'owner_id'=>get_current_user_id(),'status'=>'active','title'=>RSV_Helpers::text($title,255),'payload_json'=>RSV_Helpers::json_encode(self::sanitize_payload($payload)),'updated_at'=>$now );
		if ( $existing ) {
			if ( absint( $existing['owner_id'] ) !== get_current_user_id() && ! RSV_Security::can( RSV_Contracts::CAP_MANAGE ) ) return RSV_Helpers::error( 'rsv_future_forbidden', __( 'You cannot update this item.', RSV_TEXT_DOMAIN ), 403 );
			$data['version'] = absint( $existing['version'] ) + 1;
			$ok = $wpdb->update( $table, $data, array('id'=>absint($existing['id']),'version'=>absint($existing['version'])), array('%s','%s','%d','%d','%s','%s','%s','%s','%d'), array('%d','%d') );
			if ( 1 !== $ok ) return RSV_Helpers::error( 'rsv_future_conflict', __( 'The item changed. Refresh and retry.', RSV_TEXT_DOMAIN ), 409 );
			return array( 'public_id'=>$public_id, 'version'=>$data['version'] );
		}
		$data['public_id']=$public_id; $data['version']=1; $data['created_at']=$now;
		$ok = $wpdb->insert( $table, $data, array('%s','%s','%s','%d','%d','%s','%s','%s','%s','%d','%s') );
		if ( 1 !== $ok ) return RSV_Helpers::error( 'rsv_future_write_failed', __( 'The item could not be saved.', RSV_TEXT_DOMAIN ), 500 );
		return array( 'public_id'=>$public_id, 'version'=>1 );
	}

	private static function put_edge( $feature_id, $reel_id, $edge_type, $target_owner, $target_ref, $payload, $start = 0, $end = 0 ) {
		global $wpdb;
		$table=RSV_Helpers::table('future_edges'); $now=RSV_Helpers::now();
		$target_owner=RSV_Helpers::text($target_owner,30); $target_ref=RSV_Helpers::text($target_ref,255);
		if ( ! $target_ref ) return RSV_Helpers::error('rsv_future_target_required',__('A canonical target reference is required.',RSV_TEXT_DOMAIN),422);
		$start=max(0,absint($start)); $end=max($start,absint($end));
		$public_id=RSV_Helpers::public_id('edge');
		$ok=$wpdb->insert($table,array('public_id'=>$public_id,'feature_id'=>$feature_id,'source_reel_id'=>absint($reel_id),'edge_type'=>sanitize_key($edge_type),'target_owner'=>$target_owner,'target_ref'=>$target_ref,'start_second'=>$start,'end_second'=>$end,'payload_json'=>RSV_Helpers::json_encode(self::sanitize_payload($payload)),'status'=>'active','version'=>1,'created_at'=>$now,'updated_at'=>$now),array('%s','%s','%d','%s','%s','%s','%d','%d','%s','%s','%d','%s','%s'));
		if(1!==$ok)return RSV_Helpers::error('rsv_future_edge_failed',__('The relationship could not be saved.',RSV_TEXT_DOMAIN),500);
		return array('public_id'=>$public_id,'version'=>1);
	}

	private static function object_rows( $feature_id, $reel_id = 0, $owner_id = 0, $limit = 50 ) {
		global $wpdb; $table=RSV_Helpers::table('future_objects'); $where=array('feature_id=%s','status=%s'); $args=array($feature_id,'active');
		if($reel_id){$where[]='reel_id=%d';$args[]=absint($reel_id);} if($owner_id){$where[]='owner_id=%d';$args[]=absint($owner_id);} $args[]=min(100,max(1,absint($limit)));
		$sql="SELECT public_id,feature_id,object_type,reel_id,owner_id,title,payload_json,version,created_at,updated_at FROM $table WHERE ".implode(' AND ',$where).' ORDER BY updated_at DESC,id DESC LIMIT %d';
		$rows=$wpdb->get_results($wpdb->prepare($sql,$args),ARRAY_A); foreach($rows as &$row){$row['payload']=RSV_Helpers::json_decode($row['payload_json']);unset($row['payload_json']);} return $rows;
	}

	private static function edge_rows( $feature_id, $reel_id, $limit = 100 ) {
		global $wpdb;$table=RSV_Helpers::table('future_edges');$rows=$wpdb->get_results($wpdb->prepare("SELECT public_id,feature_id,edge_type,target_owner,target_ref,start_second,end_second,payload_json,version,created_at,updated_at FROM $table WHERE feature_id=%s AND source_reel_id=%d AND status='active' ORDER BY start_second ASC,id ASC LIMIT %d",$feature_id,absint($reel_id),min(200,max(1,absint($limit)))),ARRAY_A);foreach($rows as &$row){$row['payload']=RSV_Helpers::json_decode($row['payload_json']);unset($row['payload_json']);}return $rows;
	}

	private static function state_get( $feature_id, $object_ref, $state_key, $default = array() ) {
		global $wpdb;$table=RSV_Helpers::table('future_user_state');$row=$wpdb->get_row($wpdb->prepare("SELECT payload_json,version,updated_at FROM $table WHERE user_id=%d AND feature_id=%s AND object_ref=%s AND state_key=%s",get_current_user_id(),$feature_id,RSV_Helpers::text($object_ref,100),sanitize_key($state_key)),ARRAY_A);if(!$row)return $default;$payload_default=(is_array($default)&&array_key_exists('payload',$default))?(array)$default['payload']:(array)$default;return array('payload'=>RSV_Helpers::json_decode($row['payload_json'],$payload_default),'version'=>absint($row['version']),'updated_at'=>$row['updated_at']);
	}

	private static function state_put( $feature_id, $object_ref, $state_key, $payload ) {
		global $wpdb;$table=RSV_Helpers::table('future_user_state');$now=RSV_Helpers::now();$object_ref=RSV_Helpers::text($object_ref,100);$state_key=sanitize_key($state_key);$payload=RSV_Helpers::json_encode(self::sanitize_payload($payload));
		$sql=$wpdb->prepare("INSERT INTO $table (user_id,feature_id,object_ref,state_key,payload_json,version,created_at,updated_at) VALUES (%d,%s,%s,%s,%s,1,%s,%s) ON DUPLICATE KEY UPDATE payload_json=VALUES(payload_json),version=version+1,updated_at=VALUES(updated_at)",get_current_user_id(),$feature_id,$object_ref,$state_key,$payload,$now,$now);if(false===$wpdb->query($sql))return RSV_Helpers::error('rsv_future_state_failed',__('The private setting could not be saved.',RSV_TEXT_DOMAIN),500);return self::state_get($feature_id,$object_ref,$state_key,array());
	}


	private static function idempotent_finish_result( $scope, $key, $operation, $evidence = array() ) {
		$result = RSV_DB::transaction(
			static function () use ( $scope, $key, $operation, $evidence ) {
				$value = is_callable( $operation ) ? call_user_func( $operation ) : RSV_Helpers::error( 'rsv_future_operation_invalid', __( 'The protected operation could not be executed.', RSV_TEXT_DOMAIN ), 500 );
				if ( is_wp_error( $value ) ) return $value;
				$response = array( 'ok' => true, 'result' => $value );
				if ( ! RSV_Security::idempotency_finish( $scope, $key, $response ) ) return RSV_Helpers::error( 'rsv_idempotency_finish_failed', __( 'The operation could not be committed with complete replay evidence.', RSV_TEXT_DOMAIN ), 500 );
				if ( $evidence ) {
					$entity_type = sanitize_key( $evidence['entity_type'] ?? 'future30' );
					$entity_id   = absint( $evidence['entity_id'] ?? 0 );
					$action      = sanitize_key( $evidence['action'] ?? 'update' );
					$reason      = RSV_Helpers::text( $evidence['reason'] ?? '', 500 );
					$meta        = is_array( $evidence['meta'] ?? null ) ? $evidence['meta'] : array();
					$event       = RSV_Helpers::text( $evidence['event'] ?? '', 120 );
					$event_data  = is_array( $evidence['event_data'] ?? null ) ? $evidence['event_data'] : array();
					if ( ! RSV_Helpers::audit( $entity_type, $entity_id, $action, '', '', $reason, $meta ) || ( $event && ! RSV_Helpers::outbox( $event, $entity_type, $entity_id, $event_data ) ) ) {
						return RSV_Helpers::error( 'rsv_future_evidence_failed', __( 'The operation could not be committed with complete audit and event evidence.', RSV_TEXT_DOMAIN ), 500 );
					}
				}
				return $response;
			}
		);
		if ( is_wp_error( $result ) ) { RSV_Security::idempotency_fail( $scope, $key ); return $result; }
		return rest_ensure_response( $result );
	}

}
