<?php
/**
 * The admin product add and edit page facing functionality.
 * ?post_type=product
 *
 * @package    St_Woo_Swatches
 * @subpackage St_Woo_Swatches/admin/partials
 * @author     SThemes <sthemes.envato@gmail.com>
 */
class St_Woo_Product extends St_Woo_Swatches_Base {

	public function __construct() {

		parent::__construct();

		add_action( 'woocommerce_product_option_terms', array( $this, 'wc_product_option_terms'), 10, 2 );
		add_action( 'admin_footer-post-new.php', array( $this, 'add_attribute_term_modal' ) );
		add_action( 'admin_footer-post.php', array( $this, 'add_attribute_term_modal' ) );
		add_action( 'wp_ajax_stwc_add_new_term', array( $this, 'add_new_term_ajax' ) );

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_swatch_tab' ) );
		add_filter( 'woocommerce_product_data_panels', array( $this, 'add_swatch_tab_panel' ) );

		add_action( 'woocommerce_after_product_attribute_settings', array( $this, 'add_attribute_settings_field' ), 1, 2 );
		add_action( 'wp_ajax_woocommerce_save_attributes', array( $this, 'save_attribute_settings' ), 0 );
		add_action( 'wp_ajax_stwc_attribute_swatch_type', array( $this, 'get_attribute_swatch_type' ) );

		add_action( 'wp_ajax_stwc_prodcut_swatch_settings', array( $this, 'save_swatch_settings_tab' ) );
		add_action( 'wp_ajax_stwc_swatches_settings_data_panel', array( $this, 'swatch_tab_panel_data_ajax' ) );
	}

