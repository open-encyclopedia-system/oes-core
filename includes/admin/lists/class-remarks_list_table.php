<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Constructs a table list for OES remarks.
 */
class Remarks_List_Table extends OES_List_Table
{
    protected string $meta_key = 'field_oes_comment';

    protected array $search_keys = [
        'field_oes_comment',
        'title'
    ];

    /**
     * Return the column "OES Remark"
     * @param $item
     * @return string
     */
    protected function column_field_oes_comment($item): string
    {
        return $item['field_oes_comment'] . sprintf('<div class="row-actions visible">' .
                '<span class="edit"><a href="%s">Edit</a> | </span>' .
                '<span class="view"><a href="%s">View</a></span>' .
                '<div>',
                $item['edit'],
                $item['link']
            );
    }

}
