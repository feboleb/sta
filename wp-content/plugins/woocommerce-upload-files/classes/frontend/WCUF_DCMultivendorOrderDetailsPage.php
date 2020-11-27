<?php 
class WCUF_DCMultivendorOrderDetailsPage
{
	public function __construct()
	{
		add_action('wcmp_order_details_after_customer_details', array(&$this, 'render_order_uploaded_files_area'));
	}
	
	function render_order_uploaded_files_area($order)
	{
		global $woocommerce, $WCMp, $wcuf_option_model,$wcuf_upload_field_model;
		$user = wp_get_current_user();
		$vendor = apply_filters('wcmp_dashboard_order_details_vendor', get_wcmp_vendor($user->ID));
		$vendor_items = $vendor->get_vendor_items_from_order($order->get_id(), $vendor->term_id);
		$upload_field_already_displayed = array();
		// wcuf_var_dump($vendor_items);
		if (!$vendor || !$order->get_id())
			return;
		
		 if (!$order || sizeof($order->get_items()) == 0)
			 return;
		 
		 wp_enqueue_style('wcuf-dc-multivendor-order-details-page',  wcuf_PLUGIN_PATH.'/css/wcuf-frontend-dc-multivendor-order-details-page.css')
		
		 
		 ?>
		 <dt><?php  _e('Uploads', 'woocommerce-files-upload'); ?></dt>
		 <?php 
		  foreach ($vendor_items as $item)
		  {
			$_product = apply_filters('dc_woocommerce_order_item_product', $order->get_product_from_item($item), $item);
			/* wcuf_var_dump($_product->get_id());
			wcuf_var_dump($_product->get_parent_id());   */
			
			$upload_product_id = $_product->get_id()."-".$_product->get_parent_id();
			
			//HTML
			$file_fields_meta = $wcuf_option_model->get_fields_meta_data();
			$uploaded_files = $wcuf_upload_field_model->get_uploaded_files_meta_data_by_order_id($order->get_id()); 
			//$counter = 0;
			?>
			
			
			<dd>
				<?php if(empty($uploaded_files)): echo '<strong>'.__('Customer hasn\'t uploaded any file...yet.', 'woocommerce-files-upload').'</strong>'; 
				else:?>
				<ul class="totals">
				 <?php foreach($uploaded_files as $file_meta): 
					
					if(strpos($file_meta["id"], $upload_product_id) === false && !is_numeric($file_meta["id"]))
						continue;
								
					if(isset($upload_field_already_displayed[$file_meta["id"]]))
						continue;
					
					$upload_field_already_displayed[$file_meta["id"]] = true;
					
					
																				//could be used 'soruce'. test if is setted and != 'dropbox'
					//$is_zip = is_array($file_meta['original_filename']) && !WCUF_DropBox::is_dropbox_file_path($file_meta['absolute_path']) ? true : false;
					$is_zip = $wcuf_upload_field_model->is_upload_field_content_managed_as_zip($file_meta);
					$is_multiple_files = $wcuf_upload_field_model->is_upload_field_content_managed_as_multiple_files($file_meta); 
					$original_name = "";
					//wcuf_var_dump($file_meta['id']);
					if($is_zip || $is_multiple_files)
						$original_name = __('Multiple files', 'woocommerce-files-upload');
					else if(isset($file_meta['original_filename']))
						$original_name = !is_array($file_meta['original_filename']) ? $file_meta['original_filename'] : $file_meta['original_filename'][0];
					
					if($original_name == "")
						continue;
					?>
					<li class="wcuf_upload_list_element">
						<h5 class="wcuf_upload_field_title"><?php echo $file_meta['title']." : ".$original_name;?></h5>
						<?php 
							$quantity = 1;
							if(!$is_zip && !$is_multiple_files)
								echo __('Quantity: ', 'woocommerce-files-upload')."<i>".$quantity."</i></br></br>";
						
						 
						if($is_zip || $is_multiple_files) //old multiple file managment
						{
							$files_name = "<ol>";
							//$counter = 0;
							foreach( $file_meta['original_filename'] as $counter => $temp_file_name)
							{
								if(isset($file_meta['quantity'][$counter]))
									$quantity = is_array($file_meta['quantity'][$counter]) ? array_sum($file_meta['quantity'][$counter]) : $file_meta['quantity'][$counter];
								if($is_zip)
								{
									$zip_file_name = basename ($file_meta['absolute_path']);
									if(!$wcuf_upload_field_model->is_dropbox_stored($file_meta))
										$files_name .= '<li><a target="_blank" href="'.get_site_url().'?wcuf_zip_name='.$zip_file_name.'&wcuf_single_file_name='.$temp_file_name.'&wcuf_order_id='.$order->get_id().'">'.$temp_file_name.'</a> ('.__('Quantity: ', 'woocommerce-files-upload').$quantity.')</li>';
									else
										$files_name .= '<li>'.$temp_file_name.' ('.__('Quantity: ', 'woocommerce-files-upload').$quantity.')</li>';
								}
								else
									$files_name .= '<li><a target="_blank" href="'.$file_meta['url'][$counter].'" download>'.$temp_file_name.'</a> ('.__('Quantity: ', 'woocommerce-files-upload').$quantity.')</li>';
								$counter++;
							}
							$files_name .= "</ol>";
							echo $files_name;
						}
						?>
						
						
						<?php if(isset($file_meta['user_feedback']) && $file_meta['user_feedback'] != "" && $file_meta['user_feedback'] != "undefined"):?>
							<p style="margin-top:5px;">
								<strong><?php echo _e('User feedback', 'woocommerce-files-upload'); ?></strong></br>
								<?php echo $file_meta['user_feedback'];?>
							</p>
						<?php endif;?>
						<?php $media_counter = 0;
							if(isset($file_meta['ID3_info']) && $file_meta['ID3_info'] != "none"): ?>
							<p style="margin-top:5px;">
								<strong><?php echo _e('Media info', 'woocommerce-files-upload') ?></strong></br>
								<?php	foreach($file_meta['ID3_info'] as $file_media_info):?>
												<?php if($media_counter > 0) echo "<br/>";?>
												<?php  echo __('Name: ', 'woocommerce-files-upload')."<i>".$file_media_info['file_name']."</i>";?></br> 
												<?php echo __('Duration: ', 'woocommerce-files-upload')."<i>".$file_media_info['playtime_string']."</i>"?></br>
												<!-- <?php echo __('Quantity: ', 'woocommerce-files-upload')."<i>".$file_media_info['quantity']."</i>"?></br> -->
												<?php $media_counter++; 
										endforeach; ?>
							</p>
						<?php endif;?>
							
						<p style="margin-top:3px;">
							<?php if($is_zip || !$is_multiple_files): 
								$file_url = !is_array($file_meta['url']) ? $file_meta['url'] : $file_meta['url'][0];
							?>
								<a target="_blank" class="button button-primary" href="<?php echo $file_url; ?>"><?php _e('Download', 'woocommerce-files-upload'); ?></a>
							<?php elseif($wcuf_upload_field_model->can_be_zip_file_created_upload_field_content($file_meta)): ?>
								<p><a target="_blank" class="button button-primary" href="<?php echo get_site_url();?>?wcuf_create_zip_for_field=<?php echo $file_meta['id']; ?>&wcuf_order_id=<?php echo $order->get_id();?>"><?php _e('Download all as zip', 'woocommerce-files-upload'); ?></a>
								</p>
							<?php endif; ?>
							<!-- <input  type="submit" class="button delete_button" data-fileid="<?php echo $file_meta['id'] ?>" value="<?php _e('Delete content(s)', 'woocommerce-files-upload'); ?>" onclick="clicked(event);" ></input> -->
						</p>
					</li>
				  <?php endforeach;?>
				</ul>
				<?php endif; ?>
				</dd>
			
			<script type="text/javascript">
			var index = 0;
			function clicked(e) 
				{ 
				  /*  console.log(e.target); */
				   e.preventDefault();
				   if(confirm('<?php _e('Are you sure?', 'woocommerce-files-upload'); ?>'))
				   {
					   jQuery("#upload-box").append( '<input type="hidden" name="files_to_delete['+index+']" value="'+jQuery(e.target).data('fileid')+'"></input>');
					   jQuery(e.target).parent().remove();
					   index++;
				   }
				}
			</script>
			<div class="clear"></div>
			<?php 
			//End HTML
		  }
	}
}
?>