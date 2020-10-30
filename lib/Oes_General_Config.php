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

use PhpQuery\PhpQuery as phpQuery;

abstract class Oes_General_Config
{


    const LANGUAGE_ENG = 'en';
    const LANGUAGE_GER = 'de';
    const LANGUAGE_HEL = 'el';


    const OES_DTM_FORM_GENERATED_CLASSES = "oes_dtm_form_classes";
    const PT_CONFIG_ATTR_FIELDS = 'fields';
    const PT_CONFIG_ATTR_FIELDS_SYS = 'fields_sys';
    const PT_CONFIG_ATTR_LABELS = 'labels';
    const PT_CONFIG_ATTR_NAME_FIELDS = 'namefields';
    const PT_CONFIG_ATTR_INDEX_FIELDS = 'indexfields';
    const PT_CONFIG_ATTR_TITLE_FIELD = 'titlefield';
    const PT_CONFIG_ATTR_DESCRIPTION_FIELD = 'descriptionfield';
    const PT_CONFIG_ATTR_TITLE_FIELD_LANGUAGE_BASED = 'titlefieldByLanguage';
    const PT_CONFIG_ATTR_DESCRIPTION_FIELD_LANGUAGE_BASED = 'descriptionfieldByLanguage';
    const PT_CONFIG_ATTR_TITLE_LIST_FIELD_LANGUAGE_BASED = 'titlelistfieldByLanguage';
    const PT_CONFIG_ATTR_TITLE_SORT_FIELD = 'titlesortfield';
    const PT_CONFIG_ATTR_TITLE_LIST_FIELD = 'titlelistfield';
    const PT_CONFIG_ATTR_TITLE_LIST_SORT_FIELD = 'titlesortlistfield';
    const PT_CONFIG_ATTR_HAS_VERSIONING = 'hasversioning';
    const PT_CONFIG_ATTR_HAS_NO_STATUS = 'nostatus';
    const PT_CONFIG_ATTR_LABELS_SINGULAR = 'singular';
    const PT_CONFIG_ATTR_LABELS_PLURAL = 'plural';
    const PT_CONFIG_ATTR_POST_TYPE = 'post_type';
    const PT_CONFIG_ATTR_ATTACHMENT_IS_IMAGE = 'is_image';
    const PT_CONFIG_ATTR_FORM_ID = 'acf_form_id';
    const PT_CONFIG_ATTR_DTM_CLASS = 'dtm_class';
    const PT_CONFIG_ATTR_QUERYABLE_IF_VISIBLE = 'queryableifvisible';
    const PT_CONFIG_ATTR_QUERYABLE_IF_PUBLISHED = 'queryableifpublished';
    const PT_CONFIG_ATTR_TRANSFORMER_CLASS = 'transformer_class';
    const PT_CONFIG_ATTR_DONT_ADD_POST_TYPE = 'dont_add_post_type';
    const PT_CONFIG_ATTR_IS_TERM = 'isTerm';
    const PT_CONFIG_ATTR_DONT_ADD_DEFAULT_FIELDS = 'dont_add_default_fields';
    const PT_CONFIG_ATTR_PRE_REQUISITES = 'prerequisites';
    const PT_CONFIG_ATTR_STATES = 'states';
    const PT_CONFIG_ATTR_FIELDGROUP_LOCATION = 'fieldgroup_location';
    const PT_CONFIG_POST_TYPE_REWRITE_SLUG = 'posttyperewriteslug';
    const ACTION_HOOK_OES_IS_READY = 'oes_is_ready';
    const TAX_DDC_SUBJECTS = "ddc_subjects";
    const TAX_DDC_HISTORY_GEOGRAPHY = "ddc_geographies";
    const TAX_CONTENT_PROVIDER = "contentprovider";
    const U_ITEM_TYPE_SEARCH = 'search';
    const U_ITEM_TYPE_BOOKMARK = 'bookmark';
    const U_ITEM_TYPE_PUBCOLITEM = 'pubcolitem';
    const U_COLLECTION_TYPE_PUBCOL = 'pubcol';
    const USER_STATUS_ENABLED = "Enabled";
    const BIBLIOGRAPHY_LIBRARY_LABELS = []; //@m. needs implementation for each project
    const EO_ADMIN_ROLES = [self::EO_WP_ADMIN_ROLE, self::EO_OES_ADMIN_ROLE];
    const EO_OES_TAGGING_ROLE = 'oes_tagging';
    const EO_OES_ADMIN_ROLE = 'oes_adm';
    const EO_WP_ADMIN_ROLE = 'administrator';
    const EO_OES_MANAGING_EDITOR_ROLE = 'oes_me';
    const ZOTERO_ISFWW_MAPPING = []; //@m. needs implementation for each project
    const EO_ARTICLE_VERSION_READY_FLAGS = []; //@m. needs implementation for each project
    const EXT_LINK_USER_CONTENT_TYPE_TO_DISPLAY = [
        'Book' => 'Book',
        'Book Section' => 'Article',
        'Journal Article' => 'Article',
        'Primary Document' => 'Primary Source',
        'Database' => 'Database',
        'Institutional Website' => 'Institutional Website',
        'Online Exhibition' => 'Online Exhibition',
        'Map' => 'Map',
        'Audio' => 'Audio',
        'Video' => 'Video',
    ];
    const EXT_LINK_SMW_IMPORT_TYPE_TO_DISPLAY = [
        'Article' => 'Article',
        'Book' => 'Book',
        'Primary Source' => 'Primary Source',
        'Database' => 'Database',
        'Image' => 'Image',
        'Website' => 'Institutional Website',
        'Online Exhibition' => 'Online Exhibition',
        'Map' => 'Map',
        'Audio' => 'Audio',
        'Video' => 'Video',
    ];
    const EXT_LINK_SMW_IMPORT_TYPE_TO_DISPLAY_LC = [
        'article' => 'Article',
        'book' => 'Book',
        'primary source' => 'Primary Source',
        'database' => 'Database',
        'image' => 'Image',
        'website' => 'Institutional Website',
        'online exhibition' => 'Online Exhibition',
        'map' => 'Map',
        'audio' => 'Audio',
        'video' => 'Video',
    ];
    const ARTICLE_CITATION_TEMPLATE = ''; //@m. needs implementation for each project
    const SPECIAL_CAT_published = 'published';
    const SPECIAL_CAT_not_published = "not_published";
    const SPECIAL_CAT_user_content = "user_content";
    const SPECIAL_CAT_not_user_content = "not_user_content";
    const SPECIAL_CAT_needs_approval = "needs_approval";
    const SPECIAL_CAT_needs_no_approval = "needs_no_approval";
    const SPECIAL_CAT_approved = "approved";
    const SPECIAL_CAT_not_approved = "not_approved";
    const LIST_SPECIAL_CATS = [
        self::SPECIAL_CAT_published => 'Published',
        self::SPECIAL_CAT_not_published => 'Not Published',
        self::SPECIAL_CAT_user_content => 'User Content',
        self::SPECIAL_CAT_not_user_content => 'Not User Content',
        self::SPECIAL_CAT_needs_approval => 'Needs Approval',
        self::SPECIAL_CAT_needs_no_approval => 'Needs No Approval',
        self::SPECIAL_CAT_approved => 'Approved',
        self::SPECIAL_CAT_not_approved => 'Not Approved',
    ];
    const TAX_TERM_SPECIAL_CAT_user_content = [
        'term' => self::SPECIAL_CAT_user_content, 'tax' => self::TAX_SPECIAL_CATS
    ];
    const TAX_TERM_SPECIAL_CAT_not_user_content = [
        'term' => self::SPECIAL_CAT_not_user_content, 'tax' => self::TAX_SPECIAL_CATS
    ];
    const DTM_AUTHOR_NAME_ONELINE = 1;
    const DTM_EDITOR_NAME_ONELINE = 2;
    const DTM_CONTRIBUTOR_ROLES = 3;
    const DTM_CONTRIBUTOR_NAME_HTML = 4;
    const DTM_X_TITLE_LIST_SOURCE = 5;
    const DTM_X_TITLE_SORT_SOURCE = 6;
    const DTM_CONTRIBUTOR_AFFILIATION_COMP = 7;
    const DTM_PUB_UPLOADED_BY_CONTRIBUTOR = 8;
    const DTM_PERSON_NAME = 9;
    const DTM_QUERYABLE = 10;
    const DTM_POST_TITLE = 11;
    const DTM_RELATED_ENTITY = 12;
    const MAPPING_SMW_LINK_TYPE_TO_OES = [
        'Primary Source' => 'Primary Document',
        'Website' => 'Institutional Website',
        'Book' => 'Monograph',
        'Article' => 'Journal Article',
    ];
    const CONTRIBUTOR_PROFILE_TYPE_INSTITUTIONAL = 'institutional';
    const CONTRIBUTOR_PROFILE_TYPE_PERSONAL = 'personal';
    const CONTRIBUTOR_PROFILE_TYPE_CHOICES = [
        self::CONTRIBUTOR_PROFILE_TYPE_PERSONAL => 'Individual',
        self::CONTRIBUTOR_PROFILE_TYPE_INSTITUTIONAL => 'Institution, Organization'
    ];
    const SIGNUP_TYPE_RESETPWD = 'resetpwd';
    const SIGNUP_TYPE_ACTIVATE = 'activate';
    const SIGNUP_TYPE_CHOICES = [
        self::SIGNUP_TYPE_RESETPWD => 'Reset Password',
        self::SIGNUP_TYPE_ACTIVATE => 'Activate Account',
    ];
    const CONTRIBUTOR_PROFILE_TYPE_DEFAULT = self::CONTRIBUTOR_PROFILE_TYPE_PERSONAL;
    const LIST_OF_ACF_RELATIONSHIP_TYPES = [
        'relationship', 'post_object', 'gallery', 'image', 'attachment'
    ];
    const COMP_DTM = 'comp_dtm';
    const DEFAULT_IMAGE_TYPES_OF_MATERIAL_LIST = array(
        'black-and-white photograph' => 'black-and-white photograph',
        'colour photograph' => 'colour photograph',
        'photograph' => 'photograph',
        'caricature' => 'caricature',
        'document' => 'document',
        'drawing' => 'drawing',
        'film still' => 'film still',
        'graph' => 'graph',
        'lithograph' => 'lithograph',
        'map' => 'map',
        'painting' => 'painting',
        'postcard' => 'postcard',
        'poster' => 'poster',
    );
    const ROLE_SPECIAL_CATS_PREFIX = "role_";
    const CONTRIBUTOR_PUB_STATUS = [
        Oes_General_Config::STATUS_WAITING_FOR_APPROVAL => Oes_General_Config::STATUS_WAITING_FOR_APPROVAL,
        Oes_General_Config::STATUS_IN_PREPARATION => Oes_General_Config::STATUS_IN_PREPARATION,
        Oes_General_Config::STATUS_READY_FOR_PUBLISHING => Oes_General_Config::STATUS_READY_FOR_PUBLISHING,
        Oes_General_Config::STATUS_PUBLISHED => Oes_General_Config::STATUS_PUBLISHED,
    ];
//    const CONTRIBUTOR_ROLES = [
//        'author' => "Author",
//        'section_editor' => "Section Editor",
//        'former_section_editor' => "Former Section Editor",
//        'managing_editor' => "Managing Editor",
//        'discussionarticlemoderator' => "Discussion Article Moderator",
//        'external_referee' => "External Refereee",
//        'editorial_advisory_board' => "Editorial Advisory Board",
//        'general_editor' => "General Editor",
//        'editor_in_chief' => "Editor-in-chief",
//        'translator' => "Translator",
//        'community' => "Commnuity Member",
//        'copy_editor' => "Copy-Editor",
//        'index_editor' => "Index-Editor",
//        'editorialoffice' => "Editorial Office",
//    ];
    const CONTRIBUTOR_ROLE_EXTERNAL_COMMUNITY = 'community';
    const CONTRIBUTOR_ROLE_EXTERNAL_REFEREE = 'externalreferee';
    const CONTRIBUTOR_ROLE_EDITORIAL_ADVISORY_BOARD = 'editorialadvisoryboard';
    const FG_EO_LINK = "eo_link";
    const FG_EO_BIBLIOGRAPHY = "eo_bibliography";
    const FG_EO_RECOMMEND_LINK = "user_external_link_upload";
    const FG_EO_RECOMMEND_LINK_VERIFICATION = "user_external_link_verification";
    const FG_EO_RECOMMEND_BIBLIOGRAPHY = "user_bibliography_upload";
    const FG_EO_RECOMMEND_BIBLIOGRAPHY_VERIFICATION = "user_bibliography_verification";
    const FG_EO_UPLOAD_IMAGE = "user_image_upload";
    const FG_EO_UPLOAD_IMAGE_VERIFICATION = "user_image_verification";
    const FG_EO_IMAGE = "eo_image1";
    const FG_EO_LOCATION = "eo_location";
    const FG_EO_EVENT = "eo_event";
    const POST_STATUS_NEEDS_NO_APPROVAL = "publish";
    const POST_STATUS_NEEDS_APPROVAL = "draft";
    const EXT_LINK_CITATION_LANGUAGES_NOT_INCLUDE = [
        'english', 'spanish', 'german', 'french', 'italian', 'other'
    ];
    const EXT_LINK_CITATION_ALL_LANGUAGES = [
        'english' => 'English', 'spanish' => 'Spanish', 'german' => 'German', 'french' => 'French', 'italian' => 'Italian',
        'afrikaans' => 'Afrikaans',
        'albanian' => 'Albanian',
        'arabic' => 'Arabic',
        'armenian' => 'Armenian',
        'belgian' => 'Belgian',
        'belorusian' => 'Belorusian',
        'bosnian' => 'Bosnian',
        'bulgarian' => 'Bulgarian',
        'catalan' => 'Catalan',
        'chinese' => 'Chinese',
        'czech' => 'Czech',
        'danish' => 'Danish',
        'dutch' => 'Dutch',
        'estonian' => 'Estonian',
        'finnish' => 'Finnish',
        'galician' => 'Galician',
        'japanese' => 'Japanese',
        'latvian' => 'Latvian',
        'lithuanian' => 'Lithuanian',
        'macedonian' => 'Macedonian',
        'moderngreek' => 'Modern Greek',
        'norwegian' => 'Norwegian',
        'persian' => 'Persian',
        'polish' => 'Polish',
        'portugues' => 'Portuguese',
        'romanian' => 'Romanian',
        'russian' => 'Russian',
        'serbian' => 'Serbian',
        'serbocroatian' => 'Serbo-Croatian',
        'slovenian' => 'Slovenian',
        'swedish' => 'Swedish',
        'turkish' => 'Turkish',
        'ukrainian' => 'Ukrainian',
        'vietnamese' => 'Vietnamese',
        'other' => 'Other Language',
    ];
    const REVIEWED_BY_EXTERNAL_REFEREES = "Reviewed by external referees on behalf of the General Editors";
    const IMPORTANT_CONTRIBUTOR_ROLES = [
        'externalreferee',
        'editorialadvisoryboard',
        'editorinchief',
        'formersectioneditor',
        'generaleditor',
        'sectioneditor',
        'institution',
        'organization',
    ];
    const CONTRIBUTOR_ROLE_MANAGING_EDITOR = 'managingeditor';
    const CONTRIBUTOR_ROLE_EDITORIAL_OFFICE = 'editorialoffice';
    const CONTRIBUTOR_ROLE_COPY_EDITOR = 'copyeditor';
    const CONTRIBUTOR_ROLE_AUTHOR = 'author';
    const CONTRIBUTOR_ROLE_TRANSLATOR = 'translator';
    const CONTRIBUTOR_ROLE_SECTION_EDITOR = 'sectioneditor';
    const CONTRIBUTOR_ROLE_FORMER_SECTION_EDITOR = 'formersectioneditor';
    const CONTRIBUTOR_TERM_IS_WORKING_REFEREE = 'isworkingreferee';
    const CONTRIBUTOR_TERM_IS_WORKING_REFEREE_AND_ME = 'isworkingrefereeasme';
    const CONTRIBUTOR_TERM_IS_NOT_WORKING_REFEREE = 'isnotworkingreferee';
    const CONTRIBUTOR_TERM_IS_NOT_WORKING_REFEREE_AND_ME = 'isnotworkingrefereeasme';
    const ARTICLE_TYPE_HANDBOOK = 'handbook';
    const ARTICLE_TYPE_ENCYCLOPEDIC = 'encyclopedic';
    const ARTICLE_TYPE_DISCUSSION = 'discussion';
    const ARTICLE_TYPE_INTRO = 'introduction';
    const ARTICLE_TYPES_1418 = []; //@m. part of specific project, needs implementation for each project
    const ARTICLE_CLASSIFICATION_GROUPS_1418_HB = []; //@m. part of specific project, needs implementation for each project
    const ARTICLE_REGIONAL_THEMATIC_ARTICLE = "Regional Thematic Article";
    const ARTICLE_DISCUSSION_ARTICLE = "Discussion";
    const ARTICLE_SURVEY_REGIONAL = "Survey Article (Regional)";
    const ARTICLE_SURVEY_THEMATIC = 'Survey Article (Thematic)';
    const ARTICLE_CLASSIFICATION_GROUP_PERSONS = "Persons";
    const ARTICLE_EE_CONCEPTS_PRACTICES_POLICIES = 'Concepts, Practices and Policies';
    const ARTICLE_EE_SPACES = 'Spaces';
    const ARTICLE_EE_PERSONS = 'Persons';
    const ARTICLE_EE_OBJECTS = 'Objects';
    const ARTICLE_EE_EVENTS = 'Events';
    const ARTICLE_EE_ORGANIZATIONS = 'Organizations';
    const ARTICLE_CLASSIFICATION_GROUPS_1418_EE = []; //@m. part of specific project, needs implementation for each project
    const ARTICLE_CLASSIFICATION_GROUPS_1418_OTHERS = []; //@m. part of specific project, needs implementation for each project
    /**
     * Prefix used in article types and classification groups in oes_special_cats
     */
    const ARTICLE_TAX_PREFIX = "";
    const ARTICLE_CLASSIFICATION_GROUPS_1418 = []; //@m. part of specific project, needs implementation for each project
    const STATUS_EO_ARTICLE_FIELD = ['type' => 'select',
        'key' => 'status',
        'name' => 'status',
        'label' => 'Publication Status',
        'default_value' => Oes_General_Config::STATUS_READY_FOR_PUBLISHING,
        'choices' => [
            Oes_General_Config::STATUS_PENDING => Oes_General_Config::STATUS_PENDING,
            Oes_General_Config::STATUS_IN_PREPARATION => Oes_General_Config::STATUS_IN_PREPARATION,
            Oes_General_Config::STATUS_READY_FOR_PUBLISHING => Oes_General_Config::STATUS_READY_FOR_PUBLISHING
        ]];
    const STATUS_EO_ARTICLE_VERSION_FIELD = ['type' => 'select',
        'key' => 'status',
        'name' => 'status',
        'label' => 'Publication Status',
        'default_value' => Oes_General_Config::STATUS_IN_PREPARATION,
        'choices' => [
            Oes_General_Config::STATUS_IN_PREPARATION => Oes_General_Config::STATUS_IN_PREPARATION,
            Oes_General_Config::STATUS_READY_FOR_PUBLISHING => Oes_General_Config::STATUS_READY_FOR_PUBLISHING
        ]];
    const POST_STATUS_PUBLISHED = 'publish';
    const POST_STATUS_DRAFT = 'draft';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_DELETED = 'Deleted';
    const STATUS_ON_HOLD = 'On Hold';
    const STATUS_DUPLICATE = 'Duplicate';
    const STATUS_DUPLICATE_CONFIRMED = 'Confirmed Duplicate';
    const STATUS_SMW_IMAGE_PUBLISHED = 'Published';
    const STATUS_PERMISSION_GRANTED = 'Permission Granted';
    const STATUS_PERMISSION_REQUESTED = 'Permission Requested';
    const STATUS_SMW_NO_PERMISSION_NEEDED = 'No Permission needed';
    const STATUS_SMW_IMAGE_PERMISSION_GRANTED = 'Permission granted';
    const STATUS_SMW_IMAGE_PERMISSION_REQUESTED = 'Permission requested';
    const STATUS_WAITING_FOR_APPROVAL = "Waiting for Approval";
    const STATUS_APPROVED_1 = "Approved 1";
    const STATUS_APPROVED_2 = "Approved 2";
    const STATUS_IN_PREPARATION = "In Preparation";
    const STATUS_IN_PROGRESS = "In Progress";
    const STATUS_PENDING = "Pending";
    const STATUS_RETRACTED = "Retracted";
    const STATUS_QUALITY_ASSURANCE = "Qualitätssicherung";
    const STATUS_COMPLETE = "Complete";
    const STATUS_INCOMPLETE = "Incomplete";
    const STATUS_REVISION = "Revision";
    const STATUS_READY_FOR_PUBLISHING = 'Ready for Publishing';
    const STATUS_PUBLISHED = 'Published';
    const STATUS_GENERAL_DEFAULT_FIELD = ['type' => 'select',
        'key' => 'status',
        'name' => 'status',
        'label' => 'Publication Status',
        'choices' => [
            Oes_General_Config::STATUS_PENDING => Oes_General_Config::STATUS_PENDING,
            Oes_General_Config::STATUS_WAITING_FOR_APPROVAL => Oes_General_Config::STATUS_WAITING_FOR_APPROVAL,
            Oes_General_Config::STATUS_READY_FOR_PUBLISHING => Oes_General_Config::STATUS_READY_FOR_PUBLISHING
        ]];
    const STATUS_ALWAYS_PUBLISHED_FIELD = ['type' => 'select',
        'key' => 'status',
        'name' => 'status',
        'label' => 'Publication Status',
        'default_value' => Oes_General_Config::STATUS_PUBLISHED,
        'choices' => [
            Oes_General_Config::STATUS_PUBLISHED => Oes_General_Config::STATUS_PUBLISHED
        ]];
    const TAX_ARTICLES = "all_articles";
    const TAX_PUB_ARTICLES = "articles";
    const TAX_CONTRIBUTORS = "contributors";
    const TAX_ALL_CONTRIBUTORS = "all_contributors";
    const CONTRIBUTOR_TAX_FOR_ROLES = "oes_special_cats";
    const TAX_PUB_STAT = "oes_pub_stat";
    const TAX_SPECIAL_CATS = "oes_special_cats";
    const TAX_ARTICLE_CLASS = "oes_article_class";
    const TAX_ARTICLE_TYPE = "oes_article_type";
    const AT_IS_HIDDEN = "is_hidden";
    const STATUS_HIDDEN_FIELD = [
        'key' => 'is_hidden',
        'name' => 'is_hidden',
        'type' => 'true_false', 'label' => 'Hide Entry',
        'instructions' => 'Turns this entry invisible and undiscoverable on the website.',
        'conditional_logic' =>
            array(
                array(
                    array(
                        'field' => 'status',
                        'operator' => '==',
                        'value' => Oes_General_Config::STATUS_READY_FOR_PUBLISHING
                    ,
                    ),
                ),
            ),
    ];
    const EO_GENERAL = "eo_general";
    const AMW_WORKFLOW = 'amw_workflow';
    const AMW_LOG_ITEM = 'amw_log_item';

//    static $LIST_OF_METADATA_ATTRIBUTES_MAPPING_PUB_ATTRIBUTES =
    const FORM_AMW_LOG_ITEM = 'form_amw_log_item';
    const AMW_INVITATION = 'amw_invitation';
    const AMW_ROLE = 'amw_role';
    const AMW_MANUSCRIPT = 'amw_manuscript';
    const FORM_AMW_MANUSCRIPT = 'form_amw_manuscript';
    const AMW_ROLE_AUTHOR = 'amw_role_author';
    const AMW_ROLE_SECTION_EDITOR = 'amw_role_sect_editr';
    const AMW_ROLE_EDITORIAL_OFFICE = 'amw_role_editr_offic';
    const AMW_ROLE_MANAGING_EDITOR = 'amw_role_manag_editr';
    const AMW_AGREEMENT = 'amw_agreement';
    const FORM_AMW_AGREEMENT = 'form_amw_agreement';
    const AMW_ROLE_PROXY_MANAGING_EDITOR = 'amw_role_pmng_editr';
    const AMW_ROLE_REFEREE = 'amw_role_referee';
    const AMW_ROLE_COPY_EDITOR = 'amw_role_copy_editor';
    const AMW_ROLE_TRANSLATOR = 'amw_role_translator';
    const AMW_REVIEW_REPORT = 'amw_review_report';
    const AMW_REVIEW = 'amw_review';
    const AMW_FILE = 'amw_file';
    const AMW_ARTICLE = 'amw_article';
    const AMW_SUBMISSION = 'amw_submission';
    const AMW_MESSAGE = 'amw_message';
    const AMW_DUEDATE = 'amw_duedate';
    const AMW_WORKFLOW_STATE = 'amw_workflow_state';
    const AMW_OBSERVER = 'amw_observer';
    const AMW_COPYEDITING = 'amw_copyediting';
    const AMW_PROOFREADING = 'amw_proofreading';
    const AMW_LAYOUTING = 'amw_layouting';
    const AMW_INITIALCHECK = 'amw_initialcheck';
    const FORM_AMW_OBSERVER = 'form_amw_observer';
    const FORM_AMW_INITIALCHECK = 'form_amw_initialcheck';
    const FORM_AMW_ROLE_OES_1 = 'form_amw_role_oes_1';
    const FORM_AMW_INVITATION_OES_1 = 'form_amw_invitation_oes_1';
    const FORM_AMW_WORKFLOW_OES_1 = 'form_amw_workflow_oes_1';
    const FORM_AMW_DUEDATE_OES = 'form_amw_duedate_oes';
    const FORM_AMW_MESSAGE_OES = 'form_amw_message_oes';
    const FORM_AMW_FILE_OES = 'form_amw_file_oes';
    const FORM_AMW_SUBMISSION_OES = 'form_amw_submission_oes';
    const FORM_AMW_REVIEW = 'form_amw_review';
    const FORM_AMW_COPYEDITING = 'form_amw_copyediting';
    const FORM_AMW_PROOFREADING = 'form_amw_proofreading';
    const FORM_AMW_REVIEW_REPORT = 'form_amw_review_report';
    const FORM_AMW_WORKFLOW_1418_1 = 'form_amw_workflow_1418_1';
    const FORM_AMW_WORKFLOW_STATE_OES_1 = 'form_amw_workflow_state_oes_1';
    const FORM_AMW_WORKFLOW_STATE_1418_1 = 'form_amw_workflow_state_1418_1';

