<?php
defined( 'ABSPATH' ) || exit;

/** Versioned read-only providers; companion modules never write File 11 tables. */
final class RSV_Integrations {
	const PROVIDER_VERSION = 3;

	public function register() {
		add_filter( 'rsv_provider_manifest', array( __CLASS__, 'manifest' ) );
		add_filter( 'rsv_public_author_items', array( __CLASS__, 'public_author_items' ), 10, 3 );
		add_filter( 'rsv_public_search_documents', array( __CLASS__, 'public_search_documents' ), 10, 3 );
		add_filter( 'rsv_home_cards', array( __CLASS__, 'home_cards' ), 10, 2 );
		add_filter( 'rsv_interaction_aggregate', array( __CLASS__, 'interaction_aggregate' ), 10, 3 );
		add_action( 'init', array( __CLASS__, 'announce_provider' ), 95 );
	}

	public static function manifest( $manifest = array() ) {
		$file11 = array(
			'provider_id' => 'file11-reels', 'provider_version' => self::PROVIDER_VERSION,
			'contract_version' => RSV_CONTRACT_VERSION, 'event_version' => RSV_Contracts::EVENT_VERSION,
			'canonical_owner' => 'File 11',
			'entity_types' => array( 'reel', 'story', 'highlight', 'reel-response' ),
			'public_routes' => array( 'reels', 'reel', 'stories', 'story', 'highlights' ),
			'capabilities' => array( 'timeline','home-cards','search-documents','recommendation-explanation','stories-status','highlights','attributed-responses','source-safety','wellbeing','youth-safe','value-insights' ),
			'write_policy' => 'owner-only-versioned-commands', 'privacy_policy' => 'public-eligible-fields-only',
			'maturity' => 'repository-release-candidate-staging-pending',
		);
		if ( is_array( $manifest ) && isset( $manifest['provider_id'] ) ) return $file11;
		$manifest = is_array( $manifest ) ? $manifest : array();
		$manifest['file11-reels'] = $file11;
		return $manifest;
	}

	public static function announce_provider() {
		$manifest = self::manifest( array( 'provider_id' => 'request-single' ) );
		do_action( 'rsv_provider_registered', $manifest );
		do_action( 'sabri_platform_domain_provider_registered', 'file11', $manifest );
	}

	public static function public_author_items( $items, $author_id, $args = array() ) {
		$items = is_array( $items ) ? $items : array();
		$limit = min( 50, max( 1, absint( $args['limit'] ?? 20 ) ) );
		foreach ( RSV_Repository::public_by_author( absint( $author_id ), $limit ) as $row ) $items[] = self::timeline_item( $row );
		return $items;
	}

	public static function public_search_documents( $documents, $query, $args = array() ) {
		$documents = is_array( $documents ) ? $documents : array();
		$limit = min( 30, max( 1, absint( $args['limit'] ?? 10 ) ) );
		$topic = RSV_Helpers::enum( $args['topic'] ?? '', RSV_Contracts::TOPICS, '' );
		foreach ( RSV_Repository::search_public( (string) $query, $limit, $topic ) as $row ) {
			$dto = RSV_Repository::public_dto( $row );
			$documents[] = array(
				'provider_id'=>'file11-reels','provider_version'=>self::PROVIDER_VERSION,'native_id'=>$dto['id'],'entity_type'=>'reel',
				'title'=>$dto['title'],'excerpt'=>wp_trim_words(wp_strip_all_tags($dto['caption']),32),'canonical_url'=>$dto['url'],
				'topic'=>$dto['topic'],'language'=>$dto['language'],'published_at'=>$dto['published_at'],'visibility'=>'public',
				'source_safety'=>$dto['source_safety']??array(),'safety'=>array('file10-verified','rights-checked','consent-checked','youth-filterable'),
			);
		}
		return $documents;
	}

	public static function home_cards( $cards, $args = array() ) {
		$cards = is_array( $cards ) ? $cards : array();
		$data = RSV_Repository::feed( array('limit'=>min(12,max(1,absint($args['limit']??6))),'sort'=>'recommended','topic'=>RSV_Helpers::enum($args['topic']??'',RSV_Contracts::TOPICS,''),'youth_safe'=>!empty($args['youth_safe'])) );
		if ( is_wp_error( $data ) ) return $cards;
		foreach ( $data['items'] as $dto ) {
			$cards[] = array('provider_id'=>'file11-reels','provider_version'=>self::PROVIDER_VERSION,'native_id'=>$dto['id'],'title'=>$dto['title'],'excerpt'=>wp_trim_words(wp_strip_all_tags($dto['caption']),22),'canonical_url'=>$dto['url'],'topic'=>$dto['topic'],'duration_seconds'=>$dto['duration_seconds'],'author'=>$dto['owner'],'source_safety'=>$dto['source_safety']??array(),'youth_safe'=>!empty($dto['youth_safe']));
		}
		return $cards;
	}

	public static function recommendation_explanation( $reel, $sort = 'recommended' ) {
		$reasons = array();
		if ( 'latest' === $sort ) $reasons[] = __( 'It is a recently published, approved educational Reel.', RSV_TEXT_DOMAIN );
		else {
			$reasons[] = __( 'It passed educational relevance, source, rights, consent and safety gates.', RSV_TEXT_DOMAIN );
			$reasons[] = __( 'Recommended ordering uses bounded quality, completion, freshness and diversity signals—not payment or follower count alone.', RSV_TEXT_DOMAIN );
		}
		if ( class_exists( 'RSV_Top20' ) && RSV_Top20::youth_mode() ) $reasons[] = __( 'Youth-safe mode removed age-inappropriate topics, labels and social actions before ranking.', RSV_TEXT_DOMAIN );
		if ( ! empty( $reel['topic'] ) ) $reasons[] = sprintf( __( 'Its approved topic is %s.', RSV_TEXT_DOMAIN ), RSV_Helpers::label( (string) $reel['topic'] ) );
		return (array) apply_filters( 'rsv_recommendation_explanation', $reasons, $reel, $sort );
	}

	public static function interaction_aggregate( $current, $reel_id, $owner_id ) {
		if ( is_array( $current ) ) return $current;
		$reel = RSV_Repository::find( absint( $reel_id ) );
		if ( ! $reel || absint( $reel['owner_id'] ) !== absint( $owner_id ) ) return null;
		return RSV_File10::interaction_aggregate( absint( $reel['video_id'] ) );
	}

	private static function timeline_item( $row ) {
		$dto = RSV_Repository::public_dto( $row );
		return array(
			'provider_id'=>'file11-reels','provider_version'=>self::PROVIDER_VERSION,'native_object_type'=>'reel','native_id'=>$dto['id'],'author_id'=>absint($row['owner_id']),
			'title'=>$dto['title'],'safe_excerpt'=>wp_trim_words(wp_strip_all_tags($dto['caption']),32),'canonical_url'=>$dto['url'],'published_at'=>$dto['published_at'],'updated_at'=>$dto['updated_at'],
			'visibility_state'=>'public','native_status'=>'published','content_type'=>'reel','topic'=>$dto['topic'],'language'=>$dto['language'],'thumbnail_reference'=>absint($dto['cover_id']),'media_type'=>'video',
			'available_actions'=>array_filter(array('open'=>$dto['url'],'download'=>$dto['download_url'])),'metrics_reference'=>'aggregate-only','source_safety'=>$dto['source_safety']??array(),
		);
	}
}
