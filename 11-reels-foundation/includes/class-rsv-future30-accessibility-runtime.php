<?php
defined( 'ABSPATH' ) || exit;

/** Runtime bridge for Accessibility Plus preferences that require media behavior. */
final class RSV_Future30_Accessibility_Runtime {
	public static function register() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 35 );
	}

	public static function enqueue() {
		if ( ! is_user_logged_in() || ! wp_script_is( 'rsv', 'registered' ) ) return;
		$prefs = self::preferences( get_current_user_id() );
		$position = in_array( $prefs['captions_position'], array( 'top','bottom' ), true ) ? $prefs['captions_position'] : 'bottom';
		$description_url = '';
		$public_id = get_query_var( 'rsv_reel' );
		if ( $public_id && ! empty( $prefs['audio_description_preferred'] ) ) {
			$reel = RSV_Repository::find( $public_id, true );
			if ( $reel && RSV_Security::can_view_reel( $reel ) ) {
				$provider = apply_filters( 'rsv_file10_accessibility_track', null, absint( $reel['video_id'] ), array( 'audio_description'=>true, 'consumer'=>'file11', 'contract_version'=>RSV_CONTRACT_VERSION ) );
				if ( is_array( $provider ) && ! empty( $provider['audio_description_url'] ) ) $description_url = esc_url_raw( $provider['audio_description_url'], array( 'https' ) );
			}
		}
		wp_enqueue_script( 'rsv' );
		wp_localize_script( 'rsv', 'RSV_A11Y_MEDIA', array( 'captionPosition'=>$position, 'audioDescriptionUrl'=>$description_url ) );
		$js = <<<'JS'
(function(){
 function applyCuePosition(video){
   var top=(window.RSV_A11Y_MEDIA&&RSV_A11Y_MEDIA.captionPosition==='top');
   Array.prototype.forEach.call(video.textTracks||[],function(track){
     function position(){Array.prototype.forEach.call(track.cues||[],function(cue){try{cue.line=top?0:-1;cue.snapToLines=true;}catch(e){}});}
     position(); track.addEventListener&&track.addEventListener('cuechange',position);
   });
 }
 function applyAudioDescription(video){
   var url=window.RSV_A11Y_MEDIA&&RSV_A11Y_MEDIA.audioDescriptionUrl;
   if(!url||video.querySelector('track[data-rsv-audio-description]'))return;
   var track=document.createElement('track');track.kind='descriptions';track.src=url;track.default=true;track.setAttribute('data-rsv-audio-description','1');video.appendChild(track);
 }
 function apply(){document.querySelectorAll('video').forEach(function(video){applyCuePosition(video);applyAudioDescription(video);});}
 document.addEventListener('DOMContentLoaded',apply);document.addEventListener('rsv:reel:changed',apply);
})();
JS;
		wp_add_inline_script( 'rsv', $js, 'after' );
	}

	private static function preferences( $user_id ) {
		$defaults = array( 'captions_position'=>'bottom', 'audio_description_preferred'=>false );
		global $wpdb;
		$table = RSV_Helpers::table( 'future_user_state' );
		$row = $wpdb->get_var( $wpdb->prepare( "SELECT payload_json FROM $table WHERE user_id=%d AND feature_id=%s AND object_ref=%s AND state_key=%s LIMIT 1", absint($user_id), 'F11-FUT-029', 'user', 'accessibility' ) );
		if ( ! $row ) return $defaults;
		$prefs = (array) RSV_Helpers::json_decode( $row, $defaults );
		return array( 'captions_position'=>sanitize_key( $prefs['captions_position'] ?? 'bottom' ), 'audio_description_preferred'=>! empty( $prefs['audio_description_preferred'] ) );
	}
}

RSV_Future30_Accessibility_Runtime::register();