    const PT_CONTRIBUTOR = 'contributor';
    const PT_REVIEW = 'review';
    const PT_REVIEW_SCHEMA = 'reviewschema';
    const PT_MESSAGE = 'message';
    const PT_INVITATION = "invitation";
    const PT_TASK = "task";
    const PT_WORKFLOW_SCHEMA = "workflowschema";
    const PT_ISSUE = "issue";
    const PT_PROJECT = "project";
    const PT_ISSUE_CHANGELOG = "issuechangelog";
    const PT_ISSUE_COMMENTS = "issuecomments";

    const EO_ARTICLE = "eo_article";
    const EO_ARTICLE_PUBWF = "articlepubwf";
    const EO_EVENT = "eo_event";
    const EO_LINK = "eo_link";
    const EO_BIBLIOGRAPHY = "eo_bibliography";
    const EO_IMAGE = "eo_image3";
    const EO_LOCATION = "eo_location";
    const EO_USER_IMAGE = "user_image";
    const EO_RECOMMEND_LINK = "eo_link";
    const EO_USER_PROFILE = "eo_user_profile";
    const EO_BLOG_POST = "post";
    const EO_RECOMMEND_BIBLIOGRAPHY = "eo_bibliography";
    const EO_PERSON = "eo_person";
    const EO_SUBJECT = "eo_subject";
    const P_LICENSE = "p_license";
    const S_SIGNUP = "s_signup";

    const P_LICENSE_MAIN = "p_license_main";
    const P_PERMISSION_MAIN = "p_permission_main";
    const P_PERMISSION = "p_permission";