	/**
	 * Add Selector in product edit screen for extra Product Attribute types
	 * New Attributes - Color, Image And Label
	 * Selecor - Select All, Select None and Add New 	
	 */
	public function wc_product_option_terms( $attribute_taxonomy, $index ) {

		if( !array_key_exists( $attribute_taxonomy->attribute_type, $this->attribute_types ) ) {
			return;
		}

		global $thepostid;

		// woCommerce 3.5.0 doesn't supports global $thepostid
		if( is_null( $thepostid ) && isset( $_POST['post_id'] )  ) {
			$thepostid = $_POST['post_id'];
		}

		$taxonomy_name = wc_attribute_taxonomy_name( $attribute_taxonomy->attribute_name );
		$all_terms     = get_terms( $taxonomy_name, apply_filters( 'woocommerce_product_attribute_terms', array( 'orderby' => 'name', 'hide_empty' => false ) ) );

		printf( '<select multiple="multiple" class="multiselect attribute_values wc-enhanced-select" 
			data-placeholder="%1$s" name="attribute_values[%2$s][]">',
			esc_attr__( 'Select terms', 'st-woo-swatches'),
			esc_attr( $index )
		);

		if ( $all_terms ) {

			foreach( $all_terms as $term ) {

				printf( '<option value="%1$s" %2$s> %3$s </option>',
					esc_attr( $term->term_id ),
					selected( has_term( absint( $term->term_id ), $taxonomy_name, $thepostid  ), true, false ),
					esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) )
				);
			}
		}

		print( '</select>' );

		printf( '<button class="button plus select_all_attributes"> %1$s </button>
			<button class="button minus select_no_attributes"> %2$s </button>
			<button class="button fr plus st-add-new-attribute" data-type="%3$s"> %4$s </button>',
			esc_html__( 'Select all', 'st-woo-swatches' ),
			esc_html__( 'Select none', 'st-woo-swatches' ),
			esc_attr( $attribute_taxonomy->attribute_type ),
			esc_html__( 'Add new', 'st-woo-swatches' )
		);
	}

	/**
	 * Print HTML modal template to add term in admin product screen
	 */
	public function add_attribute_term_modal() {

		global $post_type;

		if( $post_type != 'product') {
			return;
		}?>

		<div id="st-term-modal-container">
			<div class="st-term-modal">
				<div class="st-term-modal-head">
					<div class="st-term-modal-title"><?php esc_html_e( 'Add new term', 'st-woo-swatches' ); ?></div>
					<button type="button" class="media-modal-close">
						<span class="media-modal-icon"></span>						
					</button>
				</div>

				<div class="st-term-modal-content">
					<p class="st-term-name">
						<label>
							<?php esc_html_e( 'Name', 'st-woo-swatches' ) ?>
							<input type="text" name="name" class="widefat st-term-name st-term-input">
						</label>
					</p>

					<p class="st-term-slug">
						<label>
							<?php esc_html_e( 'Slug', 'st-woo-swatches' ) ?>
							<input type="text" name="slug" class="widefat st-term-slug st-term-input">
						</label>
					</p>

					<div class="st-term-swatch"></div>

					<div class="hidden st-term-tax"></div>

					<input type="hidden" class="st-term-input" name="st-term-nonce" value="<?php echo wp_create_nonce('_st_term_create_attribute');?>">
				</div>

				<div class="st-term-modal-footer">
					<button type="button" class="button button-primary button-large st-term-insert" data-label="<?php esc_attr_e( 'Add New', 'st-woo-swatches' ); ?>">
						<?php esc_html_e( 'Add New', 'st-woo-swatches' ) ?>
					</button>
					<button type="button" class="button button-secondary button-large st-term-cancel"><?php esc_html_e( 'Cancel', 'st-woo-swatches' ) ?></button>
				</div>				
			</div>
			<div class="media-modal-backdrop"></div>
		</div>

		<script type="text/template" id="tmpl-st-color-swatch">
			<label>
				<?php esc_html_e( 'Color', 'st-woo-swatches' ) ?>
				<input type="text" class="st-term-input st-color-swatch-picker" name="swatch"/>
			</label>
		</script>

		<script type="text/template" id="tmpl-st-label-swatch">
			<label>
				<?php esc_html_e( 'Label', 'st-woo-swatches' ) ?>
				<input type="text" class="st-term-input widefat st-label-swatch-holder" name="swatch"/>
			</label>
		</script>

		<script type="text/template" id="tmpl-st-image-swatch">
			<label>
				<?php esc_html_e( 'Image', 'st-woo-swatches' ); ?>
			</label>
			<div class="st-image-swatch-image-holder">
			</div>
			<div class="st-image-swatch-holder">
				<input type="hidden" readonly class="st-term-input st-image-swatch-id" name="swatch"/>
				<button class="button st-image-swatch-picker" 
					data-title="<?php esc_attr_e('Choose an Image', 'st-woo-swatches');?>"
					data-button="<?php esc_attr_e('Set Image', 'st-woo-swatches');?>"
					data-remove="<?php esc_attr_e('Remove Image', 'st-woo-swatches');?>"><?php esc_html_e( 'Add Image', 'st-woo-swatches' );?></button>
			</div>			
		</script>

		<script type="text/template" id="tmpl-st-input-term-tax">
			<input type="hidden" class="st-term-input" name="taxonomy" value="{{data.tax}}">
			<input type="hidden" class="st-term-input" name="type" value="{{data.type}}">
		</script>
		<?php
	}

	/**
	 * Ajax handler for adding new term in product add and edit screen.
	 * ?post_type=product
	 */
	public function add_new_term_ajax() {

		$nonce    = isset( $_POST['st-term-nonce'] ) ? $_POST['st-term-nonce'] : '';
		$taxonomy = isset( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : '';
		$type     = isset( $_POST['type'] ) ? $_POST['type'] : '';
		$name     = isset( $_POST['name'] ) ? $_POST['name'] : '';
		$slug     = isset( $_POST['slug'] ) ? $_POST['slug'] : '';
		$swatch   = isset( $_POST['swatch'] ) ? $_POST['swatch'] : '';

		if( !wp_verify_nonce( $nonce, '_st_term_create_attribute' ) ) {

			wp_send_json_error( array(
				'msg'      => esc_html__('Wrong Action', 'st-woo-swatches'),
				'btn_span' => 'dashicons dashicons-no',
				'btn_txt'  => esc_html__('Try Again', 'st-woo-swatches'),
			) );
		}

		if ( empty( $name ) || empty( $swatch ) || empty( $taxonomy ) || empty( $type ) ) {

			wp_send_json_error( array(
				'msg'      => esc_html__('Require more data', 'st-woo-swatches'),
				'btn_span' => 'dashicons dashicons-no',
				'btn_txt'  => esc_html__('Try Again', 'st-woo-swatches'),
			) );
		}

		if ( ! taxonomy_exists( $taxonomy ) ) {

			wp_send_json_error( array(
				'msg'      => esc_html__('Taxonomy is not exists', 'st-woo-swatches'),
				'btn_span' => 'dashicons dashicons-no',
				'btn_txt'  => esc_html__('Try Again', 'st-woo-swatches'),
			) );			
		}

		if ( term_exists( $name, $taxonomy ) ) {

			wp_send_json_error( array(
				'msg'      => esc_html__('This term is already exists', 'st-woo-swatches'),
				'btn_span' => 'dashicons dashicons-no',
				'btn_txt'  => esc_html__('Try Again', 'st-woo-swatches'),
			) );						
		}

		$term = wp_insert_term( $name, $taxonomy, array( 'slug' => sanitize_text_field( $slug ) ) );

		if ( is_wp_error( $term ) ) {

			wp_send_json_error( array(
				'msg'      => $term->get_error_message(),
				'btn_span' => 'dashicons dashicons-no',
				'btn_txt'  => esc_html__('Try Again', 'st-woo-swatches'),
			) );
		} else {
			$term = get_term_by( 'id', $term['term_id'], sanitize_text_field( $taxonomy ) );
			update_term_meta( $term->term_id, sanitize_text_field( $type ), sanitize_text_field( $swatch ) );			
		}

		wp_send_json_success(
			array(
				'msg'     => esc_html__( 'Added successfully', 'st-woo-swatches' ),
				'btn_txt' => esc_html__('Add New', 'st-woo-swatches'),
				'id'      => $term->term_id,
				'slug'    => $term->slug,
				'name'    => $term->name,
			)
		);

		wp_die();
	}

	/**
	 * Add "Swatches Settings" panel
	 */
	public function add_swatch_tab( $tabs ) {

		$tabs['stwc-product-swatches-settings'] = array(
			'label'    => esc_html__( 'Swatches Settings', 'st-woo-swatches' ),
			'target'   => 'stwc-product-swatches-settings-data-panel',
			'class'    => array( 'variations_tab', 'show_if_variable' ),
			'priority' => 61,
		);

		return $tabs;
	}
	/**
	 * Add "Swatches Settings" panel data
	 */
	public function add_swatch_tab_panel() {
		global $post, $thepostid, $product_object;
		?>
		<div id="stwc-product-swatches-settings-data-panel" class="panel wc-metaboxes-wrapper hidden">
			<div id="stwc-product-swatches-settings-data-panel-inner">
				<?php $this->swatch_tab_panel_data( $product_object, $default_lang = '', $lang = '' ); ?>
			</div>
			<div class="toolbar">
				<button type="button" class="button button-primary st-prodcut-save-swatch-settings"><?php esc_html_e('Save Settings', 'st-woo-swatches'); ?> </button>
			</div>
		</div>
		<?php
	}

	/**
	 * Catalog mode attribte section
	 */
	public function swatch_tab_panel_catalog_section( $product_object, $default_lang, $lang ) {

		$options    = array();

		$product_id = $product_object->get_id();
		$attributes = $product_object->get_attributes();

		$swatches = get_post_meta( $product_id, "_product_attributes_swatch", true );
		$catalog  = get_post_meta( $product_id, "_product_stwc_catalog_mode", true );

		if( empty( $catalog ) ) {

			$catalog               = array();
			$catalog['mode']       = 'global';
			$catalog['attributes'] = array();
		}

		foreach( $attributes as $attribute ) {

			$name  = $attribute->get_name();

			if( $attribute->is_taxonomy() && $attribute_taxonomy = $attribute->get_taxonomy_object() ) {

				if( array_key_exists( $attribute_taxonomy->attribute_type, $this->attribute_types ) ) {

					$label          = wc_attribute_label( $name );
					$options[$name] = $label;
				}
			} else {

				$slug        =  sanitize_title( $name );
				$attr_swatch = isset( $swatches[$slug] ) ? $swatches[$slug] : array();

				if( !empty( $attr_swatch ) ) {

					$options[$slug] = $name;
				}
			}
		}

		/**
		 * For WPML Compatibility
		 */
		$disabled = "";
		if( !empty( $lang ) ) {

			if( $default_lang != $lang ) {

				$disabled = "disabled";
			}
		}
		?>
		<div class="wc-metabox closed">
			<h3>
				<div class="handlediv" title="<?php esc_attr_e( 'Click to toogle','st-woo-swatches' ); ?>"></div>
				<strong><?php esc_html_e( 'Catalog Attribute Settings', 'st-woo-swatches' );?></strong>	
			</h3>
			<div id="st-swatches-catalog-attribute-setting" class="woocommerce_variable_attributes wc-metabox-content hidden">
				<div class="data">
					<p class="form-row form-row-first">
						<label><?php esc_html_e('Swatch Setting','st-woo-swatches');?></label>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('Override customizer product attribute setting','st-woo-swatches'); ?>"></span>
						<select id="catalog-mode-swatch" name="catalog-mode-swatch" <?php echo esc_attr( $disabled );?>>
							<option value="global" <?php selected( $catalog['mode'], 'global' );?>><?php esc_html_e('Global Swatch', 'st-woo-swatches'); ?></option>
							<option value="custom" <?php selected( $catalog['mode'], 'custom' );?>><?php esc_html_e('Custom Swatch(es)', 'st-woo-swatches'); ?></option>
						</select>
					</p>
					<p id="catalog-custom-swatch" class="form-row form-row-last">
						<label><?php esc_html_e('Select Swatches','st-woo-swatches');?></label>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('Choose what product custom attribute to display on product catalog and archive pages','st-woo-swatches'); ?>"></span>
						<select name="custom-swatch[]" multiple="multiple" data-placeholder="<?php esc_attr_e('Select Swatches', 'st-woo-swatches');?>" class="wc-enhanced-select" <?php echo esc_attr( $disabled );?>>
							<?php
								foreach( $options as $slug => $name ) {
									$selected = in_array( $slug, $catalog['attributes'] ) ? "selected='selected'" : "";
									echo '<option value="'.esc_attr( $slug ).'" '.$selected.'>'.esc_html( $name ).'</option>';
								}
							?>
						</select>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Custom Attribute swatch settings
	 */
	public function swatch_tab_panel_custom_attr_swatch_section( $product_object ) {

		$product_id = $product_object->get_id();
		$attributes = $product_object->get_attributes();
		$swatches   = get_post_meta( $product_id, "_product_attributes_swatch", true );

		foreach( $attributes as $attribute ) {

			if( !$attribute->is_taxonomy() ) {

				$name    = $attribute->get_name();
				$slug    =  sanitize_title( $name );
				$options = $attribute->get_options();
				$options = ! empty( $options ) ? $options : array();

				$attr_swatch = isset( $swatches[$slug] ) ? $swatches[$slug] : array();

				$attr_swatch_type = isset( $attr_swatch['swatch'] ) ? $attr_swatch['swatch'] : "";
				$attr_options     = isset( $attr_swatch['options'] ) ? $attr_swatch['options'] : array();

				if( empty( $attr_swatch ) ) {

					return;
				}
				?>
				<div class="wc-metabox closed">
					<h3>
						<div class="handlediv" title="<?php esc_attr_e( 'Click to toogle','st-woo-swatches' ); ?>"></div>
						<strong><?php echo esc_html( $name );?></strong>	
					</h3>
					<div class="woocommerce_variable_attributes wc-metabox-content hidden">
						<?php foreach( $options as $option ) { ?>
							<div class="wc-metabox closed">
								<h3>
									<div class="handlediv" title="<?php esc_attr_e( 'Click to toogle','st-woo-swatches' ); ?>"></div>
									<strong><?php echo esc_html( $option );?></strong>	
								</h3>
								<div class="woocommerce_variable_attributes wc-metabox-content hidden">
									<div class="data">
										<?php
											$a_name = 'custom-attribute-settings['.$slug.']['.$option.'][value]';
											$value  = $attr_options[$option]['value'];

											$t_name = 'custom-attribute-settings['.$slug.']['.$option.'][tooltip]';
										?>
										<?php if( $attr_swatch_type == "st-label-swatch" ) { ?>
											<p class="form-row form-row-first">
												<label><?php esc_html_e('Label','st-woo-swatches'); ?></label>
												<input type="text" class="short" name="<?php echo esc_attr( $a_name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
											</p>
										<?php } elseif( $attr_swatch_type == "st-color-swatch" ) { ?>
											<p class="form-row form-row-first">
												<label><?php esc_html_e('Color','st-woo-swatches'); ?></label>
												<input type="text" class="short st-term-input st-color-swatch-picker" name="<?php echo esc_attr( $a_name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
											</p>
										<?php } elseif( $attr_swatch_type == "st-image-swatch" ) {
											$remove = "";
											$image  = wc_placeholder_img_src();

											if( !empty( $value ) ) {
												$remove = "remove";
												$image  = wp_get_attachment_thumb_url( $value );
											}?>
											<p class="form-row form-row-first upload_image">
												<a href="#" class="upload_image_button <?php echo esc_attr( $remove );?>">
													<img src="<?php echo esc_url( $image ); ?>"/>
													<input type="hidden" class="upload_image_id" name="<?php echo esc_attr( $a_name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
												</a>
											</p>
										<?php } ?>

										<p class="form-row form-row-last">
											<label><?php esc_html_e( 'Tooltip','st-woo-swatches' );?></label>
											<input type="text" class="short" name="<?php echo esc_attr( $t_name ); ?>" value="<?php echo esc_attr( $attr_options[$option]['tooltip'] ); ?>"/>
										</p>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>	
				<?php
			}
		}
	}

	/**
	 * Custom attribute section
	 */
	public function swatch_tab_panel_data( $product_object, $default_lang, $lang ) {
		?>
		<div class="st-swatches-tab-panel-settings wc-metaboxes">
			<?php 
				$this->swatch_tab_panel_catalog_section( $product_object, $default_lang, $lang );
				$this->swatch_tab_panel_custom_attr_swatch_section( $product_object );
			?>
		</div>
		<?php
	}

	public function swatch_tab_panel_data_ajax() {

		$product_id     = absint( $_POST[ 'post_id' ] );
		$product_object = wc_get_product( $product_id );
		$lang           = isset( $_POST['lang'] ) ? $_POST['lang'] : '';
		$default_lang   = isset( $_POST['default_lang'] ) ? $_POST['default_lang'] : '';

		ob_start();
			$this->swatch_tab_panel_data( $product_object, $default_lang, $lang );
		$data = ob_get_clean();

		wp_send_json_success( $data );
	}

	/**
	 * Add swatch option setting field for custom attribute type
	 * 
	 */
	public function add_attribute_settings_field( $attribute, $i ) {

		if( $attribute->is_taxonomy() ) {

			return;
		}

		global $post;

		$options        = '';
		$attribute_name = sanitize_title ( $attribute->get_name() );
		$types          = array_merge( array( 'select' => esc_html__( 'Select', 'st-woo-swatches' ) ), $this->attribute_types );
		$swatch_values  = get_post_meta( $post->ID, '_product_attributes_swatch', true );
		$selected       = is_array( $swatch_values ) ? $swatch_values[ $attribute_name ]['swatch'] : '';
		$disabled       = '';

		/**
		 * For WPML Compatibility
		 */
		if( isset( $_GET['lang'] ) ) {
			$sitepress    = get_option( 'icl_sitepress_settings' );
			$default_lang = isset( $sitepress['default_language'] ) ? $sitepress['default_language'] : '';
			$current_lang = $_GET['lang'];

			if( $default_lang != $current_lang ) {

				$disabled = "disabled";
			}
		}

		foreach ( $types as $key => $value ) {

			$options .= sprintf( '<option value="%1$s" %3$s>%2$s</option>',
				esc_attr( $key ),
				esc_attr( $value ),
				selected( $selected, $key, false )
			);
		}

		printf( '<tr>  
			<td>
				<div class="enable_variation show_if_variable">
					<label> %1$s </label>
					<select name="attribute_variation_swatch_type[%2$s]" data-st-custom-attribute-swatch="%3$s" %4$s>%5$s</select>
				</div>
			</td>
			</tr>',
			esc_html__( 'Swatch Type', 'st-woo-swatches' ),
			$i,
			$attribute_name,
			$disabled,
			$options
		);
	}

	/**
	 * Save custom attribute type for product
	 * 
	 */
	public function save_attribute_settings() {

		check_ajax_referer( 'save-attributes', 'security' );

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_die( -1 );
		}

		parse_str( $_POST['data'], $data );

		if ( isset( $data['attribute_names'], $data['attribute_values'], $data['attribute_variation_swatch_type']  ) ) {

			$attribute_names  = $data['attribute_names'];
			$attribute_values = $data['attribute_values'];

			$product_id    = absint( $_POST['post_id'] );

			$old_swatch_settings = get_post_meta( $product_id, "_product_attributes_swatch", true );
			$old_swatch_settings = is_array( $old_swatch_settings ) ? $old_swatch_settings : array();

			$new_swatch_settings = array();

			if( is_array( $data["attribute_variation_swatch_type"] ) ) {

				$swatch_types = array_keys( $this->attribute_types );

				foreach( $data["attribute_variation_swatch_type"] as $i => $swatch ) {

					if( in_array( $swatch, $swatch_types ) ) {

						$attribute_name = sanitize_title( $attribute_names[ $i ] );

						$options = isset( $attribute_values[ $i ] ) ? $attribute_values[ $i ] : '';
						$options = wc_get_text_attributes( $options );

						$new_swatch_settings[ $attribute_name ] = array( 
							'swatch' => $swatch,
							'options' => array_flip( $options )
						);
					}					
				}
			}

			$swatch_settings = array();

			if( empty( $old_swatch_settings ) ) {

				$swatch_settings = $new_swatch_settings;
			} else {
				foreach( $new_swatch_settings as $key => $new_swatch_setting ){

					if( array_key_exists( $key , $old_swatch_settings) ) {

						$old_swatch = $old_swatch_settings[$key]['swatch'];
						$new_swatch = $new_swatch_setting['swatch'];

						if( $old_swatch == $new_swatch ) {

							$swatch_settings[ $key ] = array(
								'swatch'  => $old_swatch,
								'options' => array()
							);

							$old_options = $old_swatch_settings[$key]['options'];
							$new_options = $new_swatch_setting['options'];

							foreach ($new_options as $o_key => $new_option ) {

								if( array_key_exists( $o_key, $old_options) ) {
									$swatch_settings[$key]['options'][ $o_key ] = $old_options[$o_key];
								} else {
									$swatch_settings[$key]['options'][ $o_key ] = array( "tooltip" => "", "value" => "");									
								}
							}
						} else {
							$swatch_settings[ $key ] = $new_swatch_setting;
						}
					} else {
						$swatch_settings[ $key ] = $new_swatch_setting;						
					}
				}
			}

			update_post_meta( $product_id, "_product_attributes_swatch", $swatch_settings );
		}
	}

	/**
	 * Return custom attributes swatch types via ajax
	 * 
	 */
	public function get_attribute_swatch_type() {

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_die( -1 );
		}

		$product_id = absint( $_POST['post_id'] );
		$swatch_type = get_post_meta( $product_id, "_product_attributes_swatch", true );

		if( is_array( $swatch_type ) ) {
			wp_send_json_success( array(
				'msg'  => esc_html__( 'Custom attributes has swatch type', 'st-woo-swatches'),
				'data' => $swatch_type
			) );
		} else {
			wp_send_json_error( array(
				'msg'  => esc_html__( 'No custom attributes', 'st-woo-swatches' ),
			) );
		}
		
		wp_die();		
	}

	/**
	 * Save Swatch Settings Tab content
	 */
	public function save_swatch_settings_tab() {

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_die( -1 );
		}

		$product_id  = absint( $_POST['post_id'] );
		$swatch_type = get_post_meta( $product_id, "_product_attributes_swatch", true );

		$settings = $_POST['data']['custom-attribute-settings'];

		foreach( $settings as $attr => $setting ) {

			$swatch_type[$attr]['options'] = $setting;
		}

		update_post_meta( $product_id, "_product_attributes_swatch", $swatch_type );

		$catalog_mode_settings = array();
		$catalog_mode          = $_POST['data']['catalog-mode-swatch'];

		if( $catalog_mode == "custom" ) {
			$catalog_mode_swatch = $_POST['data']['custom-swatch'];
			$catalog_mode_settings['attributes'] = $catalog_mode_swatch;
		}

		$catalog_mode_settings['mode'] = $catalog_mode;
		update_post_meta( $product_id, "_product_stwc_catalog_mode", $catalog_mode_settings );

		wp_send_json_success();
		wp_die();
	}
}