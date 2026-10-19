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

    /** Only declared public source metadata may cross the File 16 context boundary. */
    private static function safe_context_row( $bucket, $row ) {
        $safe = array();
        foreach ( array( 'public_id','feature_id','object_type','edge_type','version','start_second','end_second','created_at','updated_at' ) as $key ) {
            if ( isset( $row[ $key ] ) && is_scalar( $row[ $key ] ) ) $safe[ $key ] = $row[ $key ];
        }
        if ( in_array( $bucket, array( 'citations','chapters' ), true ) ) {
            if ( 'citations' === $bucket ) {
                $safe['target_owner'] = RSV_Helpers::text( $row['target_owner'] ?? '', 30 );
                $safe['target_ref'] = RSV_Helpers::text( $row['target_ref'] ?? '', 255 );
            }
            if ( 'chapters' === $bucket ) {
                $p = (array) ( $row['payload'] ?? array() );
                $safe['payload'] = array( 'title'=>RSV_Helpers::text( $p['title'] ?? '', 255 ), 'summary'=>RSV_Helpers::textarea( $p['summary'] ?? '', 1000 ) );
            }
            return $safe;
        }
        $payload = (array) ( $row['payload'] ?? array() );
        if ( 'evidence' === $bucket ) {
            $owner = RSV_Helpers::text( $payload['source_owner'] ?? 'File 06', 30 );
            $safe['payload'] = array(
                'grade'=>RSV_Helpers::text( $payload['grade'] ?? '', 50 ),
                'source_owner'=>$owner,
                'source_refs'=>array_values( array_map( static function( $v ) { return RSV_Helpers::text( $v, 255 ); }, (array) ( $payload['source_refs'] ?? array() ) ) ),
                'claim_scope'=>RSV_Helpers::textarea( $payload['claim_scope'] ?? '', 1500 ),
                'limitations'=>RSV_Helpers::textarea( $payload['limitations'] ?? '', 1500 ),
            );
        } elseif ( 'knowledge_card' === $bucket ) {
            $points = array();
            foreach ( array_slice( (array) ( $payload['key_points'] ?? array() ), 0, 20 ) as $point ) {
                if ( is_scalar( $point ) ) $points[] = RSV_Helpers::text( $point, 500 );
            }
            $safe['payload'] = array(
                'summary'=>RSV_Helpers::textarea( $payload['summary'] ?? '', 2000 ),
                'key_points'=>$points,
                'difficulty'=>RSV_Helpers::text( $payload['difficulty'] ?? '', 30 ),
            );
            foreach ( array( 'remedy_refs','disease_refs','book_refs','source_refs' ) as $key ) {
                $safe['payload'][ $key ] = array_values( array_map( static function( $v ) { return RSV_Helpers::text( $v, 255 ); }, (array) ( $payload[ $key ] ?? array() ) ) );
            }
        }
        return $safe;
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
                $clean[] = self::safe_context_row( $bucket, $row );
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
                $clean[] = self::safe_context_row( $bucket, $row );
            }
            $context[ $bucket ] = $clean;
        }
        // Discard undeclared provider keys, even when external filters supplied them.
        $context = array(
            'reel' => RSV_Repository::public_dto( $reel ),
            'citations' => array_values( (array) ( $context['citations'] ?? array() ) ),
            'evidence' => array_values( (array) ( $context['evidence'] ?? array() ) ),
            'knowledge_card' => array_values( (array) ( $context['knowledge_card'] ?? array() ) ),
            'chapters' => array_values( (array) ( $context['chapters'] ?? array() ) ),
            'medical_authority' => 'education-only-no-diagnosis-prescription-dose-or-emergency-replacement',
        );
        $data['context'] = $context;
        $data['execute_ai'] = false;
        $response->set_data( $data );
        $response->header( 'Cache-Control', 'private, no-store, max-age=0' );
        $response->header( 'X-Robots-Tag', 'noindex, nofollow, noarchive' );
        return $response;
    }
}

RSV_Future30_AI_Context_Safety::register();
