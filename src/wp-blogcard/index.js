/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';
import metadata from './block.json';

/**
 * Block styles
 */
import './style.scss';
import './editor.scss';

/**
 * Register the block
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save,
} );
