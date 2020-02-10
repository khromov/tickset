<?php
add_action('init', function() {
	add_shortcode('tickset', function($atts, $content = '') {
		$atts = shortcode_atts( array(
				'event_id' => '',
				'event_url' => '',
		), $atts, 'tickset' );

		return tickset_block_render($atts);
	});

	add_shortcode('tickset_list', function($atts, $content = '') {
		$atts = shortcode_atts( array(
		), $atts, 'tickset' );

		return tickset_block_list_render($atts);
	});
});
