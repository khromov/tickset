<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function tickset_gutenberg_cgb_block_assets() { // phpcs:ignore
	// Register block styles for both frontend + backend.
	wp_register_style(
		'tickset_gutenberg-cgb-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		array( 'wp-editor' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
	);

	// Register block editor script for backend.
	wp_register_script(
		'tickset_gutenberg-cgb-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'jquery' ), // Dependencies, defined above.
		null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);

	// Register block editor styles for backend.
	wp_register_style(
		'tickset_gutenberg-cgb-block-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
	);

	// WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
	wp_localize_script(
		'tickset_gutenberg-cgb-block-js',
		'cgbGlobal', // Array containing dynamic data for a JS Global.
		[
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'pluginDirPath' => plugin_dir_path( __DIR__ ),
			'pluginDirUrl'  => plugin_dir_url( __DIR__ ),
			'events' => WP_Tickset_API::get_events(false),
			'url'   => site_url('/wp-json/tickset/v1/'),
			'translations' => [
				'blockTitle' => __('Tickset event', 'tickset'),
				'blockListTitle' => __('Tickset list', 'tickset')
			]
		]
	);

	/**
	 * Register Gutenberg block on server-side.
	 *
	 * Register the block on server-side to ensure that the block
	 * scripts and styles for both frontend and backend are
	 * enqueued when the editor loads.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
	 * @since 1.16.0
	 */
	register_block_type(
		'cgb/block-tickset-gutenberg', array(
			// Enqueue blocks.style.build.css on both frontend & backend.
			'style'         => 'tickset_gutenberg-cgb-style-css',
			// Enqueue blocks.build.js in the editor only.
			'editor_script' => 'tickset_gutenberg-cgb-block-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'  => 'tickset_gutenberg-cgb-block-editor-css',
			// Attributes
			'attributes'  => array(
				'event_id' => array(
					'type' => 'string'
				),
				'event_url' => array(
					'type' => 'string'
				)
			),
			// Callback
			'render_callback' => 'tickset_block_render',
		)
	);

	register_block_type(
		'cgb/block-tickset-list-gutenberg', array(
			'style'         => 'tickset_gutenberg-cgb-style-css',
			'editor_script' => 'tickset_gutenberg-cgb-block-js',
			'editor_style'  => 'tickset_gutenberg-cgb-block-editor-css',
			'attributes'  => [],
			'render_callback' => 'tickset_block_list_render',
		)
	);
}

function tickset_block_list_render($attributes) {
	$is_in_edit_mode = isset($_SERVER['REQUEST_URI']) && strrpos($_SERVER['REQUEST_URI'], "context=edit");
	$uid = uniqid();

	$events = WP_Tickset_API::get_events();
	ob_start();
	?>
	<?php foreach($events as $id => $event): ?>
		<?php echo tickset_block_render(['event_id' => $id]); ?>
	<?php endforeach; ?>

	<?php if(!$events): ?>
	<div id="tickset-<?php echo $uid; ?>" class="tickset-gutenberg-block no-image no-description">

		<div class="tickset-content-wrapper">
			<h4 class="tickset-title">
				<?php _e('Tickset: No events found.', 'tickset'); ?>
			</h4>
		</div>
	</div>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}

/**
 * @param $attributes
 *
 * @return false|string
 */
function tickset_block_render($attributes) {
	$attributes = array_merge([
		'event_id' => 0,
		'event_url' => ''
	], $attributes);

	/** @var  $is_in_edit_mode boolean Check if we are in the editor */
	$is_in_edit_mode = $renew_data = isset($_SERVER['REQUEST_URI']) && strrpos($_SERVER['REQUEST_URI'], "context=edit");

	/** @var $uid string Unique ID for the element*/
	$uid = uniqid();

	if($attributes['event_url']) {
		$matches = [];
		preg_match('/.*tickset.com\/e\/([^\s]*)/', $attributes['event_url'], $matches);
		if(isset($matches[1])) {
			$event = WP_Tickset_API::get_event_by_slug($matches[1], $renew_data);
		}
	}
	else {
		$event = WP_Tickset_API::get_event_by_slug($attributes['event_id'], $renew_data);
	}


	// No event received from API. Either the block was just inserted or the event ID is invalid.
	if(!$event) {
		//If we're not in edit mode, just hide the event for now.
		if(!$is_in_edit_mode) {
			return;
		}
		ob_start();
		?>
		<div id="tickset-<?php echo $uid; ?>" class="tickset-gutenberg-block no-image no-description">
			<div class="tickset-content-wrapper">
				<h4 class="tickset-title">
					<?php _e('Tickset: No event selected yet, or event could not be found.', 'tickset'); ?>
				</h4>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	$eventStartDate = DateTime::createFromFormat(DateTime::ISO8601, $event->start_time);
	$eventEndDate = DateTime::createFromFormat(DateTime::ISO8601, $event->end_time);
	$eventStartDatePrintable = date_i18n(get_option('date_format'), $eventStartDate->getTimestamp() + $eventStartDate->getOffset());
	$eventEndDatePrintable = date_i18n(get_option('date_format'), $eventEndDate->getTimestamp() + $eventEndDate->getOffset());

	$allowedTags = '<b><i><strong><em><cite><bold><br>';

	ob_start();
	?>
	<div id="tickset-<?php echo $uid; ?>" class="tickset-gutenberg-block<?php echo isset($event->image->url) ? ' has-image' : ' no-image'; ?>">
		<?php if(isset($event->image->url)): ?>
			<img class="tickset-image" src="<?php echo esc_attr($event->image->url); ?>" alt="<?php echo esc_attr($event->name); ?>" />
		<?php endif; ?>
		<div class="tickset-content-wrapper">
			<h3 class="tickset-title">
				<?php echo strip_tags($event->name, $allowedTags); ?>
			</h3>
			<p class="tickset-description">
				<?php echo strip_tags($event->description, $allowedTags); ?>
			</p>
			<p>
				&#128197;  <?php echo $eventStartDatePrintable; ?> - <?php echo $eventEndDatePrintable; ?>
			</p>
			<a href="<?php echo esc_attr($event->url); ?>" target="_blank" class="tickset-button"><?php _e('Get tickets', 'tickset'); ?></a>
		</div>
	</div>
	<?php
	$content = ob_get_clean();

	/** If we are in the editor */
	if ($is_in_edit_mode) {
		/** If we are in the front end */
	} else {

	}
	return $content;
}

// Hook: Block assets.
add_action( 'init', 'tickset_gutenberg_cgb_block_assets' );
