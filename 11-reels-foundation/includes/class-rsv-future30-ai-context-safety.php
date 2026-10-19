<?php
defined( 'ABSPATH' ) || exit;

/**
 * Minimize and revalidate every external source before the File 16 AI handoff.
 * Stored provider assertions do not establish current publication or consent.
 */
final class RSV_Future30_AI_Context_Safety {
    public static function register() {
        add_filter( 'rest_post_dispatch', array( __CLASS__, 'filter_response' ), 95, 3 );
    }

    private static function public_ref_valid( $owner, $ref, $purpose ) {
        $owner = RSV_Helpers::text( $owner, 30 );
        $ref = RSV_Helpers::text( $ref, 255 );
        if ( ! $owner || ! $ref ) return false;
        if ( 'File 11' === $owner ) {
            $target = RSV_Repository::find( $ref, true );
            return (bool) ( $target && RSV_Security::can_view_reel( $target, 0 ) );
        }
        return true === apply_filters( 'rsv_future30_public_ref_valid', false, $owner, $ref, sanitize_key( $purpose ) );
    }

    private static function source_object_valid( $bucket, $row ) {
        $payload = (array) ( $row['payload'] ?? array() );
        if ( 'evidence' === $bucket ) {
            $refs = (array) ( $payload['source_refs'] ?? array() );
            if ( ! $refs ) return false;
            $owner = RSV_Helpers::text( $payload['source_owner'] ?? 'File 06', 30 );
        } else {
            $refs = array_merge(
                (array) ( $payload['source_refs'] ?? array() ),
                (array) ( $payload['remedy_refs'] ?? array() ),
                (array) ( $payload['disease_refs'] ?? array() ),
                (array) ( $payload['book_refs'] ?? array() )
            );
            if ( ! $refs ) return false;
            $owner = 'File 06';
        }
        foreach ( $refs as $ref ) {
            if ( ! is_scalar( $ref ) || ! self::public_ref_valid( $owner, $ref, 'ai-grounded-context' ) ) return false;
        }
        return true;
    }

    private static function timed_row_valid( $row, $duration ) {
        $start = absint( $row['start_second'] ?? 0 );
        $end = absint( $row['end_second'] ?? 0 );
        return $duration >= 60 && $duration <= 600 && $start <= $duration
            && ( ! $end || ( $end >= $start && $end <= $duration ) );
    }

    public static function filter_response( $response, $server, $request ) {
        unset( $server );
        if ( ! ( $response instanceof WP_REST_Response )
            || 'GET' !== $request->get_method()
            || ! preg_match( '#^/rsv/v1/reels/(reel_[a-f0-9-]{36})/ai-context$#', $request->get_route(), $m ) ) return $response;
        $data = $response->get_data();
        if ( ! is_array( $data ) || ! isset( $data['context'] ) ) return $response;
        $reel = RSV_Repository::find( $m[1], true );
        if ( ! $reel || ! RSV_Security::can_view_reel( $reel ) ) return $response;
        $video = RSV_File10::video( absint( $reel['video_id'] ?? 0 ) );
        $duration = $video ? absint( $video['duration_seconds'] ?? 0 ) : 0;
        $context = (array) $data['context'];

        foreach ( array( 'evidence', 'knowledge_card' ) as $bucket ) {
            $clean = array();
            foreach ( (array) ( $context[ $bucket ] ?? array() ) as $row ) {
                if ( ! is_array( $row ) || ! self::source_object_valid( $bucket, $row ) ) continue;
                unset( $row['id'], $row['owner_id'], $row['reel_id'], $row['payload_json'] );
                $clean[] = $row;
            }
            $context[ $bucket ] = $clean;
        }

        foreach ( array( 'citations', 'chapters' ) as $bucket ) {
            $clean = array();
            foreach ( (array) ( $context[ $bucket ] ?? array() ) as $row ) {
                if ( ! is_array( $row ) || ! self::timed_row_valid( $row, $duration ) ) continue;
                if ( 'citations' === $bucket && ! self::public_ref_valid(
                    RSV_Helpers::text( $row['target_owner'] ?? '', 30 ),
                    RSV_Helpers::text( $row['target_ref'] ?? '', 255 ),
                    'ai-timestamp-citation'
                ) ) continue;
                unset( $row['id'], $row['owner_id'], $row['reel_id'], $row['source_reel_id'], $row['payload_json'] );
                $clean[] = $row;
            }
            $context[ $bucket ] = $clean;
        }
        $context['medical_authority'] = 'education-only-no-diagnosis-prescription-dose-or-emergency-replacement';
        $data['context'] = $context;
        $data['execute_ai'] = false;
        $response->set_data( $data );
        $response->header( 'Cache-Control', 'private, no-store, max-age=0' );
        $response->header( 'X-Robots-Tag', 'noindex, nofollow, noarchive' );
        return $response;
    }
}

RSV_Future30_AI_Context_Safety::register();
