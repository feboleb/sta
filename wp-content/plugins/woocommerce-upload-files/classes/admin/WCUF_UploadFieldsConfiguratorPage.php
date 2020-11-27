<?php
class WCUF_UploadFieldsConfiguratorPage
{
	public static  $WCUF_current_lang;
	public function __construct()
	{
		//add_action( 'wp_enqueue_scripts', array(&$this, 'force_dequeue_scripts'),100 );
		//add_action('admin_enqueue_scripts', array(&$this, 'force_dequeue_scripts') );
		//add_action('wp_head',  array(&$this, 'enqueue_scripts'));
	}
	public static function force_dequeue_scripts($enqueue_styles)
	{
		if ( class_exists( 'woocommerce' ) && isset($_GET['page']) && $_GET['page'] == 'woocommerce-files-upload') 
		{
			global $wp_scripts,$wcuf_option_model;
			if(!$wcuf_option_model->configurator_page_force_load_js_scripts())
				$wp_scripts->queue = array();
			WCUF_UploadFieldsConfiguratorPage::enqueue_scripts();
		} 
	}
	public static function enqueue_scripts()
	{
		if ( class_exists( 'woocommerce' ) && isset($_GET['page']) && $_GET['page'] == 'woocommerce-files-upload') 
		{
			/* wp_dequeue_script( 'select2');
			wp_deregister_script('select2'); */
			
			 global /*$wp_scripts*/ $wcuf_option_model;
			//$wp_scripts->queue = array();	 
		
			$general_options = $wcuf_option_model->get_all_options(); 
			wp_enqueue_style( 'select2.css', wcuf_PLUGIN_PATH.'/css/select2.min.css' ); 
			wp_enqueue_style( 'wcuf-common', wcuf_PLUGIN_PATH.'/css/wcuf-common.css' ); 
			wp_enqueue_style( 'wcuf-backend', wcuf_PLUGIN_PATH.'/css/wcuf-backend.css' );
			wp_enqueue_style( 'wcuf-upload-configurator-page-tabs', wcuf_PLUGIN_PATH.'/css/wcuf-admin-upload-configurator-page-tabs.css' );
			wp_enqueue_style( 'wp-color-picker' );
			
			//wcuf_var_dump($wp_scripts);
			wp_enqueue_script( 'jquery' );		
			//wp_enqueue_script( 'select2-js', wcuf_PLUGIN_PATH.'/js/select2.min.js', array('jquery') );
			wp_enqueue_script( 'selectWoo' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'utils' );
			wp_enqueue_script( 'wp-color-picker');
			wp_enqueue_script( 'wcuf-autocomplete-product-and-categories', wcuf_PLUGIN_PATH.'/js/wcuf-admin-product_and_categories-autocomplete.js', array('jquery'),false,false );			
			wp_register_script( 'wcuf-admin-upload-configurator-page-tabs', wcuf_PLUGIN_PATH.'/js/wcuf-admin-upload-configurator-page-tabs.js', array('jquery'),false,false );			
			if($general_options['show_warning_alert_on_configurator'] == 'yes')
				wp_enqueue_script( 'wcuf-admin-menu-debug', wcuf_PLUGIN_PATH.'/js/wcuf-debug-alert.js', array('jquery'),false,false );
			
			//js paramenters
			$js_options = array(
				'missing_required_value' => __( 'Please fill all the required fields (the ones marked with a red asterisk) before switching tab.', 'woocommerce-files-upload' )
			);
			wp_localize_script( 'wcuf-admin-upload-configurator-page-tabs', 'wcuf_options', $js_options );
			wp_enqueue_script( 'wcuf-admin-upload-configurator-page-tabs' );
			
		}
	}
	public static function WCUF_switch_to_default_lang()
	{
		if(defined("ICL_LANGUAGE_CODE") && ICL_LANGUAGE_CODE != null)
		{
			global $sitepress;
			WCUF_UploadFieldsConfiguratorPage::$WCUF_current_lang = ICL_LANGUAGE_CODE;
			$sitepress->switch_lang($sitepress->get_default_language());
		}
	}
	public static function WCUF_restore_current_lang()
	{
		if(defined("ICL_LANGUAGE_CODE") && ICL_LANGUAGE_CODE != null)
		{
			global $sitepress;
			$sitepress->switch_lang(WCUF_UploadFieldsConfiguratorPage::$WCUF_current_lang);
		}
	}
	
	private function update_settings()
	{
		global $wcuf_option_model;
		$wcuf_file_meta = isset($_POST['wcuf_file_meta']) ? $_POST['wcuf_file_meta'] : null;
			return $wcuf_option_model->save_bulk_options($wcuf_file_meta);
		
		return null;
	}
	/* private function reset_data()
	{
		delete_option( 'wcuf_last_file_id');
		delete_option( 'wcuf_files_fields_meta');
	}  */
	
	
	
	public function render_page()
	{
		global $wcuf_option_model, $wcuf_product_model, $wcuf_customer_model, $wcuf_html_helper;
		if (isset($_POST['wcuf_file_meta']) || isset($_POST['wcuf_is_submit']) )//$_SERVER['REQUEST_METHOD'] == 'POST')
			$file_fields_meta = $this->update_settings();
		else
			$file_fields_meta = $wcuf_option_model->get_fields_meta_data();
		
		wp_register_script( 'wcuf-admin-menu', wcuf_PLUGIN_PATH.'/js/wcuf-admin-menu.js', array('jquery'),false,false );
		
		//vars
		$last_id = $wcuf_option_model->get_option( 'wcuf_last_file_id');
		$last_id = !$last_id ? 0 : $last_id++;
		$variables = array(
			'last_id' => $last_id,
			'confirm_delete_message' => __('Are you sure you want to delete the field?', 'woocommerce-files-upload')
		);
		wp_localize_script( 'wcuf-admin-menu', 'wcuf', $variables );	
		wp_enqueue_script( 'wcuf-admin-menu');	
		
		?>
		<!-- <script>
			jQuery.fn.select2=null;
		</script>
		<script type='text/javascript' src='<?php echo wcuf_PLUGIN_PATH.'/js/select2.min.js'; ?>'></script>-->
		<div id="icon-themes" class="icon32"><br></div> 
		<h2><?php _e('Uploads options', 'woocommerce-files-upload');?></h2>
		<h3></h3>
		<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') 
				echo '<div id="message" class="wcuf_updated"><p>' . __('Saved successfully.', 'woocommerce-files-upload') . '</p></div>'; ?>
		<div class="wrap">
		<!-- <div id="wcuf_error_box">
		
		</div>-->
			<form action="" method="post"  style="padding-left:20px">
			<input type="hidden" name="wcuf_is_submit" value="true"></input>
			<?php //settings_fields('wcuf_files_fields_meta_groups'); ?> 
				<button class="add_field_button button-primary"><?php _e('Add new Upload Field', 'woocommerce-files-upload');?></button>
				<img class="wcuf_preloader_image" src="<?php echo wcuf_PLUGIN_PATH.'/img/preloader.gif' ?>" ></img>
				<ul class="input_fields_wrap wcuf_sortable">
				<?php echo $wcuf_html_helper->upload_field_configurator_template($file_fields_meta, 0 ); ?>
				</ul>
				<button class="add_field_button button-primary"><?php _e('Add new Upload Field', 'woocommerce-files-upload');?></button>
				<img class="wcuf_preloader_image" src="<?php echo wcuf_PLUGIN_PATH.'/img/preloader.gif' ?>" ></img>
				
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'wshipinfo-patsatech'); ?>" />
				</p>
			</form>
		</div>
		<?php
	}
}
?>