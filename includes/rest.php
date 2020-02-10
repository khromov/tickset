<?php
class WP_Tickset_REST {
    function __construct() {
	    add_action( 'rest_api_init', [$this, 'rest'] );
    }

    function rest() {
	    /**
	     * Dismiss onbooarding notice
	     */
	    register_rest_route( 'tickset/v1', '/onboarding_admin_notice_dismissed', array(
		    'methods' => ['POST', 'GET'],
		    'callback' => function (WP_REST_Request $request) {
		    	$value = $request->get_param('value') === 'true' ? true : false;
			    update_option(TICKSET_PREFIX . 'onboarding_admin_notice_dismissed', $value, true);
			    return $value;
		    },
		    'args' => [
			    'value' => [
				    'required' => true,
				    'validate_callback' => function($param, $request, $key) {
					    return $param === 'true' || $param === 'false';
				    }
			    ],
		    ],
		    'permission_callback' => function () {
			    return current_user_can( 'manage_options' );
		    }
	    ));

	    /**
	     * Check is API key is valid
	     */
	    register_rest_route( 'tickset/v1', '/verify_api_key', array(
		    'methods' => ['POST', 'GET'],
		    'callback' => function (WP_REST_Request $request) {
			    return WP_Tickset_API::is_valid_api_key($request->get_param('apiKey'));
		    },
		    'args' => [
			    'apiKey' => [
				    'required' => true,
				   // 'validate_callback' => function($param, $request, $key) {
				   //    return $param === 'true' || $param === 'false';
				   //  }
			    ],
		    ],
		    'permission_callback' => function () {
			    return current_user_can( 'manage_options' );
		    }
	    ));

	    /**
	     * Check is API key is valid
	     */
	    register_rest_route( 'tickset/v1', '/events', array(
		    'methods' => ['GET'],
		    'callback' => function (WP_REST_Request $request) {
			    //Random mock events for testing
			    /*
			    return [
			    	1 => ['name' => 'Random 1 ' . rand(0, 9999), 'id' => 1],
			        2 => ['name' => 'Random 2 ' . rand(0, 9999), 'id' => 1],
			    ];
			    */
			    return WP_Tickset_API::get_events(true);
		    },
		    'args' => [],
		    'permission_callback' => function () {
			    return current_user_can( 'read' );
		    }
	    ));
    }
}

