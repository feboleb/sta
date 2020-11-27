<?php 
class WCUF_OptionPage
{
	public function __construct()
	{
		//dd_filter('acf/init', array(&$this,'init_options_menu'));
		$this->init_options_menu();
		//add_action('acf/save_post', array(&$this,'after_saving_options'), 20);
		add_action('wp_loaded', array(&$this,'after_saving_options')); //if performed on save, when generating assets data has not been saved. In this way the assets are generated on page reload
	}
	function init_options_menu()
	{
		if( function_exists('acf_add_options_page') ) 
		{
			/*acf_add_options_page(array(
				'page_title' 	=> 'Menu name',
				'menu_title'	=> 'Menu name',
				'menu_slug' 	=> 'wcuf-option-menu',
				'capability'	=> 'edit_posts',
				'icon_url'      => 'dashicons-upload',
				'redirect'		=> false
			));*/
			
			 acf_add_options_sub_page(array(
				'page_title' 	=> 'Options',
				'menu_title'	=> 'Options',
				'parent_slug'	=> 'woocommerce-files-upload',
			));	
			
			
			add_action( 'current_screen', array(&$this, 'cl_set_global_options_pages') );
		}
	}
	function after_saving_options($post_id = 0)
	{
		
		if( /* isset($_POST['_acf_post_id']) && $_POST['_acf_post_id'] == 'options' &&
		    isset($_POST['_acf_screen']) && $_POST['_acf_screen'] == 'options' && */
		    isset($_GET['page']) && $_GET['page'] == 'acf-options-options')
			{
				global $wcuf_asset_model;
				$wcuf_asset_model->generate_assets();
			}
	}
	/**
	 * Force ACF to use only the default language on some options pages
	 */
	function cl_set_global_options_pages($current_screen) 
	{
	  global $wcuf_wpml_helper;
	  if(!is_admin())
		  return;
	  
	 //wcuf_var_dump($current_screen->id);
	  /* wcuf_var_dump(get_locale());
	  wcuf_var_dump($wcuf_wpml_helper->get_available_locale()); */
	  
	  $page_ids = array(
		"upload-files_page_acf-options-options",
		"upload-files-configurator_page_acf-options-options"
	  );
	  //wcuf_var_dump($current_screen->id);
	  if (in_array($current_screen->id, $page_ids)) 
	  {
	
		$wcuf_wpml_helper->switch_to_default_language();
		add_filter('acf/settings/current_language', array(&$this, 'cl_acf_set_language'), 100);
	  }
	}
	

	function cl_acf_set_language() 
	{
	  return acf_get_setting('default_language');
	}

	/**
	 * Wrapper around get_field() to get the "global" option values.
	 * This is the function you'll want to use in your templates instead of get_field() for "global" options.
	 */
	/* function get_global_option($name) 
	{
	  add_filter('acf/settings/current_language',  array(&$this, 'cl_acf_set_language'), 100);
	  $option = get_field($name, 'option');
	  remove_filter('acf/settings/current_language', array(&$this,'cl_acf_set_language'), 100);
	  return $option;
	} */
}
?>