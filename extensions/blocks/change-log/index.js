/**
 * External dependencies
 */
import { __, _x } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getIconColor } from '../../shared/block-icons';
import attributes from './attributes';
import edit from './edit';
import save from './save';

/**
 * Style dependencies
 */
import './style.scss';

export const name = 'change-log';
export const title = __( 'Changelog block', 'jetpack' );
export const settings = {
	title,
	description: __( 'Changelog', 'jetpack' ),
	icon: {
		src: 'yes',
		foreground: getIconColor(),
	},
	category: 'layout',
	keywords: [
		_x( 'changelog', 'block search term', 'jetpack' ),
	],
	supports: {
		'align': true,
	},
	edit,
	save,
	attributes,
	usesContext: [ 'change-log/labels' ],
};