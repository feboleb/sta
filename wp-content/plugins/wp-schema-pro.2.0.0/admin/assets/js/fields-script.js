(function ($) {

	$(window).on('load', function () {
		$('.aiosrs-pro-custom-field.aiosrs-pro-custom-field-repeater .aiosrs-pro-repeater-table-wrap').hide();
	});

	$(document).ready(function ($) {

		// Added support to repeater validation.
		$('.aiosrs-pro-custom-field-repeater').each(function ( index, repeater ) {
			if( ! $(repeater).find('.wpsp-required-error-field').length ) {
				$(repeater).parents('.bsf-aiosrs-schema-row-content').prev().removeClass('wpsp-required-error-field');
			}
		});

		$('.wpsp-local-fields').find("select, textarea, input").on('change keyup', function (event) {

			if (event.isTrigger && !$(this).hasClass('wpsp-specific-field') && !$(this).hasClass('wpsp-date-field')) {
				return false;
			}

			var parent = $(this).parents('.wpsp-local-fields');
			parent.find('.wpsp-default-hidden-value').val($(this).val());
			parent.find('.wpsp-default-hidden-fieldtype').val($(this).parents('.wpsp-parent-field').attr('data-type'));

			if ($(this).is("select") && $(this).parent().hasClass('wpsp-connect-field')) {

				let selected_option = $(this).val();

				if ("create-field" === selected_option || "specific-field" === selected_option) {
					if ("create-field" === selected_option) {
						display_custom_field(parent);
						parent.find('.wpsp-default-hidden-fieldtype').val('custom-field');
					}
					if ("specific-field" === selected_option) {
						display_specific_field(parent);
						parent.find('.wpsp-default-hidden-fieldtype').val('specific-field');
					}
					parent.find('.wpsp-default-hidden-value').val("");
				}
			}

		});

		$('select.bsf-aiosrs-schema-meta-field').change(function () {

			var parent = $(this).parents('.wpsp-local-fields');
			var label = parent.find('select option:selected').html();

			let selected_option = $(this).val();

			if ('none' === selected_option || 'create-field' === selected_option || 'specific-field' === selected_option) {
				parent.find('.bsf-aiosrs-schema-heading-help').attr('title', 'Please connect any field to apply in the Schema Markup!');
			} else {
				parent.find('.bsf-aiosrs-schema-heading-help').attr('title', 'The ' + label + ' value in this field will be added to the schema markup of this particular post/page.');
			}
		});

		$('.wpsp-show-repeater-field').click(function () {

			var parent = $(this).parents('.aiosrs-pro-custom-field-repeater');
			parent.find('.aiosrs-pro-repeater-table-wrap').show();
			parent.find('.wpsp-show-repeater-field').addClass('bsf-hidden');
			parent.find('.wpsp-hide-repeater-field').removeClass('bsf-hidden');
		});

		$('.wpsp-hide-repeater-field').click(function () {

			var parent = $(this).parents('.aiosrs-pro-custom-field-repeater');
			parent.find('.aiosrs-pro-repeater-table-wrap').hide();
			parent.find('.wpsp-hide-repeater-field').addClass('bsf-hidden');
			parent.find('.wpsp-show-repeater-field').removeClass('bsf-hidden');
		});

		function display_specific_field(parent) {

			parent.find('.wpsp-connect-field,.wpsp-custom-field').hide();
			parent.find('.wpsp-specific-field').removeClass('bsf-hidden').show().find("select, textarea, input").val('');
		}

		function display_custom_field(parent) {

			parent.find('.wpsp-connect-field,.wpsp-specific-field').hide();
			parent.find('.wpsp-custom-field').removeClass('bsf-hidden').show().find("select, textarea, input").val('');
		}

		$('.wpsp-field-close').click(function () {

			var parent = $(this).parents('.wpsp-local-fields');
			parent.find('.wpsp-default-hidden-value').val("");
			parent.find('.wpsp-default-hidden-fieldtype').val("custom-field");
			display_custom_field(parent);
		});

		$('.wpsp-specific-field-connect, .wpsp-custom-field-connect').click(function () {

			let parent = $(this).parents('.wpsp-local-fields');
			let select = parent.find('.wpsp-connect-field')
				.removeClass('bsf-hidden').show()
				.find("select").removeAttr('disabled');

			let select_val = select.val();

			if ("create-field" === select_val || "specific-field" === select_val) {
				select_val = "none";
			}

			parent.find('.wpsp-default-hidden-value').val(select_val);
			parent.find('.wpsp-default-hidden-fieldtype').val("global-field");
			parent.find('.wpsp-custom-field, .wpsp-specific-field').hide();
		});

		$(document).on('change input', '.bsf-rating-field', function () {

			var star_wrap = $(this).next('.aiosrs-star-rating-wrap'),
				value = $(this).val(),
				filled = (value > 5) ? 5 : ((value < 0) ? 0 : parseInt(value)),
				half = (value == filled || value > 5 || value < 0) ? 0 : 1,
				empty = 5 - (filled + half);

			star_wrap.find('span').each(function (index, el) {
				$(this).removeClass('dashicons-star-filled dashicons-star-half dashicons-star-empty');
				if (index < filled) {
					$(this).addClass('dashicons-star-filled')
				} else if (index == filled && half == 1) {
					$(this).addClass('dashicons-star-half')
				} else {
					$(this).addClass('dashicons-star-empty')
				}
			});
		});

		$(document).on('click', '.aiosrs-star-rating-wrap:not(.disabled) > .aiosrs-star-rating', function (e) {
			e.preventDefault();
			var index = $(this).data('index');
			star_wrap = $(this).parent();
			var parent = $(this).parents('.wpsp-local-fields');
			star_wrap.prev('.bsf-rating-field').val(index);
			parent.find('.wpsp-default-hidden-value').val(index);
			star_wrap.find('.aiosrs-star-rating').each(function (i, el) {
				$(this).removeClass('dashicons-star-filled dashicons-star-half dashicons-star-empty');
				if (i < index) {
					$(this).addClass('dashicons-star-filled')
				} else {
					$(this).addClass('dashicons-star-empty')
				}
			});
		});

		$(document).on('change', '#aiosrs-pro-custom-fields .aiosrs-pro-custom-field-checkbox input[type="checkbox"]', function (e) {
			e.preventDefault();

			var siblings = $(this).closest('tr.row').siblings('tr.row');
			if ($(this).prop('checked')) {
				siblings.show();
			} else {
				siblings.hide();
			}
		});

		$('#aiosrs-pro-custom-fields .aiosrs-pro-custom-field-checkbox input[type="checkbox"]').trigger('change');

		$(document).on('click', '.aiosrs-reset-rating', function (e) {
			e.preventDefault();

			if (confirm(AIOSRS_Rating.reset_rating_msg)) {
				var parent = $(this).closest('.aiosrs-pro-custom-field-rating');

				var schema_id = $(this).data('schema-id');
				$(this).addClass('reset-disabled');
				parent.find('.spinner').addClass('is-active');

				jQuery.ajax({
					url: ajaxurl,
					type: 'post',
					dataType: 'json',
					data: {
						action: 'aiosrs_reset_post_rating',
						post_id: AIOSRS_Rating.post_id,
						schema_id: schema_id,
						nonce: AIOSRS_Rating.reset_rating_nonce
					}
				}).success(function (response) {
					if ('undefined' != typeof response['success'] && response['success'] == true) {
						var avg_rating = response['rating-avg'],
							review_count = response['review-count'];

						parent.find('.aiosrs-rating').text(avg_rating);
						parent.find('.aiosrs-rating-count').text(review_count);

						parent.find('.aiosrs-star-rating-wrap > .aiosrs-star-rating')
							.removeClass('dashicons-star-filled dashicons-star-half dashicons-star-empty')
							.addClass('dashicons-star-empty');

					} else {
						$(this).removeClass('reset-disabled');
					}
					parent.find('.spinner').removeClass('is-active');
				});
			}
		});

		$(document).on('change', '.multi-select-wrap select', function () {

			var multiselect_wrap = $(this).closest('.multi-select-wrap'),
				select_wrap = multiselect_wrap.find('select'),
				input_field = multiselect_wrap.find('input[type="hidden"]'),
				value = select_wrap.val();

			if ('undefined' != typeof value && null != value && value.length > 0) {
				input_field.val(value.join(','));
			} else {
				input_field.val('');
			}
		});

		// Verticle Tabs
		$(document).on('click', '.aiosrs-pro-meta-fields-tab', function (e) {
			e.preventDefault();

			var id = $(this).data('tab-id');
			$(this).siblings('.aiosrs-pro-meta-fields-tab').removeClass('active');
			$(this).addClass('active');

			$('#aiosrs-pro-custom-fields').find('.aiosrs-pro-meta-fields-wrap').removeClass('open');
			$('#aiosrs-pro-custom-fields').find('.' + id).addClass('open');
		});

		// Call Tooltip
		$('.bsf-aiosrs-schema-heading-help').tooltip({
			content: function () {
				return $(this).prop('title');
			},
			tooltipClass: 'bsf-aiosrs-schema-ui-tooltip',
			position: {
				my: 'center top',
				at: 'center bottom+10',
			},
			hide: {
				duration: 200,
			},
			show: {
				duration: 200,
			},
		});

		var file_frame;
		window.inputWrapper = '';

		$(document.body).on('click', '.image-field-wrap .aiosrs-image-select', function (e) {

			e.preventDefault();

			window.inputWrapper = $(this).closest('.bsf-aiosrs-schema-custom-text-wrap, .aiosrs-pro-custom-field-image');

			// Create the media frame.
			file_frame = wp.media({
				button: {
					text: 'Select Image',
					close: false
				},
				states: [
					new wp.media.controller.Library({
						title: 'Select Custom Image',
						library: wp.media.query({type: 'image'}),
						multiple: false,
					})
				]
			});

			// When an image is selected, run a callback.
			file_frame.on('select', function () {

				var attachment = file_frame.state().get('selection').first().toJSON();

				var image = window.inputWrapper.find('.image-field-wrap img');
				if (image.length == 0) {
					window.inputWrapper.find('.image-field-wrap').append('<a href="#" class="aiosrs-image-select img"><img src="' + attachment.url + '" /></a>');
				} else {
					image.attr('src', attachment.url);
				}
				window.inputWrapper.find('.image-field-wrap').addClass('bsf-custom-image-selected');
				window.inputWrapper.find('.single-image-field').val(attachment.id);

				var parent = window.inputWrapper.parents('.wpsp-local-fields');
				parent.find('.wpsp-default-hidden-value').val(attachment.id);
				parent.find('.wpsp-default-hidden-fieldtype').val(window.inputWrapper.parents('.wpsp-parent-field').attr('data-type'));

				file_frame.close();
			});

			file_frame.open();
		});


		$(document).on('click', '.aiosrs-image-remove', function (e) {

			e.preventDefault();
			var parent = $(this).closest('.bsf-aiosrs-schema-custom-text-wrap, .aiosrs-pro-custom-field-image');
			parent.find('.image-field-wrap').removeClass('bsf-custom-image-selected');
			parent.find('.single-image-field').val('');
			parent.find('.image-field-wrap img').removeAttr('src');
		});

		var file_frame;
		window.inputWrapper = '';
	});

})(jQuery);