    const EO_ARTICLE_MAIN = "eo_article_main";
    const U_MESSAGE_MAIN = "u_message_main";
    const U_SUBMISSION_MAIN = "u_submission_main";
    const PAGE_MAIN = "page_main";
    const U_ITEM_MAIN = "u_item_main";
    const U_BOOKMARK_MAIN = "u_bookmark_main";
    const S_SIGNUP_MAIN = "s_signup_main";
    const U_AUTHORSNOTE_MAIN = "u_authorsnote_main";
    const U_COLLECTION_MAIN = "u_collection_main";
    const U_MEMBERSHIP_MAIN = "u_membership_main";
    const U_MESSAGE = "u_message";
    const U_SUBMISSION = "u_submission";
    const U_COLLECTION = "u_collection";
    const ATTACHMENT = 'attachment';
    const PT_IMAGE = 'image';
    const U_MEMBERSHIP = "u_membership";
    const U_ITEM = "u_item";
    const U_BOOKMARK = "u_bookmark";
    const U_AUTHORSNOTE = "u_authorsnote";
    const EO_ARTICLE_VERSION_MAIN = "eo_article_version_main";
    const EO_ARTICLE_VERSION_BSB = "eo_article_version_bsb";
    const EO_ARTICLE_VERSION_TITLE = "eo_article_version_title";
    const EO_ARTICLE_VERSION_TEXT = "eo_article_version_text";
    const EO_CONTRIBUTOR_MAIN = "eo_contributor_main";
    const EO_CALL_FOR_PAPERS = "eo_call_for_papers";
    const EO_CALL_FOR_PAPERS_MAIN = "eo_call_for_papers_main";
    const EO_CONTRIBUTOR_USER = "eo_contributor_user";
    const SYS_USERDATA = "sys_userdata";
    const COUNTRIES_EXPANDED = [
        'AF' => 'Afghanistan',
        'AX' => 'Åland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua & Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AC' => 'Ascension Island',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BA' => 'Bosnia & Herzegovina',
        'BW' => 'Botswana',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory',
        'VG' => 'British Virgin Islands',
        'BN' => 'Brunei',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'IC' => 'Canary Islands',
        'CV' => 'Cape Verde',
        'BQ' => 'Caribbean Netherlands',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'EA' => 'Ceuta & Melilla',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo - Brazzaville',
        'CD' => 'Congo - Kinshasa',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => 'Côte d’Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CW' => 'Curaçao',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DG' => 'Diego Garcia',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong SAR China',
        'HK@1' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'XK' => 'Kosovo',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Laos',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macau SAR China',
        'MK' => 'Macedonia',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar (Burma)',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'KP' => 'North Korea',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestinian Territories',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn Islands',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Réunion',
        'RO' => 'Romania',
        'RU' => 'Russia',
        'RU@1' => 'Russian Federation',
        'RW' => 'Rwanda',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'São Tomé & Príncipe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SX' => 'Sint Maarten',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia & South Sandwich Islands',
        'KR' => 'South Korea',
        'SS' => 'South Sudan',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'BL' => 'St. Barthélemy',
        'SH' => 'St. Helena',
        'KN' => 'St. Kitts & Nevis',
        'LC' => 'St. Lucia',
        'MF' => 'St. Martin',
        'PM' => 'St. Pierre & Miquelon',
        'VC' => 'St. Vincent & Grenadines',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard & Jan Mayen',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syria',
        'TW' => 'Taiwan',
        'TW@1' => 'Taiwan, Republic of China',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad & Tobago',
        'TT@1' => 'Trinidad And Tobago',
        'TA' => 'Tristan da Cunha',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks & Caicos Islands',
        'TC@1' => 'Turks And Caicos Islands',
        'TV' => 'Tuvalu',
        'UM' => 'U.S. Outlying Islands',
        'VI' => 'U.S. Virgin Islands',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States of America',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VA' => 'Vatican City',
        'VE' => 'Venezuela',
        'VN' => 'Vietnam',
        'WF' => 'Wallis & Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
    ];
    const COUNTRIES = [
        'AF' => 'Afghanistan',
        'AX' => 'Åland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua & Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AC' => 'Ascension Island',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BA' => 'Bosnia & Herzegovina',
        'BW' => 'Botswana',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory',
        'VG' => 'British Virgin Islands',
        'BN' => 'Brunei',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'IC' => 'Canary Islands',
        'CV' => 'Cape Verde',
        'BQ' => 'Caribbean Netherlands',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'EA' => 'Ceuta & Melilla',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo - Brazzaville',
        'CD' => 'Congo - Kinshasa',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => 'Côte d’Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CW' => 'Curaçao',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DG' => 'Diego Garcia',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong SAR China',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'XK' => 'Kosovo',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Laos',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macau SAR China',
        'MK' => 'Macedonia',
        'MK@1' => 'Macedonia, The Former Yugoslav Republic Of',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar (Burma)',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'KP' => 'North Korea',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestinian Territories',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn Islands',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Réunion',
        'RO' => 'Romania',
        'RU' => 'Russia',
        'RW' => 'Rwanda',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'São Tomé & Príncipe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SX' => 'Sint Maarten',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia & South Sandwich Islands',
        'KR' => 'South Korea',
        'SS' => 'South Sudan',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'BL' => 'St. Barthélemy',
        'SH' => 'St. Helena',
        'KN' => 'St. Kitts & Nevis',
        'LC' => 'St. Lucia',
        'MF' => 'St. Martin',
        'PM' => 'St. Pierre & Miquelon',
        'VC' => 'St. Vincent & Grenadines',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard & Jan Mayen',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syria',
        'TW' => 'Taiwan',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad & Tobago',
        'TA' => 'Tristan da Cunha',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks & Caicos Islands',
        'TV' => 'Tuvalu',
        'UM' => 'U.S. Outlying Islands',
        'VI' => 'U.S. Virgin Islands',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VA' => 'Vatican City',
        'VE' => 'Venezuela',
        'VN' => 'Vietnam',
        'WF' => 'Wallis & Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
    ];
    const LANGUAGE_CODES_TO_ENGLISH_LABELS = array(
        'ab' => 'Abkhazian',
        'abk' => 'Abkhazian',
        'Abkhazian' => 'Abkhazian',
        'abkhazian' => 'Abkhazian',
        'aa' => 'Afar',
        'aar' => 'Afar',
        'Afar' => 'Afar',
        'afar' => 'Afar',
        'af' => 'Afrikaans',
        'afr' => 'Afrikaans',
        'Afrikaans' => 'Afrikaans',
        'afrikaans' => 'Afrikaans',
        'sq' => 'Albanian',
        'alb' => 'Albanian',
        'sqi' => 'Albanian',
        'Albanian' => 'Albanian',
        'albanian' => 'Albanian',
        'am' => 'Amharic',
        'amh' => 'Amharic',
        'Amharic' => 'Amharic',
        'amharic' => 'Amharic',
        'ar' => 'Arabic',
        'ara' => 'Arabic',
        'Arabic' => 'Arabic',
        'arabic' => 'Arabic',
        'an' => 'Aragonese',
        'arg' => 'Aragonese',
        'Aragonese' => 'Aragonese',
        'aragonese' => 'Aragonese',
        'hy' => 'Armenian',
        'arm' => 'Armenian',
        'hye' => 'Armenian',
        'Armenian' => 'Armenian',
        'armenian' => 'Armenian',
        'as' => 'Assamese',
        'asm' => 'Assamese',
        'Assamese' => 'Assamese',
        'assamese' => 'Assamese',
        'ae' => 'Avestan',
        'ave' => 'Avestan',
        'Avestan' => 'Avestan',
        'avestan' => 'Avestan',
        'ay' => 'Aymara',
        'aym' => 'Aymara',
        'Aymara' => 'Aymara',
        'aymara' => 'Aymara',
        'az' => 'Azerbaijani',
        'aze' => 'Azerbaijani',
        'Azerbaijani' => 'Azerbaijani',
        'azerbaijani' => 'Azerbaijani',
        'ba' => 'Bashkir',
        'bak' => 'Bashkir',
        'Bashkir' => 'Bashkir',
        'bashkir' => 'Bashkir',
        'eu' => 'Basque',
        'baq' => 'Basque',
        'eus' => 'Basque',
        'Basque' => 'Basque',
        'basque' => 'Basque',
        'be' => 'Belarusian',
        'bel' => 'Belarusian',
        'Belarusian' => 'Belarusian',
        'belarusian' => 'Belarusian',
        'bn' => 'Bengali',
        'ben' => 'Bengali',
        'Bengali' => 'Bengali',
        'bengali' => 'Bengali',
        'bh' => 'Bihari',
        'bih' => 'Bihari',
        'Bihari' => 'Bihari',
        'bihari' => 'Bihari',
        'bi' => 'Bislama',
        'bis' => 'Bislama',
        'Bislama' => 'Bislama',
        'bislama' => 'Bislama',
        'bs' => 'Bosnian',
        'bos' => 'Bosnian',
        'Bosnian' => 'Bosnian',
        'bosnian' => 'Bosnian',
        'br' => 'Breton',
        'bre' => 'Breton',
        'Breton' => 'Breton',
        'breton' => 'Breton',
        'bg' => 'Bulgarian',
        'bul' => 'Bulgarian',
        'Bulgarian' => 'Bulgarian',
        'bulgarian' => 'Bulgarian',
        'my' => 'Burmese',
        'bur' => 'Burmese',
        'mya' => 'Burmese',
        'Burmese' => 'Burmese',
        'burmese' => 'Burmese',
        'ca' => 'Catalan',
        'cat' => 'Catalan',
        'Catalan' => 'Catalan',
        'catalan' => 'Catalan',
        'ch' => 'Chamorro',
        'cha' => 'Chamorro',
        'Chamorro' => 'Chamorro',
        'chamorro' => 'Chamorro',
        'ce' => 'Chechen',
        'che' => 'Chechen',
        'Chechen' => 'Chechen',
        'chechen' => 'Chechen',
        'zh' => 'Chinese',
        'chi' => 'Chinese',
        'zho' => 'Chinese',
        'Chinese' => 'Chinese',
        'chinese' => 'Chinese',
        'cu' => 'Church Slavic',
        'chu' => 'Church Slavic',
        'Church Slavic' => 'Church Slavic',
        'church slavic' => 'Church Slavic',
        'cv' => 'Chuvash',
        'chv' => 'Chuvash',
        'Chuvash' => 'Chuvash',
        'chuvash' => 'Chuvash',
        'kw' => 'Cornish',
        'cor' => 'Cornish',
        'Cornish' => 'Cornish',
        'cornish' => 'Cornish',
        'co' => 'Corsican',
        'cos' => 'Corsican',
        'Corsican' => 'Corsican',
        'corsican' => 'Corsican',
        'hr' => 'Croatian',
        'scr' => 'Croatian',
        'hrv' => 'Croatian',
        'Croatian' => 'Croatian',
        'croatian' => 'Croatian',
        'Hrvatski' => 'Croatian',
        'kr' => 'Croatian',
        'cs' => 'Czech',
        'cz' => 'Czech',
        'cze' => 'Czech',
        'ces' => 'Czech',
        'Czech' => 'Czech',
        'czech' => 'Czech',
        'da' => 'Danish',
        'dan' => 'Danish',
        'dk' => 'Danish',
        'Danish' => 'Danish',
        'danish' => 'Danish',
        'dv' => 'Divehi',
        'div' => 'Divehi',
        'Divehi' => 'Divehi',
        'divehi' => 'Divehi',
        'nl' => 'Dutch',
        'dut' => 'Dutch',
        'Belgian' => 'Dutch',
        'belgian' => 'Dutch',
        'nld' => 'Dutch',
        'Dutch' => 'Dutch',
        'dutch' => 'Dutch',
        'dz' => 'Dzongkha',
        'dzo' => 'Dzongkha',
        'Dzongkha' => 'Dzongkha',
        'dzongkha' => 'Dzongkha',
        'en' => 'English',
        'eng' => 'English',
        'English' => 'English',
        'Englis' => 'English',
        'englis' => 'English',
        'english' => 'English',
        'anglais' => 'English',
        'Anglais' => 'English',
        'EN' => 'English',
        'us' => 'English',
        'US' => 'English',
        'eo' => 'Esperanto',
        'epo' => 'Esperanto',
        'Esperanto' => 'Esperanto',
        'esperanto' => 'Esperanto',
        'et' => 'Estonian',
        'est' => 'Estonian',
        'Estonian' => 'Estonian',
        'estonian' => 'Estonian',
        'fo' => 'Faroese',
        'fao' => 'Faroese',
        'Faroese' => 'Faroese',
        'faroese' => 'Faroese',
        'fj' => 'Fijian',
        'fij' => 'Fijian',
        'Fijian' => 'Fijian',
        'fijian' => 'Fijian',
        'fi' => 'Finnish',
        'fin' => 'Finnish',
        'Finnish' => 'Finnish',
        'finnish' => 'Finnish',
        'fr' => 'French',
        'fre' => 'French',
        'fra' => 'French',
        'French' => 'French',
        'french' => 'French',
        'français' => 'French',
        'Français' => 'French',
        'gd' => 'Gaelic',
        'gla' => 'Gaelic',
        'Gaelic' => 'Gaelic',
        'gaelic' => 'Gaelic',
        'gl' => 'Galician',
        'glg' => 'Galician',
        'Galician' => 'Galician',
        'galician' => 'Galician',
        'ka' => 'Georgian',
        'geo' => 'Georgian',
        'kat' => 'Georgian',
        'Georgian' => 'Georgian',
        'georgian' => 'Georgian',
        'de' => 'German',
        'Deutsch' => 'German',
        'deutsch' => 'German',
        'ger' => 'German',
        'allemand' => 'German',
        'deu' => 'German',
        'German' => 'German',
        'german' => 'German',
        'el' => 'Modern Greek',
        'gr' => 'Modern Greek',
        'gre' => 'Modern Greek',
        'ell' => 'Modern Greek',
        'Modern Greek' => 'Modern Greek',
        'modern greek' => 'Modern Greek',
        'Greek, Modern' => 'Modern Greek',
        'gn' => 'Guarani',
        'grn' => 'Guarani',
        'Guarani' => 'Guarani',
        'guarani' => 'Guarani',
        'gu' => 'Gujarati',
        'guj' => 'Gujarati',
        'Gujarati' => 'Gujarati',
        'gujarati' => 'Gujarati',
        'ht' => 'Haitian',
        'hat' => 'Haitian',
        'Haitian' => 'Haitian',
        'haitian' => 'Haitian',
        'ha' => 'Hausa',
        'hau' => 'Hausa',
        'Hausa' => 'Hausa',
        'hausa' => 'Hausa',
        'he' => 'Hebrew',
        'heb' => 'Hebrew',
        'Hebrew' => 'Hebrew',
        'hebrew' => 'Hebrew',
        'hz' => 'Herero',
        'her' => 'Herero',
        'Herero' => 'Herero',
        'herero' => 'Herero',
        'hi' => 'Hindi',
        'hin' => 'Hindi',
        'Hindi' => 'Hindi',
        'hindi' => 'Hindi',
        'ho' => 'Hiri Motu',
        'hmo' => 'Hiri Motu',
        'Hiri Motu' => 'Hiri Motu',
        'hiri motu' => 'Hiri Motu',
        'hu' => 'Hungarian',
        'hg' => 'Hungarian',
        'hun' => 'Hungarian',
        'Hungarian' => 'Hungarian',
        'hungarian' => 'Hungarian',
        'is' => 'Icelandic',
        'ice' => 'Icelandic',
        'isl' => 'Icelandic',
        'Icelandic' => 'Icelandic',
        'icelandic' => 'Icelandic',
        'io' => 'Ido',
        'ido' => 'Ido',
        'Ido' => 'Ido',
        'id' => 'Indonesian',
        'ind' => 'Indonesian',
        'Indonesian' => 'Indonesian',
        'indonesian' => 'Indonesian',
        'ia' => 'Interlingua',
        'ina' => 'Interlingua',
        'Interlingua' => 'Interlingua',
        'interlingua' => 'Interlingua',
        'ie' => 'Interlingue',
        'ile' => 'Interlingue',
        'Interlingue' => 'Interlingue',
        'interlingue' => 'Interlingue',
        'multilingue' => 'Multilingue',
        'mul' => 'Multilingue',
        'iu' => 'Inuktitut',
        'iku' => 'Inuktitut',
        'Inuktitut' => 'Inuktitut',
        'inuktitut' => 'Inuktitut',
        'ik' => 'Inupiaq',
        'ipk' => 'Inupiaq',
        'Inupiaq' => 'Inupiaq',
        'inupiaq' => 'Inupiaq',
        'ga' => 'Irish',
        'gle' => 'Irish',
        'Irish' => 'Irish',
        'irish' => 'Irish',
        'it' => 'Italian',
        'ita' => 'Italian',
        'Italian' => 'Italian',
        'italian' => 'Italian',
        'italien' => 'Italian',
        'Italien' => 'Italian',
        'ja' => 'Japanese',
        'jpn' => 'Japanese',
        'jp' => 'Japanese',
        'Japanese' => 'Japanese',
        'japanese' => 'Japanese',
        'jv' => 'Javanese',
        'jav' => 'Javanese',
        'Javanese' => 'Javanese',
        'javanese' => 'Javanese',
        'kl' => 'Kalaallisut',
        'kal' => 'Kalaallisut',
        'Kalaallisut' => 'Kalaallisut',
        'kalaallisut' => 'Kalaallisut',
        'kn' => 'Kannada',
        'kan' => 'Kannada',
        'Kannada' => 'Kannada',
        'kannada' => 'Kannada',
        'ks' => 'Kashmiri',
        'kas' => 'Kashmiri',
        'Kashmiri' => 'Kashmiri',
        'kashmiri' => 'Kashmiri',
        'kk' => 'Kazakh',
        'kaz' => 'Kazakh',
        'Kazakh' => 'Kazakh',
        'kazakh' => 'Kazakh',
        'km' => 'Khmer',
        'khm' => 'Khmer',
        'Khmer' => 'Khmer',
        'khmer' => 'Khmer',
        'ki' => 'Kikuyu',
        'kik' => 'Kikuyu',
        'Kikuyu' => 'Kikuyu',
        'kikuyu' => 'Kikuyu',
        'rw' => 'Kinyarwanda',
        'kin' => 'Kinyarwanda',
        'Kinyarwanda' => 'Kinyarwanda',
        'kinyarwanda' => 'Kinyarwanda',
        'ky' => 'Kirghiz',
        'kir' => 'Kirghiz',
        'Kirghiz' => 'Kirghiz',
        'kirghiz' => 'Kirghiz',
        'kv' => 'Komi',
        'kom' => 'Komi',
        'Komi' => 'Komi',
        'komi' => 'Komi',
        'ko' => 'Korean',
        'kor' => 'Korean',
        'Korean' => 'Korean',
        'korean' => 'Korean',
        'kj' => 'Kuanyama',
        'kua' => 'Kuanyama',
        'Kuanyama' => 'Kuanyama',
        'kuanyama' => 'Kuanyama',
        'ku' => 'Kurdish',
        'kur' => 'Kurdish',
        'Kurdish' => 'Kurdish',
        'kurdish' => 'Kurdish',
        'lo' => 'Lao',
        'lao' => 'Lao',
        'Lao' => 'Lao',
        'la' => 'Latin',
        'lat' => 'Latin',
        'Latin' => 'Latin',
        'latin' => 'Latin',
        'lv' => 'Latvian',
        'lav' => 'Latvian',
        'Latvian' => 'Latvian',
        'latvian' => 'Latvian',
        'li' => 'Limburgan',
        'lim' => 'Limburgan',
        'Limburgan' => 'Limburgan',
        'limburgan' => 'Limburgan',
        'ln' => 'Lingala',
        'lin' => 'Lingala',
        'Lingala' => 'Lingala',
        'lingala' => 'Lingala',
        'lt' => 'Lithuanian',
        'lit' => 'Lithuanian',
        'Lithuanian' => 'Lithuanian',
        'lithuanian' => 'Lithuanian',
        'lb' => 'Luxembourgish',
        'ltz' => 'Luxembourgish',
        'Luxembourgish' => 'Luxembourgish',
        'luxembourgish' => 'Luxembourgish',
        'mk' => 'Macedonian',
        'mac' => 'Macedonian',
        'mkd' => 'Macedonian',
        'Macedonian' => 'Macedonian',
        'macedonian' => 'Macedonian',
        'mg' => 'Malagasy',
        'mlg' => 'Malagasy',
        'Malagasy' => 'Malagasy',
        'malagasy' => 'Malagasy',
        'ms' => 'Malay',
        'may' => 'Malay',
        'msa' => 'Malay',
        'Malay' => 'Malay',
        'malay' => 'Malay',
        'ml' => 'Malayalam',
        'mal' => 'Malayalam',
        'Malayalam' => 'Malayalam',
        'malayalam' => 'Malayalam',
        'mt' => 'Maltese',
        'mlt' => 'Maltese',
        'Maltese' => 'Maltese',
        'maltese' => 'Maltese',
        'gv' => 'Manx',
        'glv' => 'Manx',
        'Manx' => 'Manx',
        'manx' => 'Manx',
        'mi' => 'Maori',
        'mao' => 'Maori',
        'mri' => 'Maori',
        'Maori' => 'Maori',
        'maori' => 'Maori',
        'mr' => 'Marathi',
        'mar' => 'Marathi',
        'Marathi' => 'Marathi',
        'marathi' => 'Marathi',
        'mh' => 'Marshallese',
        'mah' => 'Marshallese',
        'Marshallese' => 'Marshallese',
        'marshallese' => 'Marshallese',
        'mo' => 'Moldavian',
        'mol' => 'Moldavian',
        'Moldavian' => 'Moldavian',
        'moldavian' => 'Moldavian',
        'mn' => 'Mongolian',
        'mon' => 'Mongolian',
        'Mongolian' => 'Mongolian',
        'mongolian' => 'Mongolian',
        'na' => 'Nauru',
        'nau' => 'Nauru',
        'Nauru' => 'Nauru',
        'nauru' => 'Nauru',
        'nv' => 'Navaho',
        'nav' => 'Navaho',
        'Navaho' => 'Navaho',
        'navaho' => 'Navaho',
        'nd' => 'Ndebele, North',
        'nde' => 'Ndebele, North',
        'Ndebele, North' => 'Ndebele, North',
        'ndebele, north' => 'Ndebele, North',
        'nr' => 'Ndebele, South',
        'nbl' => 'Ndebele, South',
        'Ndebele, South' => 'Ndebele, South',
        'ndebele, south' => 'Ndebele, South',
        'ng' => 'Ndonga',
        'ndo' => 'Ndonga',
        'Ndonga' => 'Ndonga',
        'ndonga' => 'Ndonga',
        'ne' => 'Nepali',
        'nep' => 'Nepali',
        'Nepali' => 'Nepali',
        'nepali' => 'Nepali',
        'se' => 'Northern Sami',
        'sme' => 'Northern Sami',
        'Northern Sami' => 'Northern Sami',
        'northern sami' => 'Northern Sami',
        'no' => 'Norwegian',
        'nor' => 'Norwegian',
        'Norwegian' => 'Norwegian',
        'norwegian' => 'Norwegian',
        'nb' => 'Norwegian Bokmal',
        'nob' => 'Norwegian Bokmal',
        'Norwegian Bokmal' => 'Norwegian Bokmal',
        'norwegian bokmal' => 'Norwegian Bokmal',
        'nn' => 'Norwegian Nynorsk',
        'nno' => 'Norwegian Nynorsk',
        'Norwegian Nynorsk' => 'Norwegian Nynorsk',
        'norwegian nynorsk' => 'Norwegian Nynorsk',
        'ny' => 'Nyanja',
        'nya' => 'Nyanja',
        'Nyanja' => 'Nyanja',
        'nyanja' => 'Nyanja',
        'oc' => 'Occitan',
        'oci' => 'Occitan',
        'Occitan' => 'Occitan',
        'occitan' => 'Occitan',
        'or' => 'Oriya',
        'ori' => 'Oriya',
        'Oriya' => 'Oriya',
        'oriya' => 'Oriya',
        'om' => 'Oromo',
        'orm' => 'Oromo',
        'Oromo' => 'Oromo',
        'oromo' => 'Oromo',
        'os' => 'Ossetian',
        'oss' => 'Ossetian',
        'Ossetian' => 'Ossetian',
        'ossetian' => 'Ossetian',
        'pi' => 'Pali',
        'pli' => 'Pali',
        'Pali' => 'Pali',
        'pali' => 'Pali',
        'pa' => 'Panjabi',
        'pan' => 'Panjabi',
        'Panjabi' => 'Panjabi',
        'panjabi' => 'Panjabi',
        'fa' => 'Persian',
        'per' => 'Persian',
        'fas' => 'Persian',
        'Persian' => 'Persian',
        'persian' => 'Persian',
        'pl' => 'Polish',
        'pol' => 'Polish',
        'Polish' => 'Polish',
        'polish' => 'Polish',
        'pt' => 'Portuguese',
        'po' => 'Portuguese',
        'por' => 'Portuguese',
        'port' => 'Portuguese',
        'portugais' => 'Portuguese',
        'Portuguese' => 'Portuguese',
        'portuguese' => 'Portuguese',
        'ps' => 'Pushto',
        'pus' => 'Pushto',
        'Pushto' => 'Pushto',
        'pushto' => 'Pushto',
        'qu' => 'Quechua',
        'que' => 'Quechua',
        'Quechua' => 'Quechua',
        'quechua' => 'Quechua',
        'rm' => 'Raeto-Romance',
        'roh' => 'Raeto-Romance',
        'Raeto-Romance' => 'Raeto-Romance',
        'raeto-romance' => 'Raeto-Romance',
        'ro' => 'Romanian',
        'rum' => 'Romanian',
        'ron' => 'Romanian',
        'rou' => 'Romanian',
        'Romanian' => 'Romanian',
        'romanian' => 'Romanian',
        'rn' => 'Rundi',
        'run' => 'Rundi',
        'Rundi' => 'Rundi',
        'rundi' => 'Rundi',
        'ru' => 'Russian',
        'rus' => 'Russian',
        'Russian' => 'Russian',
        'russian' => 'Russian',
        'sm' => 'Samoan',
        'smo' => 'Samoan',
        'Samoan' => 'Samoan',
        'samoan' => 'Samoan',
        'sg' => 'Sango',
        'sag' => 'Sango',
        'Sango' => 'Sango',
        'sango' => 'Sango',
        'sa' => 'Sanskrit',
        'san' => 'Sanskrit',
        'Sanskrit' => 'Sanskrit',
        'sanskrit' => 'Sanskrit',
        'sc' => 'Sardinian',
        'srd' => 'Sardinian',
        'Sardinian' => 'Sardinian',
        'sardinian' => 'Sardinian',
        'sr' => 'Serbian',
        'scc' => 'Serbian',
        'srp' => 'Serbian',
        'srb' => 'Serbian',
        'Serbian' => 'Serbian',
        'serbian' => 'Serbian',
        'Serbo-Croatian' => 'Serbian',
        'sn' => 'Shona',
        'sna' => 'Shona',
        'Shona' => 'Shona',
        'shona' => 'Shona',
        'ii' => 'Sichuan Yi',
        'iii' => 'Sichuan Yi',
        'Sichuan Yi' => 'Sichuan Yi',
        'sichuan yi' => 'Sichuan Yi',
        'sd' => 'Sindhi',
        'snd' => 'Sindhi',
        'Sindhi' => 'Sindhi',
        'sindhi' => 'Sindhi',
        'si' => 'Sinhala',
        'sin' => 'Sinhala',
        'Sinhala' => 'Sinhala',
        'sinhala' => 'Sinhala',
        'sk' => 'Slovak',
        'slo' => 'Slovak',
        'slk' => 'Slovak',
        'Slovak' => 'Slovak',
        'slovak' => 'Slovak',
        'sl' => 'Slovenian',
        'slv' => 'Slovenian',
        'Slovenian' => 'Slovenian',
        'slovenian' => 'Slovenian',
        'so' => 'Somali',
        'som' => 'Somali',
        'Somali' => 'Somali',
        'somali' => 'Somali',
        'st' => 'Sotho, Southern',
        'sot' => 'Sotho, Southern',
        'Sotho, Southern' => 'Sotho, Southern',
        'sotho, southern' => 'Sotho, Southern',
        'es' => 'Spanish',
        'spa' => 'Spanish',
        'Spanish' => 'Spanish',
        'spanish' => 'Spanish',
        'Castellano' => 'Spanish',
        'su' => 'Sundanese',
        'sun' => 'Sundanese',
        'Sundanese' => 'Sundanese',
        'sundanese' => 'Sundanese',
        'sw' => 'Swahili',
        'swa' => 'Swahili',
        'Swahili' => 'Swahili',
        'swahili' => 'Swahili',
        'ss' => 'Swati',
        'ssw' => 'Swati',
        'Swati' => 'Swati',
        'swati' => 'Swati',
        'sv' => 'Swedish',
        'swe' => 'Swedish',
        'Swedish' => 'Swedish',
        'swedish' => 'Swedish',
        'tl' => 'Tagalog',
        'tgl' => 'Tagalog',
        'Tagalog' => 'Tagalog',
        'tagalog' => 'Tagalog',
        'ty' => 'Tahitian',
        'tah' => 'Tahitian',
        'Tahitian' => 'Tahitian',
        'tahitian' => 'Tahitian',
        'tg' => 'Tajik',
        'tgk' => 'Tajik',
        'Tajik' => 'Tajik',
        'tajik' => 'Tajik',
        'ta' => 'Tamil',
        'tam' => 'Tamil',
        'Tamil' => 'Tamil',
        'tamil' => 'Tamil',
        'tt' => 'Tatar',
        'tat' => 'Tatar',
        'Tatar' => 'Tatar',
        'tatar' => 'Tatar',
        'te' => 'Telugu',
        'tel' => 'Telugu',
        'Telugu' => 'Telugu',
        'telugu' => 'Telugu',
        'th' => 'Thai',
        'tha' => 'Thai',
        'Thai' => 'Thai',
        'thai' => 'Thai',
        'bo' => 'Tibetan',
        'tib' => 'Tibetan',
        'bod' => 'Tibetan',
        'Tibetan' => 'Tibetan',
        'tibetan' => 'Tibetan',
        'ti' => 'Tigrinya',
        'tir' => 'Tigrinya',
        'Tigrinya' => 'Tigrinya',
        'tigrinya' => 'Tigrinya',
        'to' => 'Tonga',
        'ton' => 'Tonga',
        'Tonga' => 'Tonga',
        'tonga' => 'Tonga',
        'ts' => 'Tsonga',
        'tso' => 'Tsonga',
        'Tsonga' => 'Tsonga',
        'tsonga' => 'Tsonga',
        'tn' => 'Tswana',
        'tsn' => 'Tswana',
        'Tswana' => 'Tswana',
        'tswana' => 'Tswana',
        'tr' => 'Turkish',
        'tur' => 'Turkish',
        'Turkish' => 'Turkish',
        'turkish' => 'Turkish',
        'tk' => 'Turkmen',
        'tuk' => 'Turkmen',
        'Turkmen' => 'Turkmen',
        'turkmen' => 'Turkmen',
        'tw' => 'Twi',
        'twi' => 'Twi',
        'Twi' => 'Twi',
        'ug' => 'Uighur',
        'uig' => 'Uighur',
        'Uighur' => 'Uighur',
        'uighur' => 'Uighur',
        'uk' => 'Ukrainian',
        'ukr' => 'Ukrainian',
        'Ukrainian' => 'Ukrainian',
        'ukrainian' => 'Ukrainian',
        'ukrainien' => 'Ukrainian',
        'ur' => 'Urdu',
        'urd' => 'Urdu',
        'Urdu' => 'Urdu',
        'urdu' => 'Urdu',
        'uz' => 'Uzbek',
        'uzb' => 'Uzbek',
        'Uzbek' => 'Uzbek',
        'uzbek' => 'Uzbek',
        'vi' => 'Vietnamese',
        'vie' => 'Vietnamese',
        'Vietnamese' => 'Vietnamese',
        'vietnamese' => 'Vietnamese',
        'vo' => 'Volapuk',
        'vol' => 'Volapuk',
        'Volapuk' => 'Volapuk',
        'volapuk' => 'Volapuk',
        'wa' => 'Walloon',
        'wln' => 'Walloon',
        'Walloon' => 'Walloon',
        'walloon' => 'Walloon',
        'cy' => 'Welsh',
        'wel' => 'Welsh',
        'cym' => 'Welsh',
        'Welsh' => 'Welsh',
        'welsh' => 'Welsh',
        'fy' => 'Western Frisian',
        'fry' => 'Western Frisian',
        'Western Frisian' => 'Western Frisian',
        'western frisian' => 'Western Frisian',
        'wo' => 'Wolof',
        'wol' => 'Wolof',
        'Wolof' => 'Wolof',
        'wolof' => 'Wolof',
        'xh' => 'Xhosa',
        'xho' => 'Xhosa',
        'Xhosa' => 'Xhosa',
        'xhosa' => 'Xhosa',
        'yi' => 'Yiddish',
        'yid' => 'Yiddish',
        'Yiddish' => 'Yiddish',
        'yiddish' => 'Yiddish',
        'yo' => 'Yoruba',
        'yor' => 'Yoruba',
        'Yoruba' => 'Yoruba',
        'yoruba' => 'Yoruba',
        'za' => 'Zhuang',
        'zha' => 'Zhuang',
        'Zhuang' => 'Zhuang',
        'zhuang' => 'Zhuang',
        'zu' => 'Zulu',
        'zul' => 'Zulu',
        'Zulu' => 'Zulu',
        'zulu' => 'Zulu',
    );
    const ALPHABET_A_Z_EXTENDED = 'a_z_extended';
    const ALPHABET_A_Z = 'a_z';
    const ALPHABET_GREEK = 'greek';
    const ALPHABETS = [
        self::ALPHABET_A_Z_EXTENDED => [

            '#' => '#',
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'e' => 'E',
            'f' => 'F',
            'g' => 'G',
            'h' => 'H',
            'i' => 'I',
            'j' => 'J',
            'k' => 'K',
            'l' => 'L',
            'm' => 'M',
            'n' => 'N',
            'o' => 'O',
            'p' => 'P',
            'q' => 'Q',
            'r' => 'R',
            's' => 'S',
            't' => 'T',
            'u' => 'U',
            'v' => 'V',
            'w' => 'W',
            'x' => 'X',
            'y' => 'Y',
            'z' => 'Z',
        ],
        self::ALPHABET_A_Z => [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'e' => 'E',
            'f' => 'F',
            'g' => 'G',
            'h' => 'H',
            'i' => 'I',
            'j' => 'J',
            'k' => 'K',
            'l' => 'L',
            'm' => 'M',
            'n' => 'N',
            'o' => 'O',
            'p' => 'P',
            'q' => 'Q',
            'r' => 'R',
            's' => 'S',
            't' => 'T',
            'u' => 'U',
            'v' => 'V',
            'w' => 'W',
            'x' => 'X',
            'y' => 'Y',
            'z' => 'Z',
        ],

        self::ALPHABET_GREEK => ['α' => 'Α', 'β' => 'Β', 'γ' => 'Γ', 'δ' => 'Δ', 'ε' => 'Ε', 'ζ' => 'Ζ', 'η' => 'Η', 'θ' => 'Θ', 'ι' => 'Ι', 'κ' => 'Κ', 'λ' => 'Λ', 'μ' => 'Μ', 'ν' => 'Ν', 'ξ' => 'Ξ', 'ο' => 'Ο', 'π' => 'Π', 'ρ' => 'Ρ', 'σ' => 'Σ', 'τ' => 'Τ', 'υ' => 'Υ', 'φ' => 'Φ', 'χ' => 'Χ', 'ψ' => 'Ψ', 'ω' => 'Ω']

    ];
    public const LOKALISIERUNG_POST_TYPE = "lokalisierung";
    public const LOKALISIERUNGSGRUPPE_TABELLEN = 'Tabellen';
    public const LOKALISIERUNGSGRUPPE_GLOBAL = 'Global';
    public const LOKALISIERUNGSGRUPPE_CHOICES = [
        Oes_General_Config::LOKALISIERUNGSGRUPPE_GLOBAL => 'Global',
        Oes_General_Config::LOKALISIERUNGSGRUPPE_TABELLEN => 'Tabellen',
    ];
    const WEBSITE_LANGUAGE_CODE2_ENGLISH = 'en';
    const WEBSITE_LANGUAGE_CODE2_GERMAN = 'de';
    const WEBSITE_LANGUAGE_CODE2_GREEK = 'el';
    const WEBSITE_LANGUAGE_CODE2_FRENCH = 'fr';
    const WEBSITE_LANGUAGE_CODE2_SPANISH = 'es';
    const WEBSITE_LANGUAGE_CODE2_ITALIAN = 'it';
    const FILTER_X_TYPE = 'x_type';
    const FILTER_X_IS_IMAGE = 'x_is_image';
    const FILTER_X_VISIBLE = 'x_visible';
    const FILTER_X_TITLE_SORT_CLASS = Oes_General_Config::x_title_sort_class_s;
    const TAG_X_TITLE_SORT_CLASS = 'x_title_sort_class';
    const TAG_X_TITLE_LIST_SORT_CLASS = 'x_title_list_sort_class';
    const FILTER_VALUE_X_TITLE_SORT_CLASS = '{!tag=' . self::TAG_X_TITLE_SORT_CLASS . '}' . self::x_title_sort_class_s . ':(XXX)';
    const FILTER_VALUE_X_TITLE_LIST_SORT_CLASS = '{!tag=' . self::TAG_X_TITLE_SORT_CLASS . '}' . self::x_title_list_sort_class_s . ':(XXX)';
    const x_title_sort_class_s = 'x_title_sort_class_s';
    const x_title_list_sort_class_s = 'x_title_list_sort_class_s';
    const x_type_s = 'x_type_s';
    static $CONCAT_LAST_SEPARATOR = ' und ';
    static $MAIN_APP_ID = null;
    static $IMAGE_SIZE_ID_THUMBNAIL = 'thumbnail';
    static $IMAGE_SIZE_ID_MEDIUM = 'medium';
    static $IMAGE_SIZE_ID_LARGE = 'large';
    static $IMAGE_SIZE_ID_2K = '2048x2048';
    /**
     * centered and cropped to 64x64
     * @var string
     */
    static $IMAGE_SIZE_SQUARE64_CC = 'square64_CC';
    /**
     * centered and cropped to 128x128
     * @var string
     */
    static $IMAGE_SIZE_SQUARE128_CC = 'square128_CC';
    /**
     * top/left oriented and cropped to 64x64
     * @var string
     */
    static $IMAGE_SIZE_SQUARE64_TL = 'square64_TL';
    /**
     * top/left oriented and cropped to 128x128
     * @var string
     */
    static $IMAGE_SIZE_SQUARE128_TL = 'square128_TL';
    /**
     * @var Oes_Project_Config_Base
     */
    static $PROJECT_CONFIG = false;
    static $BOOT_IMPORTER = false;
    static $DoNotLoadDtm = false;
    static $postTypeConfigFiles = [];
    static $userName;
    static $userProfileId;
    static $userId;
    /**
     * @var Oes_Zotero
     */
    static $zoteroLibrary = false;
    static $request_flags = [];
    static $EO_LINK_FIELDS = false;
    static $EO_BIBLIOGRAPHY_FIELDS = false;
    static $EO_IMAGE_FIELDS = false;
    static $LIST_OF_METADATA_ATTRIBUTES = [
        'eo_images',
        'eo_portrayal',
        'eo_links',
        'eo_locations',
        'user_images',
        'user_bibliography',
        'user_links',
        'article_section_editor',
        'article_managing_editor',
        'article_translator',
        'u_article_mentioned_author',
    ];
    static $LIST_OF_METADATA_ATTRIBUTES_MAPPING_TO_CLASS = [
        'eo_images',
        'eo_portrayal',
        'eo_links',
        'eo_locations',
        'user_images',
        'user_bibliography',
        'user_links',
        'article_section_editor',
        'article_managing_editor',
        'article_translator',
        'u_article_mentioned_author',
    ];
    static $CONTRIBUTOR_ROLES_1418 = [];//@m. part of specific project, needs implementation for each project
    static $CONTRIBUTOR_ADDITIONAL_TERMS = [
        self::CONTRIBUTOR_TERM_IS_WORKING_REFEREE => 'Working as Referee',
        self::CONTRIBUTOR_TERM_IS_WORKING_REFEREE_AND_ME => 'Working as Referee (Managing Editor)',
        self::CONTRIBUTOR_TERM_IS_NOT_WORKING_REFEREE => 'Is Not Working as Referee',
        self::CONTRIBUTOR_TERM_IS_NOT_WORKING_REFEREE_AND_ME => 'Is Not Working as Referee (Managing Editor)',
    ];
    static $VISIBLE_CONTRIBUTOR_ROLES_1418 = [];//@m. part of specific project, needs implementation for each project
    static $TAX_REGIONS_ALL = "all_regions";
    static $TAX_REGIONS_PUB = "regions";
    static $TAX_THEMES = "all_themes";
    static $TAX_THEMES_PUB = "themes";
    static $TAX_TOPICS = "all_topics";
    static $TAX_TOPICS_PUB = "topics";
    static $OES_SPECIAL_CATS = "oes_special_cats";
    static $X_IS_PUBLISHED = "x_is_published";
    static $X_IS_VISIBLE = "x_is_visible";
    static $X_IS_HIDDEN = "x_is_hidden";
    static $x_special_categories = "x_special_categories";
    static $x_title_list = "x_title_list";
    static $x_title_list_sort = "x_title_list_sort";
    static $x_title_list_sort_class = "x_title_list_sort_class";
    static $X_IS_QUERYABLE = "x_is_queryable";
    static $AT_STATUS = "status";
    static $X_DEFAULT_FIELDS = [

        'x_uid' => [
            'type' => 'text'
        ],

        /*
         *
         */

        'x_special_categories' => [
            'type' => 'taxonomy',
            'taxonomy' => 'oes_special_cats',
            'save_terms' => 1, 'load_terms' => 1
        ],

        'x_feature_image' => ['type' => 'image', 'no_remote' => 1],

        'x_title' => ['type' => 'text'],

        'x_title_sort' => ['type' => 'text'],

//        'x_title_sort_class' => ['type' => 'text'],

        'x_title_list' => ['type' => 'text'],

        'x_title_list_sort' => ['type' => 'text'],

//        'x_title_list_sort_class' => ['type' => 'text'],

        'x_is_hidden' => [
            'type' => 'true_false'
        ],

        'x_is_in_trash' => [
            'type' => 'true_false'
        ],

        'x_archived_data' => [
            'type' => 'textarea',
        ],

        'x_archived_data_date' => [
            'type' => 'date_time_picker',
        ],

        'x_archived_data_user' => [
            'type' => 'user'
        ],

        'x_is_queryable' => [
            'type' => 'true_false'
        ],

        'x_is_listed' => [
            'type' => 'true_false'
        ],

        'x_is_indexable' => [
            'type' => 'true_false'
        ],

        'x_is_visible' => [
            'type' => 'true_false'
        ],

        'x_rescan_queryability' => [
            'type' => 'text',
            'dtm_transform_source' => [
                Oes_General_Config::DTM_QUERYABLE,
            ]
        ],

        'x_is_published' => [
            'type' => 'true_false'
        ],

        'x_is_recommendation' => [
            'type' => 'true_false',
            'label' => 'Recommendation',
            'class' => 'oes-hidden-field'
        ],

        'x_is_not_approved' => [
            'type' => 'true_false',
            'label' => 'Is not approved',
            'class' => 'oes-hidden-field'
        ],

        'x_is_approved' => [
            'type' => 'true_false',
            'label' => 'Is approved',
            'class' => 'oes-hidden-field'
        ],

        'x_needs_approval' => [
            'type' => 'true_false',
            'label' => 'Needs approved',
            'class' => 'oes-hidden-field'
        ],

        'x_approved_by' => [
            'type' => 'user'
        ],

        'x_approved_by_name' => [
            'type' => 'text'
        ],

        'x_approved_by_date' => [
            'type' => 'date_time_picker'
        ],

        'x_is_user_content' => [
            'type' => 'true_false', 'label' => 'User Content'
        ],

        'x_remote_ref' => [
            'type' => 'text', 'label' => 'Remote Ref'
        ],

        'x_created' => ['type' => 'date_time_picker'],

        'x_imported' => ['type' => 'date_time_picker'],

        'x_last_updated' => ['type' => 'date_time_picker'],

        'x_last_updated_by_user' => ['type' => 'user'],

    ];
    static $GENERAL_DEFAULT_FIELDS = [

        'is_recommendation' => [
            'type' => 'true_false',
            'label' => 'Recommendation',
            'class' => 'oes-hidden-field'
        ],

        'is_not_approved' => [
            'type' => 'true_false',
            'label' => 'Is not approved',
            'class' => 'oes-hidden-field'
        ],

        '_is_saved' => ['type' => 'true_false'],

        '_is_visible' => ['type' => 'true_false', 'class' => 'oes-hidden-field'],

        '_is_visibleandpublished' => ['type' => 'true_false', 'class' => 'oes-hidden-field'],

        '_is_queryable' => ['type' => 'true_false', 'class' => 'oes-hidden-field'],

        '_is_queryableandpublished' => ['type' => 'true_false', 'class' => 'oes-hidden-field'],

        'title_sort_comp' => ['type' => 'text', 'class' => 'oes-hidden-field'],

        'title_char_class_comp' => ['type' => 'text', 'class' => 'oes-hidden-field'],

        '_created' => ['type' => 'date_time_picker'],

        'linked_object' => ['type' => 'post_object'],

        'is_user_content' => ['type' => 'true_false', 'label' => 'User Content'],

//    'uploaded_by' => [
//        'type' => 'user', 'label' => 'Uploaded by (User)', 'class' => 'oes-hidden-field'
//    ],
//
//    'contributor_uploaded_image' => [
//        'type' => 'post_object',
//        'post_type' => [
//            'eo_contributor'
//        ],
//        'label' => 'Uploaded by (Contributor)', 'class' => 'oes-hidden-field'
//    ],
//
//    'uploaded_by_name' => [
//        'type' => 'text', 'label' => 'Uploaded by (Name)', 'class' => 'oes-hidden-field'
//    ],
//
//    'uploaded_by_email' => [
//        'type' => 'text', 'label' => 'Uploaded by (Email)', 'class' => 'oes-hidden-field'
//    ],
//
//    'uploaded_by_date' => [
//        'type' => 'date_time_picker', 'label' => 'Uploaded by (Date)', 'class' => 'oes-hidden-field'
//    ],

        'gen_attachment_type' => [
            'type' => 'text',
        ],


    ];
    static $dateRenderFormat = 'd.m.Y';

