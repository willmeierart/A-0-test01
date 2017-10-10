<?php


		/*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*/
		/*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*/
		class AZ_Builder_Module_Fullwidth_Portfolio extends ET_Builder_Module {
			function init() {
				$this->name       = esc_html__( 'AZ Fullwidth Portfolio', 'et_builder' );
				$this->slug       = 'et_pb_fullwidth_portfolio';
				$this->fullwidth  = true;

				// need to use global settings from the slider module
				$this->global_settings_slug = 'et_pb_portfolio';

				$this->whitelisted_fields = array(
					'title',
					'fullwidth',
					'include_categories',
					'posts_number',
					'show_title',
					'show_date',
					'background_layout',
					'auto',
					'auto_speed',
					'hover_icon',
					'hover_overlay_color',
					'zoom_icon_color',
					'admin_label',
					'module_id',
					'module_class',
				);

				$this->main_css_element = '%%order_class%%';

				$this->advanced_options = array(
					'fonts' => array(
						'title'   => array(
							'label'    => esc_html__( 'Title', 'et_builder' ),
							'css'      => array(
								'main' => "{$this->main_css_element} h3",
								'important' => 'all',
							),
						),
						'caption' => array(
							'label'    => esc_html__( 'Meta', 'et_builder' ),
							'css'      => array(
								'main' => "{$this->main_css_element} .post-meta, {$this->main_css_element} .post-meta a",
							),
						),
					),
					'background' => array(
						'settings' => array(
							'color' => 'alpha',
						),
					),
					'border' => array(
						'css' => array(
							'main' => "{$this->main_css_element} .et_pb_portfolio_item",
						),
					),
				);

				$this->custom_css_options = array(
					'portfolio_title' => array(
						'label'    => esc_html__( 'Portfolio Title', 'et_builder' ),
						'selector' => '> h2',
					),
					'portfolio_item' => array(
						'label'    => esc_html__( 'Portfolio Item', 'et_builder' ),
						'selector' => '.et_pb_portfolio_item',
					),
					'portfolio_overlay' => array(
						'label'    => esc_html__( 'Item Overlay', 'et_builder' ),
						'selector' => 'span.et_overlay',
					),
					'portfolio_item_title' => array(
						'label'    => esc_html__( 'Item Title', 'et_builder' ),
						'selector' => '.meta h3',
					),
					'portfolio_meta' => array(
						'label'    => esc_html__( 'Meta', 'et_builder' ),
						'selector' => '.meta p',
					),
					'portfolio_arrows' => array(
						'label'    => esc_html__( 'Navigation Arrows', 'et_builder' ),
						'selector' => '.et-pb-slider-arrows a',
					),
				);

				$this->fields_defaults = array(
					'fullwidth'         => array( 'on' ),
					'show_title'        => array( 'on' ),
					'show_date'         => array( 'on' ),
					'background_layout' => array( 'light' ),
					'auto'              => array( 'off' ),
					'auto_speed'        => array( '7000' ),
				);
			}

			function get_fields() {
				$fields = array(
					'title' => array(
						'label'           => esc_html__( 'Portfolio Title', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'basic_option',
						'description'     => esc_html__( 'Title displayed above the portfolio.', 'et_builder' ),
					),
					'fullwidth' => array(
						'label'             => esc_html__( 'Layout', 'et_builder' ),
						'type'              => 'select',
						'option_category'   => 'layout',
						'options'           => array(
							'on'  => esc_html__( 'Carousel', 'et_builder' ),
							'off' => esc_html__( 'Grid', 'et_builder' ),
						),
						'affects'           => array(
							'#et_pb_auto',
						),
						'description'        => esc_html__( 'Choose your desired portfolio layout style.', 'et_builder' ),
					),
					'include_categories' => array(
						'label'           => esc_html__( 'Include Categories', 'et_builder' ),
						'renderer'        => 'et_builder_include_categories_option',
						'option_category' => 'basic_option',
						'description'     => esc_html__( 'Select the categories that you would like to include in the feed.', 'et_builder' ),
					),
					'posts_number' => array(
						'label'           => esc_html__( 'Posts Number', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'configuration',
						'description'     => esc_html__( 'Control how many projects are displayed. Leave blank or use 0 to not limit the amount.', 'et_builder' ),
					),
					'show_title' => array(
						'label'             => esc_html__( 'Show Title', 'et_builder' ),
						'type'              => 'yes_no_button',
						'option_category'   => 'configuration',
						'options'           => array(
							'on'  => esc_html__( 'Yes', 'et_builder' ),
							'off' => esc_html__( 'No', 'et_builder' ),
						),
						'description'        => esc_html__( 'Turn project titles on or off.', 'et_builder' ),
					),
					'show_date' => array(
						'label'             => esc_html__( 'Show Date', 'et_builder' ),
						'type'              => 'yes_no_button',
						'option_category'   => 'configuration',
						'options'           => array(
							'on'  => esc_html__( 'Yes', 'et_builder' ),
							'off' => esc_html__( 'No', 'et_builder' ),
						),
						'description'        => esc_html__( 'Turn the date display on or off.', 'et_builder' ),
					),
					'background_layout' => array(
						'label'             => esc_html__( 'Text Color', 'et_builder' ),
						'type'              => 'select',
						'option_category'   => 'color_option',
						'options'           => array(
							'light'  => esc_html__( 'Dark', 'et_builder' ),
							'dark' => esc_html__( 'Light', 'et_builder' ),
						),
						'description'        => esc_html__( 'Here you can choose whether your text should be light or dark. If you are working with a dark background, then your text should be light. If your background is light, then your text should be set to dark.', 'et_builder' ),
					),
					'auto' => array(
						'label'             => esc_html__( 'Automatic Carousel Rotation', 'et_builder' ),
						'type'              => 'yes_no_button',
						'option_category'   => 'configuration',
						'options'           => array(
							'off'  => esc_html__( 'Off', 'et_builder' ),
							'on' => esc_html__( 'On', 'et_builder' ),
						),
						'affects'           => array(
							'#et_pb_auto_speed',
						),
						'depends_show_if' => 'on',
						'description'        => esc_html__( 'If you the carousel layout option is chosen and you would like the carousel to slide automatically, without the visitor having to click the next button, enable this option and then adjust the rotation speed below if desired.', 'et_builder' ),
					),
					'auto_speed' => array(
						'label'             => esc_html__( 'Automatic Carousel Rotation Speed (in ms)', 'et_builder' ),
						'type'              => 'text',
						'option_category'   => 'configuration',
						'depends_default'   => true,
						'description'       => esc_html__( "Here you can designate how fast the carousel rotates, if 'Automatic Carousel Rotation' option is enabled above. The higher the number the longer the pause between each rotation. (Ex. 1000 = 1 sec)", 'et_builder' ),
					),
					'zoom_icon_color' => array(
						'label'             => esc_html__( 'Zoom Icon Color', 'et_builder' ),
						'type'              => 'color',
						'custom_color'      => true,
						'tab_slug'          => 'advanced',
					),
					'hover_overlay_color' => array(
						'label'             => esc_html__( 'Hover Overlay Color', 'et_builder' ),
						'type'              => 'color-alpha',
						'custom_color'      => true,
						'tab_slug'          => 'advanced',
					),
					'hover_icon' => array(
						'label'               => esc_html__( 'Hover Icon Picker', 'et_builder' ),
						'type'                => 'text',
						'option_category'     => 'configuration',
						'class'               => array( 'et-pb-font-icon' ),
						'renderer'            => 'et_pb_get_font_icon_list',
						'renderer_with_field' => true,
						'tab_slug'            => 'advanced',
					),
					'disabled_on' => array(
						'label'           => esc_html__( 'Disable on', 'et_builder' ),
						'type'            => 'multiple_checkboxes',
						'options'         => array(
							'phone'   => esc_html__( 'Phone', 'et_builder' ),
							'tablet'  => esc_html__( 'Tablet', 'et_builder' ),
							'desktop' => esc_html__( 'Desktop', 'et_builder' ),
						),
						'additional_att'  => 'disable_on',
						'option_category' => 'configuration',
						'description'     => esc_html__( 'This will disable the module on selected devices', 'et_builder' ),
					),
					'admin_label' => array(
						'label'       => esc_html__( 'Admin Label', 'et_builder' ),
						'type'        => 'text',
						'description' => esc_html__( 'This will change the label of the module in the builder for easy identification.', 'et_builder' ),
					),
					'module_id' => array(
						'label'           => esc_html__( 'CSS ID', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'configuration',
						'tab_slug'        => 'custom_css',
						'option_class'    => 'et_pb_custom_css_regular',
					),
					'module_class' => array(
						'label'           => esc_html__( 'CSS Class', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'configuration',
						'tab_slug'        => 'custom_css',
						'option_class'    => 'et_pb_custom_css_regular',
					),
				);
				return $fields;
			}

			function shortcode_callback( $atts, $content = null, $function_name ) {
				$title               = $this->shortcode_atts['title'];
				$module_id           = $this->shortcode_atts['module_id'];
				$module_class        = $this->shortcode_atts['module_class'];
				$fullwidth           = $this->shortcode_atts['fullwidth'];
				$include_categories  = $this->shortcode_atts['include_categories'];
				$posts_number        = $this->shortcode_atts['posts_number'];
				$show_title          = $this->shortcode_atts['show_title'];
				$show_date           = $this->shortcode_atts['show_date'];
				$background_layout   = $this->shortcode_atts['background_layout'];
				$auto                = $this->shortcode_atts['auto'];
				$auto_speed          = $this->shortcode_atts['auto_speed'];
				$zoom_icon_color     = $this->shortcode_atts['zoom_icon_color'];
				$hover_overlay_color = $this->shortcode_atts['hover_overlay_color'];
				$hover_icon          = $this->shortcode_atts['hover_icon'];

				$module_class = ET_Builder_Element::add_module_order_class( $module_class, $function_name );

				if ( '' !== $zoom_icon_color ) {
					ET_Builder_Element::set_style( $function_name, array(
						'selector'    => '%%order_class%% .et_overlay:before',
						'declaration' => sprintf(
							'color: %1$s !important;',
							esc_html( $zoom_icon_color )
						),
					) );
				}

				if ( '' !== $hover_overlay_color ) {
					ET_Builder_Element::set_style( $function_name, array(
						'selector'    => '%%order_class%% .et_overlay',
						'declaration' => sprintf(
							'background-color: %1$s;
							border-color: %1$s;',
							esc_html( $hover_overlay_color )
						),
					) );
				}

				$args = array();
				if ( is_numeric( $posts_number ) && $posts_number > 0 ) {
					$args['posts_per_page'] = $posts_number;
				} else {
					$args['nopaging'] = true;
				}

				if ( '' !== $include_categories ) {
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'project_category',
							'field' => 'id',
							'terms' => explode( ',', $include_categories ),
							'operator' => 'IN'
						)
					);
				}

				$projects = et_divi_get_projects( $args );

				ob_start();
				if( $projects->post_count > 0 ) {
					while ( $projects->have_posts() ) {
						$projects->the_post();
						?>
						<div id="post-<?php the_ID(); ?>" <?php post_class( 'az-client et_pb_portfolio_item et_pb_grid_item ' ); ?>>
						<?php
							$thumb = '';

							$width = 510;
							$width = (int) apply_filters( 'et_pb_portfolio_image_width', $width );

							$height = 382;
							$height = (int) apply_filters( 'et_pb_portfolio_image_height', $height );

							list($thumb_src, $thumb_width, $thumb_height) = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), array( $width, $height ) );

							$orientation = ( $thumb_height > $thumb_width ) ? 'portrait' : 'landscape';

							if ( '' !== $thumb_src ) : ?>
								<div class="et_pb_portfolio_image <?php echo esc_attr( $orientation ); ?>">
									<img src="<?php echo esc_url( $thumb_src ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>"/>
									<div class="meta">
										<a href="<?php esc_url( the_permalink() ); ?>">
										<?php
											$data_icon = '' !== $hover_icon
												? sprintf(
													' data-icon="%1$s"',
													esc_attr( et_pb_process_font_icon( $hover_icon ) )
												)
												: '';

											printf( '<span class="et_overlay%1$s"%2$s></span>',
												( '' !== $hover_icon ? ' et_pb_inline_icon' : '' ),
												$data_icon
											);
										?>
											<?php if ( 'on' === $show_title ) : ?>
												<h3><?php the_title(); ?></h3>
											<?php endif; ?>

											<?php if ( 'on' === $show_date ) : ?>
												<p class="post-meta"><?php echo get_the_date(); ?></p>
											<?php endif; ?>
										</a>
									</div>
								</div>
						<?php endif; ?>
						</div>
						<?php
					}
				}

				wp_reset_postdata();

				$posts = ob_get_clean();

				$class = " et_pb_module et_pb_bg_layout_{$background_layout}";

				$output = sprintf(
					'<div%4$s class="az-portfolio et_pb_fullwidth_portfolio %1$s%3$s%5$s" data-auto-rotate="%6$s" data-auto-rotate-speed="%7$s">
						%8$s
						<div class="et_pb_portfolio_items clearfix data-portfolio-columns="2">
							%2$s
						</div><!-- .et_pb_portfolio_items -->
					</div> <!-- .et_pb_fullwidth_portfolio -->',
					( 'on' === $fullwidth ? 'et_pb_fullwidth_portfolio_carousel' : 'et_pb_fullwidth_portfolio_grid clearfix' ),
					$posts,
					esc_attr( $class ),
					( '' !== $module_id ? sprintf( ' id="%1$s"', esc_attr( $module_id ) ) : '' ),
					( '' !== $module_class ? sprintf( ' %1$s', esc_attr( $module_class ) ) : '' ),
					( '' !== $auto && in_array( $auto, array('on', 'off') ) ? esc_attr( $auto ) : 'off' ),
					( '' !== $auto_speed && is_numeric( $auto_speed ) ? esc_attr( $auto_speed ) : '7000' ),
					( '' !== $title ? sprintf( '<h2>%s</h2>', esc_html( $title ) ) : '' )
				);

				return $output;
			}
		}
		new AZ_Builder_Module_Fullwidth_Portfolio;
		/*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*/

		//																																								BEGIN GALLERY

		/*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*/
		class AZ_Builder_Module_Gallery extends ET_Builder_Module {
			function init() {
				$this->name       = esc_html__( 'AZ Gallery', 'et_builder' );
				$this->slug       = 'et_pb_gallery_az17';

				$this->whitelisted_fields = array(
					'src',
					'gallery_ids',
					'gallery_orderby',
					'fullwidth',
					'posts_number',
					'az_masonry',
					'show_title_and_caption',
					'show_pagination',
					'background_layout',
					'auto',
					'auto_speed',
					'admin_label',
					'module_id',
					'module_class',
					'enable_zoom',
					'zoom_icon_color',
					'hover_overlay_color',
					'hover_icon',
				);

				$this->fields_defaults = array(
					'fullwidth'              => array( 'off' ),
					'posts_number'           => array( 4, 'add_default_setting' ),
					'show_title_and_caption' => array( 'off' ),
					'az_masonry'			 => array( 'off' ),
					'enable_zoom'			 => array( 'off' ),
					'show_pagination'        => array( 'on' ),
					'background_layout'      => array( 'light' ),
					'auto'                   => array( 'off' ),
					'auto_speed'             => array( '7000' ),
				);

				$this->main_css_element = '%%order_class%%.et_pb_gallery';
				$this->advanced_options = array(
					'fonts' => array(
						'caption' => array(
							'label'    => esc_html__( 'Caption', 'et_builder' ),
							'use_all_caps' => true,
							'css'      => array(
								'main' => "{$this->main_css_element} .mfp-title, {$this->main_css_element} .et_pb_gallery_caption",
							),
							'line_height' => array(
								'range_settings' => array(
									'min'  => '1',
									'max'  => '100',
									'step' => '1',
								),
							),
						),
						'title'   => array(
							'label'    => esc_html__( 'Title', 'et_builder' ),
							'css'      => array(
								'main' => "{$this->main_css_element} .et_pb_gallery_title",
							),
						),
					),
					'border' => array(
						'css' => array(
							'main' => "{$this->main_css_element} .et_pb_gallery_item",
						),
					),
				);

				$this->custom_css_options = array(
					'gallery_item' => array(
						'label'    => esc_html__( 'Gallery Item', 'et_builder' ),
						'selector' => '.et_pb_gallery_item',
					),
					'overlay' => array(
						'label'    => esc_html__( 'Overlay', 'et_builder' ),
						'selector' => '.et_overlay',
					),
					'overlay_icon' => array(
						'label'    => esc_html__( 'Overlay Icon', 'et_builder' ),
						'selector' => '.et_overlay:before',
					),
					'gallery_item_title' => array(
						'label'    => esc_html__( 'Gallery Item Title', 'et_builder' ),
						'selector' => '.et_pb_gallery_title',
					),
					'gallery_item_caption' => array(
						'label'    => esc_html__( 'Gallery Item Caption', 'et_builder' ),
						'selector' => '.et_pb_gallery_caption',
					),
					'gallery_pagination' => array(
						'label'    => esc_html__( 'Gallery Pagination', 'et_builder' ),
						'selector' => '.et_pb_gallery_pagination',
					),
					'gallery_pagination_active' => array(
						'label'    => esc_html__( 'Pagination Active Page', 'et_builder' ),
						'selector' => '.et_pb_gallery_pagination a.active',
					),
				);
			}

			function get_fields() {
				$fields = array(
					'src' => array(
						'label'           => esc_html__( 'Gallery Images', 'et_builder' ),
						'renderer'        => 'et_builder_get_gallery_settings',
						'option_category' => 'basic_option',
					),
					'gallery_ids' => array(
						'type'  => 'hidden',
						'class' => array( 'et-pb-gallery-ids-field' ),
					),
					'gallery_orderby' => array(
						'label' => esc_html__( 'Gallery Images', 'et_builder' ),
						'type'  => 'hidden',
						'class' => array( 'et-pb-gallery-ids-field' ),
					),
					'fullwidth' => array(
						'label'             => esc_html__( 'Layout', 'et_builder' ),
						'type'              => 'select',
						'option_category'   => 'layout',
						'options'           => array(
							'on'  => esc_html__( 'Slider', 'et_builder' ),
							'off' => esc_html__( 'Grid', 'et_builder' ),
						),
						'description'       => esc_html__( 'Toggle between the various blog layout types.', 'et_builder' ),
						'affects'           => array(
							'#et_pb_caption_font',
							'#et_pb_caption_font_color',
							'#et_pb_caption_font_size',
							'#et_pb_auto',
							'#et_pb_posts_number',
							'#et_pb_az_masonry',
							'#et_pb_show_title_and_caption',
							'#et_pb_enable_zoom',
						),
					),
					'posts_number' => array(
						'label'             => esc_html__( 'Images Number', 'et_builder' ),
						'type'              => 'text',
						'option_category'   => 'configuration',
						'description'       => esc_html__( 'Define the maximum number of images to be displayed per page.', 'et_builder' ),
						'depends_show_if'   => 'off',
					),
					'az_masonry' => array(
						'label'             => esc_html__( 'Masonry Grid', 'et_builder' ),
						'type'              => 'yes_no_button',
						'option_category'   => 'configuration',
						'options'           => array(
							'off' => esc_html__( 'No', 'et_builder' ),
							'on'  => esc_html__( 'Yes', 'et_builder' ),
						),
						'description'        => esc_html__( 'Enable or disable masonry grid.', 'et_builder' ),
						'depends_show_if'   => 'off',
						'affects'           => array(
							'#et_pb_enable_zoom',
						),
					),
					'enable_zoom' => array(
						'label'             => esc_html__( 'Enable Zoom', 'et_builder' ),
						'type'              => 'yes_no_button',
						'option_category'   => 'configuration',
						'options'           => array(
							'off' => esc_html__( 'No', 'et_builder' ),
							'on'  => esc_html__( 'Yes', 'et_builder' ),
						),
						'description'        => esc_html__( 'Enables modal view of individual images', 'et_builder' ),
						'depends_show_if'	  => 'off',
						'affects'           => array(
							'#et_pb_zoom_icon_color',
							'#et_pb_hover_overlay_color',
							'#et_pb_hover_icon',
						),
					),
					'hover_icon' => array(
						'label'               => esc_html__( 'Hover Icon Picker', 'et_builder' ),
						'type'                => 'text',
						'option_category'     => 'configuration',
						'class'               => array( 'et-pb-font-icon' ),
						'renderer'            => 'et_pb_get_font_icon_list',
						'renderer_with_field' => true,
						'depends_show_if'	  => 'on',
					),
					'zoom_icon_color' => array(
						'label'             => esc_html__( 'Zoom Icon Color', 'et_builder' ),
						'type'              => 'color',
						'option_category'	=> 'configuration',
						'custom_color'      => true,
						'depends_show_if'   => 'on',
					),
					'hover_overlay_color' => array(
						'label'             => esc_html__( 'Hover Overlay Color', 'et_builder' ),
						'type'              => 'color-alpha',
						'option_category'	=> 'configuration',
						'custom_color'      => true,
						'depends_show_if'   => 'on',
					),
					'show_title_and_caption' => array(
						'label'              => esc_html__( 'Show Title and Caption', 'et_builder' ),
						'type'               => 'yes_no_button',
						'option_category'    => 'configuration',
						'options'            => array(
							'on'  => esc_html__( 'Yes', 'et_builder' ),
							'off' => esc_html__( 'No', 'et_builder' ),
						),
						'description'        => esc_html__( 'Here you can choose whether to show the images title and caption, if the image has them.', 'et_builder' ),
						'depends_show_if'    => 'off',
					),
					'show_pagination' => array(
						'label'             => esc_html__( 'Show Pagination', 'et_builder' ),
						'type'              => 'yes_no_button',
						'option_category'   => 'configuration',
						'options'           => array(
							'on'  => esc_html__( 'Yes', 'et_builder' ),
							'off' => esc_html__( 'No', 'et_builder' ),
						),
						'description'        => esc_html__( 'Enable or disable pagination for this feed.', 'et_builder' ),
					),
					'background_layout' => array(
						'label'             => esc_html__( 'Text Color', 'et_builder' ),
						'type'              => 'select',
						'option_category'   => 'color_option',
						'options'           => array(
							'light'  => esc_html__( 'Dark', 'et_builder' ),
							'dark' => esc_html__( 'Light', 'et_builder' ),
						),
						'description'        => esc_html__( 'Here you can choose whether your text should be light or dark. If you are working with a dark background, then your text should be light. If your background is light, then your text should be set to dark.', 'et_builder' ),
					),
					'auto' => array(
						'label'           => esc_html__( 'Automatic Animation', 'et_builder' ),
						'type'            => 'yes_no_button',
						'option_category' => 'configuration',
						'options'         => array(
							'off' => esc_html__( 'Off', 'et_builder' ),
							'on'  => esc_html__( 'On', 'et_builder' ),
						),
						'affects' => array(
							'#et_pb_auto_speed',
						),
						'depends_show_if'   => 'on',
						'description'       => esc_html__( 'If you would like the slider to slide automatically, without the visitor having to click the next button, enable this option and then adjust the rotation speed below if desired.', 'et_builder' ),
					),
					'auto_speed' => array(
						'label'             => esc_html__( 'Automatic Animation Speed (in ms)', 'et_builder' ),
						'type'              => 'text',
						'option_category'   => 'configuration',
						'depends_default'   => true,
						'description'       => esc_html__( "Here you can designate how fast the slider fades between each slide, if 'Automatic Animation' option is enabled above. The higher the number the longer the pause between each rotation.", 'et_builder' ),
					),
					'disabled_on' => array(
						'label'           => esc_html__( 'Disable on', 'et_builder' ),
						'type'            => 'multiple_checkboxes',
						'options'         => array(
							'phone'   => esc_html__( 'Phone', 'et_builder' ),
							'tablet'  => esc_html__( 'Tablet', 'et_builder' ),
							'desktop' => esc_html__( 'Desktop', 'et_builder' ),
						),
						'additional_att'  => 'disable_on',
						'option_category' => 'configuration',
						'description'     => esc_html__( 'This will disable the module on selected devices', 'et_builder' ),
					),
					'admin_label' => array(
						'label'       => esc_html__( 'Admin Label', 'et_builder' ),
						'type'        => 'text',
						'description' => esc_html__( 'This will change the label of the module in the builder for easy identification.', 'et_builder' ),
					),
					'module_id' => array(
						'label'           => esc_html__( 'CSS ID', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'configuration',
						'tab_slug'        => 'custom_css',
						'option_class'    => 'et_pb_custom_css_regular',
					),
					'module_class' => array(
						'label'           => esc_html__( 'CSS Class', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'configuration',
						'tab_slug'        => 'custom_css',
						'option_class'    => 'et_pb_custom_css_regular',
					),
				);

				return $fields;
			}

			function shortcode_callback( $atts, $content = null, $function_name ) {
				$module_id              = $this->shortcode_atts['module_id'];
				$module_class           = $this->shortcode_atts['module_class'];
				$gallery_ids            = $this->shortcode_atts['gallery_ids'];
				$fullwidth              = $this->shortcode_atts['fullwidth'];
				$show_title_and_caption = $this->shortcode_atts['show_title_and_caption'];
				$background_layout      = $this->shortcode_atts['background_layout'];
				$posts_number           = $this->shortcode_atts['posts_number'];
				$show_pagination        = $this->shortcode_atts['show_pagination'];
				$az_masonry        		= $this->shortcode_atts['az_masonry'];
				$gallery_orderby        = $this->shortcode_atts['gallery_orderby'];
				$enable_zoom        	= $this->shortcode_atts['enable_zoom'];
				$zoom_icon_color        = $this->shortcode_atts['zoom_icon_color'];
				$hover_overlay_color    = $this->shortcode_atts['hover_overlay_color'];
				$hover_icon             = $this->shortcode_atts['hover_icon'];
				$auto                   = $this->shortcode_atts['auto'];
				$auto_speed             = $this->shortcode_atts['auto_speed'];

				$module_class = ET_Builder_Element::add_module_order_class( $module_class, $function_name );

				if ( 'on' === $az_masonry ) {
					wp_enqueue_script( 'salvattore' );
				}

				if ( 'on' === $enable_zoom ) {
					if ( '' !== $zoom_icon_color ) {
						ET_Builder_Element::set_style( $function_name, array(
							'selector'    => '%%order_class%% .et_overlay:before',
							'declaration' => sprintf(
								'color: %1$s !important;',
								esc_html( $zoom_icon_color )
							),
						) );
					}

					if ( '' !== $hover_overlay_color ) {
						ET_Builder_Element::set_style( $function_name, array(
							'selector'    => '%%order_class%% .et_overlay',
							'declaration' => sprintf(
								'background-color: %1$s;
								border-color: %1$s;',
								esc_html( $hover_overlay_color )
							),
						) );
					}
				}

				$attachments = array();
				if ( ! empty( $gallery_ids ) ) {
					$attachments_args = array(
						'include'        => $gallery_ids,
						'post_status'    => 'inherit',
						'post_type'      => 'attachment',
						'post_mime_type' => 'image',
						'order'          => 'ASC',
						'orderby'        => 'post__in',
					);

					if ( 'rand' === $gallery_orderby ) {
						$attachments_args['orderby'] = 'rand';
					}

					$_attachments = get_posts( $attachments_args );

					foreach ( $_attachments as $key => $val ) {
						$attachments[$val->ID] = $_attachments[$key];
					}
				}

				if ( empty($attachments) )
					return '';

				wp_enqueue_script( 'hashchange' );

				$fullwidth_class = 'on' === $fullwidth ?  ' et_pb_slider et_pb_gallery_fullwidth' : ' et_pb_gallery_grid';
				$background_class = " et_pb_bg_layout_{$background_layout}";
				$masonry_bool = 'on' === $az_masonry ?  ' data-columns=4' : '';
				$masonry_class = 'on' === $az_masonry ?  ' az-masonry' : '';

				$module_class .= 'on' === $auto && 'on' === $fullwidth ? ' et_slider_auto et_slider_speed_' . esc_attr( $auto_speed ) : '';

				$output = sprintf(
					'<div%1$s class="et_pb_module et_pb_gallery%2$s%3$s%4$s clearfix">
						<div class="%7$s et_pb_gallery_items et_post_gallery" data-per_page="%5$d"%6$s>',
					( '' !== $module_id ? sprintf( ' id="%1$s"', esc_attr( $module_id ) ) : '' ),
					( '' !== $module_class ? sprintf( ' %1$s', esc_attr( ltrim( $module_class ) ) ) : '' ),
					esc_attr( $fullwidth_class ),
					esc_attr( $background_class ),
					esc_attr( $posts_number ),
					esc_attr( $masonry_bool ),
					esc_attr( $masonry_class )
				);

				$i = 0;
				foreach ( $attachments as $id => $attachment ) {

					$width = 'on' === $fullwidth ?  1080 : 400;
					$width = (int) apply_filters( 'et_pb_gallery_image_width', $width );

					$height = 'on' === $fullwidth ?  9999 : 400;  // was 284
					$height = (int) apply_filters( 'et_pb_gallery_image_height', $height );

					list($full_src, $full_width, $full_height) = wp_get_attachment_image_src( $id, 'full' );
					list($thumb_src, $thumb_width, $thumb_height) = wp_get_attachment_image_src( $id, array( $width, $height ) );

					$data_icon = '' !== $hover_icon
						? sprintf(
							' data-icon="%1$s"',
							esc_attr( et_pb_process_font_icon( $hover_icon ) )
						)
						: '';

					// masonry uses the full source to get irregular images
					$img_el = sprintf(
						'<img src="%1$s" alt="%2$s" />
						 <span class="et_overlay%3$s"%4$s></span>',
						esc_url( ( 'off' === $enable_zoom || 'on' === $masonry_bool ) ? $full_src : $thumb_src ),
						esc_attr( $attachment->post_title ),
						( '' !== $hover_icon ? ' et_pb_inline_icon' : '' ),
						$data_icon
					);

					if ( 'on' === $enable_zoom ) {
						$image_output = sprintf(
							'<a href="%1$s" title="%2$s">
								%3$s
							</a>',
							esc_url( $full_src ),
							esc_attr( $attachment->post_title ),
							$img_el
						);
					} else {
						$image_output = $img_el;
					}

					$orientation = ( $thumb_height > $thumb_width ) ? 'portrait' : 'landscape';

					// hide the et_pb_item div if we're on masonry
					if ( 'off' === $masonry_bool ) {
						$output .= sprintf(
							'<div class="et_pb_gallery_item%2$s%1$s">',
							esc_attr( $background_class ),
							( 'on' !== $fullwidth ? ' et_pb_grid_item' : '' )
						);
					}
					$output .= "
						<div class='et_pb_gallery_image {$orientation}'>
							$image_output
						</div>";

					if ( 'on' !== $fullwidth && 'on' === $show_title_and_caption ) {
						if ( trim($attachment->post_title) ) {
							$output .= "
								<h3 class='et_pb_gallery_title'>
								" . wptexturize($attachment->post_title) . "
								</h3>";
						}
						if ( trim($attachment->post_excerpt) ) {
						$output .= "
								<p class='et_pb_gallery_caption'>
								" . wptexturize($attachment->post_excerpt) . "
								</p>";
						}
					}
					if ( 'off' === $masonry_bool ) {
						$output .= "</div>";
					}
				}

				$output .= "</div><!-- .et_pb_gallery_items -->";

				// hide the et_pb_item div if we're on masonry
				if ( 'on' !== $fullwidth && 'on' === $show_pagination ) {
					$output .= "<div class='et_pb_gallery_pagination'></div>";
				}

				$output .= "</div><!-- .et_pb_gallery -->";

				return $output;
			}
		}
		new AZ_Builder_Module_Gallery;
		/*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*/

		//																																							NEXT MODULE HERE

		/*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*/
		class AZ_Builder_Module_Team_Member extends ET_Builder_Module {
			function init() {
				$this->name       = esc_html__( 'Person', 'et_builder' );
				$this->slug       = 'et_pb_team_member';
				$this->fb_support = true;

				$this->whitelisted_fields = array(
					'name',
					'position',
					'image_url',
					'animation',
					'background_layout',
					'facebook_url',
					'twitter_url',
					'google_url',
					'linkedin_url',
					'content_new',
					'admin_label',
					'module_id',
					'module_class',
					'icon_color',
					'icon_hover_color',
				);

				$this->fields_defaults = array(
					'animation'         => array( 'off' ),
					'background_layout' => array( 'light' ),
				);

				$this->main_css_element = '%%order_class%%.et_pb_team_member';
				$this->advanced_options = array(
					'fonts' => array(
						'header' => array(
							'label'    => esc_html__( 'Header', 'et_builder' ),
							'css'      => array(
								'main' => "{$this->main_css_element} h4",
							),
						),
						'body'   => array(
							'label'    => esc_html__( 'Body', 'et_builder' ),
							'css'      => array(
								'main' => "{$this->main_css_element} *",
							),
						),
					),
					'background' => array(
						'settings' => array(
							'color' => 'alpha',
						),
					),
					'border' => array(),
					'custom_margin_padding' => array(
						'css' => array(
							'important' => 'all',
						),
					),
				);
				$this->custom_css_options = array(
					'member_image' => array(
						'label'    => esc_html__( 'Member Image', 'et_builder' ),
						'selector' => '.et_pb_team_member_image',
					),
					'member_description' => array(
						'label'    => esc_html__( 'Member Description', 'et_builder' ),
						'selector' => '.et_pb_team_member_description',
					),
					'title' => array(
						'label'    => esc_html__( 'Title', 'et_builder' ),
						'selector' => '.et_pb_team_member_description h4',
					),
					'member_position' => array(
						'label'    => esc_html__( 'Member Position', 'et_builder' ),
						'selector' => '.et_pb_member_position',
					),
					'member_social_links' => array(
						'label'    => esc_html__( 'Member Social Links', 'et_builder' ),
						'selector' => '.et_pb_member_social_links',
					),
				);
			}

			function get_fields() {
				$fields = array(
					'name' => array(
						'label'           => esc_html__( 'Name', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'basic_option',
						'description'     => esc_html__( 'Input the name of the person', 'et_builder' ),
					),
					'position' => array(
						'label'           => esc_html__( 'Position', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'basic_option',
						'description'     => esc_html__( "Input the person's position.", 'et_builder' ),
					),
					'image_url' => array(
						'label'              => esc_html__( 'Image URL', 'et_builder' ),
						'type'               => 'upload',
						'option_category'    => 'basic_option',
						'upload_button_text' => esc_attr__( 'Upload an image', 'et_builder' ),
						'choose_text'        => esc_attr__( 'Choose an Image', 'et_builder' ),
						'update_text'        => esc_attr__( 'Set As Image', 'et_builder' ),
						'description'        => esc_html__( 'Upload your desired image, or type in the URL to the image you would like to display.', 'et_builder' ),
					),
					'animation' => array(
						'label'             => esc_html__( 'Animation', 'et_builder' ),
						'type'              => 'select',
						'option_category'   => 'configuration',
						'options'           => array(
							'off'     => esc_html__( 'No Animation', 'et_builder' ),
							'fade_in' => esc_html__( 'Fade In', 'et_builder' ),
							'left'    => esc_html__( 'Left To Right', 'et_builder' ),
							'right'   => esc_html__( 'Right To Left', 'et_builder' ),
							'top'     => esc_html__( 'Top To Bottom', 'et_builder' ),
							'bottom'  => esc_html__( 'Bottom To Top', 'et_builder' ),
						),
						'description'       => esc_html__( 'This controls the direction of the lazy-loading animation.', 'et_builder' ),
					),
					'background_layout' => array(
						'label'           => esc_html__( 'Text Color', 'et_builder' ),
						'type'            => 'select',
						'option_category' => 'color_option',
						'options'           => array(
							'light' => esc_html__( 'Dark', 'et_builder' ),
							'dark'  => esc_html__( 'Light', 'et_builder' ),
						),
						'description' => esc_html__( 'Here you can choose the value of your text. If you are working with a dark background, then your text should be set to light. If you are working with a light background, then your text should be dark.', 'et_builder' ),
					),
					'readmore_url' => array(
						'label'           => esc_html__( 'Read More Url', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'basic_option',
						'description'     => esc_html__( 'Input Detail Url.', 'et_builder' ),
					),
					'facebook_url' => array(
						'label'           => esc_html__( 'Facebook Profile Url', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'basic_option',
						'description'     => esc_html__( 'Input Facebook Profile Url.', 'et_builder' ),
					),
					'twitter_url' => array(
						'label'           => esc_html__( 'Twitter Profile Url', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'basic_option',
						'description'     => esc_html__( 'Input Twitter Profile Url', 'et_builder' ),
					),
					'google_url' => array(
						'label'           => esc_html__( 'Google+ Profile Url', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'basic_option',
						'description'     => esc_html__( 'Input Google+ Profile Url', 'et_builder' ),
					),
					'linkedin_url' => array(
						'label'           => esc_html__( 'LinkedIn Profile Url', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'basic_option',
						'description'     => esc_html__( 'Input LinkedIn Profile Url', 'et_builder' ),
					),
					'content_new' => array(
						'label'           => esc_html__( 'Description', 'et_builder' ),
						'type'            => 'tiny_mce',
						'option_category' => 'basic_option',
						'description'     => esc_html__( 'Input the main text content for your module here.', 'et_builder' ),
					),
					'icon_color' => array(
						'label'             => esc_html__( 'Icon Color', 'et_builder' ),
						'type'              => 'color',
						'custom_color'      => true,
						'tab_slug'          => 'advanced',
					),
					'icon_hover_color' => array(
						'label'             => esc_html__( 'Icon Hover Color', 'et_builder' ),
						'type'              => 'color',
						'custom_color'      => true,
						'tab_slug'          => 'advanced',
					),
					'disabled_on' => array(
						'label'           => esc_html__( 'Disable on', 'et_builder' ),
						'type'            => 'multiple_checkboxes',
						'options'         => array(
							'phone'   => esc_html__( 'Phone', 'et_builder' ),
							'tablet'  => esc_html__( 'Tablet', 'et_builder' ),
							'desktop' => esc_html__( 'Desktop', 'et_builder' ),
						),
						'additional_att'  => 'disable_on',
						'option_category' => 'configuration',
						'description'     => esc_html__( 'This will disable the module on selected devices', 'et_builder' ),
					),
					'admin_label' => array(
						'label'       => esc_html__( 'Admin Label', 'et_builder' ),
						'type'        => 'text',
						'description' => esc_html__( 'This will change the label of the module in the builder for easy identification.', 'et_builder' ),
					),
					'module_id' => array(
						'label'           => esc_html__( 'CSS ID', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'configuration',
						'tab_slug'        => 'custom_css',
						'option_class'    => 'et_pb_custom_css_regular',
					),
					'module_class' => array(
						'label'           => esc_html__( 'CSS Class', 'et_builder' ),
						'type'            => 'text',
						'option_category' => 'configuration',
						'tab_slug'        => 'custom_css',
						'option_class'    => 'et_pb_custom_css_regular',
					),
				);
				return $fields;
			}

			function shortcode_callback( $atts, $content = null, $function_name ) {
				$module_id         = $this->shortcode_atts['module_id'];
				$module_class      = $this->shortcode_atts['module_class'];
				$name              = $this->shortcode_atts['name'];
				$position          = $this->shortcode_atts['position'];
				$image_url         = $this->shortcode_atts['image_url'];
				$animation         = $this->shortcode_atts['animation'];
				$readmore_url      = $this->shortcode_atts['readmore_url'];
				$facebook_url      = $this->shortcode_atts['facebook_url'];
				$twitter_url       = $this->shortcode_atts['twitter_url'];
				$google_url        = $this->shortcode_atts['google_url'];
				$linkedin_url      = $this->shortcode_atts['linkedin_url'];
				$background_layout = $this->shortcode_atts['background_layout'];
				$icon_color        = $this->shortcode_atts['icon_color'];
				$icon_hover_color  = $this->shortcode_atts['icon_hover_color'];

				$module_class = ET_Builder_Element::add_module_order_class( $module_class, $function_name );

				$image = $social_links = '';

				if ( '' !== $icon_color ) {
					ET_Builder_Element::set_style( $function_name, array(
						'selector'    => '%%order_class%% .et_pb_member_social_links a',
						'declaration' => sprintf(
							'color: %1$s !important;',
							esc_html( $icon_color )
						),
					) );
				}

				if ( '' !== $icon_hover_color ) {
					ET_Builder_Element::set_style( $function_name, array(
						'selector'    => '%%order_class%% .et_pb_member_social_links a:hover',
						'declaration' => sprintf(
							'color: %1$s !important;',
							esc_html( $icon_hover_color )
						),
					) );
				}

				if ( '' !== $facebook_url ) {
					$social_links .= sprintf(
						'<li><a href="%1$s" class="et_pb_font_icon et_pb_facebook_icon"><span>%2$s</span></a></li>',
						esc_url( $facebook_url ),
						esc_html__( 'Facebook', 'et_builder' )
					);
				}

				if ( '' !== $twitter_url ) {
					$social_links .= sprintf(
						'<li><a href="%1$s" class="et_pb_font_icon et_pb_twitter_icon"><span>%2$s</span></a></li>',
						esc_url( $twitter_url ),
						esc_html__( 'Twitter', 'et_builder' )
					);
				}

				if ( '' !== $google_url ) {
					$social_links .= sprintf(
						'<li><a href="%1$s" class="et_pb_font_icon et_pb_google_icon"><span>%2$s</span></a></li>',
						esc_url( $google_url ),
						esc_html__( 'Google+', 'et_builder' )
					);
				}

				if ( '' !== $linkedin_url ) {
					$social_links .= sprintf(
						'<li><a href="%1$s" class="et_pb_font_icon et_pb_linkedin_icon"><span>%2$s</span></a></li>',
						esc_url( $linkedin_url ),
						esc_html__( 'LinkedIn', 'et_builder' )
					);
				}

				if ( '' !== $social_links ) {
					$social_links = sprintf( '<ul class="et_pb_member_social_links">%1$s</ul>', $social_links );
				}

				if ( '' !== $image_url ) {
					$image = sprintf(
						'<div class="et_pb_team_member_image et-waypoint%3$s">
							<div class="az-headshot" style="background-image:url(%1$s)"></div>
						</div>',
						esc_url( $image_url ),
						esc_attr( $name ),
						esc_attr( " et_pb_animation_{$animation}" )
					);
				}

				$output = sprintf(
					'<div%3$s class="et_pb_module et_pb_team_member%4$s%9$s et_pb_bg_layout_%8$s clearfix">
						%2$s
						<div class="et_pb_team_member_description">
							%5$s
							%6$s
							%1$s
							%7$s
						</div> <!-- .et_pb_team_member_description -->
					</div> <!-- .et_pb_team_member -->',
					$this->shortcode_content,
					( '' !== $image ? $image : '' ),
					( '' !== $module_id ? sprintf( ' id="%1$s"', esc_attr( $module_id ) ) : '' ),
					( '' !== $module_class ? sprintf( ' %1$s', esc_attr( $module_class ) ) : '' ),
					( '' !== $name ? sprintf( '<h4>%1$s</h4>', esc_html( $name ) ) : '' ),
					( '' !== $position ? sprintf( '<p class="et_pb_member_position">%1$s</p>', esc_html( $position ) ) : '' ),
					$social_links,
					$background_layout,
					( '' === $image ? ' et_pb_team_member_no_image' : '' )
				);

				return $output;
			}
		}
		new AZ_Builder_Module_Team_Member;

function ex_divi_child_theme_setup() {

	if ( class_exists('ET_Builder_Module')) {
	}
}
add_action('et_builder_ready', 'ex_divi_child_theme_setup');