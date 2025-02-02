var br_saved_timeout;
var br_savin_ajax = false;
var br_each_parent_tr;
var page_loading = true;
(function ($){
    br_each_parent_tr = function(selector, hide, thtd) {
        var better_position = $('.berocket_label_better_position').prop('checked');

        $(selector).each(function(i, o) {
            if( $(o).is('.berocket_label_better_position_hide') && better_position 
                || $(o).is('.berocket_label_better_position_show') && ! better_position) {
                hide = true;
            }
            var whathide = $(o).parents('tr').first();
            if( thtd ) {
                whathide = whathide.find('th, td');
            }
            if( hide ) {
                whathide.hide();
            } else {
                whathide.show();
                if ( $(o).hasClass('berocket_label_attribute_select') ) {
                    whathide.removeClass('berocket_template_hide_not_worked_option');
                }
            }
        });

        if ( better_position ) {
            $('.berocket_framework_menu_position .berocket_label_better_position_hide').closest('tr').hide();
            $('.berocket_framework_menu_position .berocket_label_better_position_show').closest('tr').show();
        } else {
            $('.berocket_framework_menu_position .berocket_label_better_position_hide').closest('tr').show();
            $('.berocket_framework_menu_position .berocket_label_better_position_show').closest('tr').hide();
        }

        var $selected_template = $('.br_label_css_templates input[name="br_labels[template]"]:checked');
    };

    $(document).ready( function () {
        hide_by_default();

        $(document).on('change', '.berocket_label_units', function() {
            var self = $(this);

            self.closest('td').find('input[type=number]').attr('data-ext', self.val());
        });

        if( $('.br_range').length ) {
            function br_apl_range_size_change() {
                var self = $(this);
                self.closest('td').find('.br_range_num').val( self.val() );
                self.closest('td').find('.br_range').val( self.val() );
            }
            $(document).on('change input', '.br_range, .br_range_num', br_apl_range_size_change);
            $(document).on('mousedown touchstart', '.br_range', function() {
                $('.br_range').addClass('br_js_change');
            });
            $(document).on('mouseup touchend', function() {
                $('.br_range').removeClass('br_js_change');
            });
            $('.br_range').change();
        }

        $(document).on('change', '.br_option_scale', function() {
            var fields = $(this).closest('td').find('input, select').not('.br_option_scale');

            fields.each( function() {
                var self = $(this);
                self.val( self.attr('data-default') );
            });
            fields.parent().toggle();
        });

        $(document).on('change', '.berocket_label_content_type', function() {
            br_each_parent_tr('.berocket_label_', true, false);
            br_each_parent_tr('.berocket_label_'+$(this).val(), false, false);
        });

        $(document).on('change', '.berocket_label_type_select', function() {
            br_each_parent_tr('.berocket_label_type_', true, false);
            br_each_parent_tr('.berocket_label_type_'+$(this).val(), false, false);

            var 
                hideInTitle = $('[name="br_labels[line]"], [name="br_labels[zindex]"]')
                    .closest('tr');

            if ( $('.br_label_css_templates input[name="br_labels[template]"]:checked').data('template_rotate') != 1 ) {
                hideInTitle = hideInTitle.add('[name="br_labels[position]"] option[value="center"]');
            }

            if ( $(this).val() == 'in_title' ) {
                hideInTitle.hide();
            } else {
                hideInTitle.show();
            }
        });

        $(document).on('change', '.br_label_backcolor_use', function() {
            br_each_parent_tr('.br_label_backcolor', ! $(this).prop('checked'), false);
        });

        $(document).on('change', '.pos_label', function() {
            // br_each_parent_tr('.pos_label_', true, true);
            // br_each_parent_tr('.pos_label_'+$(this).val(), false, true);
            var val = $(this).val();

            $('.pos__').hide();
            if ( val == 'center' ) {
                $('.pos__').closest('th').next().hide();
            } else {
                $('.pos__').closest('th').next().show();
                $('.pos__'+val).show();
            }
        });

        $(document).on('change', '[name="br_labels[tooltip_open_on]"]', function() {
            $('[name="br_labels[tooltip_close_on_click]"]').prop( "disabled", $(this).val() === 'mouseenter' );
        });
        $('[name="br_labels[tooltip_open_on]"]').change();

        var br_label_ajax_demo = null;
        $(document).on('input', '#size_multiplier, #size_multiplier_num, [name="br_labels[text]"]', changePreview );

        $(document).on('change', 
            '.br_alabel_settings input, .br_alabel_settings textarea, .br_alabel_settings select, .br_alabel_settings input[name="br_labels[template]"]', 
            changePreview );

        function changePreview() {
            hide_by_default();

            if( $(this).is('.br_not_change') ) {
                if ( $(this).attr('name') == 'br_labels[template]' ) {
                    br_apply_template_values( $(this) );
                    $('.berocket_selected_image').html('');
                }
            } else if( $(this).is('.br_js_change') ) {
                if( ( typeof $(this).data('style') !== 'undefined' ) && $(this).data('style').search('use:') != -1 ) {
                    style = $(this).data('style');
                    style = style.replace('use:', '');
                    if( $(this).is('[type=checkbox]') ) {
                        if( $(this).prop('checked') ) {
                            value = $('[data-style='+style+']').val();
                        } else {
                            value = '';
                        }
                    } else {
                        value = $(this).val();
                    }
                } else {
                    if( $(this).val().length ) {
                        var use_ext = true;
                        if( $(this).data('notext') ) {
                            var search_val = $(this).val();
                            var notext = $(this).data('notext');
                            notext = notext.split(',');
                            notext.forEach(function(notext_element) {
                                if( search_val.search(notext_element) != -1 ) {
                                    use_ext = false;
                                }
                            });
                        }
                        if( use_ext ) {
                            if( ( typeof $(this).data('ext') !== 'undefined' ) && $(this).data('ext').search('VAL') == -1 ) {
                                var value = $(this).val()+$(this).attr('data-ext');
                            } else if ( typeof $(this).data('ext') !== 'undefined' ) {
                                var value = $(this).data('ext').replace('VAL', $(this).val());
                            }
                        } else {
                            var value = $(this).val();
                        }
                    } else {
                        var value = $(this).val();
                    }
                    if( $(this).data('from') ) {
                        var style = $($(this).data('from')).val();
                    } else {
                        var style = $(this).data('style');
                    }
                }
                $('.berocket_label_preview_wrap').find($(this).data('for')).css(style, value);
                if ( style == 'background-color' ) {
                    $('.berocket_label_preview').find($(this).data('for')).find('i')
                        .css(style, value)
                        .css('border-color', value);
                }
            } else if( !page_loading ) {
                br_apl_reload_labels_preview_ajax($(this).parents('form#post'));
            }            
        }

        function br_apl_reload_labels_preview_ajax(form) {
            var form_data = $(form).serialize();
            $('.berocket_label_preview_wrap .br_alabel').remove();
            if( br_label_ajax_demo != null ) {
                br_label_ajax_demo.abort();
            }
            br_label_ajax_demo = $.post(ajaxurl, form_data+'&action=br_label_ajax_demo', function(data) {

                $('.berocket_label_preview_wrap .br_alabel').remove();

                if( $('[name="br_labels[type]"]').val() == 'in_title' && $('[name="br_labels[position]"]').val() == 'left' ) {
                    $('.berocket_product_' + $('[name="br_labels[type]"]').val() + '_wrap').prepend(data);
                } else {
                    $('.berocket_product_' + $('[name="br_labels[type]"]').val() + '_wrap').append(data);
                }

                br_label_ajax_demo = null;
                // $('.tippy-popper').remove();
                $('.tippy-box').parent().remove();
                if( typeof(berocket_regenerate_tooltip) != 'undefined' ) {
                    berocket_regenerate_tooltip();
                }
            });
        }

        $(document).on('mousedown', '.br_template_select li', function() {
            var $radio = $(this).find('input[type=radio]');
            if( $radio.prop('checked') ) {
                $(this).addClass('waschecked');
            }
        });
        $(document).on('mouseup', '.br_template_select li', function() {
            var $radio = $(this).find('input[type=radio]');
            if( $(this).is('.waschecked') ) {
                $(this).removeClass('waschecked');
                setTimeout(function() {
                    $radio.prop('checked', false);
                    var $template_values = berocket_products_label_admin.custom_post_default_set;
                    br_set_template_values($template_values);
                    br_hide_selected_template_options();
                    $('select[name="br_labels[type]"]').trigger('change');
                    $('select[name="br_labels[content_type]"]').trigger('change');
                }, 10);
            }
        });
        $(document).on('change', '.berocket_label_attribute_type_select .br_colorpicker_value', function() {
            $('.berocket_color_image_term_'+$(this).data('term_id')).css('background-color', $(this).val());
        });
        $(document).on('change', '.berocket_label_attribute_type_select .berocket_image_value', function() {
            var term_id = $(this).data('term_id');
            var $item = $('.berocket_color_image_term_'+term_id);
            var term_name = $(this).data('term_name');
            var value = $(this).val();
            if( !value || value.substring(0, 3) != 'fa-' ) {
                var replace_to = '<img class="berocket_color_image_term_'+term_id+' berocket_widget_icon" src="'+value+'" alt="'+term_name+'" title="'+term_name+'">';
            } else {
                var replace_to = '<i class="berocket_color_image_term_'+term_id+' fa '+value+'" title="'+term_name+'"></i>';
            }
            $item.replaceWith($(replace_to));
        });
        $(document).on('change', '.template-preview-custom-image-input', function() {
            $(this).parent().prev().data('span_custom_css', "position: relative;display: block;color: white;text-align: center;right: 0;background-color: transparent!important;background: transparent url("+$(this).val()+") no-repeat right top/contain;");
            $(this).parent().addClass('has_custom_image');
            if ( ! $(this).parent().prev().is(':checked') ) {
                $(this).parent().prev().prop( "checked", true).trigger('change');
            }
        });
        $(document).ready(function() {
            $('.br_label_templates_use').trigger('change');
            $('.berocket_label_content_type, .berocket_label_type_select, .br_label_backcolor_use, .pos_label').trigger('change');
            $('.berocket_label_better_position').trigger('change');
            br_apl_reload_labels_preview_ajax($('.berocket_label_better_position').parents('form#post'));
            page_loading = false;
        });

        $(document).on("change", ".berocket_label_better_position", function(){
            br_each_parent_tr('.berocket_label_better_position_show', ! $(this).prop('checked'), false);
            br_each_parent_tr('.berocket_label_better_position_hide', $(this).prop('checked'), false);
            $('.berocket_label_type_select').trigger('change');
        });

        $(document).on('change', '.br_alabel_settings input, .br_alabel_settings textarea, .br_alabel_settings select', function() {
            if( ! $(this).is('.br_js_change, .br_not_change') ) {
                $('.berocket_label_preview .berocket_better_labels').remove();
            }
        });

        br_hide_selected_template_options();
    });

    $(document).on("click", '.br_settings_vtab', function (event) {
        event.preventDefault();
        $('.br_settings_vtab.active').removeClass('active');
        $(this).addClass('active');

        $('.br_settings_vtab-content.active').removeClass('active');
        $('.br_settings_vtab-content.tab-'+$(this).data('tab')).addClass('active');
    });

    function br_apply_template_values( $obj ) {
        var $template_values = berocket_products_label_admin.custom_post_default;
        br_set_template_values($template_values);
        br_set_template_values($obj.data());
        br_hide_selected_template_options();
        $('.br_alabel_settings select[name="br_labels[type]"]').trigger('change');
        if ( /^(image)/.test( $('.br_label_css_templates input[name="br_labels[template]"]:checked').val() ) ) {
            if ( $('.br_alabel_settings input[name="br_labels[text]"]').val() == '' ) {
                $('.br_alabel_settings input[name="br_labels[text]"]').val(' ');
            }
            $('.br_alabel_settings .berocket_labels_attribute_type_select').val('name').trigger('change');
        }
        $('.br_alabel_settings select[name="br_labels[content_type]"]').trigger('change');
    }

    function br_set_template_values( template_values ) {
        $.each(template_values, function (key, value) {
            $el = $('input[name="br_labels[' + key + ']"], select[name="br_labels[' + key + ']"], textarea[name="br_labels[' + key + ']"]', $('.br_alabel_settings')).first();
            if( $el.length ) {
                if ( $el.is(':checkbox') ) {
                    $el.prop("checked", (value*1 > 0));
                } else if ( $el.is('input') || $el.is('textarea') || $el.is('select') ) {
                    $el.val( value );
                    $el.attr( 'data-default', value );
                    if ( $el.hasClass('br_colorpicker_value') ) {
                        $el.closest('.berocket_color').find('.br_colorpicker')
                            .attr('data-color', value)
                            .attr('style', 'background-color: ' + value + ';');
                    }
                }
            }
        });

        $('[name$="_num]"]').each( function() {
            var 
                self = $(this),
                main_element_name = self.attr('name').replace('_num]', ']');
            self.val( $('[name="' + main_element_name + '"]').val() );
        });
    }
    function br_hide_selected_template_options() {
        br_template_hide_class_for_elements(berocket_products_label_admin.custom_post_setting_names, false);
        var $selected_template = $('.br_label_css_templates input[name="br_labels[template]"]:checked');
        if( $selected_template.length ) {
            let template_hide = $selected_template.data('template_hide');

            br_template_hide_class_for_elements(template_hide, true);

            if ( template_hide.includes('image_width') ) {
                $('#size_multiplier').closest('tr').show();
                $('.br_option_scale').each( function() {
                    var self = $(this);
                    if ( self.prop( "checked" ) ) {
                        self.closest('td').find('input, select').not('.br_option_scale').parent().hide();
                    }                    
                });
                $('.br_option_scale').parent().show();
            } else {
                $('#size_multiplier').closest('tr').add('label:has(.br_option_scale)').hide();
                $('.br_option_scale').closest('tr').find('input, select').not('.br_option_scale').parent().show();
            }

            if ( $selected_template.data('template_rotate') == 1 || $('[name="br_labels[type]"]').val() == 'in_title' ) {
                $('[name="br_labels[position]"] option[value=center]').hide();
            } else {
                $('[name="br_labels[position]"] option[value=center]').show();
            }

            if( $('.br_label_backcolor_use').prop('checked') ) {
                $('[name="br_labels[color]"]').closest('tr').show();
            }
        }
    }
    function br_template_hide_class_for_elements (elements, add) {
        if( typeof(elements) != 'object' ) return;

        // $('.berocket_template_hide_not_worked_option').removeClass('berocket_template_hide_not_worked_option').show();

        $.each(elements, function(i, key) {
            var 
                $element = $('input[name="br_labels['+key+']"], select[name="br_labels['+key+']"], textarea[name="br_labels['+key+']"]', $('.br_framework_settings.br_alabel_settings')),
                parent = ( ['text_before_nl', 'text_after_nl'].includes(key) ) 
                    ? $element.closest('label') : $element.closest('tr');

            if( add ) {
                parent.addClass('berocket_template_hide_not_worked_option');
            } else {
                parent.removeClass('berocket_template_hide_not_worked_option');
            }                    
        });
    }

    function hide_by_default() {
        var template = $('[name="br_labels[template]"]:checked').val();
        if ( typeof template !== 'undefined' ) return;

        $('.br_option_scale').closest('td').find('label').show();
        $('.br_option_scale').closest('label').hide();
        $('.br_hide_by_default').closest('tr').hide();

        $('[name="br_labels[size_multiplier]"], [name="br_labels[size_multiplier_num]"]').val(1);
    }
})(jQuery);
