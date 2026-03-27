<?php

if (!defined('ABSPATH')) {
    exit;
}

function oes_resolve_context_param(string $param, array $args = []) {

    global $oes_post;

    if($param == 'translation_id'){

        $language = $args['language'] ?? false;

        if(!$language){
            return $oes_post->translations[0]['id'] ?? null;
        }

        foreach($oes_post->translations as $translation){
            if($translation['language'] == $language){
                return $translation['id'] ?? null;
            }
        }

        return null;
    }

    return match ($param) {

        'object_id' => $oes_post->object_ID ?? null,
        'return_url' => home_url($_SERVER['REQUEST_URI']),
        'split_id' => $oes_post->split_id ?? null,

        default => null
    };
}

global $oes_language;

$label = $attributes['labels'][$oes_language] ?? __('Open', 'oes');
$url = $attributes['link'] ?? '';
$additional = json_decode($attributes['additional'] ?? '', true) ?? [];

$params = [];

foreach ($attributes['params'] ?? [] as $param) {

    $value = oes_resolve_context_param($param, $additional);

    if ($value !== null) {
        $params[$param] = $value;
    }
}

if (!empty($params)) {
    $url = add_query_arg($params, $url);
}

?>

<a href="<?php echo esc_url($url); ?>" class="wp-element-button">
    <?php echo esc_html($label); ?>
</a>