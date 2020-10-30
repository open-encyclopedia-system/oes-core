<?php
/*
 * This file is part of OES, the Open Encyclopedia System.
 *
 * Copyright (C) 2020 Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>

<?php

if( ! class_exists('acf_field_button_group_oes') ) :

class acf_field_button_group_oes extends acf_field {
	
	
	/**
	*  initialize()
	*
	*  This function will setup the field type data
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	 
	function initialize() {
		
		// vars
		$this->name = 'button_group_oes';
		$this->label = __("Button Group (OES)",'acf');
		$this->category = 'choice';
		$this->defaults = array(
			'choices'			=> array(),
			'default_value'		=> '',
			'allow_null' 		=> 0,
			'attribs' => [],
			'return_format'		=> 'value',
			'layout'			=> 'horizontal',
		);
		
	}
	
	
	/**
	*  render_field()
	*
	*  Creates the field's input HTML
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	array $field The field settings array
	*  @return	n/a
	*/
	
	function render_field( $field ) {
		
		// vars
		$html = '';
		$selected = null;
		$buttons = array();
		$value = esc_attr( $field['value'] );

		$add_attrs = x_as_array($field['attribs']);
		
		
		// bail ealrly if no choices
		if( empty($field['choices']) ) return;
		
		
		// buttons
		foreach( $field['choices'] as $_value => $_label ) {
			
			// checked
			$checked = ( $value === esc_attr($_value) );
			if( $checked ) $selected = true;

			$a = array(
                'name'		=> $field['name'],
                'value'		=> $_value,
                'checked'	=> $checked
            );

			if (is_string($_label)) {
                $a['label']	= $_label;
            } else {
                $a = array_merge($a, $_label);
            }
			
			// append
			$buttons[] = array_merge($a, $add_attrs);


							
		}
		
		
		// maybe select initial value
		if( !$field['allow_null'] && $selected === null ) {
			$buttons[0]['checked'] = true;
		}
		
		
		// div
		$div = array( 'class' => 'acf-button-group' );
		
		if( $field['layout'] == 'vertical' )	{ $div['class'] .= ' -vertical'; }
		if( $field['class'] )					{ $div['class'] .= ' ' . $field['class']; }
		if( $field['allow_null'] )				{ $div['data-allow_null'] = 1; }
		
		
		// hdden input
		$html .= acf_get_hidden_input( array('name' => $field['name']) );
			
			
		// open
		$html .= '<div ' . acf_esc_attr($div) . '>';
			
			// loop
			foreach( $buttons as $button ) {
				
				// checked
				if( $button['checked'] ) {
					$button['checked'] = 'checked';
				} else {
					unset($button['checked']);
				}
				
				
				// append
				$html .= acf_get_radio_input( $button );
				
			}
			
					
		// close
		$html .= '</div>';
		
		
		// return
		echo $html;
		
	}
	
	
	/**
	*  render_field_settings()
	*
	*  Creates the field's settings HTML
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	array $field The field settings array
	*  @return	n/a
	*/
	
	function render_field_settings( $field ) {
		
		// encode choices (convert from array)
		$field['choices'] = acf_encode_choices($field['choices']);
		
		
		// choices
		acf_render_field_setting( $field, array(
			'label'			=> __('Choices','acf'),
			'instructions'	=> __('Enter each choice on a new line.','acf') . '<br /><br />' . __('For more control, you may specify both a value and label like this:','acf'). '<br /><br />' . __('red : Red','acf'),
			'type'			=> 'textarea',
			'name'			=> 'choices',
		));
		
		
		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Appears when creating a new post','acf'),
			'type'			=> 'text',
			'name'			=> 'default_value',
		));
		
		
		// layout
		acf_render_field_setting( $field, array(
			'label'			=> __('Layout','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'layout',
			'layout'		=> 'horizontal', 
			'choices'		=> array(
				'horizontal'	=> __("Horizontal",'acf'),
				'vertical'		=> __("Vertical",'acf'), 
			)
		));
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Value','acf'),
			'instructions'	=> __('Specify the returned value on front end','acf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'value'			=> __('Value','acf'),
				'label'			=> __('Label','acf'),
				'array'			=> __('Both (Array)','acf')
			)
		));
		
	}
	
	
	/*
	*  update_field()
	*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	array $field The field array holding all the field options
	*  @return	$field
	*/

	function update_field( $field ) {
		
		return acf_get_field_type('radio')->update_field( $field );
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is appied to the $value after it is loaded from the db
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	mixed	$value		The value found in the database
	*  @param	mixed	$post_id	The post ID from which the value was loaded from
	*  @param	array	$field		The field array holding all the field options
	*  @return	$value
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		return acf_get_field_type('radio')->load_value( $value, $post_id, $field );
		
	}
	
	
	/*
	*  translate_field
	*
	*  This function will translate field settings
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	array $field The field array holding all the field options
	*  @return	$field
	*/
	
	function translate_field( $field ) {
		
		return acf_get_field_type('radio')->translate_field( $field );
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	mixed	$value		The value found in the database
	*  @param	mixed	$post_id	The post ID from which the value was loaded from
	*  @param	array	$field		The field array holding all the field options
	*  @return	$value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		return acf_get_field_type('radio')->format_value( $value, $post_id, $field );
		
	}
	
}


// initialize
acf_register_field_type( 'acf_field_button_group_oes' );

endif; // class_exists check

?>