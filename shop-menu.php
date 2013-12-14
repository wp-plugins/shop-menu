<?php
/*
 Plugin Name: Shop Menu
Plugin URI: http://residentbird.main.jp/bizplugin/
Description: 商品一覧、メニュー一覧を作成するプラグインです
Version: 1.0.1
Author:WordPress Biz Plugin
Author URI: http://residentbird.main.jp/bizplugin/
*/

if (!class_exists( 'WPAlchemy_MetaBox' ) ) {
	include_once( dirname(__FILE__) . "/wpalchemy/MetaBox.php" );
}
include_once( dirname(__FILE__) . "/admin-ui.php" );
new ShopMenu();


class SM{
	const SHORTCODE = "showshopmenu";
	const SHORTCODE_PRICE = "showprice";
	const OPTIONS = "shop_memu_options";

	public static function get_option(){
		return get_option(self::OPTIONS);
	}

	public static function update_option( $options ){
		if ( empty($options)){
			return;
		}
		update_option(self::OPTIONS, $options);
	}

	public static function enqueue_css_js(){
		wp_enqueue_style('shop-menu-style', plugins_url('shop-menu.css', __FILE__ ));
		wp_enqueue_script('shop-menu-js', plugins_url('next-page.js', __FILE__ ), array('jquery'));
	}

	public static function localize_js(){
		wp_localize_script( 'shop-menu-js', 'SM_Setting', array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'action' => 'get_menu_ajax',
				'next_page' => '1'
		));
	}

	public static function get_dummy_img_tag(){
		$imgpath = plugin_dir_url( __FILE__ ) . "image/noimage.png";
		return "<img src='{$imgpath}'>";
	}
}


class ShopMenu{

	var $adminUi;
	var $custom_metabox;

	public function __construct(){
		register_activation_hook(__FILE__, array(&$this,'on_activation'));
		add_action( 'init', array(&$this,'on_init') );
		add_action( 'admin_init', array(&$this,'on_admin_init') );
		add_action( 'admin_menu', array(&$this, 'on_admin_menu'));
		add_action( 'wp_enqueue_scripts', array(&$this,'on_enqueue_sctipts') );
		add_action( 'wp_ajax_get_menu_ajax', array(&$this,'get_menu_ajax') );
		add_action( 'wp_ajax_nopriv_get_menu_ajax', array(&$this,'get_menu_ajax') );
		add_shortcode( SM::SHORTCODE, array(&$this,'show_shortcode'));
		add_shortcode( SM::SHORTCODE_PRICE, array(&$this,'show_price'));
	}

	function init_custom_metabox(){
		$this->custom_metabox = $simple_mb = new WPAlchemy_MetaBox(array
				(
						'id' => '_shop_menu',
						'title' => 'Shop Menuデータ',
						'template' => dirname(__FILE__) . '/meta-view.php',
						'hide_editor' => false,
						'save_filter' => array( &$this, 'valid_custom_metabox') ,
						'types' => array('shop_menu'),
				));
	}

	function valid_custom_metabox($meta, $post_id) {
		$meta['price'] = mb_ereg_replace ("[^0-9]","", $meta['price']);
		if (empty($meta['price'])){
			$meta['price'] = 0;
		}
		return $meta;
	}

	function get_post_meta($post_id){
		return get_post_meta( $post_id, $this->custom_metabox->get_the_id(), true);
	}

	function on_activation() {
		$this->register_shop_menu();
		flush_rewrite_rules();

		/*
		 * option初期値設定
		*/
		$option = SM::get_option();
		if( $option ) {
			return;
		}
		$arr = array(
				"sm_show_price" => true,
				"sm_item_num" => 12,
				"sm_monetary_unit" => "円（税込）"
		);
		SM::update_option( $arr );
	}

	function on_init() {
		$this->init_custom_metabox();
		$this->register_shop_menu();
	}

