<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Contracts {
	const API_NAMESPACE = 'rsv/v1';
	const EVENT_VERSION = 4;

	const CAP_SUBMIT   = 'rsv_submit_reel';
	const CAP_PUBLISH  = 'rsv_publish_reel';
	const CAP_MODERATE = 'rsv_moderate_reel';
	const CAP_MANAGE   = 'rsv_manage_reels';
	const CAP_INSIGHTS = 'rsv_view_reel_insights';

	const REEL_STATES   = array( 'draft', 'media_processing', 'review', 'published', 'restricted', 'removed', 'archived' );
	const REPORT_STATES = array( 'submitted', 'triaged', 'action', 'no_action', 'appealed', 'closed' );
	const VISIBILITIES  = array( 'public', 'unlisted', 'member', 'entitled' );
	const INTERACTIONS  = array( 'like', 'dislike', 'save' );

	/** Founder-approved canonical report routing taxonomy (CV-254). */
	const REPORT_REASONS = array( 'harm', 'false-claim', 'impersonation', 'privacy', 'abuse', 'copyright', 'scam', 'child-safety', 'other' );
	/** Historical values remain readable for migrated RC6 records, but new writes use REPORT_REASONS. */
	const LEGACY_REPORT_REASONS = array( 'medical-claim', 'patient-privacy', 'harassment', 'spam' );
	const RISK_TIERS = array( 'low', 'medium', 'high', 'critical' );
	const MODERATION_DECISIONS = array( 'no_action', 'restrict', 'remove', 'close', 'restore' );

	const STORY_TYPES = array( 'announcement', 'event-reminder', 'clinic-update', 'course-prompt', 'status' );
	const STORY_STATES = array( 'draft', 'review', 'published', 'expired', 'restricted', 'removed', 'archived' );
	const RESPONSE_STATES = array( 'review', 'published', 'restricted', 'removed' );
	const VALUE_SIGNALS = array( 'source-open', 'share', 'natural-stop' );

	const TOPICS = array(
		'homeopathy-foundations',
		'materia-medica',
		'repertory',
		'philosophy',
		'case-taking',
		'clinical-learning',
		'remedy-study',
		'miasms',
		'research',
		'public-health-education',
		'platform-guidance',
	);

	const REQUIREMENTS = array(
		'F11-FR-001','F11-FR-002','F11-FR-003','F11-FR-004','F11-FR-005',
		'F11-FR-006','F11-FR-007','F11-FR-008','F11-FR-009','F11-FR-010',
		'F11-FR-011','F11-FR-012','F11-FR-013','F11-FR-014','F11-FR-015',
		'F11-NFR-001','F11-NFR-002','F11-NFR-003','F11-NFR-004','F11-NFR-005',
		'F11-NFR-006','F11-NFR-007','F11-NFR-008','F11-NFR-009','F11-NFR-010',
	);

	const TOP20_REQUIREMENTS = array(
		'CV-119','CV-120','CV-121','CV-122','CV-123','CV-124',
		'CV-125','CV-126','CV-127','CV-128','CV-129',
	);

	/**
	 * Cross-cutting catalogue transferred into the rewritten File 11 plan.
	 * Presence here establishes consumer/assurance traceability; it does not
	 * duplicate the canonical owner of global shell, security, operations, etc.
	 */
	const CROSS_CUTTING_REQUIREMENTS = array(
		'CV-239','CV-240','CV-241','CV-242','CV-243','CV-244','CV-245','CV-246','CV-247','CV-248','CV-249',
		'CV-250','CV-251','CV-252','CV-253','CV-254','CV-255','CV-256','CV-257','CV-258','CV-259','CV-260','CV-261',
		'CV-262','CV-263','CV-264','CV-265','CV-266','CV-267','CV-268','CV-269','CV-270','CV-271','CV-272','CV-273',
		'CV-274','CV-275','CV-276','CV-277','CV-278','CV-279','CV-280','CV-281','CV-282','CV-283','CV-284','CV-285',
	);

	const FILE_CENTRAL_REQUIREMENTS = array( 'F11-CEN-01' );
	const ACCEPTANCE_JOURNEYS = array( 'AJ-17','AJ-24','AJ-25','AJ-31','AJ-32','AJ-33','AJ-34','AJ-35','AJ-36','AJ-37','AJ-38','AJ-39','AJ-40' );

	public static function all_requirements() {
		return array_values( array_unique( array_merge( self::REQUIREMENTS, self::TOP20_REQUIREMENTS, self::CROSS_CUTTING_REQUIREMENTS, self::FILE_CENTRAL_REQUIREMENTS ) ) );
	}

	public static function normalize_report_reason( $value ) {
		$value = sanitize_key( (string) $value );
		$legacy = array(
			'medical-claim'   => 'false-claim',
			'patient-privacy' => 'privacy',
			'harassment'      => 'abuse',
			'spam'            => 'scam',
		);
		$value = $legacy[ $value ] ?? $value;
		return in_array( $value, self::REPORT_REASONS, true ) ? $value : '';
	}

	public static function event( $name ) {
		return sanitize_key( $name ) . '.v' . self::EVENT_VERSION;
	}
}
