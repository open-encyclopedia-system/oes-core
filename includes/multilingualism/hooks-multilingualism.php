<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('oes/data_model_registered', 'oes_term_multilingualism');


/**
 * Enable multilingualism for terms (add translation meta data).
 * @return void
 */
function oes_term_multilingualism(): void
{
    global $oes;
    if (!empty($oes->taxonomies))
        foreach ($oes->taxonomies as $taxonomyKey => $ignore) {
            add_action($taxonomyKey . '_edit_form_fields', 'oes_term_add_fields_for_multilingualism', 10, 2);
            add_action('edited_' . $taxonomyKey, 'oes_term_save_fields_for_multilingualism', 10, 2);
        }
}


/**
 * Add translation fields to edit term screen.
 *
 * @param WP_Term $tag Current taxonomy term object.
 * @return void
 */
function oes_term_add_fields_for_multilingualism(WP_Term $tag): void
{

    // Check for existing taxonomy meta for the term you're editing
    $metaData = get_term_meta($tag->term_id);


    global $oes;
    if (sizeof($oes->languages) > 1):
        foreach ($oes->languages as $languageKey => $languageData):
            if ($languageKey !== 'language0'):

                ?>
                <tr class="form-field">
                    <th scope="row">
                        <label for="term_meta[<?php echo 'name_' . $languageKey; ?>]"><?php printf(__('Name (%s)'), $languageData['label'] ?? $languageKey); ?></label>
                    </th>
                    <td>
                        <input type="text" name="term_meta[<?php echo 'name_' . $languageKey; ?>]"
                               id="term_meta[<?php echo 'name_' . $languageKey; ?>]"
                               value="<?php echo $metaData['name_' . $languageKey][0] ?? ''; ?>"><br/>
                        <p class="description"><?php _e('The name as displayed in the selected language.'); ?></p>
                    </td>
                </tr>
                <tr class="form-field term-description-wrap">
                    <th scope="row">
                        <label for="term_meta[<?php echo 'description_' . $languageKey; ?>]"><?php printf(__('Description (%s)'), $languageData['label'] ?? $languageKey); ?></label>
                    </th>
                    <td><textarea name="term_meta[<?php echo 'description_' . $languageKey; ?>]"
                                  id="term_meta[<?php echo 'description_' . $languageKey; ?>]" rows="5" cols="50"
                                  class="large-text"><?php echo $metaData['description_' . $languageKey][0] ?? ''; ?></textarea>
                        <p class="description"><?php _e('The description as displayed in the selected language.'); ?></p>
                    </td>
                </tr>
            <?php
            endif;
        endforeach;
    endif;
}


/**
 * Save translation field for term.
 *
 * @param int $term_id Term ID.
 * @return void
 */
function oes_term_save_fields_for_multilingualism(int $term_id): void
{
    if (isset($_POST['term_meta']) && is_array($_POST['term_meta'])) {

        /* get existing metadata */
        $metaData = get_term_meta($term_id);
        foreach ($_POST['term_meta'] as $key => $value) {
            if (isset($metaData[$key])) update_term_meta($term_id, $key, $value);
            else add_term_meta($term_id, $key, $value);
        }
    }
}