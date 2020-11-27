function berocket_sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds){
            break;
        }
    }
}

var br_tooltip_number = 1;
function berocket_regenerate_tooltip() {
	jQuery('.tippy-box').parent().remove();
    jQuery('.br_alabel .br_tooltip').each(function() {
        if( ! jQuery(this).is('.br_tooltip_ready') ) {
            jQuery(this).addClass('br_tooltip_'+br_tooltip_number).addClass('br_tooltip_ready');
            jQuery(this).parents('.br_alabel').first().addClass('br_alabel_tooltip_'+br_tooltip_number);
            tippy('.br_alabel_tooltip_'+br_tooltip_number+' > span', {
            	content: jQuery('.br_tooltip_'+br_tooltip_number).html(),
            	allowHTML: true,
                onClickOutside(instance, event) {
                    if ( instance.props.hideOnClick === true ) {
                        berocket_sleep(instance.props.delay[1]);
                    }
                },
            });
            jQuery(this).parents('.br_alabel').find('*').attr('title', '');
            br_tooltip_number++;
        }
    });
    jQuery('.br_alabel > span[data-tippy-trigger="click"]').on('click', function(event) {
        event.preventDefault();	
        event.stopImmediatePropagation();
    });
}
berocket_regenerate_tooltip();
jQuery(document).on('load berocket_ajax_products_loaded berocket_ajax_products_infinite_loaded', berocket_regenerate_tooltip);
