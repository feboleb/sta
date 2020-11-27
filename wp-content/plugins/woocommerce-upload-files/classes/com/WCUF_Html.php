<?php class WCUF_Html 
{
	function __construct()
	{
		
		add_action('wp_ajax_wcuf_get_upload_field_configurator_template', array(&$this, 'ajax_get_upload_field_configurator_template'));
		add_action('wp_ajax_wcuf_duplicate_field', array(&$this, 'ajax_duplicate_field'));
		
	}
	/* private function return_bytes($val) 
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				//$val *= 1024;
				$val *= 1024;
			case 'm':
				$val *= 1;
				break;
			case 'k':
				$val = 1;
		}
		return $val;
	} */
	public function ajax_duplicate_field()
	{
		global  $wcuf_option_model;
		$start_index = isset($_POST['start_index']) ? $_POST['start_index'] + 1: null;
		$index_to_duplicate = isset($_POST['index_to_duplicate']) ? $_POST['index_to_duplicate'] : null;
		if(isset($start_index) && isset($index_to_duplicate ))
		{
			$file_fields_meta = $wcuf_option_model->get_fields_meta_data();
			foreach($file_fields_meta as $field_index => $field_data)
			{
				if($field_data['id'] == $index_to_duplicate)
				{
					$field_data['original_index'] = $field_data['id'] ;
					$field_data['id'] = $start_index ;
					$this->upload_field_configurator_template(array($field_data), $start_index );
				}
			}
			
		}
		wp_die();
	}public function ajax_get_upload_field_configurator_template()
	{
		$start_index = isset($_POST['start_index']) ? $_POST['start_index'] + 1: null;
		if(isset($start_index))
			$this->upload_field_configurator_template(array(array()), $start_index );
		wp_die();
	}
	public function upload_field_configurator_template($file_fields_meta, $start_index)
	{
		global $wcuf_customer_model, $wcuf_product_model, $wcuf_option_model, $wcuf_order_model;
		$php_settings = $wcuf_option_model->get_php_settings();
		//text
		$already_uploaded_default_message = __('<h4>Uploaded files:</h4>', 'woocommerce-files-upload')."\n"."[file_name_with_media_preview]";
		$upload_per_product_instruction = __("By default one upload field will be displayed per product. Disabling the following options will display one upload field per order.",'woocommerce-files-upload');
		$upload_product_page_before_instruction = __('<strong>NOTE:</strong> By default the upload field will appear <strong>AFTER</strong> the product has been added to the cart. Enabling this option the following features will not work: <ol><li><strong>Max number of uploadable files depends on product quantity</strong></li><!--<li><strong>Extra costs for specific variation (percentage option):</strong> If you have selected to add extra cost based on variation price percentage, the plugin will be not able to properly compute it</li>--></ol>', 'woocommerce-files-upload');
		$product_filtering_instruction = __('Select Products (search typing product name, id or sku code)', 'woocommerce-files-upload');
		$required_field_instruction = __('In case the field is visible before adding the product to the cart, the plugin will try to hide <strong>Add to cart button</strong> (with some themes not 100% WooCommerce compliant this feature could not work). In case the product has been added to the cart, the plugin will <strong>try to deny the page leaving</strong> until all the required files have not been uploaded <strong>propting a warning dialog</strong> (some browsers, for security reasons, may not permit this denial).','woocommerce-files-upload');
		//
		$enable_for_all_text  = __('Visible for every product', 'woocommerce-files-upload');
		$enable_for_selected_categories_and_products  = __('Visible for selected categories and products', 'woocommerce-files-upload');
		$enable_for_selected_categories_and_products_and_children_text  = __('Visible for selected categories (and all its children) and products', 'woocommerce-files-upload');
		$disabled_for_selected_categories_and_products_text  = __('Hidden for selected categories and products', 'woocommerce-files-upload');
		$disabled_for_selected_categories_and_products_and_children_text  = __('Hidden for selected categories (and all its children) and products', 'woocommerce-files-upload');
		//
		$post_max_size = WCUF_File::return_bytes($php_settings['post_max_size']);
		$post_max_size_text =$php_settings['post_max_size'];
		$upload_max_filesize =  WCUF_File::return_bytes($php_settings['upload_max_filesize']);
		$upload_max_filesize_text = $php_settings['upload_max_filesize'];
		$max_file_uploads = $php_settings['max_file_uploads'];
		$php_settings_notice = sprintf(__('The plugin has detected that your host has the following PHP settings: <strong>post_max_size</strong> value is <strong>%s</strong> and <strong>upload_max_filesize</strong> value is <strong>%s</strong>. The first setting means that <strong>the sum of the files sizes you are trying to upload cannot be greater than %s</strong> and the <strong>single uploadable file size cannot be greater than %s</strong> (min value between post_max_size and upload_max_filesize).', 'woocommerce-files-upload'),$post_max_size_text, $upload_max_filesize_text,$post_max_size_text, size_format( wp_max_upload_size() ));
		$size_that_can_be_posted = $post_max_size < $upload_max_filesize ? $post_max_size : $upload_max_filesize;
		//Error checking
		if($post_max_size == 0)
			$size_that_can_be_posted = $upload_max_filesize;
		if($upload_max_filesize == 0)
			$size_that_can_be_posted = $post_max_size;
		if($post_max_size == 0 && $upload_max_filesize == 0)
			$size_that_can_be_posted = 1000;
		
		$counter  = $start_index;
		$file_fields_meta  = !is_array($file_fields_meta ) ? array(array()) : $file_fields_meta ;
		foreach($file_fields_meta as $file_meta): 
						
				$file_meta['enable_for'] = !isset($file_meta['enable_for']) ?  'always':$file_meta['enable_for'];
				$file_meta['text_field_on_order_details_page'] = !isset($file_meta['text_field_on_order_details_page']) ?  false:$file_meta['text_field_on_order_details_page'];
				$file_meta['is_text_field_on_order_details_page_required'] = !isset($file_meta['is_text_field_on_order_details_page_required']) ?  false:$file_meta['is_text_field_on_order_details_page_required'];
				$file_meta['sort_order'] = !isset($file_meta['sort_order']) ?  0:$file_meta['sort_order'];
				$file_meta['notify_admin'] = !isset($file_meta['notify_admin']) ?  false:$file_meta['notify_admin'];
				$file_meta['notify_attach_to_admin_email'] = !isset($file_meta['notify_attach_to_admin_email']) ?  false:$file_meta['notify_attach_to_admin_email'];
				$file_meta['message_already_uploaded'] = !isset($file_meta['message_already_uploaded']) ?  $already_uploaded_default_message:$file_meta['message_already_uploaded'];
				$file_meta['disclaimer_checkbox'] = !isset($file_meta['disclaimer_checkbox']) ?  false:$file_meta['disclaimer_checkbox'];
				$file_meta['disclaimer_text'] = !isset($file_meta['disclaimer_text']) ?  "":$file_meta['disclaimer_text'];
				$selected_categories = !isset($file_meta['category_ids']) ? array():$file_meta['category_ids'];
				$selected_products = !isset($file_meta['products_ids']) ? array():$file_meta['products_ids'];
				$notifications_recipients = !isset($file_meta['notifications_recipients']) ? '':$file_meta['notifications_recipients'];
				$file_meta['width_limit'] = isset($file_meta['width_limit']) ? $file_meta['width_limit'] : 0;
				$file_meta['height_limit'] = isset($file_meta['height_limit']) ? $file_meta['height_limit'] : 0;
				$file_meta['ratio_y'] = isset($file_meta['ratio_y']) ? $file_meta['ratio_y'] : 0;				
				$file_meta['ratio_x'] = isset($file_meta['ratio_x']) ? $file_meta['ratio_x'] : 0;				
				$file_meta['min_width_limit'] = isset($file_meta['min_width_limit']) ? $file_meta['min_width_limit'] : 0;
				$file_meta['min_height_limit'] = isset($file_meta['min_height_limit']) ? $file_meta['min_height_limit'] : 0;
				$file_meta['upload_fields_editable_for_completed_orders'] = isset($file_meta['upload_fields_editable_for_completed_orders']) ? $file_meta['upload_fields_editable_for_completed_orders'] : false;
				$file_meta['enable_crop_editor'] = isset($file_meta['enable_crop_editor']) ? $file_meta['enable_crop_editor'] : false;
				$file_meta['cropped_image_width'] = isset($file_meta['cropped_image_width']) ? $file_meta['cropped_image_width'] : 200;
				$file_meta['cropped_image_height'] = isset($file_meta['cropped_image_height']) ? $file_meta['cropped_image_height'] : 200;
				$file_meta['min_dpi_limit'] = isset($file_meta['min_dpi_limit']) ? $file_meta['min_dpi_limit'] : 0;
				$file_meta['max_dpi_limit'] = isset($file_meta['max_dpi_limit']) ? $file_meta['max_dpi_limit'] : 0;
				$text_field_description = isset($file_meta['text_field_description']) ? $file_meta['text_field_description'] : "";
				$counter = isset($file_meta['id']) ? $file_meta['id'] : $counter;
				$is_multiple_file_upload_enabled = isset($file_meta['enable_multiple_uploads_per_field']) && $file_meta['enable_multiple_uploads_per_field'] ? true : false;
				
				?>
				<li class="input_box " id="input_box_<?php echo $counter ?>"> 
					
					<div class="wcuf_drag_button_container">
						<label class="wcuf_sort_button wcuf_no_margin_top "><span class="dashicons dashicons-sort"></span><?php _e('Drag to sort', 'woocommerce-files-upload');?></label>
					</div>
					<div class="wcuf_title_container">
						<label class="wcuf_required wcuf_no_margin_top"><?php echo sprintf(__('ID: %d - Title', 'woocommerce-files-upload'), $counter);?></label>
						<input type ="hidden" class="wcuf_file_meta_id" name= "wcuf_file_meta[<?php echo $counter ?>][id]" value="<?php echo $counter; ?>" ></input>
						<input type ="hidden" class="wcuf_file_meta_sort_order" name= "wcuf_file_meta[<?php echo $counter ?>][sort_order]" value="<?php if(isset($file_meta['sort_order'])) echo $file_meta['sort_order']; else echo $counter; ?>" ></input> <!-- useless -->
						<input type="text" placeholder="<?php  _e('Type the upload field title. HTML code is not allowed.', 'woocommerce-files-upload'); ?>" class="wcuf_upload_field_name" value="<?php if(isset($file_meta['title'])) echo $file_meta['title']; ?>" name="wcuf_file_meta[<?php echo $counter ?>][title]"  size="90" required></textarea >
						<button data-id="<?php echo $counter ?>" class="button wcuf_collapse_options"><?php _e('Show/Hide field options', 'woocommerce-files-upload');?></button>
						<!-- Duplicate button -->
						<a class="wcuf_tooltip duplicate_field button-secondary " data-id="<?php echo $counter ?>" <?php if(isset($file_meta['original_index'])) echo ' disabled="disabled" '; ?>>
							
								<?php if(!isset($file_meta['original_index'])) _e('Duplicate field*', 'woocommerce-files-upload'); else _e('Duplication available only after the field has been saved', 'woocommerce-files-upload'); ?>
								<span><?php _e('The latest saved version will be the one that will be duplicated. So before duplicating a field, save it before.', 'woocommerce-files-upload');?></span>
						</a>
						<!-- end -->
						<button class="remove_field button-secondary" data-id="<?php echo $counter ?>"><?php _e('Delete field', 'woocommerce-files-upload');?></button>
						<!-- <p class="wcuf_duplicate_field_description">* <?php _e('In case the field has been modified, save it before duplicate it. Otherwise the latest saved version will be the one that will be duplicated.', 'woocommerce-files-upload');?></p> -->
					</div>
					<div class="wcuf_visibility_info_box">
						<label><?php _e('PRODUCT VISIBILITY INFO. THIS UPLOAD FIELD WILL BE:', 'woocommerce-files-upload');?></label>
						<!-- <span class="wcuf_visibility_type_info"><?php _e('Enabled/Disabled for:', 'woocommerce-files-upload');?></span> -->
						<i>
						<?php 
							switch($file_meta['enable_for'])
							{
								case 'always':
									echo $enable_for_all_text."<br/>". __('(product visibility can be customized through the <strong>Visibility tab -> Product/Category restriction</strong> area)', 'woocommerce-files-upload') ;
								break;
								case 'categories':
									echo $enable_for_selected_categories_and_products ;	
								break;
								case 'categories_children':
									echo $enable_for_selected_categories_and_products_and_children_text ;	
								break;
								case 'disable_categories':
									echo $disabled_for_selected_categories_and_products_text ;	
								break;
								case 'disable_categories_children':
									echo $disabled_for_selected_categories_and_products_and_children_text ;	
								break;
							}
						
						?>
						</i>
						<?php //Categories
						if(!empty($selected_categories)): ?>
						<span class="wcuf_visibility_type_category_label"><?php _e('Categories:', 'woocommerce-files-upload');?></span>
						<ul>
						<?php foreach( $selected_categories as $category_id)
								{
									echo '<li>'.$wcuf_product_model->get_product_category_name($category_id).'</li>';
								}
						?>
						</ul>
						<?php endif; ?>
						<?php //Products
						if(!empty($selected_products)): ?>
						<span class="wcuf_visibility_type_product_label"><?php _e('Products:', 'woocommerce-files-upload');?></span>
						<ul>
						<?php foreach( $selected_products as $product_id)
								{
									echo '<li>'.$wcuf_product_model->get_product_name($product_id).'</li>';
								}
						?>
						</ul>
						<?php endif; ?>
					</div>
					<div id="wcuf_collapsable_box_<?php echo $counter ?>" class="wcuf_collapsable_box wcuf_box_hidden">
						
						<div class="tab" id="tab-<?php echo $counter ?>">
						  <button class="tablinks active" data-target="#general-tab-<?php echo $counter ?>" data-group-id="<?php echo $counter ?>"><?php _e('General ', 'woocommerce-files-upload');?></button>
						  <button class="tablinks" data-target="#visibility-tab-<?php echo $counter ?>" data-group-id="<?php echo $counter ?>"><?php _e('Visibility ', 'woocommerce-files-upload');?></button>
						  <button class="tablinks" data-target="#media-tab-<?php echo $counter ?>" data-group-id="<?php echo $counter ?>"><?php _e('Media', 'woocommerce-files-upload');?></button>
						  <button class="tablinks" data-target="#extra-costs-tab-<?php echo $counter ?>" data-group-id="<?php echo $counter ?>"><?php _e('Price, fee & discount', 'woocommerce-files-upload');?></button>
						</div>

						<div id="general-tab-<?php echo $counter ?>" class="tabcontent" data-group-id="<?php echo $counter ?>">
							 <div class="wcuf_section_header_container header_container_small_margin" "><!-- style="margin-top:0px -->
								<!--<h2 class=""><?php _e('General ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Description, file types restriction, etc.', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Description, file types restriction, etc.', 'woocommerce-files-upload');?></h3>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Description (HTML code permitted)', 'woocommerce-files-upload');?></label>
								<textarea class="upload_description"  rows="5" cols="80" name="wcuf_file_meta[<?php echo $counter ?>][description]" placeholder="<?php _e('Description (you can use HTML code)', 'woocommerce-files-upload'); ?>"><?php if(isset($file_meta['description'])) echo $file_meta['description']; ?></textarea>
							
								<label class="option_label"><?php _e('Hide description after an upload has been completed?', 'woocommerce-files-upload');?></label>
								<label class="switch">
								  <input type="checkbox" class="" name="wcuf_file_meta[<?php echo $counter ?>][hide_upload_after_upload]" value="true" <?php if(isset($file_meta['hide_upload_after_upload']) && $file_meta['hide_upload_after_upload']) echo 'checked="checked"'?>>
								  <span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Allowed file type(s)', 'woocommerce-files-upload');?></label>
								<input type="text" name="wcuf_file_meta[<?php echo $counter ?>][types]" placeholder="<?php _e('File type(s), ex: .jpg,.bmp,.png leave empty to accept all file types. ', 'woocommerce-files-upload'); ?>" value="<?php if(isset($file_meta['types'])) echo $file_meta['types']; ?>" size="80"></input>
							
								<!-- <label class="option_label"><?php _e('Custom CSS id', 'woocommerce-files-upload');?></label>
								<p><?php _e('Custom id assigned for each Upload files area.', 'woocommerce-files-upload');?></p>
								<input class="" type="text" name="wcuf_file_meta[<?php echo $counter ?>][field_css_id]" value="<?php if(isset($file_meta['field_css_id'])) echo $file_meta['field_css_id']; ?>" ></input>
								--> 
								
								<label class="option_label"><?php _e('In case of Variable Product, display full product name?', 'woocommerce-files-upload');?></label>
								<p><?php _e('Product name and variant details will be displayed. If unchecked will be displayed only product name', 'woocommerce-files-upload');?></p>
								<label class="switch">
								  <input type="checkbox" class="" name="wcuf_file_meta[<?php echo $counter ?>][full_name_display]" value="true" <?php if(!isset($file_meta['full_name_display']) || $file_meta['full_name_display']) echo 'checked="checked"'?> >
								  <span class="slider"></span>
								</label>
								
								<label class="option_label"><?php _e('In case of Simple Product with attributes, display them next to the product name?', 'woocommerce-files-upload');?></label>
								<p><?php _e('This option will display product attributes next to the product name', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input class="" type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][display_simple_product_name_with_attributes]" value="true" <?php if(isset($file_meta['display_simple_product_name_with_attributes']) && $file_meta['display_simple_product_name_with_attributes']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="wcuf_already_uploaded_message_label"><?php _e('Text to show after the upload has been completed (HTML code permitted)', 'woocommerce-files-upload'); ?></label>
								<p><?php _e('Permitted shortcodes:<br/><strong>[file_name]</strong> to display the file(s) name list. For every file is also reported the additional cost (only if any of the extra costs option have been enabled)<br/><strong>[file_name_no_cost]</strong> like previous but without costs display<br/><strong>[file_name_with_media_preview]</strong> like [file_name] shotcode with image preview (if the file(s) is a jpg/png) and audio files (mp3/wav) <br/><strong>[file_name_with_media_preview_no_cost]</strong> like previous shotcode without costs display<br/><strong>[image_preview_list]</strong> to display image preview (if the file(s) is a jpg/png) and audio files (mp3/wav) <br/><strong>[uploaded_files_num]</strong> to display total number of the uploaded files (useful if the "Multiple files upload" option has been enabled)<br/><strong>[additional_costs]</strong> (tax excluded) to display the sum of the additional costs of all the uploaded files', 'woocommerce-files-upload');?></p>
								<textarea  class="upload_description"  rows="5" cols="80" name="wcuf_file_meta[<?php echo $counter ?>][message_already_uploaded]" placeholder="<?php _e('This message is displayed after file description only if a file have been uploaded (you can use HTML code)', 'woocommerce-files-upload'); ?>"><?php if(isset($file_meta['message_already_uploaded'])) echo $file_meta['message_already_uploaded']; ?></textarea>
							</div>
							
							<div class="half_block_fixed_container">
								<label><?php _e('Can user delete file(s)?', 'woocommerce-files-upload');?></label>
								<p><?php _e('Valid only for Order details page', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][user_can_delete]" value="true" <?php if(!isset($file_meta['user_can_delete']) || $file_meta['user_can_delete']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
								
								<label><?php _e('Can user download uploaded file?', 'woocommerce-files-upload');?></label>
								<p><?php _e('Valid only for Order details page', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][user_can_download_his_files]" value="true" <?php if(!isset($file_meta['user_can_download_his_files']) || $file_meta['user_can_download_his_files']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
								<label><?php _e('Hide extra info?', 'woocommerce-files-upload');?></label>
								<p><?php _e('This option will hide the info related to the min/max files, min/max width, min/max DPI, etc', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][hide_extra_info]" value="true" <?php if(isset($file_meta['hide_extra_info']) && $file_meta['hide_extra_info']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							<!-- <label><?php _e('Upload fields are visible also for Orders marked as <i>completed</i> (Valid only for Order details page)?', 'woocommerce-files-upload');?></label>
							<input type="checkbox" class="wcuf_display_as_block" name="wcuf_file_meta[<?php echo $counter ?>][upload_fields_editable_for_completed_orders]" value="true" <?php if(isset($file_meta['upload_fields_editable_for_completed_orders']) && $file_meta['upload_fields_editable_for_completed_orders']) echo 'checked="checked"'?> ></input>
							-->
							
							<div class="wcuf_section_header_container">
								<!-- <h2 class=""><?php _e('General ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Single/Multiple files upload & size restriction', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Single/Multiple files upload & size restriction', 'woocommerce-files-upload');?></h3>
							</div>
							
							<label class="option_label"  ><?php _e('Enable multiple files upload per single field?', 'woocommerce-files-upload');?></label>
							<label class="switch">	
								<input type="checkbox"  name="wcuf_file_meta[<?php echo $counter ?>][enable_multiple_uploads_per_field]" value="true" class="wcuf_multiple_files_upload_checkbox" data-id="<?php echo $counter ?>" <?php if($is_multiple_file_upload_enabled ) echo 'checked="checked"'?> ></input>
								<span class="slider"></span>
							</label>
							
							<div  id="wcuf_single_file_upload_options_container_<?php echo $counter ?>" data-id="<?php echo $counter ?>" class="wcuf_single_file_upload_options_container <?php //if($is_multiple_file_upload_enabled ) echo 'wcuf_hidden'; ?>">
								
								<div class="half_block_container">
									<label class="option_label wcuf_required"><?php _e('Min file size (MB) limit', 'woocommerce-files-upload');?></label>
									<p><?php _e('Leave 0 for no limits. In case of multiple files upload field, each uploaded file size cannot be greater of the specified value.', 'woocommerce-files-upload');?></p>
									<input type="number" min="0"  step="0.01" name="wcuf_file_meta[<?php echo $counter ?>][min_size]" value="<?php if(isset($file_meta['min_size'])) echo $file_meta['min_size']; else echo "0";?>" required></input>
								</div>
								<div class="half_block_container">
									<label class="option_label wcuf_required"><?php _e('Max file size (MB) limit', 'woocommerce-files-upload');?></label>
									<p><?php _e('Leave 0 for no limits. In case of multiple files upload field, each uploaded file size cannot be greater of the specified value.', 'woocommerce-files-upload');?></p>
									<input type="number" min="0"  step="0.01" name="wcuf_file_meta[<?php echo $counter ?>][size]" value="<?php if(isset($file_meta['size'])) echo $file_meta['size']; /*else echo $size_that_can_be_posted; */ else echo "0";?>" required></input>
								</div>
							</div>
							
							<div id="wcuf_multiple_files_upload_options_container_<?php echo $counter ?>" data-id="<?php echo $counter ?>" class="wcuf_multiple_files_upload_options_container <?php if(!$is_multiple_file_upload_enabled ) echo 'wcuf_hidden'; ?>">	
								<div class="half_block_container">
									<label class="option_label"><?php _e('Disable images preview before uploading (jpg/png)?', 'woocommerce-files-upload');?></label>
									<label class="switch">
										<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][preview_images_before_upload_disabled]" value="true" <?php if(isset($file_meta['preview_images_before_upload_disabled']) && $file_meta['preview_images_before_upload_disabled']) echo 'checked="checked"'?> ></input>
										<span class="slider"></span>
									</label>
								</div>
								
								<div class="wcuf_standard_bordered_box">
									<h4><?php _e('Size sum restriction', 'woocommerce-files-upload');?></h4>
									<div class="half_block_container">
										<label class="wcuf_required"><?php _e('Min file sizes sum limit (MB)', 'woocommerce-files-upload');?></label>
										<p><?php echo  _e('Leave 0 for no limit. The <strong>sum of the uploaded file sizes</strong> cannot excede the following value.', 'woocommerce-files-upload'); ?></p>
										<input type="number" min="0" step="0.01" name="wcuf_file_meta[<?php echo $counter ?>][multiple_files_min_size_sum]" value="<?php if(isset($file_meta['multiple_files_min_size_sum'])) echo $file_meta['multiple_files_min_size_sum']; else echo 0; ?>"  required></input>
									</div>	
									<div class="half_block_container">
										<label  class="wcuf_required"><?php _e('Max file sizes sum limit (MB)', 'woocommerce-files-upload');?></label>
										<p><?php echo  _e('Leave 0 for no limit. The <strong>sum of the uploaded file sizes</strong> cannot be lesser than following value.', 'woocommerce-files-upload'); ?></p>
										<input type="number" min="0" step="0.01" name="wcuf_file_meta[<?php echo $counter ?>][multiple_files_max_size_sum]" value="<?php if(isset($file_meta['multiple_files_max_size_sum'])) echo $file_meta['multiple_files_max_size_sum']; else echo 0; ?>"  required></input>
									</div>
								</div>
								
								
								<div class="wcuf_standard_bordered_box">
									<h4><?php _e('Quantity restriction', 'woocommerce-files-upload');?></h4>
									<p><?php _e('Click on the <i>Upload files Configurator -> Options</i> menu to enable the special <strong>Enable quantity selection</strong> option that allows your customers to specify a quantity value for each uploaded file.', 'woocommerce-files-upload');?></p>
									<div class="half_block_container">
										<label class="wcuf_required"><?php _e('Minimum number of files that can uploaded', 'woocommerce-files-upload');?></label>
										<p><?php  _e('Leave 0 for no limits. This option will work <strong>ONLY</strong> if the <strong>Multiple files upload per single field</strong> option has been enabled','woocommerce-files-upload'); ?></p>
										<input type="number"  min="0" name="wcuf_file_meta[<?php echo $counter ?>][multiple_uploads_minimum_required_files]" value="<?php if(isset($file_meta['multiple_uploads_minimum_required_files']) && $file_meta['multiple_uploads_minimum_required_files']) echo $file_meta['multiple_uploads_minimum_required_files']; else echo 0; ?>" required></input>
									</div>
									<div class="half_block_container">
										<label  class="wcuf_required"><?php _e('Max number of files that can be uploaded', 'woocommerce-files-upload');?></label>
										<p><?php  _e('Leave 0 for no limits. This option will work <strong>ONLY</strong> if the <strong>Multiple files upload per single field</strong> option has been enabled','woocommerce-files-upload'); ?></p>
										<input type="number"  min="0"  name="wcuf_file_meta[<?php echo $counter ?>][multiple_uploads_max_files]" value="<?php if(isset($file_meta['multiple_uploads_max_files']) && $file_meta['multiple_uploads_max_files']) echo $file_meta['multiple_uploads_max_files']; else echo 0 /*$max_file_uploads;*/ ?>"   required></input>
										<!-- max="<?php echo $max_file_uploads;?>" -->
									</div>
								</div>
								
								<div class="wcuf_standard_bordered_box">
									<h4><?php _e('Cart quantity restriction', 'woocommerce-files-upload');?></h4>
									<div class="half_block_container">
										<label><?php _e('Max number of uploadable files  depends on product quantity?', 'woocommerce-files-upload');?></label>
										<p><?php  _e('This option will work <strong>ONLY</strong> if the <strong>Upload per product</strong> and the <strong>Multiple files upload per single field</strong> options have been enabled and <strong>if the field is not displayed BEFORE adding items to the cart</strong> on product page','woocommerce-files-upload'); ?></p>
										<label class="switch">
											<input type="checkbox"  name="wcuf_file_meta[<?php echo $counter ?>][multiple_uploads_max_files_depends_on_quantity]" value="true" <?php if(isset($file_meta['multiple_uploads_max_files_depends_on_quantity']) && $file_meta['multiple_uploads_max_files_depends_on_quantity']) echo 'checked="checked"'?> ></input>
											<span class="slider"></span>
										</label>
									</div>
									<div class="half_block_container">								
										<label><?php _e('Minimum number of uploadable files  depends on product quantity?', 'woocommerce-files-upload');?></label>
										<p><?php  _e('This option will work <strong>ONLY</strong> if the <strong>Upload per product</strong> and the <strong>Multiple files upload per single field</strong> options have been enabled and <strong>if the field is not displayed BEFORE adding items to the cart</strong> on product page','woocommerce-files-upload'); ?></p>
										<label class="switch">	
											<input type="checkbox"  name="wcuf_file_meta[<?php echo $counter ?>][multiple_uploads_min_files_depends_on_quantity]" value="true" <?php if(isset($file_meta['multiple_uploads_min_files_depends_on_quantity']) && $file_meta['multiple_uploads_min_files_depends_on_quantity']) echo 'checked="checked"'?> ></input>
											<span class="slider"></span>
										</label>
									</div>
								</div>
							</div>
							<?php //endif; ?>
							
							<div class="wcuf_section_header_container">
								<!-- <h2 class=""><?php _e('General ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Mandatory', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Mandatory', 'woocommerce-files-upload');?></h3>
							</div>
							<label class="option_label"><?php _e('Upload is required', 'woocommerce-files-upload');?></label>
							<label class="switch">
								<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][required_on_checkout]" value="true" <?php if(isset($file_meta['required_on_checkout']) && $file_meta['required_on_checkout']) echo 'checked="checked"'?> ></input>
								<span class="slider"></span>
							</label>
							<p><?php echo $required_field_instruction; ?><br/>
							<?php _e('In case you want to <strong>give the possibility to leave the page</strong>, go to the <strong>Options</strong> menu and under <strong>Allow user to leave page in case of required field</strong> section select <strong>Yes</strong> option.','woocommerce-files-upload'); ?></p>
							<p><strong><?php _e('NOTE','woocommerce-files-upload');?>:</strong> <?php _e('if enabling this option your are experiencing multiple "Add to cart" buttons issues on your shop page, go to the Option menu and set False for the Disable View Button option', 'woocommerce-files-upload'); ?></p>
							
							
							<div class="wcuf_section_header_container">
								<!--<h2 class=""><?php _e('General ', 'woocommerce-files-upload');?></h2>	
								<h3><?php _e('Feedback', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Feedback', 'woocommerce-files-upload');?></h3>
							</div>
							<div class="full_block_container">
								<label class="option_label"><?php _e('Add a text field where the customer can enter a text?', 'woocommerce-files-upload');?></label>
								<p><?php _e('<strong>NOTE</strong>: text must be inserted before files are uploaded.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][text_field_on_order_details_page]" value="true"  <?php if(isset($file_meta['text_field_on_order_details_page']) && $file_meta['text_field_on_order_details_page']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Label', 'woocommerce-files-upload');?></label>
								<input type="text" name="wcuf_file_meta[<?php echo $counter ?>][text_field_label]" value="<?php if(isset($file_meta['text_field_label'])) echo $file_meta['text_field_label']; ?>"   ></input>
							
								<label ><?php _e('Description (HTML  allowed)', 'woocommerce-files-upload');?></label>
								<textarea type="text" name="wcuf_file_meta[<?php echo $counter ?>][text_field_description]" cols="80" rows="5"><?php echo $text_field_description; ?></textarea>
							</div>
							
							<div class="half_block_fixed_container">	
								<label style=""><?php _e('Is required?', 'woocommerce-files-upload');?></label>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][is_text_field_on_order_details_page_required]" value="true"  <?php if(isset($file_meta['is_text_field_on_order_details_page_required']) && $file_meta['is_text_field_on_order_details_page_required']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
								<label style=""><?php _e('Max input characters', 'woocommerce-files-upload');?></label>
								<p><?php _e('Leave 0 for no limits.', 'woocommerce-files-upload');?></p>
								<input type="number" min="0" name="wcuf_file_meta[<?php echo $counter ?>][text_field_max_input_chars]" value="<?php if(isset($file_meta['text_field_max_input_chars'])) echo $file_meta['text_field_max_input_chars']; else echo 0; ?>"   ></input>
							
							</div>
							
							<div class="wcuf_section_header_container">
								<!--<h2 class=""><?php _e('General ', 'woocommerce-files-upload');?></h2>	
								<h3><?php _e('Disclaimer', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Disclaimer', 'woocommerce-files-upload');?></h3>
							</div>
							<div class="full_block_container">	
								<label class="option_label"><?php _e('Add a disclaimer checkbox?', 'woocommerce-files-upload');?></label>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][disclaimer_checkbox]" value="true"  <?php if(isset($file_meta['disclaimer_checkbox']) && $file_meta['disclaimer_checkbox']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							<div class="half_block_fixed_container">	
								<label class="option_label"><?php _e('Disclameir checkbox label', 'woocommerce-files-upload');?></label>
								<p><?php  _e('HTML accepted. Ex: "I have read and accepted the &lt;a href="www.link.to/disclaimer"&gt; Disclaimer &lt;/a&gt;".', 'woocommerce-files-upload'); ?></p>
								<textarea type="text" class="wcuf_disclaimer_text" name="wcuf_file_meta[<?php echo $counter ?>][disclaimer_text]" cols="80" rows="5"><?php if(isset($file_meta['disclaimer_text'])) echo $file_meta['disclaimer_text']; ?></textarea>
							</div>
							<div class="wcuf_section_header_container">
								<!--<h2 class=""><?php _e('General ', 'woocommerce-files-upload');?></h2>	
								<h3><?php _e('Notifications', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Notifications & emails', 'woocommerce-files-upload');?></h3>
							</div>
							
							<div class="full_block_container">	
								<label class="option_label"><?php _e('Notify admin via email when customer completed the upload?', 'woocommerce-files-upload');?></label>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][notify_admin]" value="true" <?php if(isset($file_meta['notify_admin']) && $file_meta['notify_admin']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							<div class="half_block_fixed_container">	
								<label class="option_label"><?php _e('Attach uploaded file to admin notification email?', 'woocommerce-files-upload');?></label>
								<p><?php _e('This option works only if admin notification email option has been enabled and for files stored locally.', 'woocommerce-files-upload'); ?><br/><?php _e('<strong>Note:</strong> some some server email provider will not receive emails with attachments bigger than 10MB (<a target="_blank" href="https://www.outlook-apps.com/maximum-email-size/">Gmail: 25MB, Outlook and Hotmail 10MB,...</a>)', 'woocommerce-files-upload'); ?></p>
								<label class="switch">	
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][notify_attach_to_admin_email]" value="true" <?php if(isset($file_meta['notify_attach_to_admin_email']) && $file_meta['notify_attach_to_admin_email']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							<div class="half_block_fixed_container">	
								<label class="option_label"><?php _e('Recipient(s)', 'woocommerce-files-upload');?></label>
								<p><?php  _e('Leave empty to send notifications to site admin email address.', 'woocommerce-files-upload'); ?></p>
								<input type="text" name="wcuf_file_meta[<?php echo $counter ?>][notifications_recipients]" placeholder="<?php _e("You can insert multiple email addresses comma separated, ex.: 'admin@site.com, managment@site.com'", "woocommerce-files-upload"); ?>" value="<?php echo $notifications_recipients; ?>" size="100"></input>
							</div>
							<div class="full_block_container">	
								<label class="option_label"><?php _e('Attach files to "New Order" email?', 'woocommerce-files-upload');?></label>
								<p><?php  _e('This option requires to enable the previous <strong>Notify admin via email when customer completed the upload</strong> option, the files to be stored locally (<strong>Options</strong> -> <strong>Cloud storage service</strong> -> <strong>Locally</strong>) and that in the <strong>Options</strong> -> <strong>Checkout - Files to order association method</strong> the <strong>When the order is placed</strong> option to be selected.', 'woocommerce-files-upload'); ?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][email_attach_files_to_new_order]" value="true" <?php if(isset($file_meta['email_attach_files_to_new_order']) && $file_meta['email_attach_files_to_new_order']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							<button class="scroll-to-top-button button-primary" data-target="#tab-<?php echo $counter ?>"><?php _e('Go to top', 'woocommerce-files-upload');?> <span class="dashicons dashicons-arrow-up-alt"></button>
							
						</div> <!-- end tab conent -->
						
						<!-- Visibility -->
						<div id="visibility-tab-<?php echo $counter ?>" class="tabcontent tabcontent-hidden " data-group-id="<?php echo $counter ?>">
							<div class="wcuf_section_header_container header_container_small_margin">
								<!--<h2 class=""><?php _e('Visibility ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Pages & form', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Pages & form', 'woocommerce-files-upload');?></h3>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Display field on Checkout page?', 'woocommerce-files-upload');?></label>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][display_on_checkout]" value="true" <?php if(isset($file_meta['display_on_checkout']) && $file_meta['display_on_checkout']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							<div class="half_block_fixed_container">	
								<label class="option_label"><?php _e('Display field on Cart page?', 'woocommerce-files-upload');?></label>
								<label class="switch">		
									<input type="checkbox" data-id="<?php echo $counter ?>" class="wcuf_display_on_cart_checkbox" name="wcuf_file_meta[<?php echo $counter ?>][display_on_cart]" value="true" <?php if(isset($file_meta['display_on_cart']) && $file_meta['display_on_cart']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Display field on Product page?', 'woocommerce-files-upload');?></label>
								<p><?php  _e('This will enable the <strong>Upload per product</strong> option. <strong>NOTE:</strong> for products for which has been enabled the <strong>Addable multiple times to cart</strong> feature (through the special options menu) the upload field will appear automatically <strong>BEFORE</strong> adding them to cart. ', 'woocommerce-files-upload') ?></p> 
								<label class="switch">	
									<input type="checkbox" data-id="<?php echo $counter ?>" class="wcuf_display_on_product_checkbox" name="wcuf_file_meta[<?php echo $counter ?>][display_on_product]" value="true" <?php if(!isset($file_meta['display_on_product']) || $file_meta['display_on_product']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
								<div class="wcuf_product_page_visibility_sub_option" id="wcuf_display_on_product_before_adding_to_cart_container_<?php echo $counter ?>">
									<label class="option_label"><?php _e('on Product page, display the field BEFORE adding an item to the cart?', 'woocommerce-files-upload');?></label>
									<label class="switch">	
										<input type="checkbox" data-id="<?php echo $counter ?>" id="wcuf_display_on_product_before_adding_to_cart_<?php echo $counter ?>" class="" name="wcuf_file_meta[<?php echo $counter ?>][display_on_product_before_adding_to_cart]" value="true" <?php if(!isset($file_meta['display_on_product_before_adding_to_cart']) || $file_meta['display_on_product_before_adding_to_cart']) echo 'checked="checked"'?> ></input>
										<span class="slider"></span>
									</label>
									<p><?php  echo $upload_product_page_before_instruction; ?></p>
								</div>
							</div>
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Display field on Thank you page?', 'woocommerce-files-upload');?></label>
								<p><?php  _e('Thank you page is the one in which the user lands after the checkout process is completed.', 'woocommerce-files-upload') ?></p> 
								<label class="switch">	
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][display_on_thank_you]" value="true" <?php if(isset($file_meta['display_on_thank_you']) && $file_meta['display_on_thank_you']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Display field on Order detail page?', 'woocommerce-files-upload');?></label>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][display_on_order_detail]" value="true" <?php if(!isset($file_meta['display_on_order_detail']) || $file_meta['display_on_order_detail']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Hide on shortcode upload form?', 'woocommerce-files-upload');?></label>
								<p><?php _e('By default using the <strong>[wcuf_upload_form]</strong> shortcode all the upload fields that  match products in the cart are visible. Enabling this option this field will be hidden.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" class="wcuf_display_as_block"  name="wcuf_file_meta[<?php echo $counter ?>][hide_on_shortcode_form]" value="true" <?php if(isset($file_meta['hide_on_shortcode_form']) && $file_meta['hide_on_shortcode_form']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="wcuf_section_header_container">
								<!--<h2 class=""><?php _e('Visibility ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Show the upload field for each product on cart/order', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Show the upload field for each product on cart/order', 'woocommerce-files-upload');?></h3>
							</div>
							<p class="section_description"><?php echo $upload_per_product_instruction; ?></p>
							<div class="half_block_fixed_container">
								<label class="option_label"  ><?php _e('Upload per product', 'woocommerce-files-upload');?></label>
								<p><?php _e('This option cannot be disabled if the upload field is displayed on Product page.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" id="wcuf_multiple_uploads_checkbox_<?php echo $counter ?>" name="wcuf_file_meta[<?php echo $counter ?>][disable_stacking]" value="true" <?php if(!isset($file_meta['disable_stacking']) || $file_meta['disable_stacking']) echo 'checked="checked"' ?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"  ><?php _e('Display the upload field for every product variation? ', 'woocommerce-files-upload');?></label>
								<p><?php _e('Valid only for variable products. This options works only if the previous <strong>Upload per product</strong> option has been enabled. In case of generic variations (the ones for which has been assigned the "Any" value to any attribute), the upload field will not work.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox"  name="wcuf_file_meta[<?php echo $counter ?>][disable_stacking_for_variation]" value="true" <?php if(!isset($file_meta['disable_stacking_for_variation']) || $file_meta['disable_stacking_for_variation']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="wcuf_section_header_container">
								<!--<h2 class=""><?php _e('Visibility ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Product/Category restriction', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Product/Category restriction', 'woocommerce-files-upload');?></h3>
							</div>
							
							<p class="section_description"><?php _e('Upload field can optionally <strong>visible/hidden</strong> only if the selected products are in cart/order.', 'woocommerce-files-upload');?></p>
							<label class="option_label"><?php _e('This upload field will be', 'woocommerce-files-upload');?></label>							
							<select  class="upload_type" data-id="<?php echo $counter ?>" name="wcuf_file_meta[<?php echo $counter ?>][enable_for]">
							  <option value="always" <?php if(isset($file_meta['enable_for']) && $file_meta['enable_for'] == 'always') echo 'selected'; ?>><?php echo $enable_for_all_text; ?></option>
							  <option value="categories" <?php if(isset($file_meta['enable_for']) && $file_meta['enable_for'] == 'categories') echo 'selected'; ?>><?php echo $enable_for_selected_categories_and_products; ?></option>
							  <option value="categories_children" <?php if(isset($file_meta['enable_for']) && $file_meta['enable_for'] == 'categories_children') echo 'selected'; ?>><?php echo $enable_for_selected_categories_and_products_and_children_text ?></option>
							  <option value="disable_categories"  <?php if(isset($file_meta['enable_for']) && $file_meta['enable_for'] == 'disable_categories') echo 'selected'; ?>><?php echo $disabled_for_selected_categories_and_products_text?></option>
							  <option value="disable_categories_children"  <?php if(isset($file_meta['enable_for']) && $file_meta['enable_for'] == 'disable_categories_children') echo 'selected'; ?>><?php echo $disabled_for_selected_categories_and_products_and_children_text?></option>
							</select>
							
							<div class="upload_categories_box" id='upload_categories_box<?php echo $counter ?>'>
								<label><?php _e('Select categories (search typing category name)', 'woocommerce-files-upload');?></label>
								<select class="js-data-product-categories-ajax wcuf_select2"  id='upload_type_id<?php echo $counter; ?>' name='wcuf_file_meta[<?php echo $counter; ?>][categories][]'  multiple='multiple'> 
										<?php 
											foreach( $selected_categories as $category_id)
												{
													echo '<option value="'.$category_id.'" selected="selected" >'.$wcuf_product_model->get_product_category_name($category_id).'</option>';
												}
											?>
								</select>
								
								<label><?php echo $product_filtering_instruction;?></label>
								<select class="js-data-products-ajax wcuf_select2" id="product_select_box<?php echo $counter; ?>"  name='wcuf_file_meta[<?php echo $counter; ?>][products][]' multiple='multiple'> 
								<?php 
									foreach( $selected_products as $product_id)
										{
											echo '<option value="'.$product_id.'" selected="selected" >'.$wcuf_product_model->get_product_name($product_id).'</option>';
										}
									?>
								</select>
							</div>
							
							<div class="wcuf_section_header_container">
								<!--<h2 class=""><?php _e('Visibility ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Order status', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Order status', 'woocommerce-files-upload');?></h3>
							</div>
							<p class="section_description"><?php _e('Select for which status the upload field will be <strong>hidden</strong>. This option only affects the <strong>Order details / Thank</strong> page and if no option is selected the field will be <strong>always visible</strong>.', 'woocommerce-files-upload');?></p>
							
							<?php foreach($wcuf_order_model->get_available_order_statuses() as $status_code => $status_name): ?>
							<?php $checked = isset($file_meta['order_status'][$status_code]) ? ' checked="checked" ' : "";?>
								<label style="font-weight:normal;">
									<input type="checkbox" <?php echo $checked; ?> name="wcuf_file_meta[<?php echo $counter ?>][order_status][<?php echo $status_code; ?>]" value="1"><?php echo $status_name ?>
								</label>
							<?php endforeach; ?>
							
							<div class="wcuf_section_header_container">
								<!--<h2 class=""><?php _e('Visibility ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('User role', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('User role', 'woocommerce-files-upload');?></h3>
							</div>
							<p class="section_description"><?php _e('<strong>Leave unselected to leave the upload field visible for all.</strong> Selecting at least one role will make the upload field to be visible/unvisible to that role.', 'woocommerce-files-upload');?></p>
							<label class="option_label"><?php _e('Visibility type', 'woocommerce-files-upload');?></label>		
							<select  class="upload_type"  name="wcuf_file_meta[<?php echo $counter ?>][roles_policy]">
							  <option value="allow" <?php if(isset($file_meta['roles_policy']) && $file_meta['roles_policy'] == 'allow') echo 'selected'; ?>><?php _e('Allow for selected roles', 'woocommerce-files-upload');?></option>
							  <option value="deny" <?php if(isset($file_meta['roles_policy']) && $file_meta['roles_policy'] == 'deny') echo 'selected'; ?>><?php _e('Deny for selected roles', 'woocommerce-files-upload');?></option>
							</select>
							
							<label class="option_label"><?php _e('Select roles', 'woocommerce-files-upload');?></label>	
							<?php foreach($wcuf_customer_model->get_user_roles() as $role_code => $role_name): ?>
								<?php $checked = isset($file_meta['roles'][$role_code]) ? ' checked="checked" ' : "";?>
								<label style="font-weight:normal;"><input type="checkbox" <?php echo $checked; ?> name="wcuf_file_meta[<?php echo $counter ?>][roles][<?php echo $role_code; ?>]" value="1"><?php echo $role_name['name'] ?></label>
							<?php endforeach; ?>
								<?php $checked = isset($file_meta['roles']['not_logged']) ? ' checked="checked" ' : "";?>
								<label style="font-weight:normal;"><input type="checkbox" <?php echo $checked; ?> name="wcuf_file_meta[<?php echo $counter ?>][roles][not_logged]" value="1"><?php _e('Guest (<strong>Not logged user</strong>)', 'woocommerce-files-upload');?></label>
							
							<div class="wcuf_section_header_container">
								<!--<h2 class="wcuf_upper_spacer"><?php _e('Visibility ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Payment gateway', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Payment gateway', 'woocommerce-files-upload');?></h3>
							</div>
							
							<p class="section_description"><?php _e('<strong>Leave unselected to leave the upload field visible for all gateways.</strong> Selecting at least one gateway option will make the upload field to be visible/unvisible to that gateway and only in <strong>Order details</strong> and <strong>Checkout</strong> pages. If any option is selected, the field will be <strong>always invisible</strong> on Product and Cart pages.', 'woocommerce-files-upload');?></p>
							
							<label class="option_label"><?php _e('Visibility type', 'woocommerce-files-upload');?></label>		
							<select  class="upload_type"  name="wcuf_file_meta[<?php echo $counter ?>][visibility_payment_gateway_policy]">
							  <option value="allow" <?php if(isset($file_meta['visibility_payment_gateway_policy']) && $file_meta['visibility_payment_gateway_policy'] == 'allow') echo 'selected'; ?>><?php _e('Allow for selected gateways', 'woocommerce-files-upload');?></option>
							  <option value="deny" <?php if(isset($file_meta['visibility_payment_gateway_policy']) && $file_meta['visibility_payment_gateway_policy'] == 'deny') echo 'selected'; ?>><?php _e('Deny for selected gateways', 'woocommerce-files-upload');?></option>
							</select>
							
							<label class="option_label"><?php _e('Select gateways', 'woocommerce-files-upload');?></label>	
							<?php $gateways = new WC_Payment_Gateways() ?>
							<?php foreach($gateways->payment_gateways( ) as $gateway_code => $gateway): ?>
								<?php $checked = isset($file_meta['visibility_gateways'][$gateway_code]) ? ' checked="checked" ' : "";?>
								<label style="font-weight:normal;"><input type="checkbox" <?php echo $checked; ?> name="wcuf_file_meta[<?php echo $counter ?>][visibility_gateways][<?php echo $gateway_code; ?>]" value="1"><?php echo $gateway->title; ?></label>
							<?php endforeach; ?>
							<button class="scroll-to-top-button button-primary" data-target="#tab-<?php echo $counter ?>"><?php _e('Go to top', 'woocommerce-files-upload');?> <span class="dashicons dashicons-arrow-up-alt"></button>
						</div><!-- end tab conent -->
						
						<div id="media-tab-<?php echo $counter ?>" class="tabcontent tabcontent-hidden " data-group-id="<?php echo $counter ?>">
							<div class="wcuf_section_header_container header_container_small_margin">
								<!--<h2 class=""><?php _e('Media ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Image crop, dimensions and DPI restriction', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Image crop', 'woocommerce-files-upload');?></h3>
							</div>
							<p class="section_description">
								<strong><?php _e('The following options will have effects only for or jpg/png media files', 'woocommerce-files-upload'); ?></strong>
							</p>
							<div class="wcuf_crop_box">
								<h4><?php _e('Crop', 'woocommerce-files-upload');?></h4>
								<div class="full_block_container">	
									<label class="option_label"><?php _e('Enable crop editor', 'woocommerce-files-upload');?></label>
									<label class="switch">	
										<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][enable_crop_editor]" value="true" <?php if(isset($file_meta['enable_crop_editor']) && $file_meta['enable_crop_editor']) echo 'checked="checked"'; ?>></input>
										<span class="slider"></span>
									</label>
								</div>
								<div class="one_third_block_container">	
									<label class="option_label"><?php _e('Cropped image shape', 'woocommerce-files-upload');?></label>
									<p><?php _e('Accroding the selected width and height the cropped image will be shaped as square (rectangle if the width and height are not equal) or a cirle (ellipse if the width and height are not equal).', 'woocommerce-files-upload');?></p>
									<select name="wcuf_file_meta[<?php echo $counter ?>][crop_area_type]">
										<option value="square" <?php if(isset($file_meta['crop_area_type']) && $file_meta['crop_area_type'] == 'square') echo 'selected="selected"'; ?>><?php _e('Square / rectangle', 'woocommerce-files-upload');?></option>
										<option value="circle" <?php if(isset($file_meta['crop_area_type']) && $file_meta['crop_area_type'] == 'circle') echo 'selected="selected"'; ?>><?php _e('Circle / ellipse', 'woocommerce-files-upload');?></option>
									</select>
								</div>
								<div class="one_third_block_container">	
									<label class="option_label wcuf_required"><?php _e('Cropped image width', 'woocommerce-files-upload');?></label>
									<input type="number" min="1" step="1" name="wcuf_file_meta[<?php echo $counter ?>][cropped_image_width]" value="<?php if(isset($file_meta['cropped_image_width'])) echo $file_meta['cropped_image_width']; ?>" required></input>
								</div>
								<div class="one_third_block_container">	
									<label class="option_label wcuf_required"><?php _e('Cropped image height', 'woocommerce-files-upload');?></label>
									<input type="number" min="1"  step="1" name="wcuf_file_meta[<?php echo $counter ?>][cropped_image_height]" value="<?php if(isset($file_meta['cropped_image_height'])) echo $file_meta['cropped_image_height']; ?>" required></input>
								</div>
								<div class="half_block_container">	
									<label class="option_label"><?php _e('Cropping is mandatory in case of multiple files upload', 'woocommerce-files-upload');?></label>
									<p><?php _e('In case of multiple files upload, the user will be force to cropp all the selected files. For single file upload, the cropping is always mandatory.', 'woocommerce-files-upload');?></p>
									<label class="switch">
										<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][crop_mandatory_for_multiple_files_upload]" value="true" <?php if(isset($file_meta['crop_mandatory_for_multiple_files_upload']) && $file_meta['crop_mandatory_for_multiple_files_upload']) echo 'checked="checked"'; ?>></input>
										<span class="slider"></span>
									</label>
								</div>	
							</div>
							<div class="wcuf_dimensions_box">
								<h4><?php _e('Size restriction', 'woocommerce-files-upload');?></h4>
								<div class="half_block_container">	
									<label class="option_label wcuf_required"><?php _e('Input image min width in px', 'woocommerce-files-upload');?></label>
									<p><?php _e('Leave 0 for no limits.', 'woocommerce-files-upload');?></p>
									<input type="number" min="0" name="wcuf_file_meta[<?php echo $counter ?>][min_width_limit]" value="<?php if(isset($file_meta['min_width_limit'])) echo $file_meta['min_width_limit']; ?>" required></input>
								</div>								
								<div class="half_block_container">
									<label class="option_label wcuf_required"><?php _e('Input image min height in px', 'woocommerce-files-upload');?></label>
									<p><?php _e('Leave 0 for no limits.', 'woocommerce-files-upload');?></p>
									<input type="number" min="0" name="wcuf_file_meta[<?php echo $counter ?>][min_height_limit]" value="<?php if(isset($file_meta['min_height_limit'])) echo $file_meta['min_height_limit']; ?>" required></input>
								</div>
									
								<div class="dimensions_logical_operator">
									<select  name="wcuf_file_meta[<?php echo $counter ?>][dimensions_logical_operator]" class="wcuf_dimensions_logical_operator">
									  <option value="and" <?php if(isset($file_meta['dimensions_logical_operator']) && $file_meta['dimensions_logical_operator'] == 'and') echo 'selected'; ?>><?php _e('AND', 'woocommerce-files-upload');?></option>
									  <option value="or" <?php if(isset($file_meta['dimensions_logical_operator']) && $file_meta['dimensions_logical_operator'] == 'or') echo 'selected'; ?>><?php _e('OR', 'woocommerce-files-upload');?></option>
									</select>
								</div>
								<div class="half_block_container">	
									<label class="option_label wcuf_required"><?php _e('Input image max width in px', 'woocommerce-files-upload');?></label>
									<p><?php _e('Leave 0 for no limits.', 'woocommerce-files-upload');?></p>
									<input type="number" min="0" name="wcuf_file_meta[<?php echo $counter ?>][width_limit]" value="<?php if(isset($file_meta['width_limit'])) echo $file_meta['width_limit']; ?>" required></input>
								</div>
								<div class="half_block_container">	
									<label class="option_label wcuf_required"><?php _e('Input image max height in px', 'woocommerce-files-upload');?></label>
									<p><?php _e('Leave 0 for no limits.', 'woocommerce-files-upload');?></p>
									<input type="number" min="0" name="wcuf_file_meta[<?php echo $counter ?>][height_limit]" value="<?php if(isset($file_meta['height_limit'])) echo $file_meta['height_limit']; ?>" required></input>
								</div>
							</div>
							
							<div class="wcuf_dpi_box">
								<h4><?php _e('Ratio restriction', 'woocommerce-files-upload');?></h4>
								<div class="half_block_container">	
									<label class="option_label wcuf_required"><?php _e('x ratio', 'woocommerce-files-upload');?></label>
									<p><?php _e('Leave 0 to ignore. <strong>NOTE:</strong> if any of the two settings are left 0, the ratio restriction will be ignored.', 'woocommerce-files-upload');?></p>
									<input type="number" min="0" name="wcuf_file_meta[<?php echo $counter ?>][ratio_x]" value="<?php echo $file_meta['ratio_x'] ?>" required></input>
								</div>
								<div class="half_block_container">	
									<label class="option_label wcuf_required"><?php _e('y ratio', 'woocommerce-files-upload');?></label>
									<p><?php _e('Leave 0 for ignore. <strong>NOTE:</strong> if any of the two settings are left 0, the ratio restriction will be ignored.', 'woocommerce-files-upload');?></p>
									<input type="number" min="0" name="wcuf_file_meta[<?php echo $counter ?>][ratio_y]" value="<?php echo $file_meta['ratio_y'] ?>" required></input>
								</div>
							</div>
							
							<div class="wcuf_dpi_box">
								<h4><?php _e('DPI restriction', 'woocommerce-files-upload');?></h4>
								<div class="half_block_container">	
									<label class="option_label wcuf_required"><?php _e('Input image min DPI', 'woocommerce-files-upload');?></label>
									<p><?php _e('Leave 0 for no limits. <strong>NOTE:</strong> DPI are read from EXIF so If an image has no valid EXIF data check will fail and the upload will not be performed.', 'woocommerce-files-upload');?></p>
									<input type="number" min="0" name="wcuf_file_meta[<?php echo $counter ?>][min_dpi_limit]" value="<?php echo $file_meta['min_dpi_limit'] ?>" required></input>
								</div>
								<div class="half_block_container">	
									<label class="option_label wcuf_required"><?php _e('Input image max DPI', 'woocommerce-files-upload');?></label>
									<p><?php _e('Leave 0 for no limits. <strong>NOTE:</strong> DPI are read from EXIF so If an image has no valid EXIF data check will fail and the upload will not be performed.', 'woocommerce-files-upload');?></p>
									<input type="number" min="0" name="wcuf_file_meta[<?php echo $counter ?>][max_dpi_limit]" value="<?php echo $file_meta['max_dpi_limit'] ?>" required></input>
								</div>
							</div>
							
							<div class="wcuf_section_header_container">
								<!--<h2 class=""><?php _e('Media ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Audio/Video length restriction', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Audio/Video length restriction', 'woocommerce-files-upload');?></h3>
							</div>
							<p class="section_description">
								<strong><?php _e('The following options will have effects only for mp3/mp4/wav/webm/m4v/flac media files', 'woocommerce-files-upload'); ?></strong>
							</p>
							<div class="half_block_container">	
								<label class="option_label wcuf_required"><?php _e('Min length (seconds)', 'woocommerce-files-upload');?></label>
								<p><?php _e('Leave 0 for no limits.', 'woocommerce-files-upload');?></p>								
								<input type="number" min="0" step="1" name="wcuf_file_meta[<?php echo $counter ?>][min_seconds_length]" value="<?php if(isset($file_meta['min_seconds_length'])) echo $file_meta['min_seconds_length']; else echo 0; ?>" required></input>
							</div>
							<div class="half_block_container">	
								<label class="option_label wcuf_required"><?php _e('Max length (seconds)', 'woocommerce-files-upload');?></label>
								<p><?php _e('Leave 0 for no limits.', 'woocommerce-files-upload');?></p>								
								<input type="number" min="0"  step="1" name="wcuf_file_meta[<?php echo $counter ?>][max_seconds_length]" value="<?php if(isset($file_meta['max_seconds_length'])) echo $file_meta['max_seconds_length'];  else echo 0; ?>" required></input>
							</div>
							
							<label class="option_label"><?php _e('In case of multiple files upload, consider as length the sum of all file seconds?', 'woocommerce-files-upload');?></label>
							<label class="switch">
								<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][consider_sum_of_media_seconds]" value="true" <?php if(isset($file_meta['consider_sum_of_media_seconds']) && $file_meta['consider_sum_of_media_seconds']) echo 'checked="checked"'?> ></input>
								<span class="slider"></span>
							</label>
							<button class="scroll-to-top-button button-primary" data-target="#tab-<?php echo $counter ?>"><?php _e('Go to top', 'woocommerce-files-upload');?> <span class="dashicons dashicons-arrow-up-alt"></button>
						</div><!-- end tab conent -->
						
						<!-- Extra costs -->
						<div id="extra-costs-tab-<?php echo $counter ?>" class="tabcontent tabcontent-hidden " data-group-id="<?php echo $counter ?>">
						<!-- Checkout -->
							<div class="wcuf_section_header_container header_container_small_margin">
								<!--<h2 class=""><?php _e('Checkout', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Order sub total percentage discount', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Order sub total percentage discount', 'woocommerce-files-upload');?></h3>
							</div>
							<p class="section_description"><strong><?php _e('This options ill not take effect on Order details and Thank you pages','woocommerce-files-upload'); ?></strong></p>
							<div class="full_block_container">
								<label class="option_label"><?php _e('Enable checkout discount?', 'woocommerce-files-upload');?></label>
								<p><?php _e('If at least one upload has been performed, a percentage discount will be applied to the order sub total.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][checkout_percentage_enabled]" value="true" <?php if(isset($file_meta['checkout_percentage_enabled']) && $file_meta['checkout_percentage_enabled']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Percentage', 'woocommerce-files-upload');?></label>
								<p><?php _e('This is the percentage discount applied to sub total.', 'woocommerce-files-upload');?></p>
								<input class="wcuf_no_margin_bottom" type="number" name="wcuf_file_meta[<?php echo $counter ?>][checkout_percentage_value]"  step="0.01" min="0.01" max="100" value="<?php if(isset($file_meta['checkout_percentage_value'])) echo $file_meta['checkout_percentage_value']; else echo '1';?>" ></input>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Discount description to show on cart', 'woocommerce-files-upload');?></label>
								<p><?php _e('If left black will be used as description the upload field name. Use the <strong>%field_title</strong> placeholder to print the upload field title inside the discount description.', 'woocommerce-files-upload');?></p>
								<input type="text" class="wcuf_text_field_large" name="wcuf_file_meta[<?php echo $counter ?>][checkout_percentage_description]" placeholder ="<?php _e('Discount for %field_title','woocommerce-files-upload'); ?>" value="<?php if(isset($file_meta['checkout_percentage_description'])) echo $file_meta['checkout_percentage_description']; ?>"  ></input>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Apply only once', 'woocommerce-files-upload');?></label>
								<p><?php _e('Enable this option if you do not want to apply the order sub total discount if another upload field already has.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][checkout_percentage_only_once]" value="true" <?php if(isset($file_meta['checkout_percentage_only_once']) && $file_meta['checkout_percentage_only_once']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							<!-- Fee -->
							<div class="wcuf_section_header_container">
								<!--<h2 class=""><?php _e('Extra costs ', 'woocommerce-files-upload');?></h2>
								<h3><?php _e('Cart fee/discount', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Cart fee/discount', 'woocommerce-files-upload');?></h3>
							</div>
							<p class="section_description"><strong><?php _e('This options ill not take effect on Order and Thank you pages','woocommerce-files-upload'); ?></strong></p>
							
							<div class="full_block_container">
								<label class="option_label"><?php _e('Enable fee/discount per upload?', 'woocommerce-files-upload');?></label>
								<p><?php _e('For each uploaded file the plugin will compute additional costs according the following options. The extra costs will be added to cart as fee.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_enabled]" value="true" <?php if(isset($file_meta['extra_cost_enabled']) && $file_meta['extra_cost_enabled']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_container">
								<label class="option_label"><?php _e('Overcharge type', 'woocommerce-files-upload');?></label>							
								<p><?php _e('<strong>NOTE:</strong> <strong>Percentage</strong> option will work with Variations/Variable products, only if: <ol><li><strong>Display the upload field for every product variation</strong> option has been enabled</li><!-- <li><strong>on Product page, display the field BEFORE adding an item to the cart</strong> option has been disabled</li>--></ol>', 'woocommerce-files-upload');?></p>
								
								<select  name="wcuf_file_meta[<?php echo $counter ?>][extra_overcharge_type]">
								  <option value="fixed" <?php if(isset($file_meta['extra_overcharge_type']) && $file_meta['extra_overcharge_type'] == 'fixed') echo 'selected'; ?>><?php _e('Fixed value', 'woocommerce-files-upload');?></option>
								  <option value="percentage" <?php if(isset($file_meta['extra_overcharge_type']) && $file_meta['extra_overcharge_type'] == 'percentage') echo 'selected'; ?>><?php _e('Percentage of item price', 'woocommerce-files-upload');?></option>
								</select>
							</div>
							
							<div class="half_block_container">
								<label class="option_label"><?php _e('Value', 'woocommerce-files-upload');?></label>
								<p><?php _e('This will be the percentage or the fixed value added/subtracted to the original item price. Using <strong>negative</strong> values, the fixed/percentage value will be subtracted to the cart (applying then a <strong>discount</strong>).', 'woocommerce-files-upload');?></p>
							
								<input class="wcuf_no_margin_bottom" type="number" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_value]"  step="any" value="<?php if(isset($file_meta['extra_cost_value'])) echo $file_meta['extra_cost_value']; else echo '1';?>" ></input>
							</div>
							
							<div class="half_block_container">
								<label class="option_label"><?php _e('Fee/Discount description to show on cart', 'woocommerce-files-upload');?></label>
								<p><?php _e('If left black will be used as description the upload field name. Use the <strong>%prod_name</strong> placeholder to print the product name and the <strong>%field_title</strong> placeholder to print the upload field title inside the fee description.', 'woocommerce-files-upload');?></p>
								<input type="text" class="wcuf_text_field_large" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_fee_description]" placeholder ="<?php _e('Extra costs %field_title for %prod_name','woocommerce-files-upload'); ?>" value="<?php if(isset($file_meta['extra_cost_fee_description'])) echo $file_meta['extra_cost_fee_description']; ?>"  ></input>
							</div>
							<div class="half_block_container">
								<label class="option_label"><?php _e('Is taxable?', 'woocommerce-files-upload');?></label>
								<p><?php _e('Note that in case of negative values (discount) due to a WooCommerce bug, the fee will be always taxes included.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_is_taxable]" value="true" <?php if(isset($file_meta['extra_cost_is_taxable']) && $file_meta['extra_cost_is_taxable']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_container">
								<label class="option_label"><?php _e('Apply extra costs/discount considering the item cart quantity', 'woocommerce-files-upload');?></label>
								<p><?php _e('The computed extra cost will be multiplied for the product cart quantity. If not, the extra cost will be applied only once regardles of item cart quantity. <strong>NOTE:</strong> This option will only work if the <strong>Upload per product</strong> option has been enabled.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_multiply_per_product_cart_quantity]" value="true" <?php if(isset($file_meta['extra_cost_multiply_per_product_cart_quantity']) && $file_meta['extra_cost_multiply_per_product_cart_quantity']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_container">
								<?php if (true/* extension_loaded('imagick') */): ?>
								<label class="option_label"><?php _e('Detect <span class="wcuf_pdf_label">PDF</span>', 'woocommerce-files-upload');?></label>
								<p><?php _e('The extra costs will be applied to each detected page.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_detect_pdf]" value="true" <?php if(isset($file_meta['extra_cost_detect_pdf']) && $file_meta['extra_cost_detect_pdf']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
								<?php endif; ?>
							</div>
							<div class="half_block_container">
								<label class="option_label"><?php _e('Overcharge uploads limit', 'woocommerce-files-upload');?></label>
								<p><?php _e('Leave 0 for no limits. Applies only if the <strong>Multiple files upload per single field</strong> option has been enabled. If the number of uploaded files (excluding the "Free intems" defined in the option below) will pass this value will not be added extra overcharge for exceding uploads.', 'woocommerce-files-upload');?>
								<?php if (true/* extension_loaded('imagick') */):
									_e('In case of PDF detection: this will option will be applied to pages and in case di multiple uploads, extra cost pages limit is considered globally per field and not per each pdf.','woocommerce-files-upload');
								endif; ?>
								</p>
								<input class="wcuf_no_margin_bottom" type="number" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_overcharge_limit]" step="1" min="0" value="<?php if(isset($file_meta['extra_cost_overcharge_limit'])) echo $file_meta['extra_cost_overcharge_limit']; else echo '0';?>" ></input>
							</div>
							<div class="half_block_container">
								<label class="option_label"><?php _e('Free items', 'woocommerce-files-upload');?></label>
								<p><?php _e('Leave 0 for no free items. This option works only if the <strong>Multiple files upload per single field</strong> option has been enabled. For the first N uploads will not be applied any extra cost (where N is the value specified using the following number field). ', 'woocommerce-files-upload');?>
								<?php if (true/* extension_loaded('imagick') */):
									_e('In case of PDF detection: this will be considered as "free pages number" and in case di multiple uploads, free pages are computed globally per field and not per each pdf.','woocommerce-files-upload');
								 endif; ?>
								</p>
								<input class="wcuf_no_margin_bottom" type="number" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_free_items_number]" step="1" min="0" value="<?php if(isset($file_meta['extra_cost_free_items_number'])) echo $file_meta['extra_cost_free_items_number']; else echo '0';?>" ></input>
							</div>
							
							<div class="wcuf_section_header_container">
								<!--<h2 class=""><?php _e('Extra costs ', 'woocommerce-files-upload');?></h2>	
								<h3><?php _e('Fee per second', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Fee per second', 'woocommerce-files-upload');?></h3>
							</div>
							<p class="section_description"><?php _e('These option will apply <strong>only for Video/Audio files</strong>. WCUF will try do detect media file the duration (in seconds) extracting the info from its ID3 data (if any and well encoded). For each detected second the plugin will compute the extra costs according the following options.', 'woocommerce-files-upload');?></p>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Enable extra cost per second?', 'woocommerce-files-upload');?></label>
								<p><?php _e('The computed extra cost will be added as Cart fee.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_media_enabled]" value="true" <?php if(isset($file_meta['extra_cost_media_enabled']) && $file_meta['extra_cost_media_enabled']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Is taxable?', 'woocommerce-files-upload');?></label>
								<p><?php _e('Note that in case of negative values (discount) due to a WooCommerce bug, the fee will be always taxes included.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_media_is_taxable]" value="true" <?php if(isset($file_meta['extra_cost_media_is_taxable']) && $file_meta['extra_cost_media_is_taxable']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Fee/Discount description to show on cart', 'woocommerce-files-upload');?></label>
								<p><?php _e('If left black will be used as description the upload field name. Use the <strong>%prod_name</strong> placeholder to print the product name and the <strong>%field_title</strong> placeholder to print the upload field title inside the fee description.', 'woocommerce-files-upload');?></p>
								<input type="text" class="wcuf_text_field_large" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_media_fee_description]" placeholder ="<?php _e('Extra costs %field_title for %prod_name','woocommerce-files-upload'); ?>" value="<?php if(isset($file_meta['extra_cost_media_fee_description'])) echo $file_meta['extra_cost_media_fee_description']; ?>"  ></input>
							</div>

							<div class="half_block_fixed_container">	
								<label class="option_label"><?php _e('Display the "Cost per second" text on cart?', 'woocommerce-files-upload');?></label>
								<p><?php _e('An extra text will be added reporting how much costs a second.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][show_cost_per_second]" value="true" <?php if(isset($file_meta['show_cost_per_second']) && $file_meta['show_cost_per_second']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Cost per second', 'woocommerce-files-upload');?></label>
								<p><?php _e('This is the cost per each detected second.', 'woocommerce-files-upload');?></p>
								<input class="wcuf_no_margin_bottom" type="number" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_per_second_value]"  step="any" value="<?php if(isset($file_meta['extra_cost_per_second_value'])) echo $file_meta['extra_cost_per_second_value']; else echo '1';?>" ></input>
							</div>
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Maximun seconds overcharge limit', 'woocommerce-files-upload');?></label>
								<p><?php _e('Leave 0 for no limits. If the number of seconds (excluding the "Free seconds" defined in the option below) will pass this value will not be added extra overcharge for exceding seconds.', 'woocommerce-files-upload');?></p>
								<input class="wcuf_no_margin_bottom" type="number" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_overcharge_seconds_limit]" step="1" min="0" value="<?php if(isset($file_meta['extra_cost_overcharge_seconds_limit'])) echo $file_meta['extra_cost_overcharge_seconds_limit']; else echo '0';?>" ></input>
							</div>
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('Free seconds', 'woocommerce-files-upload');?></label>
								<p><?php _e('First N seconds can be free, set the desidered values. Leave 0 for no free seconds.', 'woocommerce-files-upload');?></p>
								<input class="wcuf_no_margin_bottom" type="number" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_free_seconds]" step="1" min="0" value="<?php if(isset($file_meta['extra_cost_free_seconds'])) echo $file_meta['extra_cost_free_seconds']; else echo '0';?>" ></input>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('In case of multiple files upload, apply the Overcharge limit and the Free seconds considering the sum of all files seconds?', 'woocommerce-files-upload');?></label>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][extra_costs_consider_sum_of_all_file_seconds]" value="true" <?php if(isset($file_meta['extra_costs_consider_sum_of_all_file_seconds']) && $file_meta['extra_costs_consider_sum_of_all_file_seconds']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="wcuf_section_header_container">
								<!--<h2 class=""><?php _e('Extra costs ', 'woocommerce-files-upload');?></h2>	
								<h3><?php _e('Fee as product cart price', 'woocommerce-files-upload');?></h3>-->
								<h3 class="tab-title"><?php _e('Product cart price', 'woocommerce-files-upload');?></h3>
							</div>
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('The computed fee will be used as product cart price', 'woocommerce-files-upload');?></label>
								<p><?php _e('Use the computed as product price on cart. <strong>NOTE:</strong> in case of multiple extra costs due to multiple upload fields bounded to the product, the product price will be the sum of the all the existing extra costs.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_as_item_price]"  id="extra_cost_as_item_price_<?php echo $counter ?>" class="wcuf_fee_item_price_checkbox" data-id-to-uncheck="extra_cost_add_to_item_price_<?php echo $counter ?>" value="true" <?php if(isset($file_meta['extra_cost_as_item_price']) && $file_meta['extra_cost_as_item_price']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<div class="half_block_fixed_container">
								<label class="option_label"><?php _e('The computed fee will be added to product cart price', 'woocommerce-files-upload');?></label>
								<p><?php _e('The computed fee will be added to the product price. <strong>NOTE:</strong> in case of multiple extra costs due to multiple upload fields bounded to the product, the product price will be the sum of the all the existing extra costs. In case some fields bounded to the same product have the "compted fee as product price" active, it will be ignored an used this option instead.', 'woocommerce-files-upload');?></p>
								<label class="switch">
									<input type="checkbox" name="wcuf_file_meta[<?php echo $counter ?>][extra_cost_add_to_item_price]" id="extra_cost_add_to_item_price_<?php echo $counter ?>" class="wcuf_fee_item_price_checkbox" data-id-to-uncheck="extra_cost_as_item_price_<?php echo $counter ?>"  value="true" <?php if(isset($file_meta['extra_cost_add_to_item_price']) && $file_meta['extra_cost_add_to_item_price']) echo 'checked="checked"'?> ></input>
									<span class="slider"></span>
								</label>
							</div>
							
							<button class="scroll-to-top-button button-primary" data-target="#tab-<?php echo $counter ?>"><?php _e('Go to top', 'woocommerce-files-upload');?> <span class="dashicons dashicons-arrow-up-alt"></button>
						</div><!-- end tab conent -->
						
						<div class="spacer" ></div>
						<button class="remove_field button-secondary" data-id="<?php echo $counter; ?>"><?php _e('Delete field', 'woocommerce-files-upload');?></button>
					</div>
				</li>
		<?php $counter++; endforeach; 
	}
}