    /**
     * @return bool
     */
    public static function isDtmDisabled()
    {
        global $DisableDTM;
        return $DisableDTM;
    }

    /**
     * @param bool $DoNotLoadDtm
     */
    public static function disableDtm(): void
    {
        global $DisableDTM;
        $DisableDTM = true;
    }

    static function renderDate($value, $format = false)
    {
        $renderFormat = copyval($format, self::getDateRenderFormat());
        return date($renderFormat, $value);
    }

    /**
     * @return string
     */
    static public function getDateRenderFormat(): string
    {
        return self::$dateRenderFormat;
    }

    /**
     * @param string $dateRenderFormat
     */
    public static function setDateRenderFormat(string $dateRenderFormat): void
    {
        self::$dateRenderFormat = $dateRenderFormat;
    }

    /**
     * @return mixed
     */
    public static function getUserName()
    {
        return self::$userName;
    }

    /**
     * @param mixed $userName
     */
    public static function setUserName($userName): void
    {
        self::$userName = $userName;
    }

    /**
     * @return mixed
     */
    public static function getUserProfileId()
    {
        return self::$userProfileId;
    }

    /**
     * @param mixed $userProfileId
     */
    public static function setUserProfileId($userProfileId): void
    {
        self::$userProfileId = $userProfileId;
    }

