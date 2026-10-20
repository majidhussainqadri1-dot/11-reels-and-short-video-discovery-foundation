<?php
/** File 11-owned, PUBLIC-ONLY candidate for the File 26 versioned connector registry.
 * This adapter MUST remain proposed until migration, deletion delivery, QA, and staging approvals.
 */
defined('ABSPATH') || exit;

final class RSV_File26_Connector {
    const SLUG = 'file11-reels';

    public static function collect($adapters) {
        $adapters = is_array($adapters) ? $adapters : array();
        $adapters['file11'] = array(
            'slug' => self::SLUG, 'contract_version' => (string) RSV_CONTRACT_VERSION,
            'entity_types' => array('reel'), 'privacy_classes' => array('public'),
            'visibility_fields' => array('status', 'visibility'),
            'deletion_semantics' => 'tombstone',
            'status' => 'proposed',
            'list_batch' => array(__CLASS__, 'list_batch'),
            'can_view' => array(__CLASS__, 'can_view'),
            'health' => array(__CLASS__, 'health'),
            'event_contract' => 'rsv.public-reel-projection.v1',
            'index_schema' => 'sabri.file26.document.v1.1',
        );
        return $adapters;
    }

    public static function health() {
        $schema = (string) get_option('rsv_schema_version', '');
        $ready = $schema === RSV_SCHEMA_VERSION && RSV_File10::ready();
        return array('state' => $ready ? 'healthy' : 'degraded', 'readiness' => $ready ? 'native-owner-ready' : 'owner-schema-or-media-unavailable');
    }

    public static function list_batch($cursor, $limit = 100, $scope = array()) {
        global $wpdb;
        if ('healthy' !== self::health()['state']) {
            throw new RuntimeException('File 11 reel connector is not ready.');
        }
        $cursor = trim((string) $cursor);
        if ('' !== $cursor && (!ctype_digit($cursor) || strlen($cursor) > 19)) {
            throw new InvalidArgumentException('Invalid File 11 owner cursor.');
        }
        $after = '' === $cursor ? 0 : (int) $cursor;
        $limit = min(100, max(1, absint($limit)));
        $table = RSV_Helpers::table('reels');
        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE id>%d AND status=%s AND visibility=%s ORDER BY id ASC LIMIT %d",
                $after, 'published', 'public', $limit+1), ARRAY_A
        );
        if (null === $rows || '' !== (string) $wpdb->last_error) {
            throw new RuntimeException('Native File 11 public projection read failed.');
        }
        $seen = array_slice((array) $rows, 0, $limit);
        $items = array();
        foreach ($seen as $row) {
            $after = (int) $row['id'];
            if (!RSV_Security::can_view_reel($row, 0)) { continue; }
            $doc = self::public_document($row);
            if ($doc) { $items[] = $doc; }
        }
        return array('items' => $items, 'next_cursor' => (string) $after, 'done' => count($rows) <= $limit);
    }

    private static function public_document(array $row) {
        $dto = RSV_Repository::public_dto($row);
        if (!is_array($dto) || empty($dto['id']) || empty($dto['url']) || empty($dto['title'])) { return null; }
        return array(
            'connector_slug' => self::SLUG, 'domain' => 'reel',
            'object_id' => (string) $dto['id'],
            'object_version' => max(1, (int) $dto['version']),
            'source_event_sequence' => max(1, (int) $dto['version']),
            'entity_type' => 'reel', 'locale' => (string) $dto['language'],
            'state' => 'published', 'visibility' => 'public',
            'title' => (string) $dto['title'],
            'excerpt' => wp_trim_words(wp_strip_all_tags((string) $dto['caption']), 40),
            'search_text' => wp_strip_all_tags((string) $dto['caption']),
            'canonical_url' => (string) $dto['url'],
            'author_key' => RSV_Helpers::opaque_user_ref( absint( $row['owner_id'] ), 'file26-public-reel-author' ),
            'topic_ids' => empty($dto['topic']) ? array() : array((string) $dto['topic']),
            'freshness_at' => (string) ($dto['published_at'] ?: $dto['updated_at']),
            'quality_score' => 0, 'authority_score' => 0, 'popularity_score' => 0,
            'payload' => array('source_owner_file' => 'File 11', 'raw_media_owner_file' => 'File 10'),
        );
    }

    public static function can_view(array $document, array $audience) {
        $id = isset($document['object_id']) ? (string) $document['object_id'] : '';
        if ('' === $id) { return false; }
        $native = RSV_Repository::find($id, true);
        return is_array($native) && RSV_Security::can_view_reel($native, 0);
    }
}
