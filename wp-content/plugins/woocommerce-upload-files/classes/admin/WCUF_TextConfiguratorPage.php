<?php 
class WCUF_TextConfiguratorPage
{
	public function __construct()
	{
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
				'page_title' 	=> 'Texts',
				'menu_title'	=> 'Texts',
				'parent_slug'	=> 'woocommerce-files-upload',
			));
			
		}
	}
	
	function after_saving_options($post_id = 0)
	{
		if( /* isset($_POST['_acf_post_id']) && $_POST['_acf_post_id'] == 'options' &&
		    isset($_POST['_acf_screen']) && $_POST['_acf_screen'] == 'options' && */
		    isset($_GET['page']) && $_GET['page'] == 'acf-options-texts')
			{
		
				global $wcuf_asset_model;
				$wcuf_asset_model->generate_assets();
			}
	}
}
?>