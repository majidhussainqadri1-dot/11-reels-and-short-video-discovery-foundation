<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Frontend {
	public function register() {
		add_shortcode('rsv_reels',array($this,'feed_shortcode'));
		add_shortcode('rsv_reels_create',array($this,'create_shortcode'));
		add_shortcode('rsv_reels_history',array($this,'history_shortcode'));
		add_action('wp_enqueue_scripts',array($this,'assets'));
		add_action('init',array($this,'rewrites'));
		add_filter('query_vars',array($this,'query_vars'));
		add_action('template_redirect',array($this,'single_route'));
		add_filter('rsv_routes',array($this,'register_routes'));
	}
	public function rewrites(){add_rewrite_rule('^reel/([^/]+)(?:/([^/]+))?/?$','index.php?rsv_reel=$matches[1]','top');}
	public function query_vars($vars){$vars[]='rsv_reel';return $vars;}
	public function register_routes($routes){return array_merge((array)$routes,RSV_Contracts::routes());}
	public function assets(){
		if(!$this->is_route())return;
		wp_enqueue_style('rsv',RSV_URL.'assets/css/rsv.css',array(),RSV_VERSION);
		wp_enqueue_script('rsv',RSV_URL.'assets/js/rsv.js',array(),RSV_VERSION,true);
		wp_localize_script('rsv','RSV',array(
			'root'=>esc_url_raw(rest_url(RSV_Contracts::API_NAMESPACE)),'nonce'=>wp_create_nonce('wp_rest'),'loggedIn'=>is_user_logged_in(),
			'homeUrl'=>home_url('/'),'reelsUrl'=>home_url('/reels/'),'reducedMotion'=>false,
			'i18n'=>array('error'=>__('Something went wrong. Please retry.',RSV_TEXT_DOMAIN),'login'=>__('Sign in to use this action.',RSV_TEXT_DOMAIN),'saved'=>__('Action saved.',RSV_TEXT_DOMAIN),'reportSubmitted'=>__('Report submitted.',RSV_TEXT_DOMAIN),'loadMore'=>__('Load more Reels',RSV_TEXT_DOMAIN),'pause'=>__('Pause feed',RSV_TEXT_DOMAIN),'resume'=>__('Resume feed',RSV_TEXT_DOMAIN),'offline'=>__('You are offline. Public content may be limited.',RSV_TEXT_DOMAIN),'break'=>__('You have viewed several Reels. Take a short break when helpful.',RSV_TEXT_DOMAIN))
		));
	}
	private function is_route(){
		global $post;
		if(get_query_var('rsv_reel'))return true;
		return $post && (has_shortcode($post->post_content,'rsv_reels')||has_shortcode($post->post_content,'rsv_reels_create')||has_shortcode($post->post_content,'rsv_reels_history'));
	}
	private function nav(){return '<nav class="rsv-local-nav" aria-label="'.esc_attr__('Page navigation',RSV_TEXT_DOMAIN).'"><button type="button" data-rsv-back aria-label="'.esc_attr__('Back',RSV_TEXT_DOMAIN).'">← <span>'.esc_html__('Back',RSV_TEXT_DOMAIN).'</span></button><a href="'.esc_url(home_url('/')).'">⌂ <span>'.esc_html__('Home',RSV_TEXT_DOMAIN).'</span></a><a href="'.esc_url(home_url('/reels/')).'">▦ <span>'.esc_html__('Reels',RSV_TEXT_DOMAIN).'</span></a></nav>';}

	public function feed_shortcode(){
		$topic=isset($_GET['topic'])?sanitize_key(wp_unslash($_GET['topic'])):'';$sort=isset($_GET['sort'])?sanitize_key(wp_unslash($_GET['sort'])):'rank';$result=RSV_Repository::feed(array('limit'=>8,'topic'=>$topic,'sort'=>$sort));
		ob_start();?>
		<main class="rsv-shell" data-rsv-app>
			<?php echo $this->nav(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<header class="rsv-hero"><div><p class="rsv-eyebrow"><?php esc_html_e('Educational short videos',RSV_TEXT_DOMAIN);?></p><h1><?php esc_html_e('Reels',RSV_TEXT_DOMAIN);?></h1><p><?php esc_html_e('One to ten minute educational videos with safety, captions and privacy controls.',RSV_TEXT_DOMAIN);?></p></div>
			<div class="rsv-feed-controls"><button type="button" data-rsv-prev>↑ <span><?php esc_html_e('Previous',RSV_TEXT_DOMAIN);?></span></button><button type="button" data-rsv-pause><?php esc_html_e('Pause feed',RSV_TEXT_DOMAIN);?></button><button type="button" data-rsv-next>↓ <span><?php esc_html_e('Next',RSV_TEXT_DOMAIN);?></span></button></div></header>
			<p class="rsv-wellbeing"><?php esc_html_e('No streaks, shame or forced autoplay. You control playback and can pause at any time.',RSV_TEXT_DOMAIN);?></p>
			<div class="rsv-status" role="status" aria-live="polite"></div>
			<section class="rsv-feed" data-rsv-feed data-next-cursor="<?php echo esc_attr($result['next_cursor']??'');?>" data-sort="<?php echo esc_attr($sort);?>" data-topic="<?php echo esc_attr($topic);?>" aria-label="<?php esc_attr_e('Reels feed',RSV_TEXT_DOMAIN);?>">
			<?php foreach($result['items'] as $item) echo self::card($item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>
			<?php if(!empty($result['next_cursor'])):?><button class="rsv-load-more" type="button" data-rsv-load-more><?php esc_html_e('Load more Reels',RSV_TEXT_DOMAIN);?></button><?php endif;?>
			<?php echo self::report_dialog(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</main><?php return ob_get_clean();
	}

	public static function card($item){
		ob_start();?><article class="rsv-reel" data-rsv-reel="<?php echo esc_attr($item['id']);?>" data-duration="<?php echo absint($item['duration_seconds']);?>" tabindex="-1" aria-labelledby="rsv-title-<?php echo esc_attr($item['id']);?>">
			<div class="rsv-media" data-rsv-media data-cover="<?php echo esc_url($item['cover_url']);?>"><button type="button" class="rsv-play-placeholder" data-rsv-load-media aria-label="<?php echo esc_attr(sprintf(__('Load and play %s',RSV_TEXT_DOMAIN),$item['title']));?>"><?php if($item['cover_url']):?><img src="<?php echo esc_url($item['cover_url']);?>" alt="" loading="lazy"><?php endif;?><span>▶</span></button></div>
			<div class="rsv-copy"><p class="rsv-topic"><?php echo esc_html($item['topic']);?></p><h2 id="rsv-title-<?php echo esc_attr($item['id']);?>"><a href="<?php echo esc_url($item['url']);?>"><?php echo esc_html($item['title']);?></a></h2><p><?php echo esc_html($item['caption']);?></p>
			<p class="rsv-author"><a href="<?php echo esc_url($item['owner']['profile_url']);?>"><?php echo esc_html($item['owner']['name']);?></a> <span><?php echo esc_html($item['owner']['label']);?></span></p>
			<div class="rsv-actions" role="group" aria-label="<?php esc_attr_e('Reel actions',RSV_TEXT_DOMAIN);?>"><button type="button" data-rsv-action="like" aria-pressed="false">👍 <span><?php esc_html_e('Like',RSV_TEXT_DOMAIN);?></span></button><button type="button" data-rsv-action="dislike" aria-pressed="false">👎 <span><?php esc_html_e('Dislike',RSV_TEXT_DOMAIN);?></span></button><button type="button" data-rsv-action="save" aria-pressed="false">🔖 <span><?php esc_html_e('Save',RSV_TEXT_DOMAIN);?></span></button><button type="button" data-rsv-share>↗ <span><?php esc_html_e('Share',RSV_TEXT_DOMAIN);?></span></button><button type="button" data-rsv-report>⚑ <span><?php esc_html_e('Report',RSV_TEXT_DOMAIN);?></span></button></div>
			<div class="rsv-context-links"><?php if(!empty($item['comments_url'])):?><a href="<?php echo esc_url($item['comments_url']);?>"><?php esc_html_e('Comments',RSV_TEXT_DOMAIN);?></a><?php endif;?><?php if(!empty($item['owner']['follow_url'])):?><a href="<?php echo esc_url($item['owner']['follow_url']);?>"><?php esc_html_e('Follow',RSV_TEXT_DOMAIN);?></a><?php endif;?><?php if(!empty($item['video_url'])):?><a href="<?php echo esc_url($item['video_url']);?>"><?php esc_html_e('Full video page',RSV_TEXT_DOMAIN);?></a><?php endif;?></div>
			<div class="rsv-item-status" role="status" aria-live="polite"></div></div>
		</article><?php return ob_get_clean();
	}

	private static function report_dialog(){ob_start();?><dialog class="rsv-report-dialog" data-rsv-report-dialog><form method="dialog" data-rsv-report-form><h2><?php esc_html_e('Report Reel',RSV_TEXT_DOMAIN);?></h2><label><?php esc_html_e('Reason',RSV_TEXT_DOMAIN);?><select name="reason" required><?php foreach(RSV_Contracts::REPORT_REASONS as $reason):?><option value="<?php echo esc_attr($reason);?>"><?php echo esc_html(ucwords(str_replace('-',' ',$reason)));?></option><?php endforeach;?></select></label><label><?php esc_html_e('Details',RSV_TEXT_DOMAIN);?><textarea name="details" maxlength="4000"></textarea></label><div class="rsv-dialog-actions"><button value="cancel" type="button" data-rsv-report-cancel><?php esc_html_e('Cancel',RSV_TEXT_DOMAIN);?></button><button value="submit" class="rsv-button" type="submit"><?php esc_html_e('Submit report',RSV_TEXT_DOMAIN);?></button></div><p class="rsv-report-status" role="status" aria-live="polite"></p></form></dialog><?php return ob_get_clean();}

	public function create_shortcode(){
		RSV_Helpers::no_cache_private();if(!RSV_Security::can(RSV_Contracts::CAP_SUBMIT,null,'create_page'))return '<p>'.esc_html__('A verified and authorized doctor account is required.',RSV_TEXT_DOMAIN).'</p>';
		ob_start();?><main class="rsv-shell rsv-private"><?php echo $this->nav(); // phpcs:ignore?><h1><?php esc_html_e('Create a Reel',RSV_TEXT_DOMAIN);?></h1><p><?php esc_html_e('Choose a File 10 video with an authoritative duration between 60 and 600 seconds.',RSV_TEXT_DOMAIN);?></p><?php if(!RSV_File10::ready()):?><div class="rsv-error"><?php esc_html_e('File 10 is unavailable. Creation is safely disabled.',RSV_TEXT_DOMAIN);?></div><?php else:?><form class="rsv-form" data-rsv-create><label><?php esc_html_e('File 10 video ID',RSV_TEXT_DOMAIN);?><input name="video_id" type="number" min="1" required></label><label><?php esc_html_e('Title',RSV_TEXT_DOMAIN);?><input name="title" maxlength="255" required></label><label><?php esc_html_e('Caption',RSV_TEXT_DOMAIN);?><textarea name="caption" maxlength="4000" required></textarea></label><label><?php esc_html_e('Topic',RSV_TEXT_DOMAIN);?><select name="topic" required><?php foreach(RSV_Contracts::TOPICS as $topic):?><option value="<?php echo esc_attr($topic);?>"><?php echo esc_html(ucwords(str_replace('-',' ',$topic)));?></option><?php endforeach;?></select></label><label><?php esc_html_e('Language',RSV_TEXT_DOMAIN);?><input name="language" value="en-US" required></label><label><?php esc_html_e('Cover attachment ID',RSV_TEXT_DOMAIN);?><input name="cover_id" type="number" min="1" required></label><label><?php esc_html_e('Visibility',RSV_TEXT_DOMAIN);?><select name="visibility"><?php foreach(RSV_Contracts::VISIBILITIES as $visibility):?><option value="<?php echo esc_attr($visibility);?>"><?php echo esc_html(ucfirst($visibility));?></option><?php endforeach;?></select></label><label><?php esc_html_e('Disclosure',RSV_TEXT_DOMAIN);?><input name="disclosure" maxlength="500"></label><button class="rsv-button" type="submit"><?php esc_html_e('Create draft',RSV_TEXT_DOMAIN);?></button><p class="rsv-form-status" role="status" aria-live="polite"></p></form><?php endif;?></main><?php return ob_get_clean();
	}
	public function history_shortcode(){RSV_Helpers::no_cache_private();if(!is_user_logged_in())return '<p>'.esc_html__('Sign in to view private Reel history.',RSV_TEXT_DOMAIN).'</p>';$rows=RSV_Repository::history(get_current_user_id(),100,0);ob_start();?><main class="rsv-shell rsv-private"><?php echo $this->nav(); // phpcs:ignore?><header class="rsv-hero"><div><h1><?php esc_html_e('Your Reel history',RSV_TEXT_DOMAIN);?></h1><p><?php esc_html_e('Private progress is not public or searchable.',RSV_TEXT_DOMAIN);?></p></div><button type="button" data-rsv-clear-history><?php esc_html_e('Clear history',RSV_TEXT_DOMAIN);?></button></header><div class="rsv-history"><?php if(!$rows):?><p><?php esc_html_e('No Reel history yet.',RSV_TEXT_DOMAIN);?></p><?php else:foreach($rows as $row):?><article><h2><?php if($row['url']):?><a href="<?php echo esc_url($row['url']);?>"><?php echo esc_html($row['title']);?></a><?php else:echo esc_html($row['title']);endif;?></h2><p><?php echo esc_html(sprintf(__('%1$d seconds • %2$s',RSV_TEXT_DOMAIN),$row['progress']['position_seconds'],$row['progress']['completed']?__('Completed',RSV_TEXT_DOMAIN):__('In progress',RSV_TEXT_DOMAIN)));?></p></article><?php endforeach;endif;?></div><div class="rsv-status" role="status" aria-live="polite"></div></main><?php return ob_get_clean();}

	public function single_route(){
		$public_id=get_query_var('rsv_reel');if(!$public_id)return;$reel=RSV_Repository::find($public_id,true);if(!$reel||!RSV_Access::can_view($reel,'single')){status_header(404);nocache_headers();wp_die(esc_html__('Reel not found.',RSV_TEXT_DOMAIN),esc_html__('Not found',RSV_TEXT_DOMAIN),array('response'=>404));}
		RSV_Helpers::no_cache_private();$dto=RSV_Repository::public_dto($reel,RSV_Security::can(RSV_Contracts::CAP_MANAGE,$reel,'single'));
		get_header();echo '<main class="rsv-shell rsv-single" data-rsv-app>'.$this->nav().'<h1>'.esc_html($dto['title']).'</h1><div class="rsv-feed" data-rsv-feed>'.self::card($dto).'</div>'.self::report_dialog().'</main>';get_footer();exit;
	}
}