	function register_shop_menu(){
		$labels = array(
				'name' => 'ShopMenu',
				'singular_name' => 'ShopMenu',
				'add_new_item' => '新規Menuを追加',
				'edit_item' => 'Menuを編集',
		);
		$supports = array('title', 'editor', 'thumbnail', 'revisions', 'page-attributes');
		$menu_setting = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => true,
				'capability_type' => 'page',
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => $supports,
				'has_archive' => true,
		);
		register_post_type( 'shop_menu', $menu_setting);
	}

	function on_enqueue_sctipts() {
		if ( is_admin() ) {
			return;
		}
		SM::enqueue_css_js();
		SM::localize_js();
	}

	function on_admin_init() {
		$this->adminUi = new SMAdminUi(__FILE__);
	}

	public function on_admin_menu() {
		add_submenu_page( "edit.php?post_type=shop_menu", "ShopMene設定", "ShopMene設定", 'administrator', __FILE__, array(&$this->adminUi, 'show_admin_page'));
	}

	/**
	 * shortcode
	 */
	function show_shortcode(){
		$info = new ShopMenuInfo( array(&$this, 'get_post_meta'));
		ob_start();
		include( dirname(__FILE__) . '/shop-menu-view.php');
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}

	function show_price(){
		$post = get_post( get_the_ID() );
		if (empty($post)){
			return "";
		}
		$post_meta = call_user_func( array(&$this, 'get_post_meta'), $post->ID );
		$shopitem = new ShopMenuItem($post, $post_meta);
		return $shopitem->price;
	}

	/**
	 * Ajax
	 */
	function get_menu_ajax(){
		$page = absint( $_REQUEST['page'] );
		if ( $page == 0){
			die();
		}
		$info = new ShopMenuInfo( array(&$this, 'get_post_meta'), $page );
		$info->isHidden = true;

		ob_start();
		include( dirname(__FILE__) . '/menu-list.php');
		$content = ob_get_contents();
		ob_end_clean();

		$charset = get_bloginfo( 'charset' );
		$next = $info->has_next ? $page + 1: null;
		$array = array( 'html' => $content, 'next_page' => $next );
		$json = json_encode( $array );
		nocache_headers();
		header( "Content-Type: application/json; charset=$charset" );
		echo $json;
		die();
	}
}


class ShopMenuInfo{
	var $items = array();
	var $has_next = false;
	var $show_price = true;
	var $isHidden = false;

	public function __construct( $callback, $page = 0){
		$options = SM::get_option();
		$this->show_price = $options['sm_show_price'];
		$item_num = $options['sm_item_num'];

		$condition = array();
		$condition['post_type'] = 'shop_menu';
		$condition['orderby'] = 'post_date';
		$condition['order'] = 'desc';
		$condition['numberposts'] = $item_num + 1;
		$condition['offset'] = $page * $item_num;

		$posts = get_posts( $condition );
		if ( !is_array($posts) ){
			return;
		}
		if ( count($posts) > $item_num){
			$this->has_next = true;
			array_pop ( $posts );
		}
		foreach($posts as $post){
			$post_meta = call_user_func( $callback, $post->ID );
			$this->items[] = new ShopMenuItem($post, $post_meta);
		}
	}
}

class ShopMenuItem{
	var $title;
	var $url;
	var $price;
	var $img_tag;

	public function __construct( $post, $post_meta ){
		$this->title = esc_html( $post->post_title );
		$this->url = get_permalink($post->ID);
		$this->price = $this->get_format_price( $post_meta['price']);
		$this->img_tag = $this->get_thumbnail( $post->ID );
	}

	private function get_format_price($price){
		$options = SM::get_option();
		return number_format( $price ) . $options['sm_monetary_unit'];
	}

	private function get_thumbnail($id){
		$img_tag = get_the_post_thumbnail( $id, array(125,125) );
		if ( !empty($img_tag) ){
			return $img_tag;
		}
		return SM::get_dummy_img_tag();
	}
}

?>