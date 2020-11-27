jQuery(document).ready(function()
{
	//console.log("here");
	//wc_fragment_refresh updated_wc_div
	jQuery(document.body).on('wc_fragment_refresh updated_wc_div', wcuf_manage_upload_area_visibility);
});
function wcuf_manage_upload_area_visibility()
{
	wcuf_show_upload_field_area();
	//jQuery('#wcuf_'+wcuf_current_page+'_ajax_container').fadeTo(100, 1);
	//jQuery('#wcuf_'+wcuf_current_page+'_ajax_container').animate({opacity: 1}, 200);
}