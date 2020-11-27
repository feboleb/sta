var wcuf_multiple_cropper = null;
jQuery(document).ready(function()
{
	jQuery(document).on('click', '.wcuf_single_crop_button', wcuf_crop_single_file);
});
function wcuf_crop_single_file(event)
{
	event.preventDefault();
	event.stopImmediatePropagation();
	
	const file_id = jQuery(event.currentTarget).data('id');
	const file_unique_id = jQuery(event.currentTarget).data('file-unique-id');
	const scroll_to = "#wcuf_upload_field_button_"+file_id;
	let delay = 400;
	
	
	
	//wcuf_multiple_files_queues[file_id][file_index]
	if(wcuf_multiple_cropper != null)
	{
		wcuf_multiple_cropper.wcuf_croppie_destroy();
		delay = 0;
	}
	//UI
	try{
		jQuery('html, body').animate({
				  scrollTop: jQuery(scroll_to).offset().top - 150
				}, 1000);
		
	}catch(error){}
	
	let file_index = -1;
	for(let i = 0; i < wcuf_multiple_files_queues[file_id].length; i++)
		if( wcuf_multiple_files_queues[file_id][i].unique_id == file_unique_id)
			file_index = i;
	
	if(file_index < 0)
		return false;
	
	let fake_event = {target: {files:[wcuf_multiple_files_queues[file_id][file_index]]},
					  file_id: file_id,
					  file_unique_id: file_unique_id,
					  file_index: file_index};
	wcuf_multiple_cropper = new wcuf_image_crop(fake_event, file_id, wcuf_on_crop_performed);

	return false;
}
function wcuf_on_crop_performed(event)
{
	/* console.log("wcuf_on_crop_performed");
	console.log(event); */
	
	const file_id = event.file_id;
	//const file_index = event.file_index;
	let file_index = -1;
	const file_unique_id = event.file_unique_id;
	const file_preview_name = "#wcuf_single_image_preview_"+file_unique_id;
	wcuf_multiple_cropper.wcuf_croppie_destroy_and_hide();
	delete wcuf_multiple_cropper;
	
	for(let i = 0; i < wcuf_multiple_files_queues[file_id].length; i++)
		if( wcuf_multiple_files_queues[file_id][i].unique_id == file_unique_id)
			file_index = i;
	if(file_index < 0)
		return;		
	
	//UI: remove mandatory crop border 
	jQuery('#wcuf_single_file_in_multiple_list_'+file_unique_id).removeClass('wcuf_mandatory_crop');
	
	//update
	/* console.log("wcuf_on_crop_performed");
	console.log(file_id);
	console.log(file_index);
	console.log(file_unique_id);
	console.log(wcuf_multiple_files_queues);
	console.log(wcuf_multiple_files_queues[file_id][file_index]); */
	jQuery(file_preview_name).attr('src',   URL.createObjectURL(event.blob) );
	const quantity = wcuf_multiple_files_queues[file_id][file_index].quantity;
	const file_name = wcuf_multiple_files_queues[file_id][file_index].name;
	const type = wcuf_multiple_files_queues[file_id][file_index].type;
	//wcuf_multiple_files_queues[file_id][file_index] = new File([event.blob], file_name);
	wcuf_multiple_files_queues[file_id][file_index] = event.blob;
	wcuf_multiple_files_queues[file_id][file_index].quantity = quantity;
	wcuf_multiple_files_queues[file_id][file_index].is_cropped = true;
	wcuf_multiple_files_queues[file_id][file_index].file_unique_id = file_unique_id; //Is needed?
	wcuf_multiple_files_queues[file_id][file_index].unique_id = file_unique_id; //unique id
	wcuf_multiple_files_queues[file_id][file_index].type = type;
	wcuf_multiple_files_mandatory_crop[file_id][file_index] = false; 
	/* console.log("***");
	console.log(wcuf_multiple_files_queues);
	console.log(wcuf_multiple_files_queues[file_id][file_index]); */
}