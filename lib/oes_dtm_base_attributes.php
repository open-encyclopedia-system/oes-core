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


/**
 *
 * @property $x_uid
 * 
 * @property $x_special_categories
 * @property $x_special_categories__id
 * @property $x_special_categories__html
 * @property $x_special_categories__ids
 *
 * @property $x_is_in_trash
 * @property $x_archived_data
 * @property $x_archived_data_date
 * @property $x_archived_data_user
 *
 * @property $x_title
 *
 * @property $x_title_sort
 * @property $x_title_sort__id
 * @property $x_title_sort__html
 * @property $x_title_sort__ids
 *
 * @property $x_title_list
 * @property $x_title_list__id
 * @property $x_title_list__html
 * @property $x_title_list__ids
 *
 * @property $x_title_list_sort
 * @property $x_title_list_sort__id
 * @property $x_title_list_sort__html
 * @property $x_title_list_sort__ids
 *
 * @property $x_is_hidden
 * @property $x_is_hidden__id
 * @property $x_is_hidden__html
 * @property $x_is_hidden__ids
 *
 * @property $x_is_queryable
 * @property $x_is_queryable__id
 * @property $x_is_queryable__html
 * @property $x_is_queryable__ids
 *
 * @property $x_is_visible
 * @property $x_is_visible__id
 * @property $x_is_visible__html
 * @property $x_is_visible__ids
 *
 * @property $x_is_published
 * @property $x_is_published__id
 * @property $x_is_published__html
 * @property $x_is_published__ids
 *
 * @property $x_is_listed
 * @property $x_is_listed__id
 * @property $x_is_listed__html
 * @property $x_is_listed__ids
 *
 * @property $x_is_indexable
 * @property $x_is_indexable__id
 * @property $x_is_indexable__html
 * @property $x_is_indexable__ids
 *
 * @property $x_rescan_queryability
 *
 * @property $x_is_recommendation
 * @property $x_is_recommendation__id
 * @property $x_is_recommendation__html
 * @property $x_is_recommendation__ids
 *
 * @property $x_is_not_approved
 * @property $x_is_not_approved__id
 * @property $x_is_not_approved__html
 * @property $x_is_not_approved__ids
 *
 * @property $x_is_user_content
 * @property $x_is_user_content__id
 * @property $x_is_user_content__html
 * @property $x_is_user_content__ids
 *
 * @property $x_remote_ref
 * @property $x_remote_ref__id
 * @property $x_remote_ref__html
 * @property $x_remote_ref__ids
 *
 * @property $x_created
 * @property $x_created__id
 * @property $x_created__html
 * @property $x_created__ids
 *
 * @property $x_imported
 * @property $x_imported__id
 * @property $x_imported__html
 * @property $x_imported__ids
 *
 * @property $x_last_updated
 * @property $x_last_updated__id
 * @property $x_last_updated__html
 * @property $x_last_updated__ids
 *
 * @property $x_last_updated_by_user
 *
 * @property $x_last_updated_by_contributor
 * @property $x_last_updated_by_contributor__id
 * @property $x_last_updated_by_contributor__obj
 *
 * @property $x_feature_image
 * @property $x_feature_image__id
 * @property $x_feature_image__html
 * @property $x_feature_image__ids
 *
 * @property $ID
 * @property $post_title
 * @property $post_excerpt
 * @property $post_content
 * @property $post_type
 * @property $post_status
 * @property $post_date
 * @property $post_date_gmt
 * @property $post_parent
 * @property $img_name
 * @property $img_title
 * @property $img_description
 * @property $img_caption
 * @property $img_alt
 * @property $img_filesize
 * @property $img_url
 * @property $img_link
 * @property $img_filename
 * @property $img_mime_type
 * @property $img_type
 * @property $img_subtype
 * @property $img_icon
 * @property $img_width
 * @property $img_height
 * @property $img_uploaded_to
 * @property $img_date
 * @property $img_modified
 * @property $img_sizes
 * @property $comment_status
 * @property $post_name
 * @property $post_sizes
 */
interface oes_dtm_base_attributes 
{

    const attr_ID = "ID";
    const attr_post_title = "post_title";
    const attr_post_excerpt = "post_excerpt";
    const attr_post_content = "post_content";
    const attr_post_type = "post_type";
    const attr_post_status = "post_status";
    const attr_post_date = "post_date";
    const attr_post_date_gmt = "post_date_gmt";
    const attr_comment_status = "comment_status";
    const attr_post_name = "post_name";
    const attr_post_sizes = "post_sizes";
    const attr_post_parent = "post_parent";

//    const attr_x_special_categories = "x_special_categories";
//
//
//    const attr_x_title_sort = "x_title_sort";
//
//
//    const attr_x_title_list = "x_title_list";
//
//
//    const attr_x_title_list_sort = "x_title_list_sort";
//
//
//    const attr_x_title_list_sort_class = "x_title_list_sort_class";
//
//
//    const attr_x_is_hidden = "x_is_hidden";
//
//
//    const attr_x_is_queryable = "x_is_queryable";
//
//
//    const attr_x_is_visible = "x_is_visible";
//
//
//    const attr_x_is_published = "x_is_published";
//
//
//    const attr_x_is_listed = "x_is_listed";
//
//
//    const attr_x_is_indexable = "x_is_indexable";
//
//
//    const attr_x_is_recommendation = "x_is_recommendation";
//
//
//    const attr_x_is_not_approved = "x_is_not_approved";
//
//
//    const attr_x_is_user_content = "x_is_user_content";
//
//
//    const attr_x_created = "x_created";


}