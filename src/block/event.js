//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType  } = wp.blocks; // Import registerBlockType() from wp.blocks
const { ServerSideRender, SelectControl, TextControl } = wp.components;
const { InspectorControls } = wp.editor;

registerBlockType( 'cgb/block-tickset-gutenberg', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: window.cgbGlobal.translations.blockTitle, // Block title.
	icon: 'tickets', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'tickset', 'tickset' ),
		__( 'event', 'tickset' ),
		__( 'create', 'tickset' ),
	],
	attributes: {
		'event_id': {
			type: 'string',
			default: ''
		},
		'event_url': {
			type: 'string',
			default: ''
		}
	},

	edit: ( props ) => {
		// console.log('Edit triggered');

		/*
		if (props.isSelected) {
			console.debug(props.attributes);
		}
		*/

		let eventsArray = Object.values(window.cgbGlobal.events);

		const events = eventsArray.map((event) => {
			return {
				label: event.name,
				value: `${event.id}/${event.slug}`
			};
		}).sort((a, b) => a.label.localeCompare(b.label)); // https://stackoverflow.com/a/45544166

		return (
			<div>
				<div className="tickset-event-container">
					<ServerSideRender block="cgb/block-tickset-gutenberg" attributes={ props.attributes } />
				</div>
				<InspectorControls>
					<hr style={{marginTop: 20}}/>
					<SelectControl
						label={'Event'}
						value={props.attributes.event_id}
						onChange={(value) => {
							props.setAttributes({ event_id: value });

							if(value !== 'custom') {
								props.setAttributes({ event_url: '' }); // Empty custom URL if we navigate from it
							}
						}}
						options={[
							{
								label: 'Please select an event',
								value: 'none'
							},
							...events,
							{
								label: '🌐 Custom event URL',
								value: 'custom'
							}
						]}
					/>
					{ props.attributes.event_id === 'custom' &&
						<TextControl
							label={'Custom event URL'}
							value={props.attributes.event_url}
							onChange={(value) => props.setAttributes({ event_url: value })}
						/> }
				</InspectorControls>

			</div>
		);
	},

	save: ( props ) => {
		return null;
	},
} );
