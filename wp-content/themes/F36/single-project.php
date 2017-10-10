<?php

	get_header();

	$is_page_builder_used = et_pb_is_pagebuilder_used( get_the_ID() );

	$show_navigation = get_post_meta( get_the_ID(), '_et_pb_project_nav', true );

	// prev/next links tfelice
	$prevPost = get_previous_post();
	$prevThum = get_the_post_thumbnail($prevPost->ID);
	$nextPost = get_next_post();
	$nextThum = get_the_post_thumbnail($nextPost->ID);
?>


<div id="main-content">

<?php if ( ! $is_page_builder_used ) : ?>

	<div class="container">
		<div id="content-area" class="clearfix">
			<div id="left-area">

<?php endif; ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php if ( ! $is_page_builder_used ) : ?>

					<div class="et_main_title">
						<h1 class="entry-title"><?php the_title(); ?></h1>
						<span class="et_project_categories"><?php echo get_the_term_list( get_the_ID(), 'project_category', '', ', ' ); ?></span>
					</div>

				<?php
					$thumb = '';

					$width = (int) apply_filters( 'et_pb_portfolio_single_image_width', 1080 );
					$height = (int) apply_filters( 'et_pb_portfolio_single_image_height', 9999 );
					$classtext = 'et_featured_image';
					$titletext = get_the_title();
					$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Projectimage' );
					$thumb = $thumbnail["thumb"];

					$page_layout = get_post_meta( get_the_ID(), '_et_pb_page_layout', true );

					if ( '' !== $thumb )
						print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height );
				?>

				<?php endif; ?>

					<div class="entry-content">
					<?php
						the_content();

						if ( ! $is_page_builder_used )
							wp_link_pages( array( 'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'Divi' ), 'after' => '</div>' ) );
					?>
					</div> <!-- .entry-content -->

				<?php if ( ! $is_page_builder_used ) : ?>

					<?php if ( 'et_full_width_page' !== $page_layout ) et_pb_portfolio_meta_box(); ?>

				<?php endif; ?>

				<?php if ( ! $is_page_builder_used || ( $is_page_builder_used && 'on' === $show_navigation ) ) : ?>

					<div class="nav-single clearfix">
						<span class="nav-previous"><?php previous_post_link('%link','<div class="az-worknav">'.$prevThum.'<span class="az-label">Previous Project</span><span class="az-jobname">%title</span></div>'); ?></span>
						<span class="nav-next"><?php next_post_link('%link','<div class="az-worknav">'.$nextThum.'<span class="az-label">Next Project</span><span class="az-jobname">%title</span></div>'); ?></span>
					</div><!-- .nav-single -->

				<?php endif; ?>

				</article> <!-- .et_pb_post -->

			<?php
				if ( ! $is_page_builder_used && comments_open() && 'on' == et_get_option( 'divi_show_postcomments', 'on' ) )
					comments_template( '', true );
			?>
			<?php endwhile; ?>

<?php if ( ! $is_page_builder_used ) : ?>

			</div> <!-- #left-area -->

			<?php if ( 'et_full_width_page' === $page_layout ) et_pb_portfolio_meta_box(); ?>

			<?php get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->

<?php endif; ?>

</div> <!-- #main-content -->

<?php get_footer(); ?>