    /**
     * @return mixed
     */
    public static function getUserId()
    {
        return self::$userId;
    }

    /**
     * @param mixed $userId
     */
    public static function setUserId($userId): void
    {
        self::$userId = $userId;
    }

    /**
     * @return Oes_Zotero
     */
    public static function getZoteroLibrary(): Oes_Zotero
    {
        return self::$zoteroLibrary;
    }

    /**
     * @param Oes_Zotero $zoteroLibrary
     */
    public static function setZoteroLibrary(Oes_Zotero $zoteroLibrary): void
    {
        self::$zoteroLibrary = $zoteroLibrary;
    }

    static function formatDate($time)
    {
        return date("d/m/Y", $time);
    }

    static function printFormattedFieldValues($obj, $fields, $printCallback)
    {

        if (empty($fields)) {
            return;
        }

        foreach ($fields as $fieldName => $fieldI) {

            $val = $obj->{$fieldName};

            $force = $fieldI['force'];

            if (empty($val) && !$force) {
                continue;
            }

            $transform = $fieldI['transform'];

            if ($transform) {
                $val = call_user_func($transform, $val, $obj, $fieldName);
            }

            $label = $fieldI['label'];

            call_user_func($printCallback, $label, $val);

        }

    }

    static function set_request_flag($flag, $value = 1)
    {
        self::$request_flags[$flag] = $value;
    }

    static function unset_request_flag($flag)
    {
        unset(self::$request_flags[$flag]);
    }

    static function isset_request_flag($flag)
    {
        return array_key_exists($flag, self::$request_flags);
    }

    static function getListOfEoRecommendBibliographyFields()
    {
        return OesMultistepModalFormBuilder::buildStandardForm("bibliography", Oes_General_Config::FG_EO_RECOMMEND_BIBLIOGRAPHY, "user_");

    }

    static function getListOfEoImageUploadFields()
    {
        return OesMultistepModalFormBuilder::buildStandardForm("imageupload", Oes_General_Config::FG_EO_UPLOAD_IMAGE, "user_");

    }

    static function getListOfEoBibliographyFields()
    {
        $msFormBuilder = OesMultistepModalFormBuilder::newBuilder("bibliography");

        $msForm = $msFormBuilder->getMultistepForm(Oes_General_Config::FG_EO_BIBLIOGRAPHY);

        $msForm->setHasChooseTypeTab(false);
        $msForm->setHasEnterFieldsTab(false);
        $msForm->setHasConfirmDetailsTab(false);

        $list = $msForm->build();

        return $list;

    }

    static function getListOfEoImageFields()
    {
        $msFormBuilder = OesMultistepModalFormBuilder::newBuilder("image_upload");

        $msForm = $msFormBuilder->getMultistepForm(Oes_General_Config::FG_EO_IMAGE);

        $msForm->setHasChooseTypeTab(false);
        $msForm->setHasEnterFieldsTab(false);
        $msForm->setHasConfirmDetailsTab(false);

        $list = $msForm->build();

        self::$EO_IMAGE_FIELDS = array_keys($list);

        return $list;

    }

    /**
     * @param \dtm_1418_image_attachment $post
     * @return string
     */
    static function buildEoImageCitation($post)
    {


        $citation_override = $post->citation_override;

        if ($citation_override) {
            $citation_html = $post->citation_html;
            return $citation_html;
        }

        $content = [];

        if ($post->image_caption) {
            $content[] = "<em class='oes-image-caption'>" . $post->image_caption . "</em>";
            $content[] = "\n";
        }

        $content[] = "<span class='oes-image-description'>";

        if ($post->image_description_opt) {
            $content[] = $post->image_description_opt . ".";
            $content[] = "\n";
        }

        if ($post->is_creator_known_opt) {
            if ($post->is_creator_a_person_opt) {
                if ($post->creator_lastname) {
                    $content[] = $post->creator_lastname;
                }
                if ($post->creator_firstname) {
                    $content[] = ", ";
                    $content[] = $post->creator_firstname;
                }
            } else {
                $content[] = $post->creator_name;
            }
        } else {
            $content[] = "Unknown creator";
        }

        if ($post->image_original_caption_opt) {
            $content[] = ": " . $post->image_original_caption_opt;
            $content[] = ", ";
        } else {
            $content[] = ", ";
        }

        if ($post->image_type_of_material_opt__term) {
            $content[] = $post->image_type_of_material_opt__term;
            $content[] = ", ";
        }

        if ($post->image_place_of_publication_opt) {
            $content[] = $post->image_place_of_publication_opt;
            $content[] = ", ";
        }

        if ($post->image_date_of_publication_opt) {
            $content[] = $post->image_date_of_publication_opt;
            $content[] = ", ";
        }

        array_pop($content);

        $content[] = "; ";

        if ($post->image_content_provider_eo_opt__term) {
            $content[] = "source: " . $post->image_content_provider_eo_opt__term;
            $content[] = ", ";
        }

        if ($post->image_identifier_opt) {
            $content[] = $post->image_identifier_opt;
            $content[] = ", ";
        }

        if ($post->image_source_url_opt) {
//            $content[] = '<a rel="nofollow" target="_blank" class="oes-image-citation-url" href="' . $post->image_source_url_opt . '">';
            $content[] = $post->image_source_url_opt;
            $content[] = "</a>";
            $content[] = ", ";
        }

        array_pop($content);
        $content[] = ".\n";

        $content[] = "</span>";

        $content[] = "<span class='oes-image-license-permission'>";

        if (!$post->permission_needed) {

            /**
             * @var \dtm_1418_license_base $license
             */
            $license = $post->license__obj;

            $content[] = $license->license_citation_text;

        } else {

            /**
             * @var \dtm_1418_permission_base $permission
             */
            $permission = $post->pub_permissions__obj;

            $content[] = $permission->citation_description;

        }
        $content[] = "</span>";


        return implode("", $content);

    }

