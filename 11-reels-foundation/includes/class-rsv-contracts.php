<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Contracts {
	const API_NAMESPACE = 'rsv/v1';
	const EVENT_VERSION = 1;

	const CAP_SUBMIT = 'rsv_submit_reel';
	const CAP_PUBLISH = 'rsv_publish_reel';
	const CAP_MODERATE = 'rsv_moderate_reel';
	const CAP_MANAGE = 'rsv_manage_reels';
	const CAP_INSIGHTS = 'rsv_view_reel_insights';

	const REEL_STATES = array( 'draft', 'media_processing', 'review', 'published', 'restricted', 'removed', 'archived' );
	const REPORT_STATES = array( 'submitted', 'triaged', 'action', 'no_action', 'appealed', 'closed' );
	const VISIBILITIES = array( 'public', 'unlisted', 'member', 'entitled' );
	const INTERACTIONS = array( 'like', 'dislike', 'save' );
	const REPORT_REASONS = array( 'medical-claim', 'patient-privacy', 'harassment', 'copyright', 'spam', 'impersonation', 'other' );
	const MODERATION_DECISIONS = array( 'triage', 'restrict', 'remove', 'restore', 'no_action', 'close' );
	const SAFETY_LABELS = array( 'medical-education', 'patient-case', 'minor-sensitive', 'graphic-clinical', 'sponsored-disclosure', 'ai-assisted', 'correction', 'urgent-safety' );
	const TOPICS = array(
		'homeopathy-foundations', 'materia-medica', 'repertory', 'philosophy',
		'case-taking', 'clinical-learning', 'remedy-study', 'miasms', 'research',
		'public-health-education', 'platform-guidance',
	);

	const REQUIREMENTS = array(
		'F11-FR-001','F11-FR-002','F11-FR-003','F11-FR-004','F11-FR-005',
		'F11-FR-006','F11-FR-007','F11-FR-008','F11-FR-009','F11-FR-010',
		'F11-FR-011','F11-FR-012','F11-FR-013','F11-FR-014','F11-FR-015',
		'F11-NFR-001','F11-NFR-002','F11-NFR-003','F11-NFR-004','F11-NFR-005',
		'F11-NFR-006','F11-NFR-007','F11-NFR-008','F11-NFR-009','F11-NFR-010',
	);

	public static function event( $name ) {
		$name = preg_replace( '/[^A-Za-z0-9_.-]/', '', (string) $name );
		return $name . '.v' . self::EVENT_VERSION;
	}

	public static function routes() {
		return array(
			'reels' => '/reels/',
			'reel' => '/reel/{public_id}/{slug}/',
			'create' => '/reels/create/',
			'history' => '/account/reels/history/',
		);
	}
}
