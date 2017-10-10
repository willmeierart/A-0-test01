<?php get_header(); ?>

<script>
	dataLayer.push({'event': '404'});
	console.log('404*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~');
</script>

<div id="main-content">
	<article class="page type-page">
		<div class="entry-content">
			<!-- <div class="et_pb_section et_section_regular">
				<div class=" et_pb_row et_pb_row_0">
					<div class="et_pb_column et_pb_column_4_4  et_pb_column_0">
						<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left az-copy et_pb_text_0"> -->



<?php
	// get the post by slug
	$the_slug = '404-page';
	$args=array(
		'name'           => $the_slug,
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1
	);
	$the_posts = get_posts( $args );
	$post_id = $the_posts[0]->ID;
	echo "<!-- *********************************************************";
	echo $post_id;
	echo "********************************************************* -->";

	// run the wordpress loop
	$queried_post = get_post($post_id);
	$content = $queried_post->post_content;
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	echo $content;
?>



						<!--</div>  .et_pb_text -->
					<!--</div>  .et_pb_column -->
				<!--</div>  .et_pb_row -->
			<!--</div>  .et_pb_section -->
		</div> <!-- .entry-content -->
	</article> <!-- .et_pb_post -->
</div>
<?php get_footer(); ?>