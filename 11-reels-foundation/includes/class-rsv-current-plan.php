<?php
defined( 'ABSPATH' ) || exit;

/**
 * Founder-approved 2026-08-11 governing-plan alignment for File 11.
 * Native File 11 duties are implemented here; cross-platform owners remain authoritative.
 */
final class RSV_Current_Plan {
	const REVISION = '2026-08-11';

	public function register() {
		add_filter( 'do_shortcode_tag', array( __CLASS__, 'enhance_shortcode' ), 40, 4 );
		add_action( 'get_footer', array( __CLASS__, 'single_reel_safety' ), 2 );
		add_filter( 'rsv_provider_manifest', array( __CLASS__, 'provider_manifest' ), 45 );
		add_filter( 'rsv_current_plan_requirements', array( __CLASS__, 'requirements_manifest' ) );
		add_filter( 'rsv_content_risk_profile', array( __CLASS__, 'risk_profile' ), 10, 2 );
		add_action( 'init', array( __CLASS__, 'announce_governance' ), 96 );
	}

	public static function requirements_manifest( $manifest = array() ) {
		$manifest = is_array( $manifest ) ? $manifest : array();
		$manifest['file11'] = array(
			'plan_revision' => self::REVISION,
			'native'        => array_merge( RSV_Contracts::REQUIREMENTS, RSV_Contracts::TOP20_REQUIREMENTS, RSV_Contracts::FILE_CENTRAL_REQUIREMENTS ),
			'cross_cutting' => RSV_Contracts::CROSS_CUTTING_REQUIREMENTS,
			'acceptance'    => RSV_Contracts::ACCEPTANCE_JOURNEYS,
			'owners'        => array(
				'raw_media'       => 'File 10',
				'identity_roles'   => 'File 00',
				'notifications'    => 'File 19',
				'shell'            => 'File 20',
				'visual_system'    => 'File 25',
				'global_discovery' => 'File 26',
				'assurance'        => 'File 24',
				'reels_domain'     => 'File 11',
			),
		);
		return $manifest;
	}

	public static function provider_manifest( $manifest ) {
		if ( is_array( $manifest ) && isset( $manifest['provider_id'] ) && 'file11-reels' === ( $manifest['provider_id'] ?? '' ) ) {
			$manifest['governing_plan_revision'] = self::REVISION;
			$manifest['requirement_manifest']     = self::requirements_manifest( array() )['file11'];
			$manifest['commercial_ranking']       = 'none';
			$manifest['donor_advantage']           = false;
			$manifest['medical_authority']         = 'education-only-no-diagnosis-prescription-dose-or-emergency-replacement';
			return $manifest;
		}
		if ( is_array( $manifest ) && isset( $manifest['file11-reels'] ) && is_array( $manifest['file11-reels'] ) ) {
			$manifest['file11-reels'] = self::provider_manifest( $manifest['file11-reels'] );
		}
		return $manifest;
	}

	public static function announce_governance() {
		do_action( 'rsv_governing_plan_registered', self::REVISION, self::requirements_manifest( array() )['file11'], RSV_CONTRACT_VERSION );
		do_action( 'sabri_platform_governing_plan_registered', 'file11', self::REVISION, RSV_CONTRACT_VERSION );
	}

	public static function enhance_shortcode( $output, $tag, $attr, $match ) {
		unset( $attr, $match );
		if ( in_array( $tag, array( 'rsv_reels', 'rsv_create' ), true ) ) $output .= self::safety_charter();
		return $output;
	}

	public static function single_reel_safety() {
		if ( ! get_query_var( 'rsv_reel' ) ) return;
		$reel = RSV_Repository::find( get_query_var( 'rsv_reel' ), true );
		if ( ! $reel || ! RSV_Security::can_view_reel( $reel ) ) return;
		echo wp_kses_post( self::safety_charter() );
	}

	public static function safety_charter() {
		$guidance = self::safe_guidance_url( apply_filters( 'rsv_verified_emergency_guidance_url', '', get_locale() ) );
		$html  = '<aside class="rsv-medical-safety" role="note" aria-label="' . esc_attr__( 'Medical safety', RSV_TEXT_DOMAIN ) . '">';
		$html .= '<strong>' . esc_html__( 'Educational content only.', RSV_TEXT_DOMAIN ) . '</strong> ';
		$html .= esc_html__( 'Reels do not provide autonomous diagnosis, prescription, dose selection, or emergency-care replacement.', RSV_TEXT_DOMAIN );
		$html .= ' ' . esc_html__( 'If symptoms or intent may be urgent, seek verified local emergency or qualified professional care without delay.', RSV_TEXT_DOMAIN );
		if ( $guidance ) $html .= ' <a rel="noopener noreferrer" href="' . esc_url( $guidance ) . '">' . esc_html__( 'Open verified local emergency guidance', RSV_TEXT_DOMAIN ) . '</a>';
		$accessibility = self::safe_guidance_url( apply_filters( 'rsv_accessibility_statement_url', '' ) );
		if ( $accessibility ) $html .= ' <a rel="noopener noreferrer" href="' . esc_url( $accessibility ) . '">' . esc_html__( 'Accessibility statement', RSV_TEXT_DOMAIN ) . '</a>';
		return $html . '</aside>';
	}

	private static function safe_guidance_url( $value ) {
		$url = esc_url_raw( (string) $value, array( 'http', 'https' ) );
		if ( ! $url ) return '';
		$parts = wp_parse_url( $url );
		return empty( $parts['host'] ) ? '' : $url;
	}

	public static function risk_profile( $profile, $reason ) {
		$reason = RSV_Contracts::normalize_report_reason( $reason );
		if ( ! $reason ) $reason = 'other';
		$defaults = array(
			'child-safety'  => array( 'tier' => 'critical', 'sla' => 'immediate', 'expert' => 'child-safety' ),
			'harm'          => array( 'tier' => 'critical', 'sla' => 'immediate', 'expert' => 'medical-safety' ),
			'false-claim'   => array( 'tier' => 'high', 'sla' => 'priority', 'expert' => 'medical-safety' ),
			'impersonation' => array( 'tier' => 'high', 'sla' => 'priority', 'expert' => 'identity-safety' ),
			'privacy'       => array( 'tier' => 'high', 'sla' => 'priority', 'expert' => 'privacy' ),
			'scam'          => array( 'tier' => 'high', 'sla' => 'priority', 'expert' => 'fraud-safety' ),
			'abuse'         => array( 'tier' => 'medium', 'sla' => 'standard', 'expert' => 'moderation' ),
			'copyright'     => array( 'tier' => 'medium', 'sla' => 'standard', 'expert' => 'copyright' ),
			'other'         => array( 'tier' => 'low', 'sla' => 'standard', 'expert' => 'moderation' ),
		);
		$base = $defaults[ $reason ];
		if ( is_array( $profile ) ) $base = array_merge( $base, $profile );
		if ( in_array( $reason, array( 'child-safety', 'harm' ), true ) ) $base['tier'] = 'critical';
		$base['tier'] = RSV_Helpers::enum( $base['tier'] ?? '', RSV_Contracts::RISK_TIERS, 'high' );
		return $base;
	}

	public static function report_risk_profile( $reason ) {
		return (array) apply_filters( 'rsv_content_risk_profile', array(), $reason );
	}
}
