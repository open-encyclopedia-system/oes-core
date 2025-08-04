<div <?php echo get_block_wrapper_attributes(); ?>>
    <a id="oes-citation-button" class="no-print"><?php
        echo $attributes['icon'] ? '<span class="dashicons dashicons-editor-quote"></span>' : '';
        echo oes_language_label_html($attributes['labels_trigger'] ?? 'Cite'); ?>
    </a><div id="oes-citation-overlay">
        <div id="oes-citation-text-box" onclick="event.stopPropagation();">
            <textarea id="oes-citation-text" rows="8" style="width: 100%;" readonly><?php
                $citation = oes_get_citation_html();
                echo (($attributes['html'] ?? false) ? $citation : strip_tags($citation));?>
            </textarea>
            <div>
                <button id="oes-citation-copy" class="has-background has-secondary-background-color wp-element-button"><?php
                    $copyLabel = oes_language_label_html($attributes['labels_copy'] ?? 'Copy');
                    echo (empty($copyLabel) ? 'Copy' : $copyLabel); ?>
                </button>
            </div>
        </div>
    </div>
</div>
