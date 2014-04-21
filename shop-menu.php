<?php
/*
 Plugin Name: Shop Menu
Plugin URI: http://residentbird.main.jp/bizplugin/
Description: 商品一覧、メニュー一覧を作成するプラグインです
Version: 1.3.0
Author:WordPress Biz Plugin
Author URI: http://residentbird.main.jp/bizplugin/
*/

if (!class_exists( 'WPAlchemy_MetaBox' ) ) {
	include_once( dirname(__FILE__) . "/wpalchemy/MetaBox.php" );
}
include_once( dirname(__FILE__) . "/admin-ui.php" );
new ShopMenu();


class SM{
	const VERSION = "1.3.0";
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
		wp_enqueue_style('shop-menu-style', plugins_url('shop-menu.css', __FILE__ ), array(), self::VERSION);
		wp_enqueue_script('shop-menu-js', plugins_url('next-page.js', __FILE__ ), array('jquery'), self::VERSION);
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
		add_action( 'after_setup_theme',  array(&$this, 'after_setup_theme'));
		add_action( 'wp_enqueue_scripts', array(&$this,'on_enqueue_sctipts') );
		add_action( 'wp_ajax_get_menu_ajax', array(&$this,'get_menu_ajax') );
		add_action( 'wp_ajax_nopriv_get_menu_ajax', array(&$this,'get_menu_ajax') );
		add_filter( 'manage_edit-shop_menu_columns', array(&$this, 'manage_posts_columns'));
		add_action( 'manage_shop_menu_posts_custom_column',  array(&$this, 'add_shop_category_column'), 10, 2);
		add_filter( 'manage_edit-menu_type_columns', array(&$this, 'manage_menu_type_columns'));
		add_action( 'manage_menu_type_custom_column',  array(&$this, 'add_shortcode_column'), 10, 3);
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
				"sm_item_orderby" => "名称順",
				"sm_item_order" => "昇順",
				"sm_monetary_unit" => "円（税込）",
		);
		SM::update_option( $arr );
	}

	function on_init() {
		$this->init_custom_metabox();
		$this->register_shop_menu();
	}

	function register_shop_menu(){
		$labels = array(
				'menu_name' => 'ShopMenu',
				'all_items' => '商品一覧',
				'name' => '商品一覧',
				'add_new_item' => '商品を追加',
				'edit_item' => '商品を編集',
		);
		$supports = array('title', 'editor', 'thumbnail', 'revisions', 'page-attributes', 'slug');
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
		$category = array(
				'label' => '商品カテゴリ',
				'public' => true,
				'show_ui' => true,
				'hierarchical' => true,
		);
		register_taxonomy( 'menu_type', 'shop_menu', $category);

	}
	function manage_posts_columns($columns) {
		$columns['shop_category'] = "商品カテゴリー";
		unset( $columns['date'] );
		$columns['date']  = '日時';
		return $columns;
	}

	function manage_menu_type_columns($columns) {
		$columns['menu_shortcode'] = "ショートコード";
		unset( $columns['description'] );
		return $columns;
	}

	function add_shop_category_column($column_name, $post_id){
		if( $column_name == 'shop_category' ){
			$category = get_the_term_list($post_id, 'menu_type');
		}
		if ( isset($category) && $category ){
			echo $category;
		}else{
			echo __('None');
		}
	}

	function add_shortcode_column( $out, $column_name, $theme_id ){
		$short = SM::SHORTCODE;
		echo "<input type='text' value='[${short} id=${theme_id}]' size='18' readonly>";
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
		add_submenu_page( "edit.php?post_type=shop_menu", "ShopMenu設定", "設定", 'administrator', __FILE__, array(&$this->adminUi, 'show_admin_page'));
	}

	public function after_setup_theme() {
		add_theme_support( 'post-thumbnails', array('shop_menu'));
	}

	/**
	 * shortcode
	 */
	function show_shortcode( $atts ){
		extract(shortcode_atts(array(
				'id' => null,
		), $atts));
		$info = new ShopMenuInfo( array(&$this, 'get_post_meta'), 0, $id);
		$category = $id;
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
		$category_id = absint( $_REQUEST['category'] );
		$info = new ShopMenuInfo( array(&$this, 'get_post_meta'), $page, $category_id );
		$charset = get_bloginfo( 'charset' );
		$info->next_page = $info->has_next ? $page + 1: null;
		$json = json_encode( $info );
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
	var $window_open = false;

	public function __construct( $callback, $page = 0, $category_id = null){
		$options = SM::get_option();
		$this->show_price = $options['sm_show_price'];
		$this->window_open = isset( $options['sm_window_open'] ) ? $options['sm_window_open'] : false;
		$item_num = $options['sm_item_num'];

		$condition = array();
		$condition['post_type'] = 'shop_menu';
		if ( empty( $options['sm_item_orderby'] ) || $options['sm_item_orderby'] == '名称順'){
			$condition['orderby'] = 'title';
		}else if( $options['sm_item_orderby'] == '更新日順' ){
			$condition['orderby'] = 'modified';
		}else{
			$condition['orderby'] = 'post_date';
		}
		$condition['order'] = ( isset($options['sm_item_order'] ) && $options['sm_item_order'] == '昇順' ) ? 'asc' : 'desc';
		$condition['numberposts'] = $item_num + 1;
		$condition['offset'] = $page * $item_num;
		if ( isset($category_id) ){
			$terms = get_term_by( 'id', $category_id, 'menu_type');
			if ( $terms ){
				$condition['menu_type'] = $terms->slug;
			}
		}
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
		$img_tag = get_the_post_thumbnail( $id, 'thumbnail' );
		if ( !empty($img_tag) ){
			return $img_tag;
		}
		return SM::get_dummy_img_tag();
	}
}

?>