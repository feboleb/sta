<div class="wcuf_crop_container wcuf_not_to_be_showed" id="wcuf_crop_container_<?php echo $cropper_product_id; ?>">
	<div class="wcuf_crop_image_box" id="wcuf_crop_image_box_<?php echo $cropper_product_id; ?>">
		<!-- <div class="wcuf_crop_thumb_box" id="wcuf_crop_thumb_box_<?php echo $cropper_product_id; ?>"></div> 
		<div class="wcuf_crop_thumb_spinner" style="display: none" id="wcuf_crop_thumb_spinner_<?php echo $cropper_product_id; ?>"><?php _e('Loading...','woocommerce-files-upload'); ?></div> -->
	</div>
	<div class="wcuf_crop_container_actions" id="wcuf_crop_container_actions_<?php echo $cropper_product_id; ?>">
		<!-- <button class="button wcuf_crop_button wcuf_remove_button_extra_content wcuf_zoomin_button" id="btnZoomIn_<?php echo $cropper_product_id; ?>" ><?php echo $button_texts['zoom_in_crop_button']; ?></button>
		<button class="button wcuf_crop_button wcuf_remove_button_extra_content wcuf_zoomout_button" id="btnZoomOut_<?php echo $cropper_product_id; ?>"  ><?php echo $button_texts['zoom_out_crop_button']; ?></button> -->
		<?php if(!$all_options['crop_disable_rotation_controller']): ?>
		<button class="button wcuf_crop_button wcuf_remove_button_extra_content wcuf_rotate_left" id="btnRotateLeft_<?php echo $cropper_product_id; ?>"  data-id="<?php echo $cropper_product_id; ?>"><?php echo $button_texts['rotate_left_button']; ?></button>
		<button class="button wcuf_crop_button wcuf_remove_button_extra_content wcuf_rotate_right" id="btnRotateRight_<?php echo $cropper_product_id; ?>"  data-id="<?php echo $cropper_product_id; ?>"><?php echo $button_texts['rotate_right_button']; ?></button>
		<?php endif; ?>
		<button class="button wcuf_crop_button wcuf_remove_button_extra_content wcuf_crop_upload_button" id="btnCrop_<?php echo $cropper_product_id; ?>"  ><?php echo $button_texts['crop_and_upload_button']; ?></button>
		
	</div>
</div>
<div id="wcuf_crop_upload_image_for_rotating_status_box_<?php echo $cropper_product_id; ?>" class="wcuf_crop_upload_image_for_rotating_status_box">
		<div class="wcuf_bar" id="wcuf_crop_rotating_upload_bar_<?php echo $cropper_product_id; ?>"></div >
		<div id="wcuf_crop_rotating_upload_percent_<?php echo $cropper_product_id; ?>">0%</div>
		<div class="wcuf_crop_rotating_upload_status_message" id="wcuf_crop_rotating_upload_status_message_<?php echo $file_fields['id']; ?>"><?php _e('Rotating the image, please wait...','woocommerce-files-upload'); ?></div>
</div>