<?php
class WP_Tickset_API {
	/**
	 * Gets all the events.
	 *
	 * @param bool $renew
	 *
	 * @return array
	 */
	static function get_events($renew = false) {
		$response = self::api_call('/events/', [], $renew);

		$events = [];

		// Create a keyed array for easy single event lookups
		if(isset($response['data']->results)) {
			foreach($response['data']->results as $event) {
				$events["{$event->id}/{$event->slug}"] = $event;
			}
		}

		return $events;
	}

	/**
	 * @param $slug string Either an "id" like "1234/xfea" or a slug like "testevent"
	 *
	 * @param bool $renew
	 *
	 * @return object|null
	 */
	static function get_event_by_slug($slug, $renew = false) {
		$response = self::api_call("/events/{$slug}/", [], $renew);

		// Create a keyed array for easy single event lookups
		if(isset($response['data']) && isset($response['data']->id)) {
			return $response['data'];
		}
		return null;
	}

	static function is_valid_api_key($apiKey) {
		// Checks that events API call doesn't result in error, hence validating the API key
		return !self::api_call('/events/', [ 'api_key' => $apiKey])['error'];
	}

	/**
	 * @param string $endpoint
	 * @param array $params
	 * @param bool $renew
	 *
	 * @return array
	 */
	private static function api_call($endpoint = '/events/', $params = [], $renew = false) {
		$api_key = WP_Tickset::get_option('api_key');

		//Generate a unique cache key for this API request
		$option_name = 'tickset_api_' . md5("{$api_key}-{$endpoint}-" . json_encode($params));

		if(($existing_data = get_option($option_name)) && !$renew) {
			return [
				'error' => false,
				'data' => $existing_data
			];
		}

		$data = array_merge([
			'api_key' => $api_key,
		], $params);

		$url = TICKSET_API_BASE . $endpoint . '?' . http_build_query($data, NULL, '&', PHP_QUERY_RFC3986);

		$response = wp_safe_remote_get($url, [
			'timeout' => 2,
		]);

		if (!is_wp_error($response) && (wp_remote_retrieve_response_code($response) === 200 || wp_remote_retrieve_response_code($response) === 201)) {
			$error = false;
		}
		else {
			$error = true;
		}

		$data = @json_decode(wp_remote_retrieve_body($response), false);

		// Save data if we received valid data back
		if($data) {
			update_option($option_name, $data, false);
		}
		// Fallback to existing data if it exists
		else {
			$data = $existing_data;
		}

		return [
			'error' => $error,
			'data' => $data
		];
	}
}