    /**
     * @param \dtm_1418_bibliography_base $bib
     * @return bool|null|string
     */
    static function buildEoBibliographyCitation($bib)
    {


//        $citation_override = $bib->citation_override;
//
//        $citation_html = $bib->citation_html;
//
//        if ($citation_override) {
//            return $citation_html;
//        }

        $zotType = $bib->zot_itemType;

        $mappings = self::getZoteroMappingByType();

        foreach ($mappings as $mapping) {

            $sourceType = $mapping['source_type'];

            if (is_array($sourceType) &&
                in_array($zotType, $sourceType)) {
                $citationFunc = $mapping['citation'];
                return $citationFunc($bib);
            }

        }

        return self::buildEoBibliographyCitationBook($bib);

//        $zotType = $bib->zot_itemType;
//
//        if ($zotType == 'thesis' || $zotType == 'book') {
//            return self::buildEoBibliographyCitationMonograph($bib);
//        } else if ($type == 'Edited Volume') {
//            return self::buildEoBibliographyCitationEditedVolume($bib);
//        } else if ($type == 'Journal Article') {
//            return self::buildEoBibliographyCitationJournalArticle($bib);
//        } else if ($type == 'Newspaper Article') {
//            return self::buildEoBibliographyCitationNewspaperArticle($bib);
//        } else if ($type == 'Book Section') {
//            return self::buildEoBibliographyCitationBookSection($bib);
//        } else if ($type == 'Manuscript') {
//            return self::buildEoBibliographyCitationManuscript($bib);
//        }

        return "missing citation builder for type {$zotType}";

    }

    static function getZoteroMappingByType($type = '')
    {

        static $mappings;

        if (!isset($mappings)) {
            include(__DIR__ . "/../config/bibliography.zotero-mapping.php");
            $mappings = $zotero;
        }

        if (empty($type)) {
            return $mappings;
        }

        $mapping = $mappings[$type];

        if (empty($mapping)) {
            throw new Exception("zotero mapping entry for type {$type} not found");
        }

        return $mapping;

    }

    /**
     * @param \dtm_1418_bibliography_base $bib
     * @return string
     */
    static function buildEoBibliographyCitationBook($bib)
    {

        $content = [];

        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

        $content = [];

        $names = [];
        $names = self::buildZoteroAuthorsLine($bib->zot_creators, $names);
        $names = self::buildZoteroEditorsLine($bib->zot_creators, $names);

        if (!empty($names)) {
            $content[] = implode(", ", $names);
            $content[] = ": ";
        }

        $content[] = "<b>" . $bib->zot_title . "</b>";

        if ($bib->zot_series) {
            $series = $bib->zot_series;
            if ($bib->zot_seriesNumber) {
                $series .= " " . $bib->zot_seriesNumber;
            }
            $content[] = ", ";
            $content[] = "($series)";
        }


        if ($bib->zot_volume) {
            $content[] = ", vol. ";
            $content[] = $bib->zot_volume;
        }

        if ($bib->zot_numberOfVolumes) {
            $content[] = ", ";
            $content[] = $bib->zot_numberOfVolumes;
            $content[] = " vols.";
        }

        if ($bib->zot_edition) {
            $content[] = ", ";
            $content[] = $bib->zot_edition;
            $content[] = " ed.";
        }

        if ($bib->zot_place) {
            $content[] = ", ";
            $content[] = $bib->zot_place;
        }

        if ($bib->zot_date) {
            $content[] = ", ";
            $content[] = $bib->zot_date;
        }

        if ($bib->zot_publisher) {
            $content[] = ": ";
            $content[] = $bib->zot_publisher;
        }

        return self::concatCitationParts($content);

    }

    static function buildZoteroAuthorsLine($creators, $content = false)
    {

        $authors = self::evalCreators($creators, "author");

        return self::buildAuthorLine($authors, $content);

    }

    static function evalCreators($creators, $type = false, $content = null)
    {

        if (empty($creators)) {
            return [];
        }

        $res = [];

        foreach ($creators as $creator) {
            $creatorType = $creator['creatorType'];
            if ($type && $creatorType != $type) {
                continue;
            }
            $name = $creator['name'];
            $it['single_name'] = !empty($name);
            $it['name'] = $creator['name'];
            $it['last_name'] = $creator['lastName'];
            $it['first_name'] = $creator['firstName'];
            $res[] = $it;
        }

        return $res;

    }

    static function buildAuthorLine($authors, $content = null)
    {

        $authornames = [];

        if (empty($authors)) {
            if (is_array($content)) {
                return $content;
            } else {
                return "";
            }
        }

        foreach ($authors as $it) {
            foreach ($it as $k => $v) {
                $$k = $v;
            }
            if ($single_name) {
                $authornames[] = $name;
            } else {
                $authornames[] = "$last_name, $first_name";
            }
        }

        $line = "";

        if (count($authornames) == 1) {
            $line = implode(" / ", $authornames);
        } else if (count($authornames) <= 3) {
            $line = implode(" / ", $authornames);
        } else if (!empty($authornames)) {
            $line = $authornames[0] . " et al.";
        }

        if (is_array($content)) {
            if ($line) {
                $content[] = $line;
            }
            return $content;
        } else {
            return $line;
        }

    }

    static function buildZoteroEditorsLine($creators, $content = null)
    {

        $authors = self::evalCreators($creators, "editor");

        return self::buildEditorsLine($authors, $content);

    }

    static function buildEditorsLine($authors, $content = null)
    {

        $authornames = [];

        if (empty($authors)) {
            if (is_array($content)) {
                return $content;
            } else {
                return "";
            }
        }

        foreach ($authors as $it) {
            foreach ($it as $k => $v) {
                $$k = $v;
            }
            if ($single_name) {
                $authornames[] = $name;
            } else {
                $authornames[] = "$last_name, $first_name";
            }
        }

        $line = "";

        if (count($authornames) == 1) {
            $line = implode(" / ", $authornames) . " (ed.)";
        } else if (count($authornames) <= 3) {
            $line = implode(" / ", $authornames) . " (eds.)";;
        } else if (!empty($authornames)) {
            $line = $authornames[0] . " et al. (eds.)";
        }

        if (is_array($content)) {
            if ($line) {
                $content[] = $line;
            }
            return $content;
        } else {
            return $line;
        }

    }

    static function concatCitationParts($parts)
    {
        $str = trim(implode("", $parts));
        if (!endswith($str, ".")) {
            $str .= ".";
        }
        return $str;
    }

    /**
     * @param \dtm_1418_link_base $post
     * @return bool|null|string
     */
    static function buildEoExternalLinkCitation($post)
    {

        $type = $post->type;

        $citation_override = $post->citation_override;
        $citation_html = $post->citation_html;
        if ($citation_override) {
            return $citation_html;
        }

        if ($type == 'Book') {
            return self::buildEoExternalLinkCitationBook($post);
        } else if ($type == 'Article') {
            return self::buildEoExternalLinkCitationArticle($post);
        } else if ($type == 'Book Section') {
            return self::buildEoExternalLinkCitationBookSection($post);
        } else if ($type == 'Primary Source') {
            return self::buildEoExternalLinkCitationPrimarySource($post);
        } else if ($type == 'Database') {
            return self::buildEoExternalLinkCitationDatabase($post);
        } else if ($type == 'Institutional Website') {
            return self::buildEoExternalLinkCitationInstitutionalWebsite($post);
        } else if ($type == 'Online Exhibition') {
            return self::buildEoExternalLinkCitationOnlineExhibition($post);
        } else if ($type == 'Map') {
            return self::buildEoExternalLinkCitationMap($post);
        } else if ($type == 'Audio') {
            return self::buildEoExternalLinkCitationAudio($post);
        } else if ($type == 'Video') {
            return self::buildEoExternalLinkCitationVideo($post);
        } else if ($type == 'Image') {
            return self::buildEoExternalLinkCitationVideo($post);
        }

        return self::buildEoExternalLinkCitationBook($post);

    }

    /**
     * @param \dtm_1418_link_base $li
     * @return string
     */
    static function buildEoExternalLinkCitationBook($li)
    {

        $authornames = [];

        foreach ($li->authors_opt__array as $it) {
            foreach ($it as $k => $v) {
                $$k = $v;
            }
            if ($single_name) {
                $authornames[] = $name;
            } else {
                $authornames[] = "$last_name, $first_name";
            }
        }

        $content[] = implode(" / ", $authornames);

        if ($li->language_opt) {
            $content[] = " ";
            $content[] = "(in " . implode(", ", x_as_array($li->language_opt)) . ")";
        }

        $content[] = ": ";

        $content[] = $li->generic_title;

        if ($li->volume_opt) {
            $content[] = ", ";
            $content[] = "vol. $li->volume_opt";
        }

        if ($li->edition_opt) {
            $content[] = ", ";
            $content[] = "ed. $li->edition_opt";
        }

        if ($li->places_of_publication_opt) {
            $content[] = ", ";
            $content[] = $li->places_of_publication_opt;
        }

        if ($li->year_of_publication_opt) {
            $content[] = ", ";
            $content[] = $li->year_of_publication_opt;
        }

        if ($li->content_provider_opt) {
            $content[] = " ";
            $content[] = "(" . $li->content_provider_opt . ")";
        }

        if ($li->type) {
            $content[] = " ";
            $content[] = "(" . $li->type . ")";
        }

        return implode("", $content);

    }

    /**
     * @param \dtm_1418_link_base $li
     * @return string
     */
    static function buildEoExternalLinkCitationArticle($li)
    {


        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */


        $names = [];

        foreach ($li->authors_opt__array as $it) {
            if ($it['single_name']) {
                $names[] = $it['name'];
            } else {
                $names[] = $it['last_name'] . ", " . $it['first_name'];
            }
        }

        if (!empty($names)) {
            $content[] = implode(" / ", $names);
        }

        if (self::doIncludeLanguageInExtLinkCitation($li->language_opt)) {
            $content[] = " (in " . implode(", ", x_as_array($li->language_opt)) . ")";
        }

        if (!empty($content)) {
            $content[] = ": ";
        }

        $content[] = $li->article_title_opt;

        $content[] = ", in: ";


        $names_composite = [];

        $names = [];

        foreach ($li->authors_opt__array as $it) {
            if ($it['single_name']) {
                $names[] = $it['name'];
            } else {
                $names[] = $it['last_name'] . ", " . $it['first_name'];
            }
        }

        if (!empty($names)) {
            $composite[] = implode(" / ", $names);
        }

        $names = [];

        foreach ($li->editors_opt__array as $it) {
            if ($it['single_name']) {
                $names[] = $it['name'] . " (ed.)";
            } else {
                $names[] = $it['last_name'] . ", " . $it['first_name'] . " (ed.)";
            }
        }

        if (!empty($names)) {
            $composite[] = implode(" / ", $names);
        }

        if (!empty($composite)) {
            $content[] = implode(", ", $composite);
            $content[] = ": ";
        }

        $composite = [];

        if ($li->book_title_opt) {
            $composite[] = $li->book_title_opt;
        }

        if ($li->volume_opt) {
            $composite[] = "(vol. $li->volume_opt)";
        }

        if ($li->edition_opt) {
            $composite[] = "(ed. $li->edition_opt)";
        }

        if ($li->places_of_publication_opt) {
            $composite[] = $li->places_of_publication_opt;
        }

//        if (!empty($composite)) {
//            $content[] = implode(", ", $composite);
//            $content[] = ", ";
//        }

//        $composite = [];

        {

            $journal = [];

            if ($li->journal_title_opt) {
                $journal[] = $li->journal_title_opt;
            }

            {

                $parts = [];

                if ($li->journal_volume_opt) {
                    $parts[] = $li->journal_volume_opt;
                }

                if ($li->journal_issue_opt) {
                    $parts[] = $li->journal_issue_opt;
                }

                if (!empty($parts)) {
                    $journal[] = implode("/", $parts);
                }

            }

            if (!empty($journal)) {
                $composite[] = implode(" ", $journal);
            }

        }

        if ($li->year_of_publication_opt) {
            $composite[] = $li->year_of_publication_opt;
        }

        if ($li->pages_opt) {
            $composite[] = self::buildPagesLine($li->pages_opt, "");
        }

        if (!empty($composite)) {
            $content[] = implode(", ", $composite);
        }

        if ($li->content_provider_opt) {
            $content[] = " ";
            $content[] = "(" . $li->content_provider_opt . ")";
        }

        if ($li->type) {
            $content[] = " ";
            $content[] = "(" . $li->type . ")";
        }


        return implode("", $content);

    }

    static function doIncludeLanguageInExtLinkCitation($list)
    {

        if (empty($list)) {
            return false;
        }

        if (is_scalar($list)) {
            $list = [$list];
        }

        foreach ($list as $x) {
            $x = strtolower($x);
            if (in_array($x, self::EXT_LINK_CITATION_LANGUAGES_NOT_INCLUDE)) {
                return false;
            }
        }

        return true;

    }

    static function buildPagesLine($pages, $prepend = ", ")
    {

        if (empty($pages)) {
            return "";
        }

        if ($prepend) {
            $line = $prepend;
        }

        if (stripos($pages, "-") !== false || stripos($pages, ",") !== false) {
            $line .= "pp. $pages";
        } else {
            $line .= "p. $pages";
        }

        return $line;

    }

    /**
     * @param \dtm_1418_link_base $li
     * @return string
     */
    static function buildEoExternalLinkCitationBookSection($li)
    {

        return self::buildEoExternalLinkCitationArticle($li);
        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

    }

    /**
     * @param \dtm_1418_link_base $li
     * @return string
     */
    static function buildEoExternalLinkCitationPrimarySource($li)
    {

        $fields = Oes_EoLink_Factory::getEoLinkFieldNames();

        $content = [];

        foreach ($fields as $field) {
            $$field = $post->{$field};
        }


        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

        $names = [];

        foreach ($li->authors_opt__array as $it) {
            foreach ($it as $k => $v) {
                $$k = $v;
            }
            if ($single_name) {
                $names[] = $name;
            } else {
                $names[] = "$last_name, $first_name";
            }
        }

        $content[] = implode(" / ", $names);

        $content[] = ": ";
        $content[] = $li->generic_title;

        if (self::doIncludeLanguageInExtLinkCitation($li->language_opt)) {
            $content[] = " (in " . implode(", ", $li->language_opt__array) . ")";
        }

        if ($li->places_of_publication_opt) {
            $content[] = ", ";
            $content[] = "$li->places_of_publication_opt";
        }

        if ($li->year_of_publication_opt) {
            $content[] = ", ";
            $content[] = $li->year_of_publication_opt;
        }

        if ($li->content_provider_opt) {
            $content[] = " ";
            $content[] = "(" . $li->content_provider_opt . ")";
        }

        if ($li->type) {
            $content[] = " ";
            $content[] = "(" . $li->type . ")";
        }


        return implode("", $content);

    }

    /**
     * @param \dtm_1418_link_base $li
     * @return string
     */
    static function buildEoExternalLinkCitationDatabase($li)
    {

        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

//        $content[] = ": ";
        $content[] = $li->generic_title;

        if (self::doIncludeLanguageInExtLinkCitation($li->language_opt)) {
            $content[] = " (in " . implode(", ", $li->language_opt__array) . ")";
        }

        if ($li->content_provider_opt) {
            $content[] = " ";
            $content[] = "(" . $li->content_provider_opt . ")";
        }

        if ($li->type) {
            $content[] = " ";
            $content[] = "(" . $li->type . ")";
        }


        return implode("", $content);

    }

    /**
     * @param \dtm_1418_link_base $li
     * @return string
     */
    static function buildEoExternalLinkCitationInstitutionalWebsite($li)
    {

        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

//        $content[] = ": ";
        $content[] = $li->generic_title;

        if (self::doIncludeLanguageInExtLinkCitation($li->language_opt)) {
            $content[] = " (in " . implode(", ", $li->language_opt__array) . ")";
        }

        if ($li->content_provider_opt) {
            $content[] = " ";
            $content[] = "(" . $li->content_provider_opt . ")";
        }

        if ($li->type) {
            $content[] = " ";
            $content[] = "(" . $li->type . ")";
        }


        return implode("", $content);

    }

    /**
     * @param \dtm_1418_link_base $li
     * @return string
     */
    static function buildEoExternalLinkCitationOnlineExhibition($li)
    {

        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

//        $content[] = ": ";
        $content[] = $li->generic_title;

        if (self::doIncludeLanguageInExtLinkCitation($li->language_opt)) {
            $content[] = " (in " . implode(", ", $li->language_opt__array) . ")";
        }

        if ($li->content_provider_opt) {
            $content[] = " ";
            $content[] = "(" . $li->content_provider_opt . ")";
        }

        if ($li->type) {
            $content[] = " ";
            $content[] = "(" . $li->type . ")";
        }


        return implode("", $content);

    }

