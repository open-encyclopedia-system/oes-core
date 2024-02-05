<?php

/* Navigation */
add_shortcode('oes_language_switch', 'oes_language_switch_html');
add_shortcode('oes_table_of_contents', 'oes_table_of_contents_html');
add_shortcode('oes_breadcrumbs', 'oes_breadcrumbs_html');
add_shortcode('oes_reading_time', 'oes_reading_time_html');
add_shortcode('oes_print_button', 'oes_print_button_html');

/* Post Content */
add_shortcode('oes_post_terms', 'oes_post_terms_html');
add_shortcode('oes_field', 'oes_field_html');

/* Label */
add_shortcode('oes_theme_label', 'oes_theme_label_html');
add_shortcode('oes_language_label', 'oes_language_label_html');

/* Archive */
add_shortcode('oes_archive_count', 'oes_archive_count_html');
add_shortcode('oes_archive', 'oes_get_archive_loop_html');

/* Filter */
add_shortcode('oes_filter', 'oes_filter_html');
add_shortcode('oes_alphabet_filter', 'oes_alphabet_filter_html');
add_shortcode('oes_post_type_filter', 'oes_post_type_filter_html');
add_shortcode('oes_active_filter', 'oes_active_filter_html');
add_shortcode('oes_index_filter', 'oes_index_filter_html');
add_shortcode('oes_search_filter', 'oes_search_term_filter_html');

/* Popup */
add_shortcode('oes_popup', '\OES\Popup\render_shortcode');

//@oesDevelopment xml output in development
add_shortcode('oes_xml_button', 'oes_xml_shortcode');