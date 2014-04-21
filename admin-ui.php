<?php

class SMAdminUi {
	var $file_path;

	public function __construct( $path){
		$this->file_path = $path;
		$this->setUi();
	}

	public function setUi(){
		register_setting(SM::OPTIONS, SM::OPTIONS, array( &$this, 'validate' ));
		add_settings_section('SM_main_section', '表示設定', array(&$this,'section_text_fn'), $this->file_path);
		add_settings_field('sm_show_price', '価格を表示する', array(&$this,'setting_show_price'), $this->file_path, 'SM_main_section');
		add_settings_field('sm_monetary_unit', '値段の単位', array(&$this,'setting_monetary_unit'), $this->file_path, 'SM_main_section');
		add_settings_field('sm_item_num', '表示件数', array(&$this,'setting_item_num'), $this->file_path, 'SM_main_section');
		add_settings_field('sm_item_orderby', '表示順序', array(&$this,'setting_item_orderby'), $this->file_path, 'SM_main_section');
		add_settings_field('sm_item_order', '', array(&$this,'setting_item_order'), $this->file_path, 'SM_main_section');
		add_settings_field('sm_window_open', '商品ページを別ウィンドウで開く', array(&$this,'setting_window_open'), $this->file_path, 'SM_main_section');
	}

	public function show_admin_page() {
		$file = $this->file_path;
		$option_name = SM::OPTIONS;
		include_once('admin-view.php');
	}

	function validate($input) {
		$output = array();
		$output['sm_monetary_unit'] = empty( $input['sm_monetary_unit'] ) ? "円（税込）" :  esc_html( $input['sm_monetary_unit'] );
		$output['sm_show_price'] = $input['sm_show_price'];
		$output['sm_item_num'] = $input['sm_item_num'];
		$output['sm_item_order'] = $input['sm_item_order'];
		$output['sm_item_orderby'] = $input['sm_item_orderby'];
		$output['sm_window_open'] = $input['sm_window_open'];
		if ( !is_numeric( $input['sm_item_num']) || $input['sm_item_num'] < 0 || $output['sm_item_num'] > 30){
			$output['sm_item_num'] = 12;
		}
		return $output;
	}

	function  section_text_fn() {
	}

	function setting_show_price() {
		$this->setting_chk( "sm_show_price" );
	}

	function setting_chk( $id ) {
		$options = SM::get_option();
		$option_name = SM::OPTIONS;
		$checked = (isset($options[$id]) && $options[$id]) ? $checked = ' checked="checked" ': "";
		$name = $option_name. "[$id]";

		echo "<input ".$checked." id='id_".$id."' name='".$name."' type='checkbox' />";
	}

	function setting_monetary_unit() {
		$options = SM::get_option();
		$option_name = SM::OPTIONS;
		$value = $options["sm_monetary_unit"];
		echo "<input id='sm_monetary_unit' name='{$option_name}[sm_monetary_unit]' size='40' type='text' value='{$value}' />";
	}

	function setting_item_orderby() {
		$options = SM::get_option();
		$items = array("名称順", "更新日順", "公開日順");
		$option_name = SM::OPTIONS;
		foreach($items as $item) {
			$checked = ($options['sm_item_orderby']==$item) ? 'checked="checked"' : '';
			echo "<label><input {$checked} value='{$item}' name='{$option_name}[sm_item_orderby]' type='radio' /> $item</label><br />";
		}
	}

	function setting_item_order() {
		$options = SM::get_option();
		$items = array("昇順", "降順");
		$option_name = SM::OPTIONS;
		echo "<select id='sm_item_order' name='{$option_name}[sm_item_order]'>";
		foreach($items as $item) {
			$selected = ($options['sm_item_order']==$item) ? 'selected="selected"' : '';
			echo "<option value='$item' $selected>$item</option>";
		}
		echo "</select>";
	}

	function setting_item_num() {
		$options = SM::get_option();
		$option_name = SM::OPTIONS;
		$value = $options["sm_item_num"];
		echo "<input id='sm_item_num' name='{$option_name}[sm_item_num]' size='2' type='text' value='{$value}' />";
	}

	function setting_window_open() {
		$options = SM::get_option();
		$option_name = SM::OPTIONS;
		$checked = (isset($options['sm_window_open']) && $options['sm_window_open']) ? $checked = ' checked="checked" ': "";
		echo "<input id='sm_window_open' name='{$option_name}[sm_window_open]' type='checkbox' {$checked} />";
	}
}