    /**
     * @param \dtm_1418_link_base $li
     * @return string
     */
    static function buildEoExternalLinkCitationMap($li)
    {

        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

        $names = [];

        foreach ($li->creators_opt__array as $it) {
            foreach ($it as $k => $v) {
                $$k = $v;
            }
            if ($single_name) {
                $names[] = $name;
            } else {
                $names[] = "$last_name, $first_name";
            }
        }

        $content[] = implode(" / ", $names);

        if (self::doIncludeLanguageInExtLinkCitation($li->language_opt)) {
            $content[] = " (in " . implode(", ", $li->language_opt__array) . ")";
        }

        $content[] = ": ";
        $content[] = $li->generic_title;

        if ($li->places_of_publication_opt) {
            $content[] = ", ";
            $content[] = $li->places_of_publication_opt;
        }

        if ($li->year_of_publication_opt) {
            $content[] = ", ";
            $content[] = $li->year_of_publication_opt;
        }

        if ($li->content_provider_opt) {
            $content[] = " ";
            $content[] = "(" . $li->content_provider_opt . ")";
        }

        if ($li->type) {
            $content[] = " ";
            $content[] = "(" . $li->type . ")";
        }


        return implode("", $content);

    }

    /**
     * @param \dtm_1418_link_base $li
     * @return string
     */
    static function buildEoExternalLinkCitationAudio($li)
    {

        $names = [];

        foreach ($li->creators_opt__array as $it) {
            foreach ($it as $k => $v) {
                $$k = $v;
            }
            if ($single_name) {
                $names[] = $name;
            } else {
                $names[] = "$last_name, $first_name";
            }
        }

        $content[] = implode(" / ", $names);


        if (self::doIncludeLanguageInExtLinkCitation($li->language_opt)) {
            $content[] = " (in " . implode(", ", $li->language_opt__array) . ")";
        }

        $content[] = ": ";
        $content[] = $li->generic_title;

        if ($li->places_of_publication_opt) {
            $content[] = ", ";
            $content[] = $li->places_of_publication_opt;
        }

        if ($li->year_of_publication_opt) {
            $content[] = ", ";
            $content[] = $li->year_of_publication_opt;
        }

        if ($li->content_provider_opt) {
            $content[] = " ";
            $content[] = "(" . $li->content_provider_opt . ")";
        }

        if ($li->type) {
            $content[] = " ";
            $content[] = "(" . $li->type . ")";
        }


        return implode("", $content);

    }

    /**
     * @param \dtm_1418_link_base $li
     * @return string
     */
    static function buildEoExternalLinkCitationVideo($li)
    {


        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

        $names = [];

        foreach ($li->creators_opt__array as $it) {
            foreach ($it as $k => $v) {
                $$k = $v;
            }
            if ($single_name) {
                $names[] = $name;
            } else {
                $names[] = "$last_name, $first_name";
            }
        }

        $content[] = implode(" / ", $names);


        if (self::doIncludeLanguageInExtLinkCitation($li->language_opt)) {
            $content[] = " (in " . implode(", ", $li->language_opt__array) . ")";
        }

        $content[] = ": ";
        $content[] = $li->generic_title;

        if ($li->places_of_publication_opt) {
            $content[] = ", ";
            $content[] = $li->places_of_publication_opt;
        }

        if ($li->year_of_publication_opt) {
            $content[] = ", ";
            $content[] = $li->year_of_publication_opt;
        }

        if ($li->content_provider_opt) {
            $content[] = " ";
            $content[] = "(" . $li->content_provider_opt . ")";
        }

        if ($li->type) {
            $content[] = " ";
            $content[] = "(" . $li->type . ")";
        }

        return implode("", $content);

    }

    /**
     * @param \dtm_1418_bibliography_base $bib
     * @return string
     */
    static function buildEoBibliographyCitationManuscript($bib)
    {

        $content = [];

        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

        $names = [];
        $names = self::buildZoteroAuthorsLine($bib->zot_creators, $names);
        $names = self::buildZoteroEditorsLine($bib->zot_creators, $names);

        if (!empty($names)) {
            $content[] = implode(", ", $names);
            $content[] = ": ";
        }

        $content[] = "<b>" . $bib->zot_title . "</b>";

        if ($bib->zot_manuscriptType) {
            $content[] = ", ";
            $content[] = $bib->zot_manuscriptType;
        }

        if ($bib->zot_archive) {
            $content[] = ", ";
            $content[] = $bib->zot_archive;
        }

        if ($bib->zot_place) {
            $content[] = ", ";
            $content[] = $bib->zot_place;
        }

        if ($bib->zot_archiveLocation) {
            $content[] = ", ";
            $content[] = $bib->zot_archiveLocation;
        }

        if ($bib->zot_callNumber) {
            $content[] = ", ";
            $content[] = $bib->zot_callNumber;
        }

        if ($bib->zot_date) {
            $content[] = ", ";
            $content[] = $bib->zot_date;
        }

        return implode("", $content) . ".";

    }

    /**
     * @param \dtm_1418_bibliography_base $bib
     * @return string
     */
    static function buildEoBibliographyCitationJournalArticle($bib)
    {

        $content = [];

        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

        $names = [];
        $names = self::buildZoteroAuthorsLine($bib->zot_creators, $names);
        $names = self::buildZoteroEditorsLine($bib->zot_creators, $names);

        if (!empty($names)) {
            $content[] = implode(", ", $names);
            $content[] = ": ";
        }

        $content[] = "<b>" . $bib->zot_title . "</b>";

        if ($bib->zot_publicationTitle) {
            $content[] = ", ";
            $content[] = "in: " . $bib->zot_publicationTitle;
        }

        $parts = [];

        if ($bib->zot_volume) {
            $parts[] = $bib->zot_volume;
        }

        if ($bib->zot_issue) {
            $parts[] = $bib->zot_issue;
        }

        if (!empty($parts)) {
            $content[] = " ";
            $content[] = implode("/", $parts);
        }

        if ($bib->zot_date) {
            $content[] = ", ";
            $content[] = $bib->zot_date;
        }

        if ($bib->zot_pages) {
//            $content[] = ", ";
            $content[] = self::buildPagesLine($bib->zot_pages);
        }

        return implode("", $content) . ".";

    }

    /**
     * @param \dtm_1418_bibliography_base $bib
     * @return string
     */
    static function buildEoBibliographyCitationNewspaperArticle($bib)
    {

        $content = [];

        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

        $names = [];
        $names = self::buildZoteroAuthorsLine($bib->zot_creators, $names);
        $names = self::buildZoteroEditorsLine($bib->zot_creators, $names);

        if (!empty($names)) {
            $content[] = implode(", ", $names);
            $content[] = ": ";
        }

        $content[] = "<b>" . $bib->zot_title . "</b>";

        if ($bib->zot_publicationTitle) {
            $content[] = ", ";
            $content[] = "in: " . $bib->zot_publicationTitle;
        }

        $parts = [];

        if ($bib->zot_volume) {
            $parts[] = $bib->zot_volume;
        }
        if ($bib->zot_issue) {
            $parts[] = $bib->zot_issue;
        }

        if (!empty($parts)) {
            $content[] = " ";
            $content[] = implode("/", $parts);
        }

        if ($bib->zot_date) {
            $content[] = ", ";
            $content[] = $bib->zot_date;
        }

        if ($bib->zot_pages) {
//            $content[] = ", ";
            $content[] = self::buildPagesLine($bib->zot_pages);
        }

        return implode("", $content) . ".";

    }

    /**
     * @param \dtm_1418_bibliography_base $bib
     * @return string
     */
    static function buildEoBibliographyCitationBookSection($bib)
    {

        $content = [];

        /*
         * [Author‘s last name, first name] [/Author‘s last name, first name] [(in [Language(s)])]: [Title], [(vol. [Volume])], [(ed. [Edition])], [Place(s) of publication] [Year of publication] [(Content Provider)] [(Type)]


         */

        $names = self::buildZoteroAuthorsLine($bib->zot_creators, []);

        if (!empty($names)) {
            $content[] = $names[0];
            $content[] = ": ";
        }

        $content[] = "<b>" . $bib->zot_title . "</b>";

        $editors = self::evalCreators($bib->zot_creators, "editor");

        $components = [];

        $pub_parts = [];

        if (!empty($editors)) {
            $pub_parts[] = self::buildEditorsLine($editors);
        }

        if ($bib->zot_bookTitle) {
            $pub_parts[] = ": " . $bib->zot_bookTitle;
        }

        if ($bib->zot_series) {
            $series = $bib->zot_series;
            if ($bib->zot_seriesNumber) {
                $series .= " " . $bib->zot_seriesNumber;
            }
            $pub_parts[] = " ($series)";
        }

        if (!empty($pub_parts)) {
            $components[] = ", in: " . implode("", $pub_parts);
        }

        if ($bib->zot_volume) {
            $components[] = "vol. " . $bib->zot_volume;
        }

        if ($bib->zot_numberOfVolumes) {
            $components[] = $bib->zot_numberOfVolumes . " vols.";
        }

        if ($bib->zot_edition) {
            $components[] = $bib->zot_edition . " ed.";
        }

        $pub_parts = [];

        if ($bib->zot_place) {
            $pub_parts[] = $bib->zot_place;
        }

        if ($bib->zot_date) {
            $pub_parts[] = $bib->zot_date;
        }

        if ($bib->zot_publisher) {
            $pub_parts[] = $bib->zot_publisher;
        }

        if (!empty($pub_parts)) {
            $pub_parts = implode(" ", $pub_parts);
            $components[] = $pub_parts;
        }

        if ($bib->zot_pages) {
            $components[] = self::buildPagesLine($bib->zot_pages, "");
        }

        if (!empty($components)) {
            $content[] = implode(", ", $components);
        }

        return self::concatCitationParts($content);

    }

    /**
     * @param \dtm_1418_link_base $li
     * @return string
     */
    static function buildEoExternalLinkCitationImage($li)
    {

        $names = [];

        foreach ($li->creators_opt__array as $it) {
            foreach ($it as $k => $v) {
                $$k = $v;
            }
            if ($single_name) {
                $names[] = $name;
            } else {
                $names[] = "$last_name, $first_name";
            }
        }

        $content[] = implode(" / ", $names);


        if (self::doIncludeLanguageInExtLinkCitation($li->language_opt)) {
            $content[] = " (in " . implode(", ", $li->language_opt__array) . ")";
        }

        $content[] = ": ";
        $content[] = $li->generic_title;

        if ($li->places_of_publication_opt) {
            $content[] = ", ";
            $content[] = $li->places_of_publication_opt;
        }

        if ($li->year_of_publication_opt) {
            $content[] = ", ";
            $content[] = $li->year_of_publication_opt;
        }

        if ($li->content_provider_opt) {
            $content[] = " ";
            $content[] = "(" . $li->content_provider_opt . ")";
        }

        if ($li->type) {
            $content[] = " ";
            $content[] = "(" . $li->type . ")";
        }


        return implode("", $content);

    }

    static function build_tax_slug($slug)
    {

        if (empty($slug)) {
            return $slug;
        }

        if (is_array($slug)) {
            $res = [];
            foreach ($slug as $s) {
                $res[] = strtolower(normalizeToSimpleSortAscii($s));
            }
            return $res;
        }
        return strtolower(normalizeToSimpleSortAscii($slug));
    }

    static function eval_date_sort_list($date)
    {

        $date_sort = null;

        $date_list = $date;

        if (preg_match("@[-]@", $date)) {
            // date-range

            list ($from_date, $to_date) =
                preg_split("@\s*[,;-]\s*@", $date, 2);

            $date_sort = strtotime("${from_date}-01-01");
            $date_list = $from_date;

        } else if ($date > 1300 && $date <= date("Y")) {

            $date_sort = strtotime("${date}-01-01");
            $date_list = $date;

        } else if (preg_match("@\d+[./\-]\d+[./\-]\d+@", $date)) {

            $date_parts = preg_split("@[./\-]@", $date);

            if (count($date_parts) == 3) {
                $date_sort = strtotime($date);
                $date_list = date("Y, M jS", $date_sort);
            }

        } else {
            $date_sort = strtotime($date);
            $date_list = date("Y, M jS", $date_sort);
        }

        return [$date_sort, $date_list];

    }

    static function getGeneralEditorsCitationLine()
    {
        return "Ute Daniel, Peter Gatrell, Oliver Janz, Heather Jones, Jennifer Keene, Alan Kramer, and Bill Nasson";
//        return x_concat_object_property(self::getListOfGeneralEditors(), "name", ", ", " and ");
    }

