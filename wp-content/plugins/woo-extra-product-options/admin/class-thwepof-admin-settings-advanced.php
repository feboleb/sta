<?php
/**
 * The admin advanced settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.3.0
 *
 * @package    woocommerce-extra-product-options-pro
 * @subpackage woocommerce-extra-product-options-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWEPOF_Admin_Settings_Advanced')):

class THWEPOF_Admin_Settings_Advanced extends THWEPOF_Admin_Settings{
	protected static $_instance = null;
	
	private $settings_fields = NULL;
	private $cell_props = array();
	private $cell_props_CB = array();
	private $cell_props_TA = array();
	
	public function __construct() {
		parent::__construct('advanced_settings');
		$this->init_constants();
	}
	
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	} 
	
	public function init_constants(){
		$this->cell_props = array( 
			'label_cell_props' => 'class="label"', 
			'input_cell_props' => 'class="field"',
			'input_width' => '260px',
			'label_cell_th' => true
		);

		$this->cell_props_TA = array( 
			'label_cell_props' => 'class="label"', 
			'input_cell_props' => 'class="field"',
			'rows' => 10,
			'cols' => 100,
		);

		$this->cell_props_CB = array( 
			'label_props' => 'style="margin-right: 40px;"', 
		);
		
		$this->settings_fields = $this->get_advanced_settings_fields();
	}
	
	public function get_advanced_settings_fields(){
		return array(
			'add-to_cart_text_settings' => array('title'=>'Add to cart text', 'type'=>'separator', 'colspan'=>'3'),
			'add_to_cart_text_addon' => array(
				'name'=>'add_to_cart_text_addon', 'label'=>'Products having Extra Options', 'type'=>'text', 'value'=>'Select options', 'placeholder'=>'ex: Select options'
			),
			'add_to_cart_text_simple' => array(
				'name'=>'add_to_cart_text_simple', 'label'=>'Simple Products', 'type'=>'text', 'value'=>'', 'placeholder'=>'ex: Add to cart'
			),
			'add_to_cart_text_variable' => array(
				'name'=>'add_to_cart_text_variable', 'label'=>'Variable Products', 'type'=>'text', 'value'=>'', 'placeholder'=>'ex: Select options'
			),
			'section_other_settings' => array('title'=>'Other Settings', 'type'=>'separator', 'colspan'=>'3'),
			'hide_in_cart' => array(
				'name'=>'hide_in_cart', 'label'=>'Hide custom fields in Cart Page', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'hide_in_checkout' => array(
				'name'=>'hide_in_checkout', 'label'=>'Hide custom fields in Checkout page', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'hide_in_order' => array(
				'name'=>'hide_in_order', 'label'=>'Hide custom fields in Order Details page', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'allow_get_method' => array(
				'name'=>'allow_get_method', 'label'=>'Allow posting extra options as url parameters', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),

			/*'disable_select2_for_select_fields' => array(
				'name'=>'disable_select2_for_select_fields', 'label'=>'Disable "Enhanced Select(Select2)" for select fields.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			)*/
		);
	}
	
	public function render_page(){
		$this->render_tabs();
		$this->render_content();
	}
		
	public function save_advanced_settings($settings){
		$result = update_option(THWEPOF_Utils::OPTION_KEY_ADVANCED_SETTINGS, $settings);
		return $result;
	}
	
	private function reset_settings(){
		delete_option(THWEPOF_Utils::OPTION_KEY_ADVANCED_SETTINGS);
		$this->print_notices('Settings successfully reset.', 'updated', false);
	}
	
	private function save_settings(){
		$settings = array();
		
		foreach( $this->settings_fields as $name => $field ) {
			$value = '';
			
			if($field['type'] === 'checkbox'){
				$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';

			}else if($field['type'] === 'multiselect_grouped'){
				$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
				$value = is_array($value) ? implode(',', wc_clean(wp_unslash($value))) : wc_clean(wp_unslash($value));

			}else if($field['type'] === 'text' || $field['type'] === 'textarea'){
				$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
				$value = !empty($value) ? wc_clean( wp_unslash($value)) : '';

			}else{
				$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
				$value = !empty($value) ? wc_clean( wp_unslash($value)) : '';
			}
			
			$settings[$name] = $value;
		}
				
		$result = $this->save_advanced_settings($settings);
		if ($result == true) {
			$this->print_notices('Your changes were saved.', 'updated', false);
		} else {
			$this->print_notices('Your changes were not saved due to an error (or you made none!).', 'error', false);
		}	
	}
	
	private function render_content(){
		if(isset($_POST['reset_settings']))
			$this->reset_settings();	
			
		if(isset($_POST['save_settings']))
			$this->save_settings();

		if(isset($_POST['save_plugin_settings'])) 
			$result = $this->save_plugin_settings();
			
    	$this->render_plugin_settings();
    	$this->render_import_export_settings();
	}

	private function render_plugin_settings(){
		$settings = THWEPOF_Utils::get_advanced_settings();
		?>            
        <div style="padding-left: 30px;">               
		    <form id="advanced_settings_form" method="post" action="">
                <table class="thwepof-settings-table thpladmin-form-table">
                    <tbody>
                    <?php
                    $this->render_add_to_cart_btn_settings($settings);
                    $this->render_other_settings($settings);
					?>
                    </tbody>
                </table> 
                <p class="submit">
					<input type="submit" name="save_settings" class="btn btn-small btn-primary" value="Save changes">
                    <input type="submit" name="reset_settings" class="btn btn-small" value="Reset to default" 
					onclick="return confirm('Are you sure you want to reset to default settings? all your changes will be deleted.');">
            	</p>
            </form>
    	</div>       
    	<?php
	}

	private function render_add_to_cart_btn_settings($settings){
		$this->render_form_elm_row_title('Modify Add to cart button text');
		$this->render_form_elm_row($this->settings_fields['add_to_cart_text_addon'], $settings);
		$this->render_form_elm_row($this->settings_fields['add_to_cart_text_simple'], $settings);
		$this->render_form_elm_row($this->settings_fields['add_to_cart_text_variable'], $settings);		
	}

	private function render_other_settings($settings){
		$this->render_form_elm_row_title('Other Settings');
		$this->render_form_elm_row_cb($this->settings_fields['hide_in_cart'], $settings, true);
		$this->render_form_elm_row_cb($this->settings_fields['hide_in_checkout'], $settings, true);
		$this->render_form_elm_row_cb($this->settings_fields['hide_in_order'], $settings, true);
		$this->render_form_elm_row_cb($this->settings_fields['allow_get_method'], $settings, true);
	}
	
    /************************************************
	 *-------- IMPORT & EXPORT SETTINGS - START -----
	 ************************************************/
	public function prepare_plugin_settings(){
		$settings_sections = get_option(THWEPOF_Utils::OPTION_KEY_CUSTOM_SECTIONS);
		$settings_hook_map = get_option(THWEPOF_Utils::OPTION_KEY_SECTION_HOOK_MAP);
		$settings_name_title_map = get_option(THWEPOF_Utils::OPTION_KEY_NAME_TITLE_MAP);
		$settings_advanced = get_option(THWEPOF_Utils::OPTION_KEY_ADVANCED_SETTINGS);

		$plugin_settings = array(
			'OPTION_KEY_CUSTOM_SECTIONS' => $settings_sections,
			'OPTION_KEY_SECTION_HOOK_MAP' => $settings_hook_map,
			'OPTION_KEY_NAME_TITLE_MAP' => $settings_name_title_map,
			'OPTION_KEY_ADVANCED_SETTINGS' => $settings_advanced,
		);

		return base64_encode(serialize($plugin_settings));
	}
	
	public function render_import_export_settings(){
		/*
		if(isset($_POST['save_plugin_settings'])) 
			$result = $this->save_plugin_settings(); 
		*/

		if(isset($_POST['import_settings'])){			   
		} 
		
		$plugin_settings = $this->prepare_plugin_settings();
		if(isset($_POST['export_settings']))
			echo $this->export_settings($plugin_settings);   
		
		$imp_exp_fields = array(
			'section_import_export' => array('title'=>'Backup and Import Settings', 'type'=>'separator', 'colspan'=>'3'),
			'settings_data' => array(
				'name'=>'settings_data', 'label'=>'Plugin Settings Data', 'type'=>'textarea', 'value' => $plugin_settings,
				'sub_label'=>'You can tranfer the saved settings data between different installs by copying the text inside the text box. To import data from another install, replace the data in the text box with the one from another install and click "Import Settings".',
				//'sub_label'=>'You can insert the settings data to the textarea field to import the settings from one site to another website.'
			),
		);
		?>
		<div style="padding-left: 30px;">               
		    <form id="import_export_settings_form" method="post" action="" class="clear">
                <table class="thwepof-settings-table">
                    <tbody>
                    <?php
                    $this->render_form_elm_row_title('Backup and Import Settings');
					$this->render_form_elm_row_ta($imp_exp_fields['settings_data']);
					?>
                    </tbody>
					<tfoot>
						<tr valign="top">
							<td colspan="2">&nbsp;</td>
							<td class="submit">
								<input type="submit" name="save_plugin_settings" class="btn btn-small btn-primary" value="Import Settings">
								<!--<input type="submit" name="import_settings" class="button" value="Import Settings(CSV)">-->
								<!--<input type="submit" name="export_settings" class="button" value="Export Settings(CSV)">-->
							</td>
						</tr>
					</tfoot>
                </table> 
            </form>
    	</div> 
		<?php
	}
		
	public function save_plugin_settings(){		
		if(isset($_POST['i_settings_data']) && !empty($_POST['i_settings_data'])) {
			$settings_data_encoded = $_POST['i_settings_data'];   
			$settings = unserialize(base64_decode($settings_data_encoded)); 
			
			if($settings){	
				foreach($settings as $key => $value){	
					if($key === 'OPTION_KEY_CUSTOM_SECTIONS'){
						$result = update_option(THWEPOF_Utils::OPTION_KEY_CUSTOM_SECTIONS, $value);	
					}
					if($key === 'OPTION_KEY_SECTION_HOOK_MAP'){ 
						$result1 = update_option(THWEPOF_Utils::OPTION_KEY_SECTION_HOOK_MAP, $value);  
					}
					if($key === 'OPTION_KEY_NAME_TITLE_MAP'){ 
						$result2 = update_option(THWEPOF_Utils::OPTION_KEY_NAME_TITLE_MAP, $value); 
					}
					if($key === 'OPTION_KEY_ADVANCED_SETTINGS'){ 
						$result3 = $this->save_advanced_settings($value);  
					}						  
				}					
			}		
									
			if($result || $result1 || $result2 || $result3){
				$this->print_notices('Your Settings Updated.', 'updated', false);
				return true; 
			}else{
				$this->print_notices('Your changes were not saved due to an error (or you made none!).', 'error', false);
				return false;
			}	 			
		}
	}

	public function export_settings($settings){
		ob_clean();
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=\"wcfe-checkout-field-editor-settings.csv\";" );
		echo $settings;	
        ob_flush();     
     	exit; 		
	}
	
	public function import_settings(){
	
	}
    /**********************************************
	 *-------- IMPORT & EXPORT SETTINGS - END -----
	 **********************************************/


    /*----- Form Element Row -----*/
	public function render_form_elm_row_title($title=''){
		?>
		<tr>
			<td colspan="3" class="section-title" ><?php echo $title; ?></td>
		</tr>
		<?php
	}

	private function render_form_elm_row($field, $settings=false){
		$name = $field['name'];
		if(is_array($settings) && isset($settings[$name])){
			$field['value'] = $settings[$name];
		}

		?>
		<tr>
			<?php $this->render_form_field_element($field, $this->cell_props); ?>
		</tr>
		<?php
	}

	private function render_form_elm_row_ta($field, $settings=false){
		$name = $field['name'];
		if(is_array($settings) && isset($settings[$name])){
			$field['value'] = $settings[$name];
		}
		
		?>
		<tr valign="top">
			<?php $this->render_form_field_element($field, $this->cell_props_TA); ?>
		</tr>
		<?php
	}

	private function render_form_elm_row_cb($field, $settings=false, $merge_cells=false){
		$name = $field['name'];
		if(is_array($settings) && isset($settings[$name])){
			if($field['value'] === $settings[$name]){
				$field['checked'] = 1;
			}
		}

		if($merge_cells){
			?>
			<tr>
				<td colspan="3">
		    		<?php $this->render_form_field_element($field, $this->cell_props_CB, false); ?>
		    	</td>
		    </tr>
			<?php
		}else{
			?>
			<tr>
				<td colspan="2"></td>
				<td class="field">
		    		<?php $this->render_form_field_element($field, $this->cell_props_CB, false); ?>
		    	</td>
		    </tr>
			<?php
		}
	}
}

endif;