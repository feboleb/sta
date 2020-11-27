var wcuf_current_paymenth_method = 'none';
jQuery(document).ready(function()
{
	if(wcuf_options.exists_at_least_one_upload_field_bounded_to_gateway == 'true')
	{
		//console.log(jQuery("li.wc_payment_method input.input-radio"));
		//jQuery(document).on('click','input.input-radio', wcuf_on_payment_method_change);
		//jQuery(document).on('click','li.wc_payment_method input.input-radio', wcuf_on_payment_method_change);
		//jQuery(document.body).on('click','ul.wc_payment_methods.payment_methods.methods li.wc_payment_method.payment_method_bacs input.input-radio', wcuf_on_payment_method_change);
		jQuery('li.wc_payment_method input.input-radio').on('click', wcuf_on_payment_method_change);
		jQuery("li.wc_payment_method input.input-radio").each(function(index, elem)
		{
			if(/* jQuery(elem).prop('name') == 'payment_method' &&  */jQuery(elem).prop('checked'))
				jQuery(elem).trigger('click');
		});  
		//to workaround the non "live" jquery method that seems to not working
		jQuery( document.body ).on( 'updated_checkout', function()
		{
			wcuf_show_upload_field_area();
			jQuery('li.wc_payment_method input.input-radio').on('click', wcuf_on_payment_method_change);
		} );
	}
	
	//this is used for upload fields showed inside the div containing the product table. That div is dynamically updated and it could happen that the upload area 
	//is reloaded remaining with 0 opacity
	jQuery( document.body ).on( 'updated_checkout', function()
	{
		wcuf_show_upload_field_area();
	} );
	
	jQuery('.woocommerce-shipping-fields__field-wrapper').css('display', 'block');
	jQuery('.woocommerce-shipping-fields__field-wrapper').animate({opacity: 1}, 0);
});
function wcuf_on_payment_method_change(event)
{
	/* console.log(jQuery(event.target).prop('name'));
	if(jQuery(event.target).prop('name') != 'payment_method')
		return; */
	
	var method_id = jQuery(event.target).val();
	var random = Math.floor((Math.random() * 1000000) + 999);
	wcuf_current_paymenth_method = method_id;
	var formData = new FormData();
	formData.append('action', 'reload_upload_fields_on_checkout');
	formData.append('payment_method', method_id);
	formData.append('wcuf_wpml_language', wcuf_wpml_language);
	
	//UI
	jQuery('#wcuf_'+wcuf_current_page+'_ajax_container').animate({ opacity: 0 }, 500, function()
	{
		//UI
		jQuery('#wcuf_'+wcuf_current_page+'_ajax_container_loading_container').html("<h4>"+wcuf_ajax_reloading_fields_text+"</h4>");
		
		jQuery.ajax({
			url: wcuf_ajaxurl+"?nocache="+random,
			type: 'POST',
			data: formData,
			async: false,
			dataType : "html",
			contentType: "application/json; charset=utf-8",
			success: function (data) 
			{
				//UI
				jQuery('#wcuf_'+wcuf_current_page+'_ajax_container_loading_container').html("");  
				jQuery('#wcuf_'+wcuf_current_page+'_ajax_container').html(data);
				jQuery('#wcuf_'+wcuf_current_page+'_ajax_container').animate({ opacity: 1 }, 500);	
							
				//Hide add to cart in case of required field 
				//wcuf_hide_add_to_cart_button_if_product_page_and_before_add();
				//wcuf_hide_add_to_cart_button_in_case_of_required_upload(600, 400);
							
			},
			error: function (data) {
				//wcuf_show_popup_alert("Error: "+data);
			},
			cache: false,
			contentType: false,
			processData: false
		});
	});
			
}
