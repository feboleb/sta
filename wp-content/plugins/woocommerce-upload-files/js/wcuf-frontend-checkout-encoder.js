function wcuf_encode_file(evt) 
{
    var files = evt.target.files;
    var file = files[0];
	var id =  jQuery(evt.currentTarget).data('id');
	
	/* console.log(files);
	console.log(file.name); */
	jQuery('#wcuf-filename-'+id).val(file.name);
    if (files && file) 
	{
        var reader = new FileReader();

        reader.onload = function(readerEvt) 
		{
            var binaryString = readerEvt.target.result;
            //document.getElementById("base64textarea").value = btoa(binaryString);
		   if(!jQuery('#wcuf-encoded-file_'+id).length)
				jQuery('#wcuf-files-box').append('<input type="hidden" name="wcuf-encoded-file['+id+']" id="wcuf-encoded-file_'+id+'" />');
			
			jQuery('#wcuf-encoded-file_'+id).val(btoa(binaryString));			
	   };
		reader.readAsBinaryString(file);
    }
};