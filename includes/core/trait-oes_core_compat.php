<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Backwards compatibility for add-ons that still read the old public properties
 * (OES()->post_types, ->languages, ->admin_pages, ...).
 *
 * Works only because OES_Core does NOT declare these properties (PHP calls __get for inaccessible ones only).
 * Reads are supported; indirect writes (OES()->post_types['x'] = ...) are not and must move to the set_* methods.
 */
trait OES_Core_Compat
{

    public function __isset(string $name): bool
    {
        $props = ['post_types', 'taxonomies', 'media_groups', 'languages']; //TODO
        return isset(self::RENAMED[$name]) || in_array($name, $props, true);
    }

    //TODO clean up inside OES_Core
    public function __get(string $name)
    {
        if (isset(self::RENAMED[$name])) {
            $this->deprecated($name, '$' . self::RENAMED[$name]);
            return $this->{self::RENAMED[$name]};
        }

        return match ($name) {
            'post_types'        => $this->model()->post_types(), //TODO
            'taxonomies'        => $this->taxonomies(),
            'media_groups'      => $this->media_groups(),
            'languages'         => $this->languages->all(),
            'admin_pages'       => $this->admin->all(),
            'config_post'       => $this->theme->config_post() ?? false,
            'theme_index_pages' => $this->theme->get('theme_index_pages', false),
            'theme_labels'      => $this->theme->get('theme_labels', []),
            'search'            => $this->theme->get('search', []),
            'tools'             => $this->tools->all(),
            default             => $this->unknown_property($name),
        };
    }

    public function __set(string $name, $value): void
    {
        if (isset(self::RENAMED[$name])) {
            $this->deprecated($name, '$' . self::RENAMED[$name]);
            $this->{self::RENAMED[$name]} = $value;
            return;
        }

        // TODO: decide per property whether to forward to the services or refuse.
        trigger_error("OES_Core::\${$name} cannot be set directly. Use the matching set_* method.", E_USER_WARNING);
    }

    private function deprecated(string $old, string $new): void
    {
        trigger_error("OES_Core::\${$old} is deprecated since 3.0.0. Use {$new} instead.", E_USER_DEPRECATED);
    }

    private function unknown_property(string $name)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            trigger_error("Undefined property OES_Core::\${$name}", E_USER_NOTICE);
        }
        return null;
    }
}
