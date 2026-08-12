<?php
defined( 'ABSPATH' ) || exit;

/**
 * Compatibility guard for the legacy migration lock takeover path.
 * It prevents an acquire_lock() caller that observed an expired option from
 * deleting a fresh lock installed by a concurrent process between read/delete.
 * Legitimate release_lock() deletion is not blocked.
 */
final class RSV_Migration_Lock_Guard {
	public static function register() {
		add_filter( 'pre_delete_option_' . RSV_Migration::LOCK_OPTION, array( __CLASS__, 'pre_delete' ), 10, 2 );
	}

	public static function pre_delete( $pre, $option ) {
		if ( null !== $pre || RSV_Migration::LOCK_OPTION !== $option ) return $pre;
		$from_acquire = false;
		$from_release = false;
		foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 ) as $frame ) {
			if ( 'RSV_Migration' !== ( $frame['class'] ?? '' ) ) continue;
			if ( 'acquire_lock' === ( $frame['function'] ?? '' ) ) $from_acquire = true;
			if ( 'release_lock' === ( $frame['function'] ?? '' ) ) $from_release = true;
		}
		if ( $from_release || ! $from_acquire ) return $pre;
		$current = (array) get_option( RSV_Migration::LOCK_OPTION, array() );
		if ( ! empty( $current['token'] ) && absint( $current['expires_at'] ?? 0 ) >= time() ) return false;
		return $pre;
	}
}

RSV_Migration_Lock_Guard::register();
