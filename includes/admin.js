jQuery(document).ready(function($) {
	/**
	 * Dismiss admin notice
	 */
	$('.tickset-notice .notice-dismiss').on( 'click', function(e) {
		const config = window.tickset_admin;

		e.preventDefault();

		const data = {
			value: 'true',
		};

		$.ajax({
			method: 'POST',
			url: config.url + 'onboarding_admin_notice_dismissed',
			data: data,
			beforeSend: function (xhr) {
				xhr.setRequestHeader( 'X-WP-Nonce', config.nonce );
			},
			success: function( response ) {
				console.log('Dismissed notice:', response);
			},
			error: function( response, status, error ) {
				console.log('Couldn\'t dismiss notice:', status, error);
			}
		});
	});

	/**
	 * Verify user API key
	 */
	$('input#tickset-edit-api-key').debounce( 'input', function(event) {
		const config = window.tickset_admin;
		let apiKey = $('input#tickset-edit-api-key').val();

		event.preventDefault();

		const data = {
			apiKey: apiKey
		};

		$.ajax({
			method: 'POST',
			url: config.url + 'verify_api_key',
			data: data,
			beforeSend: function (xhr) {
				xhr.setRequestHeader( 'X-WP-Nonce', config.nonce );
			},
			success: function( response ) {
				console.log('Checked api key:', response);

				if(response) {
					$('#tickset-api-validation-icon').addClass('dashicons-yes-alt');
					$('#tickset-api-validation-icon').removeClass('dashicons-no-alt');
				}
				else {
					$('#tickset-api-validation-icon').addClass('dashicons-no-alt');
					$('#tickset-api-validation-icon').removeClass('dashicons-yes-alt');
				}
			},
			error: function( response, status, error ) {
				console.log('Couldn\'t check api key:', status, error);
			}
		});
	}, 700);

	$('input#tickset-edit-api-key').trigger('input');
});

// https://github.com/ohaibbq/jquery-debounce
(function($) {
	function debounce(callback, delay) {
		var self = this, timeout, _arguments;
		return function() {
			_arguments = Array.prototype.slice.call(arguments, 0),
				timeout = clearTimeout(timeout, _arguments),
				timeout = setTimeout(function() {
					callback.apply(self, _arguments);
					timeout = 0;
				}, delay);

			return this;
		};
	}

	$.extend($.fn, {
		debounce: function(event, callback, delay) {
			this.bind(event, debounce.apply(this, [callback, delay]));
		}
	});
})(jQuery);
