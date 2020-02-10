<?php
class WP_Tickset_Admin {
	function __construct() {
		add_action('admin_notices', [$this, 'general_admin_notice']);
		add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
		add_action( 'admin_menu', [$this, 'admin_menu']);
		add_action( 'admin_init', [$this, 'admin_init']);
	}

	function admin_menu() {
		add_options_page( 'Tickset', 'Tickset', 'manage_options', TICKSET_PREFIX_NO_UNDERSCORE, [$this, 'options_page'] );
	}

	function admin_init() {
		register_setting( TICKSET_PREFIX_NO_UNDERSCORE, 'tickset_settings' );

		add_settings_section(
			TICKSET_PREFIX . 'section',
			'',
			[$this, 'settings_description_render'],
			TICKSET_PREFIX_NO_UNDERSCORE
		);

		add_settings_field(
			'api_key',
			__( 'API key', 'tickset' ),
			[$this, 'api_field_render'],
			TICKSET_PREFIX_NO_UNDERSCORE,
			TICKSET_PREFIX . 'section'
		);

	}

	function options_page(  ) {

		//TODO: Add "refresh events" button for debugging that runs WP_Tickset_Api::get_events(true);

		?>
		<form action='options.php' method='post'>
			<h1>Tickset</h1>

			<div class="tickset-onboarding-step">
				<img src="<?php echo TICKSET_PLUGIN_URL . '/assets/one.svg'; ?>" alt="" class="icon">
				<h1>
					<?php _e('Create an account', 'tickset'); ?>
				</h1>
				<h3>
					<?php _e('By easily a creating an account, you can start selling tickets within 5 minutes.', 'tickset'); ?>
				</h3>
				<div>
					<a href="<?php echo TICKSET_SIGNUP_URL; ?>" class="tickset-button" target="_blank"><?php _e('Create Tickset account', 'tickset'); ?></a>
				</div>
			</div>

			<div class="tickset-onboarding-step">
				<img src="<?php echo TICKSET_PLUGIN_URL . '/assets/two.svg'; ?>" alt="" class="icon">
				<h1>
					<?php _e('Get your API key', 'tickset'); ?>
				</h1>
				<h3>
					<?php _e('Go to your Tickset profile and find your API key. You will find the API key on this page.', 'tickset'); ?>
				</h3>
				<div>
					<a href="<?php echo TICKSET_PROFILE_URL; ?>" class="tickset-button" target="_blank"><?php _e('Get your API key', 'tickset'); ?></a>
				</div>
				<div class="screenshot-wrapper">
					<img src="<?php echo TICKSET_PLUGIN_URL . '/assets/screenshot-api-key.png'; ?>" alt="" class="screenshot">
					<br>
					<span><?php _e('Example of what you API key will look like.', 'tickset'); ?></span>
				</div>
			</div>

			<div class="tickset-onboarding-step">
				<img src="<?php echo TICKSET_PLUGIN_URL . '/assets/three.svg'; ?>" alt="" class="icon">
				<h1>
					<?php _e('Enter your API key', 'tickset'); ?>
				</h1>
				<h3>
					<?php _e('Enter your API key below.', 'tickset'); ?>
				</h3>
				<?php
				settings_fields( TICKSET_PREFIX_NO_UNDERSCORE );
				do_settings_sections( TICKSET_PREFIX_NO_UNDERSCORE );
				submit_button(__('Save API key', 'tickset'));
				?>
			</div>

			<div class="tickset-onboarding-step">
				<img src="<?php echo TICKSET_PLUGIN_URL . '/assets/four.svg'; ?>" alt="" class="icon">
				<h1>
					<?php _e('Start showing your events on your site', 'tickset'); ?>
				</h1>
				<h3>
					<?php _e('If you\'re using Gutenberg', 'tickset'); ?>
				</h3>
				<p>
					<?php _e('Add a new Tickset event or Tickset event list block to embed blocks on your site.', 'tickset'); ?>
				</p>
				<div class="screenshot-wrapper">
					<img src="<?php echo TICKSET_PLUGIN_URL . '/assets/gutenberg.png'; ?>" alt="" class="screenshot">
					<br>
					<span><?php _e('The Gutenberg interface.', 'tickset'); ?></span>
				</div>

				<h3>
					<?php _e('If you\'re using Classic Editor', 'tickset'); ?>
				</h3>
				<p>
					<?php _e('If you are still using Classic Editor, we recommend that you start using Gutenberg. However, you can use the shortcodes below to embed your events in the classic editor or other places on your site.', 'tickset'); ?>
				</p>
				<?php
					$tickset_events = WP_Tickset_API::get_events();
					if($tickset_events):
				?>
					<p>
						<h2><?php _e('Your event shortcodes', 'tickset'); ?></h2>
						<ul>
							<?php foreach($tickset_events as $key => $event): ?>
							<li>
									<strong><?php echo esc_html($event->name); ?></strong>
									<p>[tickset event_id="<?php echo esc_html($key); ?>"]</p>
							</li>
							<?php endforeach; ?>
						</ul>
					</p>
						<p>
						<h2><?php _e('List all your events', 'tickset'); ?></h2>
						<p>[tickset_list]</p>
						</p>
				<?php endif; ?>
			</div>
		</form>

		<div class="tickset-credits">
			<em><?php _e('This plugin uses icons made by', 'tickset'); ?>
				<a href="https://www.flaticon.com/authors/vectors-market" target="_blank" rel="nofollow" title="Vectors Market">Vectors Market</a> &
				<a href="https://www.flaticon.com/authors/itim2101" target="_blank" rel="nofollow" title="itim2101">itim2101</a>
				<?php _e('from', 'tickset'); ?>
				<a href="https://www.flaticon.com/" target="_blank" rel="nofollow" title="Flaticon">flaticon.com</a> |
				Midsummer photo by Mikael Kristenson on Unsplash
			</em>
		</div>
		<?php
	}

