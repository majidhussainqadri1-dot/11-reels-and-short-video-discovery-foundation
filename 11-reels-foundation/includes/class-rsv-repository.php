<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Repository {
	private static $update_columns = array(
		'title' => '%s', 'slug' => '%s', 'topic' => '%s', 'language' => '%s', 'caption' => '%s',
		'disclosure' => '%s', 'cover_id' => '%d', 'visibility' => '%s', 'status' => '%s',
		'rights_status' => '%s', 'consent_status' => '%s', 'safety_labels_json' => '%s',
		'rank_score' => '%f', 'published_at' => '%s', 'owner_id' => '%d', 'legacy_source_id' => '%d',
	);

	public static function find( $id, $public = false ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'reels' );
		if ( $public ) {
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE public_id=%s LIMIT 1", (string) $id ), ARRAY_A );
		}
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id=%d LIMIT 1", absint( $id ) ), ARRAY_A );
	}

	public static function report( $id, $public = false, $lock = false ) {
		global $wpdb;
		$table  = RSV_Helpers::table( 'reports' );
		$suffix = $lock ? ' FOR UPDATE' : '';
		return $public
			? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE public_id=%s LIMIT 1$suffix", (string) $id ), ARRAY_A )
			: $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id=%d LIMIT 1$suffix", absint( $id ) ), ARRAY_A );
	}

	public static function public_dto( $row, $include_private = false ) {
		if ( ! is_array( $row ) ) {
			return null;
		}
		$video = RSV_File10::video( absint( $row['video_id'] ) );
		$data = array(
			'id'               => (string) $row['public_id'],
			'video_id'         => $video ? (string) ( $video['public_id'] ?? '' ) : '',
			'title'            => (string) $row['title'],
			'slug'             => (string) $row['slug'],
			'topic'            => (string) $row['topic'],
			'language'         => (string) $row['language'],
			'caption'          => (string) $row['caption'],
			'disclosure'       => (string) $row['disclosure'],
			'cover_id'         => absint( $row['cover_id'] ),
			'visibility'       => (string) $row['visibility'],
			'status'           => (string) $row['status'],
			'owner'            => self::owner_dto( absint( $row['owner_id'] ) ),
			'duration_seconds' => $video ? absint( $video['duration_seconds'] ?? 0 ) : 0,
			'url'              => home_url( '/reel/' . rawurlencode( $row['public_id'] ) . '/' . rawurlencode( $row['slug'] ) . '/' ),
			'video_url'        => $video && ! empty( $video['public_id'] ) ? home_url( '/video/' . rawurlencode( $video['public_id'] ) . '/' . rawurlencode( $video['slug'] ?? '' ) . '/' ) : '',
			'comments_url'     => (string) apply_filters( 'rsv_comments_url', '', $row, $video ),
			'follow_url'       => (string) apply_filters( 'rsv_follow_url', '', absint( $row['owner_id'] ), $row ),
			'download_url'     => RSV_File10::download_url( absint( $row['video_id'] ), $row ),
			'published_at'     => $row['published_at'],
			'updated_at'       => $row['updated_at'],
			'version'          => absint( $row['version'] ),
		);
		if ( class_exists( 'RSV_Top20' ) ) {
			$data['source_safety'] = RSV_Top20::public_context( $row );
			$data['youth_safe']    = RSV_Top20::reel_is_youth_safe( $row );
		}
		if ( $include_private ) {
			$data['rights_status'] = (string) $row['rights_status'];
			$data['consent_status'] = (string) $row['consent_status'];
			$data['safety_labels'] = RSV_Helpers::json_decode( $row['safety_labels_json'] );
		}
		return $data;
	}

	private static function owner_dto( $user_id ) {
		$user = get_userdata( $user_id );
		return array(
			'name'        => $user ? $user->display_name : __( 'Publisher', RSV_TEXT_DOMAIN ),
			'profile_url' => apply_filters( 'rsv_profile_url', get_author_posts_url( $user_id ), $user_id ),
			'label'       => RSV_Security::publisher_label( $user_id ),
		);
	}

	/**
	 * Public feed. Unlisted/member/entitled Reels are intentionally excluded from discovery.
	 * Cursor context includes topic and youth-safety mode, preventing cross-context replay.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	public static function feed( $args = array() ) {
		global $wpdb;
		$table  = RSV_Helpers::table( 'reels' );
		$limit  = min( 50, max( 1, absint( $args['limit'] ?? 12 ) ) );
		$sort   = RSV_Helpers::enum( $args['sort'] ?? 'recommended', array( 'recommended', 'latest' ), 'recommended' );
		$topic  = RSV_Helpers::enum( $args['topic'] ?? '', RSV_Contracts::TOPICS, '' );
		$requested_youth = array_key_exists( 'youth_safe', $args ) && ! empty( $args['youth_safe'] );
		$youth  = $requested_youth || ( class_exists( 'RSV_Top20' ) && RSV_Top20::youth_mode() );
		$context = sanitize_key( ( $topic ?: 'all' ) . '-' . ( $youth ? 'youth' : 'standard' ) );
		$cursor = RSV_Helpers::cursor_decode( $args['cursor'] ?? '', $sort, $context );
		if ( is_wp_error( $cursor ) ) {
			return $cursor;
		}

		$where  = 'status=%s AND visibility=%s';
		$params = array( 'published', 'public' );
		if ( $topic ) {
			$where   .= ' AND topic=%s';
			$params[] = $topic;
		}

		if ( 'latest' === $sort ) {
			$order = 'published_at DESC, id DESC';
			if ( $cursor ) {
				$where .= ' AND (published_at < %s OR (published_at = %s AND id < %d))';
				$params[] = $cursor['primary'];
				$params[] = $cursor['primary'];
				$params[] = $cursor['id'];
			}
		} else {
			$order = 'rank_score DESC, updated_at DESC, id DESC';
			if ( $cursor ) {
				$where .= ' AND (rank_score < %f OR (rank_score = %f AND updated_at < %s) OR (rank_score = %f AND updated_at = %s AND id < %d))';
				$params[] = (float) $cursor['primary'];
				$params[] = (float) $cursor['primary'];
				$params[] = $cursor['secondary'];
				$params[] = (float) $cursor['primary'];
				$params[] = $cursor['secondary'];
				$params[] = $cursor['id'];
			}
		}

		$scan_limit = min( 200, max( $limit + 1, $limit * 4 ) );
		$params[]   = $scan_limit;
		$sql        = $wpdb->prepare( "SELECT * FROM $table WHERE $where ORDER BY $order LIMIT %d", $params );
		$rows       = (array) $wpdb->get_results( $sql, ARRAY_A );
		$items      = array();
		$last       = null;
		foreach ( $rows as $row ) {
			$last = $row;
			if ( RSV_Security::can_view_reel( $row, 0 ) ) {
				$items[] = self::public_dto( $row );
				if ( count( $items ) >= $limit ) {
					break;
				}
			}
		}
		$has_more = count( $rows ) === $scan_limit || ( $last && count( $items ) >= $limit );
		$next = null;
		if ( $has_more && $last ) {
			$next = 'latest' === $sort
				? RSV_Helpers::cursor_encode( $sort, $last['published_at'], '', $last['id'], $context )
				: RSV_Helpers::cursor_encode( $sort, $last['rank_score'], $last['updated_at'], $last['id'], $context );
		}
		return array(
			'items' => $items,
			'next_cursor' => $next,
			'sort' => $sort,
			'topic' => $topic,
			'youth_safe' => $youth,
		);
	}

	public static function update_versioned( $id, $expected_version, $changes ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'reels' );
		$sets  = array();
		$values = array();
		foreach ( (array) $changes as $key => $value ) {
			if ( ! isset( self::$update_columns[ $key ] ) ) {
				return RSV_Helpers::error( 'rsv_update_field_invalid', __( 'A Reel update field is not allowed.', RSV_TEXT_DOMAIN ), 500 );
			}
			$format = self::$update_columns[ $key ];
			if ( null === $value ) {
				$sets[] = $key . '=NULL';
				continue;
			}
			$sets[]   = $key . '=' . $format;
			$values[] = $value;
		}
		$sets[]   = 'version=%d';
		$values[] = absint( $expected_version ) + 1;
		$sets[]   = 'updated_at=%s';
		$values[] = RSV_Helpers::now();
		$values[] = absint( $id );
		$values[] = absint( $expected_version );
		$sql = $wpdb->prepare( "UPDATE $table SET " . implode( ',', $sets ) . ' WHERE id=%d AND version=%d', $values );
		$updated = $wpdb->query( $sql );
		if ( 1 !== $updated ) {
			return RSV_Helpers::error( 'rsv_version_conflict', __( 'This Reel changed. Refresh and try again.', RSV_TEXT_DOMAIN ), 409 );
		}
		return self::find( $id );
	}

	public static function history( $user_id, $limit = 100, $offset = 0 ) {
		global $wpdb;
		$progress = RSV_Helpers::table( 'progress' );
		$reels    = RSV_Helpers::table( 'reels' );
		$limit    = min( 200, max( 1, absint( $limit ) ) );
		$offset   = min( 1000, max( 0, absint( $offset ) ) );
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.position_seconds,p.completed,p.replays,p.updated_at AS progress_updated_at,r.*
				 FROM $progress p JOIN $reels r ON r.id=p.reel_id
				 WHERE p.user_id=%d ORDER BY p.updated_at DESC,p.id DESC LIMIT %d OFFSET %d",
				absint( $user_id ),
				$limit,
				$offset
			),
			ARRAY_A
		);
		$out = array();
		foreach ( (array) $rows as $row ) {
			$progress_dto = array(
				'position_seconds' => absint( $row['position_seconds'] ),
				'completed'        => (bool) $row['completed'],
				'replays'          => absint( $row['replays'] ),
				'updated_at'       => $row['progress_updated_at'],
			);
			if ( RSV_Security::can_view_reel( $row, absint( $user_id ) ) ) {
				$dto = self::public_dto( $row );
				$dto['available'] = true;
				$dto['progress']  = $progress_dto;
				$out[] = $dto;
			} else {
				$out[] = array(
					'id'        => 'unavailable-' . substr( hash( 'sha256', $row['public_id'] ), 0, 12 ),
					'title'     => __( 'Unavailable Reel', RSV_TEXT_DOMAIN ),
					'available' => false,
					'url'       => '',
					'progress'  => $progress_dto,
				);
			}
		}
		return $out;
	}

	public static function neighbors( $reel ) {
		global $wpdb;
		$table     = RSV_Helpers::table( 'reels' );
		$published = $reel['published_at'] ?: $reel['updated_at'];
		$previous_rows = (array) $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table WHERE status='published' AND visibility='public' AND (published_at<%s OR (published_at=%s AND id<%d)) ORDER BY published_at DESC,id DESC LIMIT 50", $published, $published, $reel['id'] ),
			ARRAY_A
		);
		$next_rows = (array) $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table WHERE status='published' AND visibility='public' AND (published_at>%s OR (published_at=%s AND id>%d)) ORDER BY published_at ASC,id ASC LIMIT 50", $published, $published, $reel['id'] ),
			ARRAY_A
		);
		$previous = self::first_viewable( $previous_rows );
		$next     = self::first_viewable( $next_rows );
		return array(
			'previous' => $previous ? self::public_dto( $previous ) : null,
			'next'     => $next ? self::public_dto( $next ) : null,
		);
	}

	private static function first_viewable( $rows ) {
		foreach ( (array) $rows as $row ) {
			if ( RSV_Security::can_view_reel( $row, 0 ) ) {
				return $row;
			}
		}
		return null;
	}

	/** Bounded public Reel projection for File 25 timelines. */
	public static function public_by_author( $author_id, $limit = 20 ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'reels' );
		$limit = min( 50, max( 1, absint( $limit ) ) );
		$rows  = (array) $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE owner_id=%d AND status=%s AND visibility=%s ORDER BY published_at DESC,id DESC LIMIT %d",
				absint( $author_id ), 'published', 'public', min( 150, $limit * 3 )
			),
			ARRAY_A
		);
		$out = array();
		foreach ( $rows as $row ) {
			if ( RSV_Security::can_view_reel( $row, 0 ) ) {
				$out[] = $row;
				if ( count( $out ) >= $limit ) {
					break;
				}
			}
		}
		return $out;
	}

	/** Bounded public search projection for File 26. */
	public static function search_public( $query, $limit = 10, $topic = '' ) {
		global $wpdb;
		$table = RSV_Helpers::table( 'reels' );
		$limit = min( 30, max( 1, absint( $limit ) ) );
		$query = sanitize_text_field( $query );
		$topic = RSV_Helpers::enum( $topic, RSV_Contracts::TOPICS, '' );
		$where = 'status=%s AND visibility=%s';
		$args  = array( 'published', 'public' );
		if ( '' !== $query ) {
			$like   = '%' . $wpdb->esc_like( $query ) . '%';
			$where .= ' AND (title LIKE %s OR caption LIKE %s OR topic LIKE %s)';
			$args[] = $like;
			$args[] = $like;
			$args[] = $like;
		}
		if ( $topic ) {
			$where .= ' AND topic=%s';
			$args[] = $topic;
		}
		$args[] = min( 120, $limit * 4 );
		$rows   = (array) $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table WHERE $where ORDER BY rank_score DESC,published_at DESC,id DESC LIMIT %d", $args ),
			ARRAY_A
		);
		$out = array();
		foreach ( $rows as $row ) {
			if ( RSV_Security::can_view_reel( $row, 0 ) ) {
				$out[] = $row;
				if ( count( $out ) >= $limit ) {
					break;
				}
			}
		}
		return $out;
	}
}
