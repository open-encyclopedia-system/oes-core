import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import save from './save';
import metadata from './block.json';
import oesIcon from './../../icon';
import './editor.css';

registerBlockType(metadata.name, {
	icon: oesIcon,
	edit: Edit,
	save
});