	function api_field_render() {
		?>
		<input id="tickset-edit-api-key" type='text' name='tickset_settings[api_key]' value='<?php echo WP_Tickset::get_option('api_key', ''); ?>' />
		<span id="tickset-api-validation-icon" class="dashicons">&nbsp;</span>
		<?php
	}

	function settings_description_render() {
	}

	function general_admin_notice() {
		$onboarding_completed = WP_Tickset::get_option('api_key') ? true : false;
		$on_settings_page = get_current_screen() ? get_current_screen()->base === 'settings_page_tickset' : false;
		$onboarding_admin_notice_dismissed = get_option(TICKSET_PREFIX . 'onboarding_admin_notice_dismissed', false);
		if(current_user_can('manage_options') && !$onboarding_admin_notice_dismissed && !$onboarding_completed && !$on_settings_page):
			ob_start();
			?>
			<div class="tickset-notice notice notice-info is-dismissible">
				<div class="tickset-container">
					<div class="tickset-container-left">
						<img src="<?php echo TICKSET_PLUGIN_URL . '/assets/logo.svg'; ?>" alt="Tickset Logo">
						<img src="<?php echo TICKSET_PLUGIN_URL . '/assets/connection.svg'; ?>" class="tickset-connect__hide-phone-and-smaller" alt="Onboarding logo" height="auto" width="225">
					</div>

					<div class="tickset-container-right">
						<h2><?php _e('Sell tickets digitally with Tickset. Simple.', 'tickset'); ?></h2>
						<h4><?php _e('Tickset is the simple way to sell tickets digitally.', 'tickset'); ?></h4>
						<p>
							<?php
							_e('Fully SCA and PSD2 compliant and ready to receive payments in 25+ currencies using the world\'s most common payment solutions. No monthly fees, no fixed fees and no surprises. Create your free account today and get started!', 'tickset');
							?>
						</p>
						<a href="<?php echo admin_url('options-general.php?page=tickset'); ?>" class="tickset-button"><?php _e('Set up Tickset', 'tickset'); ?></a>
					</div>
				</div>
			</div>
			<?php
			echo ob_get_clean();
		endif;
	}

	function admin_scripts() {
		wp_enqueue_script( TICKSET_PREFIX . 'admin',
			TICKSET_PLUGIN_URL . '/includes/admin.js',
			['jquery'],
			TICKSET_PLUGIN_VERSION,
			true
		);

		wp_localize_script( TICKSET_PREFIX . 'admin', TICKSET_PREFIX . 'admin',
			array(
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'url'   => site_url('/wp-json/tickset/v1/')
			)
		);

		wp_enqueue_style( TICKSET_PREFIX . 'admin', TICKSET_PLUGIN_URL . '/includes/admin.css', [], TICKSET_PLUGIN_VERSION );
	}
}
