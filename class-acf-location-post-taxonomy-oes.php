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

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_post_taxonomy_oes') ) :

class acf_location_post_taxonomy_oes extends acf_location {
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'post_taxonomy_with_attachment';
		$this->label = __("Post Taxonomy w/ Attachment",'acf');
		$this->category = 'post';
    	
	}
	
	
	/*
	*  rule_match
	*
	*  This function is used to match this location $rule to the current $screen
	*
	*  @type	function
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match( $result, $rule, $screen ) {
		
		// vars
		$post_id = acf_maybe_get( $screen, 'post_id' );

		// ToDo: Ilker added code in location-pos-taxonomy
		if (empty($post_id)) {
            $attachment_id = acf_maybe_get( $screen, 'attachment' );
            if (!empty($attachment_id)) {
                $post_id = $attachment_id;
            }
        }

		$terms = acf_maybe_get( $screen, 'post_taxonomy' );
		
		
		// bail early if not a post
		if( !$post_id ) return false;
		
		
		// get term data
		$data = acf_decode_taxonomy_term( $rule['value'] );
		$term = get_term_by( 'slug', $data['term'], $data['taxonomy'] );
		
		
		// attempt get term via ID (ACF4 uses ID)
		if( !$term && is_numeric($data['term']) ) {
			
			$term = get_term_by( 'id', $data['term'], $data['taxonomy'] );
			
		}
		
		
		// bail early if no term
		if( !$term ) return false;
		
		
		// not ajax, load real post's terms
		if( $terms === null ) {
			
			$terms = wp_get_post_terms( $post_id, $term->taxonomy, array('fields' => 'ids') );
			
		}
		
		
		// If no terms, this is a new post and should be treated as if it has the "Uncategorized" (1) category ticked
		if( empty($terms) ) {
			
			// get post type
			$post_type = get_post_type( $post_id );
			
			
			// if is category
			if( is_object_in_taxonomy($post_type, 'category') ) {
			
				$terms = array( 1 );
				
			}
			
		}
		
		
		// match
		if( !empty($terms) ) {
			
			$result = in_array( $term->term_id, $terms );
			
		}
		
		
		// reverse if 'not equal to'
        if( $rule['operator'] === '!=' ) {
	        	
        	$result = !$result;
        
        }
        
        
        // return
        return $result;
		
	}
	
	
	/*
	*  rule_operators
	*
	*  This function returns the available values for this rule type
	*
	*  @type	function
	*  @date	30/5/17
	*  @since	5.6.0
	*
	*  @param	n/a
	*  @return	(array)
	*/
	
	function rule_values( $choices, $rule ) {
		
		// get
		$choices = acf_get_taxonomy_terms();
		
			
		// unset post_format
		if( isset($choices['post_format']) ) {
		
			unset( $choices['post_format']) ;
			
		}
		
		
		// return
		return $choices;
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_post_taxonomy_oes' );

endif; // class_exists check

?>