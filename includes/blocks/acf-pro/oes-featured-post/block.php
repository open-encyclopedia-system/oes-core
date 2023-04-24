<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field;


/* get global instance */
$oes = OES();

$postTypes = [];
foreach($oes->post_types as $postTypeKey => $postTypeData)
    $postTypes[$postTypeKey] = $postTypeData['label'] ?? $postTypeKey;

/* block identifier */
$blockID = 'featured-post';

/* add to global instance if not yet registered */
if (!isset($oes->blocks['acf_pro'][$blockID]))
    $oes->blocks['acf_pro'][$blockID] = [
        'name' => 'oes-featured-post',
        'title' => 'OES Featured Post',
        'render_callback' => 'oes_block_render_featured_post',
        'keywords' => ['OES', 'feature', 'post'],
        'mode' => 'auto',
        'field_group' => [
            'title' => 'OES Content',
            'fields' => [
                [
                    'key' => 'block_field__featured_post',
                    'label' => 'Post',
                    'name' => 'featured_post',
                    'type' => 'post_object',
                    'allow_null' => true,
                    'post_type' => ''
                ],
                [
                    'key' => 'block_field__post_title',
                    'label' => 'Title',
                    'name' => 'post_title',
                    'type' => 'text',
                    'instructions' => 'Use post title if this field is left empty (might be overwritten by project configurations).'
                ],
                [
                    'key' => 'block_field__post_type',
                    'name' => 'post_type',
                    'label' => 'Random Post of Post Type',
                    'type' => 'select',
                    'choices' => $postTypes,
                    'instructions' => 'If the post field is empty, generate a random post of selected post type.'
                ]
            ],
            'location' => [[[
                'param' => 'block',
                'operator' => '==',
                'value' => 'acf/oes-featured-post',
            ]]]
        ]
    ];


/**
 * Define the server side callback to render your block in the front end.
 * @return void
 */
function oes_block_render_featured_post(): void
{
    echo oes_get_featured_post_html(
        oes_get_field('featured_post') ?? false, [
        'title' => oes_get_field('post_title'),
        'post_type' => oes_get_field('post_type')
        ]);
}