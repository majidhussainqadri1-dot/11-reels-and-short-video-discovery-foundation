<?php
defined( 'ABSPATH' ) || exit;

final class RSV_Migration {
	public static function lock($name,$ttl=300){$key='rsv_lock_'.sanitize_key($name);$now=time();$existing=(int)get_option($key,0);if($existing>$now)return false;if(!add_option($key,$now+$ttl,'','no')){if($existing&&$existing<=$now)update_option($key,$now+$ttl,false);else return false;}return true;}
	public static function unlock($name){delete_option('rsv_lock_'.sanitize_key($name));}
	public static function upgrade(){if(get_option('rsv_schema_version')===RSV_SCHEMA_VERSION)return true;if(!self::lock('schema',300))return false;try{RSV_DB::install();self::ensure_pages();flush_rewrite_rules(false);RSV_Helpers::audit('system',0,'schema_upgrade','',RSV_SCHEMA_VERSION);return true;}finally{self::unlock('schema');}}
	public static function ensure_pages(){
		$map=array(
			'reels'=>array('title'=>__('Reels',RSV_TEXT_DOMAIN),'slug'=>'reels','shortcode'=>'[rsv_reels]','parent'=>0),
			'reels_create'=>array('title'=>__('Create Reel',RSV_TEXT_DOMAIN),'slug'=>'create','shortcode'=>'[rsv_reels_create]','parent'=>'reels'),
			'account'=>array('title'=>__('Account',RSV_TEXT_DOMAIN),'slug'=>'account','shortcode'=>'','parent'=>0),
			'account_reels'=>array('title'=>__('Reels',RSV_TEXT_DOMAIN),'slug'=>'reels','shortcode'=>'','parent'=>'account'),
			'reels_history'=>array('title'=>__('Reel History',RSV_TEXT_DOMAIN),'slug'=>'history','shortcode'=>'[rsv_reels_history]','parent'=>'account_reels'),
		);
		$ids=get_option('rsv_pages',array());
		foreach($map as $key=>$config){$parent=is_string($config['parent'])?(int)($ids[$config['parent']]??0):(int)$config['parent'];$path=$parent?trim(get_page_uri($parent),'/').'/'.$config['slug']:$config['slug'];$page=get_page_by_path($path,OBJECT,'page');if($page&&!self::managed_page($page,$config['shortcode'])){$config['slug'].='-sabri';$page=null;}if(!$page){$id=wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>$config['title'],'post_name'=>$config['slug'],'post_parent'=>$parent,'post_content'=>$config['shortcode']));if(is_wp_error($id))continue;$page=get_post($id);}elseif($config['shortcode']&&!has_shortcode($page->post_content,trim($config['shortcode'],'[]'))){wp_update_post(array('ID'=>$page->ID,'post_content'=>$config['shortcode']));}$ids[$key]=(int)$page->ID;update_post_meta($page->ID,'_rsv_managed_page',1);}
		update_option('rsv_pages',$ids,false);return $ids;
	}
	private static function managed_page($page,$shortcode){return get_post_meta($page->ID,'_rsv_managed_page',true)||(!$shortcode||has_shortcode($page->post_content,trim($shortcode,'[]')));}
	public static function legacy_dry_run(){global $wpdb;$legacy=$wpdb->prefix.'srl_history';$exists=$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$legacy))===$legacy;return array('legacy_table'=>$exists,'legacy_rows'=>$exists?(int)$wpdb->get_var("SELECT COUNT(*) FROM $legacy"):0,'target_schema'=>RSV_SCHEMA_VERSION,'action'=>'dry-run-only-until-explicit-cutover');}
	public static function routes_health(){ $ids=(array)get_option('rsv_pages',array());$out=array();foreach($ids as $key=>$id)$out[$key]=(bool)get_post($id);return $out; }
}
