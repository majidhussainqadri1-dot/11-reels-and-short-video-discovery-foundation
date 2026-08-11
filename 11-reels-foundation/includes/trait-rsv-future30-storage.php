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
		$edges = self::edge_rows( $feature_id, $reel['id'], 200 );
		foreach ( $objects as &$object ) unset( $object['owner_id'] );
		if ( 'F11-FUT-006' === $feature_id ) foreach ( $edges as &$row ) $row['payload'] = array( 'effective_at'=>$row['payload']['effective_at'] ?? '' );
		if ( 'F11-FUT-007' === $feature_id ) foreach ( $objects as &$row ) $row['payload'] = array( 'reel_version'=>absint($row['payload']['reel_version'] ?? 0), 'snapshot_hash'=>RSV_Helpers::text($row['payload']['snapshot_hash'] ?? '',64) );
		if ( 'F11-FUT-012' === $feature_id ) {
			$edges = array_values( array_filter( $edges, static function( $row ) { return ! empty( $row['payload']['accepted'] ); } ) );
		}
		if ( 'F11-FUT-013' === $feature_id ) {
			$edges = array_values( array_filter( $edges, static function( $row ) { return 'approved' === ( $row['payload']['decision'] ?? '' ); } ) );
			foreach ( $edges as &$row ) {
				$row['payload'] = array( 'decision'=>'approved', 'reviewed_at'=>$row['payload']['reviewed_at'] ?? '', 'conflict_declared'=>! empty( $row['payload']['conflict_declared'] ) );
			}
		}
		if ( 'F11-FUT-016' === $feature_id ) {
			$objects = array_values( array_filter( $objects, static function( $row ) { return ! empty( $row['payload']['reviewed'] ); } ) );
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


	private static function idempotent_finish_result( $scope, $key, $result ) {
		if ( is_wp_error( $result ) ) { RSV_Security::idempotency_fail( $scope, $key ); return $result; }
		$response = array( 'ok'=>true, 'result'=>$result );
		RSV_Security::idempotency_finish( $scope, $key, $response );
		return rest_ensure_response( $response );
	}

}
