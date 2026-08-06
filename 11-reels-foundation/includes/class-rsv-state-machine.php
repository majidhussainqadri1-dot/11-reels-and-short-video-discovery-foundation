<?php
defined( 'ABSPATH' ) || exit;

final class RSV_State_Machine {
	private static $reel = array(
		'draft'=>array('media_processing','review','archived'),
		'media_processing'=>array('review','restricted','removed'),
		'review'=>array('published','restricted','removed','draft'),
		'published'=>array('restricted','removed','archived'),
		'restricted'=>array('published','removed','archived','review'),
		'removed'=>array('restricted','archived'),
		'archived'=>array('draft'),
	);
	private static $report = array(
		'submitted'=>array('triaged','closed'),
		'triaged'=>array('action','no_action','closed'),
		'action'=>array('appealed','closed'),
		'no_action'=>array('appealed','closed'),
		'appealed'=>array('triaged','action','no_action','closed'),
		'closed'=>array(),
	);
	public static function assert( $from,$to,$machine='reel' ) {
		$from=sanitize_key($from);$to=sanitize_key($to);$map='report'===$machine?self::$report:self::$reel;
		if(!isset($map[$from])||!in_array($to,$map[$from],true))return RSV_Helpers::error('rsv_invalid_transition',__('The requested state change is not allowed.',RSV_TEXT_DOMAIN),409,array('machine'=>$machine,'from'=>$from,'to'=>$to));
		return true;
	}
	public static function allowed($from,$to,$machine='reel'){return true===self::assert($from,$to,$machine);}
}
