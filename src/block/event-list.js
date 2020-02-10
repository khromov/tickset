//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType  } = wp.blocks;
const { ServerSideRender, SelectControl, TextControl } = wp.components;
const { InspectorControls } = wp.editor;

registerBlockType( 'tickset/event-list', {
	title: window.cgbGlobal.translations.blockListTitle,
	icon: 'list-view',
	category: 'common',
	keywords: [
		__( 'tickset', 'tickset' ),
		__( 'event', 'tickset' ),
		__( 'create', 'tickset' ),
		__( 'list', 'tickset' ),
	],
	attributes: {
	},

	edit: ( props ) => {
		return (
			<div>
				<div className="tickset-event-container">
					<ServerSideRender block="tickset/event-list" attributes={ props.attributes } />
				</div>
			</div>
		);
	},

	save: ( props ) => {
		return null;
	},
} );