    static function getListOfGeneralEditors()
    {

        static $list;

        if ($list) {
            return $list;
        }

        $posts = new WP_Query([
            'posts_per_page' => -1,
            'post_type' => 'eo_contributor',
            'order' => 'ASC',
            'orderby' => 'meta_value',
            'meta_key' => 'x_title_sort',
//    'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'oes_special_cats',
                    'field' => 'slug',
                    'terms' => 'role_generaleditor',
                ),
            ),
        ]);

        return oes_dtm_form::init($posts->get_posts());


    }

    static function wp_slug($str)
    {
        return sanitize_title($str);
    }

    //

    static function FromPageNameToSlug($str)
    {
        $str = normalizeFormD($str);
        $str = preg_replace('/[^a-zA-Z0-9 \-_\.]/i', '', $str);
        if (!is_string($str)) {
            print_r($str);
            throw new Exception("not a string");
        }
        return strtolower($str);
    }

    static function log_error($msg)
    {
        static $on;
        if (!isset($on)) {
            $on = hasparam("_log");
        }
        if ($on) {
            error_log("ERROR:: $msg");
        }
    }

    static function eval_sort_class($str)
    {

        if (empty($str)) {
            return "";
        }

        $char = $str[0];

        if ($char < 'a' || $char > 'z') {
            $sort_class = '#';
        } else {
            $sort_class = $char;
        }

        return $sort_class;

    }

    static function insertLookupTermByName($name, $taxonomy, $slug = null)
    {

        if (empty($name)) {
            return false;
        }

        $term = get_term_by('name', $name, $taxonomy);

        if ($term) {
            return $term->term_id;
        }

        $args = [];

        if ($slug) {
            $args = ['slug' => $slug];
        }

        $term = wp_insert_term($name,
            $taxonomy, $args);

        if (is_wp_error($term)) {
            throw new Exception("create term failed $name / $taxonomy");
        }

        return $term['term_id'];

    }

    static function syncWithZotero($groupid, $key, $isUserContent = false, $userProfile = null)
    {

        ini_set("output_buffering", "Off");

        $zot = new Oes_Zotero();

        $zot->loadExistingTitlesIntoCache();

        global $argv;

        $importcount = $argv[1];
        $groupidP = $argv[2];

        if ($deleteprevious) {
            oes_delete_posts_by_post_type(Oes_General_Config::EO_BIBLIOGRAPHY, true);
        }

        if (empty($importcount)) {
            $importcount = rparam('count', 15000);
        }

        if (!empty($groupidP)) {
            $groupid = $groupidP;
        }

        ?>
        <pre><?php

            //$zot->import("group", "163113", "jlmZuelFHxJi6FP1ZgQhUu7L", $importcount);
            $zot->import("group", $groupid, $key, $importcount, $isUserContent, $userProfile);

            ?></pre>
        <h2>Synchronizing Zotero entries finished, cleaning up …</h2>

        <?php add_action("oes/dtm/resolve_done", function () {
        ?><h2>Clean up finished.</h2><?php
    }, 10, 0);
    }

    static function get_user_profiles($user = null)
    {
        return oes_dtm_form::init_from_list(self::get_user_profile_ids($user));
    }

    static function get_user_profile_ids($user = null)
    {

        if (!$user && is_user_logged_in()) {
            $user = get_current_user_id();
        }

        $data = get_fields("user_$user");

        $userprofileids = x_as_array($data['user_profiles']);

        return $userprofileids;

    }

    /**
     * @param $item
     * @param null $profile
     * @return \dtm_1418_bookmark_base|mixed
     * @throws Exception when not found
     */
    static function createUpdateBookmark($item, $title, $notes = "", $profile = null)
    {

        if (empty($profile)) {
            $profile = oes_get_current_profile_id();
        }

        $post = get_post($item);

        $slug = 'bm-' . $profile . '-' . $item;

        try {
            $id = oes_get_post_id_by_property($slug, Oes_General_Config::U_BOOKMARK);
            $dtm = \dtm_1418_bookmark_base::init($id);
        } catch (Exception $e) {
            $dtm = \dtm_1418_bookmark_base::create();
            $dtm->post_name = $slug;
            $dtm->item = $item;
            $dtm->owner = $profile;
            $dtm->user = get_current_user_id();
            $dtm->status = Oes_General_Config::STATUS_READY_FOR_PUBLISHING;
            $dtm->type = $post->post_type;
        }

        $dtm->title = trim($title);
        $dtm->notes = trim($notes);

        $dtm->save();

        return $dtm;

    }

    /**
     * @param $item
     * @param null $profile
     * @return \dtm_1418_bookmark_base|mixed
     * @throws Exception when not found
     */
    static function getBookmark($item, $profile = null)
    {

        if (empty($profile)) {
            $profile = oes_get_current_profile_id();
        }

        $post = get_post($item);

        $slug = 'bm-' . $profile . '-' . $item;

        $id = oes_get_post_id_by_property($slug, Oes_General_Config::U_BOOKMARK);

        return \dtm_1418_bookmark_base::init($id);

    }

    /**
     * @param $item
     * @param null $profile
     * @return \dtm_1418_bookmark_base|mixed
     * @throws Exception when not found
     */
    static function existsBookmark($item, $profile = null)
    {

        if (!is_user_logged_in()) {
            return;
        }

        if (empty($profile)) {
            $profile = oes_get_current_profile_id();
        }

        $slug = 'bm-' . $profile . '-' . $item;

        try {
            $id = oes_get_post_id_by_property($slug, Oes_General_Config::U_BOOKMARK);
            return true;
        } catch (Exception $e) {

        }

        return false;

    }

    /**
     * @param $item
     * @param null $profile
     * @return \dtm_1418_bookmark_base|mixed
     * @throws Exception when not found
     */
    static function removeBookmark($item, $profile = null)
    {

        if (empty($profile)) {
            $profile = oes_get_current_profile_id();
        }

        $slug = 'bm-' . $profile . '-' . $item;

        try {
            $id = oes_get_post_id_by_property($slug, Oes_General_Config::U_BOOKMARK);
            $dtm = \dtm_1418_bookmark_base::init($id);
            $dtm->delete(true, true);
            return true;
        } catch (Exception $e) {
            return false;
        }

    }

    static function getAhrefLinkOfArticle($av)
    {
        $class = '';
        $parent = $av->u_article_versions__obj;
        $permalink = $parent->get_permalink();
        if (!$av->is_visible_and_published()) {
            $class = 'new';
        }
        $title = $av->title__html;
        return <<<EOD
<a href="$permalink" class="$class">$title</a>
EOD;


    }

    static function makeTooltipHtml($title)
    {

        $html = ashtml($title);
        return <<<EOD
<a class="oes-acf-tooltip" title='$html' data-toggle="tooltip" data-placement="top" href='#'><span class="fa fa-question-circle"></span></a>
EOD;


    }

    static function updateBsbPersonEntry($postid)
    {
        $dtm = \dtm_1418_article_version_base::init($postid);
        $dtm->u_bsb_person_entry = $dtm->u_article_classification_group == Oes_General_Config::ARTICLE_CLASSIFICATION_GROUP_PERSONS;
        return $dtm;
    }

    static function isEmailAddressInUse($email)
    {

        $wpuser = get_user_by('email', $email);

        if ($wpuser) {
            return true;
        }

        $query = new Oes_Mini_Posts(Oes_General_Config::S_SIGNUP);
        $query->addMetaCondition(\dtm_1418_signup_base::attr_email, $email);
        $query->query();
        if ($query->hasPosts()) {
            return true;
        }

        $query = new Oes_Mini_Posts(Oes_1418_Config::EO_CONTRIBUTOR);
        $query->addMetaCondition(\dtm_1418_contributor_base::attr_email_change_address, $email);
        $query->addMetaCondition(\dtm_1418_contributor_base::attr_email_change_pending, '1');
        $query->query();
        if ($query->hasPosts()) {
            return true;
        }
        return false;


    }

    static function create_user($username, $email, $name, $pwd, $firstname = null, $lastname = null, $role = 'subscriber', $locale = 'en')
    {

        $args = [
            'user_pass' => $pwd,
            'user_login' => $username,
            'user_email' => $email,
            'locale' => 'en',
            'role' => $role,
            'show_admin_bar_front' => false,
            'user_nicename' => $name,
            'nickname' => $name,
            'display_name' => $name];

        if (!empty($firstname)) {
            $args['first_name'] = $firstname;
        }

        if (!empty($lastname)) {
            $args['last_name'] = $lastname;
        }

        $userid = wp_insert_user($args);

        if (is_wp_error($userid)) {
            error_log("insert user failed " . print_r($userid, true));
            throw new Exception("inser user failed");
        }

        return $userid;

    }

    static function replace_variable_placeholders_with_values($text, $map)
    {
        return preg_replace_callback('@#([a-z][a-z0-9_]{1,31})#@', function ($matches) use ($map) {
            $variable = $matches[1];
            if (empty($variable)) {
                return '';
            }
            return $map[$variable];
        }, $text);
    }


    static function createWpTerm($title, $taxonomy, $slug, $description = '', $parentid = null)
    {


        static $existing;

        if (!isset($existing)) {
            $existing = [];
        }

        if (!array_key_exists($taxonomy, $existing)) {

            $existing[$taxonomy] = [];

            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));

            foreach ($terms as $term) {
                $existing[$taxonomy][$term->slug] = $term->term_id;
            }

        }

        $args = [
            'slug' => $slug,
            "description" => $description,
        ];

        if ($parentid) {
            $args['parent'] = $parentid;
        }

        $isOld = false;

        if (array_key_exists($slug, $existing[$taxonomy])) {
            $termid = $existing[$taxonomy][$slug];
            $args['name'] = $title;
            $isOld = true;
        }

        if ($isOld) {
            $term = wp_update_term($termid, $taxonomy, $args);
        } else {
            $term = wp_insert_term($title, $taxonomy, $args);
        }


        if ($term instanceof WP_Error) {
            throw new Exception("creating term $taxonomy with title $title failed", 0, $term);
        }

        $termid = $term['term_id'];

        $existing[$taxonomy][$term['slug']] = $termid;

        return $term;

    }

    static function convertRawHtml($html)
    {

        oes_upload_vendor_autoload();

        $html = self::convertHtmlCharsetWindows1252ToUtf($html);

        phpQuery::newDocument($html);

//echo $html;

        $body = phpQuery::pq("body");

//echo $body->html(), "\n";

//die(1);

        phpQuery::pq("h1", $body)->each(function ($v) use (&$tooltips, &$tooltipid) {

            $self = phpQuery::pq($v);

            $text = $self->text();

            $self->html($text);

        });

        phpQuery::pq("h1,h2,h3,h4,h5", $body)->each(function ($v) use (&$tooltips, &$tooltipid) {

            $self = phpQuery::pq($v);

            $text = $self->text();

            $self->html($text);

        });

        $endNotes = [];

        phpQuery::pq("[style='mso-element:footnote']", $body)->each(function ($v) use (&$endNotes) {

            $self = phpQuery::pq($v);

            $text = trim($self->html());

            $text = strip_tags($text, "<sup><b><i><em>");

            $id = $self->attr('id');

            $text = preg_replace('@^\[\d+\]@', '', $text);

            $endNotes[$id] = trim($text);

            $self->remove();

        });

//print_r($endNotes);
//
//die(1);

        foreach ($endNotes as $ftnid => $ftn) {

//    $pq = phpQuery::pq("[style='mso-footnote-id:$ftnid']");
            $pq = phpQuery::pq("[href='#_${ftnid}']'");


            $span = phpQuery::pq("<span/>");

            $pq->replaceWith("[note]${ftn}[/note]");
//    $span->text("[note]${ftn}[/note]");
//
//    $pq->replaceWithPHP($span);


        }

        phpQuery::pq("*", $body)->each(function ($v) use (&$tooltips, &$tooltipid) {

            $self = phpQuery::pq($v);

            $self->removeAttr("id");
            $self->removeAttr("lang");
            $self->removeAttr("style");
            $self->removeAttr("class");
            $self->removeAttr("title");
            $self->removeAttr("align");
            $self->removeAttr("clear");
            $self->removeAttr("size");
            $self->removeAttr("width");
            $self->removeAttr("valign");
            $self->removeAttr("border");
            $self->removeAttr("href");
            $self->removeAttr("name");

        });


        $html = $body->html();


        $html = str_replace("<hr>", "", $html);
        $html = str_replace("<br>", "", $html);
        $html = str_replace("<span>", "", $html);
        $html = str_replace("<div>", "", $html);
        $html = str_replace("</span>", "", $html);
        $html = str_replace("</div>", "", $html);
        $html = str_replace("</p>", "", $html);
        $html = str_replace("<p>", "", $html);

        $html = preg_replace("/^\s*$/msi", "%%%", $html);
        $html = preg_replace("/[\r\n]+/si", " ", $html);
        $html = preg_replace("/%{3,}/msi", "\n", $html);

        return $html;

    }

    static function convertHtmlCharsetWindows1252ToUtf($html)
    {

        $isWindows1252 = false;

        if (preg_match('@content=.*?windows-1252@mi', $html)) {
            $isWindows1252 = true;
        }
        if ($isWindows1252) {
            $html = mb_convert_encoding($html, 'utf-8', 'windows-1252');
        }

        $html = str_replace('­', '-', $html);

        return $html;


    }

    static function generateConfirmationToken()
    {
        $random = genRandomString(16);
        $confirmationToken =
            Oes_General_Config::generateSimpleActionNonce($random) . $random;
        return $confirmationToken;
    }

    static function generateSimpleActionNonce($action)
    {
        return md5(NONCE_SALT . $action);
    }

    static function verifySimpleActionNonce($action, $nonce)
    {
        $token = self::generateSimpleActionNonce($action);
        return $nonce === $token;
    }

    static function getRemoteIpAddress()
    {
        return x_getRemoteIpAddress();
    }

    static function indexPost($postid)
    {

        error_log("indexing $postid");

        if (is_numeric($postid)) {

            $dtm = oes_dtm_form::init($postid);

            $dtm->indexSearchEngine();

        } else {

            $postType = $postid;

            Oes_General_Config::deleteAllFromIndexByPostType($postType);

            $postids = oes_wp_query_post_ids($postType);

            $total = count($postids);

            /**
             * @var WP_Post $po
             */
            foreach ($postids as $poid) {

                $count++;

                error_log("indexing $poid ($postType): $count/$total");

                $dtm = oes_dtm_form::init($poid);

                $dtm->indexSearchEngine();

                error_log("indexed ($postType): $count/$total");

            }
        }

    }

    static function deleteAllFromIndexByPostType($postType)
    {

        if (is_array($postType)) {
            $postType = implode(' OR ', $postType);
        }

        solrclient()->deleteByQuery('+x_type_s:(' . $postType . ')');

        solrclient()->commit();

    }

    static function deletePostsByPostType($postTypes)
    {

        if (empty($postTypes)) {
            return;
        }

        $postTypes = x_as_array($postTypes);

        foreach ($postTypes as $postType) {
            $idByPostType[$postType] = oes_wp_query_post_ids($postType);
        }

        foreach ($postTypes as $postType) {

            $ids = $idByPostType[$postType];

            foreach ($ids as $id) {
                error_log("trashing $id $postType");
                $dtm = oes_dtm_form::init($id);
                $dtm->trash();
            }

        }

        oesChangeResolver()->resolve();

        foreach ($postTypes as $postType) {

            $ids = $idByPostType[$postType];

            foreach ($ids as $id) {
                error_log("deleting $id $postType");
                wp_delete_post($id);
            }

        }

    }

    static function getAllPostTypes()
    {
        return self::$PROJECT_CONFIG->getAllPostTypes();
    }

    static function getProjectPluginBaseDir()
    {
        return self::$PROJECT_CONFIG->getProjectPluginBaseDir();
    }

    static function getProjectPluginClassesDir()
    {
        return self::$PROJECT_CONFIG->getProjectPluginBaseDir() . '/post_types/classes/';
    }

    static function getProjectAMSExportsDir()
    {
        return self::$PROJECT_CONFIG->getProjectPluginBaseDir() . '/post_types/ams/';
    }

    static function getZoteroApiKey()
    {
        return self::$PROJECT_CONFIG->getZoteroApiKey();
    }

    static function buildGreekAlphabet()
    {
        $greek_classes = [];

        for ($i = 945; $i < 970; $i++) {
            if ($i == 962) {
                continue;
            }
            $char = html_entity_decode("&#$i;");
            $greek_classes[$char] = mb_strtoupper($char);
        }

        return $greek_classes;
    }

    static function composeListingName($lastname, $firstname = '')
    {
        if (empty($firstname)) {
            return $lastname;
        } else {
            return $lastname . ', ' . $firstname;
        }
    }

    static function getWebsiteLanguage()
    {
        return Oes_General_Config::$PROJECT_CONFIG->getWebsiteLanguage();
    }

    static function sortListOfNachweise($list, $property, $typ, $checkcallback = null)
    {
        $sortedList = [];
        if (empty($list)) {
            return [];
        }

        foreach ($list as $item) {

            if ($checkcallback) {
                $ret = call_user_func($checkcallback, $item);
                if (!$ret) {
                    continue;
                }
            }

            $label = $item->{$property};
            $sortedList[normalizeToSimpleSortAsciiWithGreek($label)] = ['label' => $label, 'item' => $item, 'typ' => $typ];
        }
        ksort($sortedList);
        return array_values($sortedList);
    }

    static function printDateTextRange($anfang, $ende)
    {
        $str = [];
        if ($anfang) {
            $str[] = Oes_General_Config::renderDateText($anfang);
        }
        if ($ende) {
            $str[] = '-';
            $str[] = Oes_General_Config::renderDateText($ende);
        }

        return implode("", $str);
    }

    static function renderDateText($value, $format = 'd.m.Y')
    {

        if (empty($value)) {
            return $value;
        }

//        if (is_integer($value)) {
//            return date($format, $value);
//        }

        list($str, $timestamp, $full) = self::parseDateTextField($value);

        if ($timestamp) {
            if ($full) {
                return date($full, $timestamp);
            } else {
                return date("Y", $timestamp);
            }
        } else {
            return $str;
        }

    }

    static function parseDateTextField($str)
    {
        $intval = intval($str);

//        if (is_numeric($str)) {
//            return [date('d.m.Y'),$str,'d.m.Y'];
//        }

        $timestamp = false;

        if (preg_match('@[a-zA-Z]@', $str)) {
            return [$str, false];
        } else {
            if (strlen($str) == 4) {
                return [$str, strtotime("$str-01-01", $str)];
            } else if (preg_match('@^(\d\d\d\d)(([\-#])((\d\d?)-(\d\d?)))?$@', $str, $matches)) {

                $str = $matches[1];

                $full = 'Y';

                if ($matches[3] == '-') {
                    $str .= $matches[2];
                    $full = 'd.m.Y';
                }

                if ($matches[1] > 2100) {
                    throw new Exception("bad year value $matches[1]");
                }

                if ($matches[5] > 12) {
//                    throw new Exception("bad month value $matches[5] $str");
                }

                if ($matches[6] > 31) {
//                    throw new Exception("bad day value $matches[5]");
                }

                $timestr = $matches[1] . '-' . $matches[4];

                return [$str, strtotime($timestr), $full];

            } else if (preg_match('@^((\d\d?[\./]\d\d?)([#\./]))(\d\d\d\d)?$@', $str, $matches)) {

                $str = $matches[4];

                $full = 'Y';

                $timestr = str_replace('/', '.', $matches[2] . '.' . $matches[4]);

                if ($matches[3] != '#') {
                    $str = $timestr;
                    $full = 'd.m.Y';
                }

                return [$str, strtotime($timestr), $full];

            } else if (preg_match('@^((\d\d?)([#\./]))(\d\d?[\./]\d\d\d\d)$@', $str, $matches)) {

                $str = $matches[4];

                $full = 'd.m.Y';

                $timestr = str_replace('/', '.', $matches[2] . '.' . $matches[4]);

                if ($matches[3] == '#') {
                    $str = $timestr;
                    $full = 'n.Y';
                }


                return [$str, strtotime($timestr), $full];

            } else {
                return [$str, false];
            }

        }

    }

}