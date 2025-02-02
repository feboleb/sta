var thwepof_settings = (function($, window, document) {
	'use strict';

	$(function() {
		var settings_form = $('#thwepof_product_fields_form');

		thwepof_base.setup_sortable_table(settings_form, '#thwepof_product_fields', '0');
		thwepof_base.setup_tiptip_tooltips();
		thwepof_base.setup_form_wizard();
	});
   
	function select_all_fields(elm){
		var checkAll = $(elm).prop('checked');
		$('#thwepof_product_fields tbody input:checkbox[name=select_field]').prop('checked', checkAll);
	}
   	
	function remove_selected_fields(){
		$('#thwepof_product_fields tbody tr').removeClass('strikeout');
		$('#thwepof_product_fields tbody input:checkbox[name=select_field]:checked').each(function () {
			var row = $(this).closest('tr');
			if(!row.hasClass("strikeout")){
				row.addClass("strikeout");
			}
			row.find(".f_deleted").val(1);
			row.find(".f_edit_btn").prop('disabled', true);
	  	});	
	}

	function enable_disable_selected_fields(enabled){
		$('#thwepof_product_fields tbody input:checkbox[name=select_field]:checked').each(function(){
			var row = $(this).closest('tr');

			if(enabled == 0){
				if(!row.hasClass("thwepof-disabled")){
					row.addClass("thwepof-disabled");
				}
			}else{
				row.removeClass("thwepof-disabled");				
			}
			
			row.find(".f_edit_btn").prop('disabled', enabled == 1 ? false : true);
			row.find(".td_enabled").html(enabled == 1 ? '<span class="dashicons dashicons-yes tips" data-tip="Yes"></span>' : '-');
			row.find(".f_enabled").val(enabled);
	  	});
	}
	   				
	return {
		select_all_fields : select_all_fields,
		remove_selected_fields : remove_selected_fields,
		enable_disable_selected_fields : enable_disable_selected_fields,
   	};
}(window.jQuery, window, document));	

function thwepofSelectAllProductFields(elm){
	thwepof_settings.select_all_fields(elm);
}

function thwepofRemoveSelectedFields(){
	thwepof_settings.remove_selected_fields();
}

function thwepofEnableSelectedFields(){
	thwepof_settings.enable_disable_selected_fields(1);
}

function thwepofDisableSelectedFields(){
	thwepof_settings.enable_disable_selected_fields(0);
}
