<div <?php echo get_block_wrapper_attributes(); ?>>
    <button id="oes-share-link-button" class="no-print wp-element-button"><?php
        echo oes_language_label_html($attributes['labels_trigger'] ?? 'Copy Link'); ?></button>
    <div id="oes-share-link-overlay" class="no-print">
        <div id="oes-share-link-text-box" onclick="event.stopPropagation();">
            <label for="oes-share-link-text"></label>
            <textarea id="oes-share-link-text" rows="8" style="width: 100%;" readonly></textarea>
            <div>
                <button id="oes-share-link-copy"
                        class="wp-element-button"><?php
                    echo oes_language_label_html($attributes['labels_copy'] ?? 'Copy'); ?></button>
            </div>
        </div>
    </div>
</div>