<?php 
class WCUF_OrderDetailsPage
{
	public function __construct()
	{
		add_action( 'init', array( &$this, 'init' ));
		//add_action( 'woocommerce_thankyou', array( &$this, 'order_has_been_placed' ),99, 1 );
		add_filter( 'woocommerce_order_item_name', array( &$this, 'process_order_table_item_name' ),10, 3 );
		
		add_action('wp_head', array( &$this,'add_meta'));
		add_action('wp', array( &$this,'add_headers_meta'));
	}
	function init()
	{
		global $wcuf_option_model;
		$position = 'woocommerce_order_details_after_order_table';
		try
		{
			$all_options = $wcuf_option_model->get_all_options();
			$position = $all_options['order_details_page_positioning'];
		}catch(Exception $e){};
		
		add_action( $position, array( &$this, 'front_end_order_page_addon' ));	
	}
	public function process_order_table_item_name($html_link, $item, $is_visible = true)
	{
		global $wcuf_order_model;		
		$item_individual_id = $wcuf_order_model->read_order_item_meta($item,'_wcuf_sold_as_individual_unique_key');
		
		return $item_individual_id ? $html_link." #".$item_individual_id : $html_link ;
	}
	public function order_has_been_placed($order_id)
	{
		//$this->front_end_order_page_addon(new WC_Order($order_id), false, true);
		$this->front_end_order_page_addon(wc_get_order($order_id), false, true);
	}
	public function front_end_order_page_addon( $order, $is_shortcode = false , $avoid_thank_you_page_check = true  )
	{	
		if(wcuf_is_request_to_rest_api())
			return;
		
		if(!wcuf_is_a_supported_browser())
			return;
		
		global $wcuf_order_model, $wcuf_upload_field_model, $wcuf_file_model, $wcuf_option_model, $wcuf_wpml_helper, $wcuf_media_model,
		       $wcuf_session_model, $wcuf_cart_model, $wcuf_shortcodes,$wcuf_product_model,$wcuf_text_model, 
			   $sitepress, $wcuf_customer_model, $wcuf_upload_field_model;
		
		
		$button_texts  = $wcuf_text_model->get_button_texts();
		$file_fields_groups = $wcuf_option_model->get_fields_meta_data();
		$order_id = $wcuf_order_model->get_order_id($order) ;
		/* $file_order_metadata =$wcuf_option_model->get_order_uploaded_files_meta_data($order_id);
		$file_order_metadata = !$file_order_metadata ? array():$file_order_metadata[0]; */
		$file_order_metadata = $wcuf_upload_field_model->get_uploaded_files_meta_data_by_order_id($order_id);
		$css_options = $wcuf_option_model->get_style_options();
		$crop_area_options = $wcuf_option_model->get_crop_area_options();
		$display_summary_box = $wcuf_option_model->get_all_options('display_summary_box_strategy');
		$all_options = $wcuf_option_model->get_all_options();
		$additional_button_class = $all_options['additional_button_class'];
		$order_items = $wcuf_order_model->get_sorted_order_items($order);
		$current_url = $this->curPageURL();
		$current_page = $is_shortcode ? "shortcode" : "order_details"; //no more used as shortcode template
		$is_order_completed_status = $wcuf_order_model->get_order_status($order) != 'completed' ? false : true;
		$is_thank_you_page = false;
		$wcuf_session_model->remove_item_data(null, true);
		$current_order_status = $wcuf_order_model->get_order_status($order) ;
		$summary_box_info_to_display = $wcuf_option_model->get_all_options('summary_box_info_to_display');
		$current_locale = $wcuf_wpml_helper->get_current_locale();
		
		/* $status_change_options = $wcuf_option_model->get_order_stratus_change_options();
		wcuf_var_dump($status_change_options); */
		
		//woocommerce_order_details_after_order_table action is called even on thank you page. 
		// In case of thank you page, upload fields are rendered after the woocommerce_thankyou action has been triggered
		if(did_action('woocommerce_thankyou') > 0  && $avoid_thank_you_page_check )
		{
			$is_thank_you_page = true;
			$current_page = $is_shortcode ? "shortcode" : "thank_you";
			//return;
		}
		
		
		//************** Save process is performed via ajax. See the "ajax_save_file_uploaded_from_order_detail_page" function on WCUF_FIle
		/* if(isset($_POST) && isset($_POST['type']) && $_POST['type'] === 'wcup_delete')
		{
			$file_order_metadata = $wcuf_file_model->delete_file($_POST['id'], $file_order_metadata, $order_id);
		}
		
		else if($_FILES) 
		{
			$file_order_metadata = $wcuf_file_model->upload_files($order, $file_order_metadata, $file_fields_groups);
		} */
		//***********************
	
		if(/* $order->status != 'completed' &&  */$file_fields_groups)
		{
			
			//wp_enqueue_style('wcuf-frontend-common', wcuf_PLUGIN_PATH.'/css/wcuf-frontend-common.css?'.http_build_query($css_options));
			wp_enqueue_style('wcuf-frontend-common', wcuf_PLUGIN_PATH.'/css/wcuf-frontend-common.css');
			wp_enqueue_style('wcuf-croppie', wcuf_PLUGIN_PATH.'/css/vendor/croppie.css');
			//wp_enqueue_style('wcuf-cropbox', wcuf_PLUGIN_PATH.'/css/vendor/cropbox.css?'.http_build_query($crop_area_options) );
			wp_enqueue_style('wcuf-cropbox', wcuf_PLUGIN_PATH.'/css/vendor/cropbox.css');
			//wp_enqueue_style('wcuf-order-detail', wcuf_PLUGIN_PATH.'/css/wcuf-frontend-order-detail.css?'.http_build_query($css_options) );
			wp_enqueue_style('wcuf-order-detail', wcuf_PLUGIN_PATH.'/css/wcuf-frontend-order-detail.css' );
			wp_enqueue_style('wcuf-magnific-popup', wcuf_PLUGIN_PATH.'/css/vendor/magnific-popup.css');
			
			wp_enqueue_script('wcuf-magnific-popup', wcuf_PLUGIN_PATH.'/js/vendor/jquery.magnific-popup.js', array('jquery'));
			
			wp_register_script('wcuf-order-details-page', wcuf_PLUGIN_PATH.'/js/wcuf-frontend-order-details-page'.'_'.$current_locale.'.js' ,array('jquery'));   
			wp_register_script( 'wcuf-multiple-file-manager', wcuf_PLUGIN_PATH.'/js/wcuf-frontend-multiple-file-manager.js', array('jquery') );
			wp_enqueue_script( 'wcuf-generic-file-manager', wcuf_PLUGIN_PATH.'/js/wcuf-frontend-generic-file-uploader.js', array('jquery') );
			wp_register_script('wcuf-frontend-ui-manager', wcuf_PLUGIN_PATH.'/js/wcuf-frontend-ui-manager.js', array('jquery'));
		
			wp_enqueue_script('wcuf-audio-video-file-manager', wcuf_PLUGIN_PATH. '/js/wcuf-audio-video-file-manager.js' ,array('jquery')); 
			wp_enqueue_script('wcuf-load-image', wcuf_PLUGIN_PATH. '/js/load-image.all.min.js' ,array('jquery')); 
			wp_enqueue_script('wcuf-image-size-checker', wcuf_PLUGIN_PATH. '/js/wcuf-image-size-checker.js' ,array('jquery')); 
			wp_enqueue_script('wcuf-cropbox', wcuf_PLUGIN_PATH. '/js/vendor/cropbox.js' ,array('jquery')); 
			wp_enqueue_script('wcuf-image-cropper', wcuf_PLUGIN_PATH. '/js/wcuf-frontend-cropper.js' ,array('jquery')); 
			wp_enqueue_script('wcuf-image-cropper-multiple', wcuf_PLUGIN_PATH. '/js/wcuf-frontend-cropper-multiple.js' ,array('jquery')); 
			wp_enqueue_script('wcuf-frontend-multiple-file-uploader', wcuf_PLUGIN_PATH.'/js/wcuf-frontend-multiple-file-uploader.js', array('jquery'));
			//wp_enqueue_script('wcuf-frontend-global-error-catcher', wcuf_PLUGIN_PATH.'/js/wcuf-frontend-global-error-catcher.js', array('jquery'));
			wp_enqueue_script('wcuf-croppie.js', wcuf_PLUGIN_PATH.'/js/vendor/croppie.min.js', array('jquery'));
			
			include WCUF_PLUGIN_ABS_PATH.'/template/alert_popup.php';	
			echo '<div id="wcuf_order_details_ajax_container" style="display:none;">';				
			include WCUF_PLUGIN_ABS_PATH.'/template/view_order_template.php';
			echo '</div>';
			
				$js_options = array(
					//'cart_quantity_as_number_of_uploaded_files' => $all_options['cart_quantity_as_number_of_uploaded_files'] ? 'true' : 'false',
					'icon_path' => wcuf_PLUGIN_PATH."/img/icons/",
					'order_id' => $order_id,
					'current_page' => $current_page
				);
			
			wp_localize_script( 'wcuf-frontend-ui-manager', 'wcuf_options', $js_options );
			wp_localize_script( 'wcuf-multiple-file-manager', 'wcuf_options', $js_options );
			wp_localize_script( 'wcuf-order-details-page', 'wcuf_options', $js_options );
			
			wp_enqueue_script( 'wcuf-multiple-file-manager' ); 
			wp_enqueue_script( 'wcuf-frontend-ui-manager' ); 
			wp_enqueue_script( 'wcuf-order-details-page' ); 
		}
					
	}
	function add_meta()
	{
		if(isset($_GET['view-order']))
		{
			
			 echo '<meta http-equiv="Cache-control" content="no-cache">';
			echo '<meta http-equiv="Expires" content="-1">';
		}
	}
	function add_headers_meta()
	{
		if(isset($_GET['view-order']))
		{
			header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
			header('Pragma: no-cache');
		}
	}
	function curPageURL() 
	{
		 $pageURL = 'http';
		 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		 $pageURL .= "://";
		 if ($_SERVER["SERVER_PORT"] != "80") {
		  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		 } else {
		  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		 }
		 return $pageURL;
	}
}
?>