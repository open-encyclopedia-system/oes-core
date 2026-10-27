<?php

namespace OES\Core;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Reads the 'oes_config' OES object post. The only place that knows where the raw configuration lives.
 *
 *
 * @oesDevelopment: decode(), validate(), log_error()
 *  do as class so it could be autoloaded
 */
final class Reader
{
    /** @return array{post_id:int,config:array}|null */
    public static function read(): ?array
    {
        $posts = get_posts([
            'post_type' => 'oes_object',
            'name' => 'oes_config',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        if (empty($posts)) {
            return null;
        }

        $config = json_decode($posts[0]->post_content ?: '{}', true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            oes_write_log(sprintf(
                'Invalid JSON in post %d: %s',
                $posts[0]->ID,
                json_last_error_msg()
            ));
            $config = [];
        }

        return [
            'post_id' => (int)$posts[0]->ID,
            'config' => is_array($config) ? $config : []
        ];
    }
}
