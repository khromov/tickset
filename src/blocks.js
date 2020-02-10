import './block/event.js';
import './block/event-list.js';

(() => {
	//TODO: Refactor to node-fetch + do the same in admin.js
	setInterval(() => {
		const { cgbGlobal: {
			nonce,
			url,
			events // Initial events list
		} } = window;

		jQuery.ajax({
			method: 'GET',
			url: url + 'events',
			//data: data,
			beforeSend: function (xhr) {
				xhr.setRequestHeader( 'X-WP-Nonce', nonce );
			},
			success: function( response ) {
				//console.log('Got response:', response);

				if(response) {
					window.cgbGlobal.events = response;
					console.log('Updated events', window.cgbGlobal.events);
				}
				else {
					console.log("Can't fetch new events");
				}
			},
			error: function( response, status, error ) {
				console.log('Couldn\'t resolve:', status, error);
			}
		});
	}, 15000);
})();
