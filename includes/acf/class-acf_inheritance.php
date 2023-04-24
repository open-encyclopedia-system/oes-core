<?php

namespace OES\ACF;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('ACF_Inheritance')) :

    /**
     * Class ACF_Inheritance
     *
     * Creating or updating bidirectional relationships of acf fields.
     * (Modified from open source WordPress plugin 'ACF Post-2-Post' by John A. Huebner,
     * https://github.com/Hube2/acf-post2post)
     *
     */
    class ACF_Inheritance
    {

        /** @var array Containing rules for overwriting relationships */
        public array $overwrite = [];

        /** @var array Containing self references for post type that will be evaluated after update */
        public array $after_processing = [];


        /**
         * ACF_Inheritance constructor.
         */
        public function __construct()
        {
            add_action('oes/data_model_registered', function () {

                add_filter('acf/update_value/type=relationship', [$this, 'update_relationship_field'], 11, 3);
                add_filter('acf/update_value/type=post_object', [$this, 'update_relationship_field'], 11, 3);

                /* force update of self reference fields of affected post */
                add_action('save_post', [$this, 'add_self_references']);
            });
        }


        /**
         * Add an overwrite rule to processing. Determines if values can be overwritten by bidirectional relationship.
         * Add a new rule by calling:
         * OES()->acf_bi_relationship->add_overwrite_rule($fieldKey, $overwrite, $type).
         *
         * @param string $fieldKey The field key.
         * @param bool $overwrite Optional boolean if values can be overwritten. Default is true.
         * @param string $type Optional string if first or last value should be overwritten. Valid options are 'first'
         * and 'last'. Default is 'first'.
         * @retun void
         */
        public function add_overwrite_rule(string $fieldKey, bool $overwrite = true, string $type = 'first'): void
        {
            $this->overwrite[$fieldKey] = [
                'overwrite' => $overwrite,
                'type' => in_array($type, ['first', 'last']) ? $type : 'first'
            ];
        }


        /**
         * Updates a relationship field. Called by 'acf/update_value'
         *
         * @param mixed $value The value to be updated.
         * @param int|string $postID The post id.
         * @param array $field The field to be processed.
         * @return mixed Returns processed value if successful.
         */
        public function update_relationship_field($value, $postID, array $field)
        {

            /* 1. get the value after all hooked function have been applied, return if no value ----------------------*/

            /* 2. get current value ----------------------------------------------------------------------------------*/
            $fieldName = $field['key'];
            $currentValue = (isset($field['parent']) && !empty($field['parent'])) ?
                maybe_unserialize(get_post_meta($postID, $field['name'], true)) :
                maybe_unserialize(get_post_meta($postID, $fieldName, true));

            /* make sure that the value is an integer array */
            if ($currentValue === '') $currentValue = [];
            if (!is_array($currentValue)) $currentValue = [$currentValue];
            $currentValue = array_map('intval', $currentValue);

            /* 3. modify new value -----------------------------------------------------------------------------------*/
            $newValue = $value;
            if (!$newValue) $newValue = [];
            if (!is_array($newValue)) $newValue = [$newValue];

            /* 4. prepare for store updated post ---------------------------------------------------------------------*/

            /* 5. remove current obsolete relationships --------------------------------------------------------------*/
            /* Loop through all current values, don't remove relationship if part of new value, otherwise get affected
            fields and prepare removal */
            if (count($currentValue))
                foreach ($currentValue as $relatedPostId)
                    if (!in_array($relatedPostId, $newValue))
                        if ($affectedFields =
                            $this->get_affected_fields(get_post_type($postID),
                                $fieldName,
                                'remove',
                                $relatedPostId))
                            foreach ($affectedFields as $affectedField)
                                $this->remove_relationship($relatedPostId, $affectedField, $postID);

            /* 6. add new relationships ------------------------------------------------------------------------------*/
            /* Loop through all new values, get affected fields and prepare insert */
            if (count($newValue))
                foreach ($newValue as $relatedPostId)
                    if ($affectedFields =
                        $this->get_affected_fields(get_post_type($postID), $fieldName, 'add', $relatedPostId))
                        foreach ($affectedFields as $affectedField)
                            $this->add_relationship($relatedPostId, $affectedField, $postID);

            /* 7. return updated value -------------------------------------------------------------------------------*/
            return $value;
        }


        /**
         * Update post by evaluating the self reference fields stored in class variable.
         *
         * @param string $postID The post id.
         * @return void
         */
        public function add_self_references(string $postID): void
        {
            foreach ($this->after_processing as $fieldID => $newValueArrays) {

                /* get current value */
                $currentValues = oes_get_field($fieldID, $postID);
                if (!is_array($currentValues)) $currentValues = [];

                /* prepare new values */
                $collectValues = [];
                foreach ($currentValues as $singleValue) $collectValues[] = strval($singleValue->ID ?? $singleValue);

                /* remove values */
                $removeValues = $newValueArrays['remove'] ?? [];
                if (!empty($removeValues)) {
                    $temp = [];
                    foreach ($collectValues as $loopValue)
                        if (!in_array($loopValue, $removeValues)) $temp[] = $loopValue;
                    $collectValues = $temp;
                }

                /* add values */
                $addValues = $newValueArrays['add'] ?? [];
                $collectValues = array_merge($collectValues, $addValues);

                /* update field */
                update_field($fieldID, $collectValues);
            }
        }


        /**
         * Returns array with post type fields that are relationship fields and match the source post type.
         *
         * @param String $sourcePostType The source post type.
         * @param String $sourceField The source field key.
         * @param String $operation The operation type. Valid values are 'add', 'remove'
         * @param mixed $value The value for the source field.
         * @param array $affectedFields Collection of affected field.
         * @param mixed $originalField The original source field.
         * @return array Returns an array of fields.
         */
        private function get_affected_fields(string $sourcePostType, string $sourceField, string $operation, $value, array $affectedFields = [], $originalField = false): array
        {

            if (!$originalField) $originalField = $sourceField;

            if ($fieldKeys = OES()->post_types[$sourcePostType]['field_options'][$sourceField]['inherit_to'] ?? false)
                if ($fieldKeys !== 'hidden' && is_array($fieldKeys))
                    foreach ($fieldKeys as $targetField) {

                        $splitValue = explode(':', $targetField);
                        if (sizeof($splitValue) > 1 && !in_array($splitValue[1], $affectedFields)) {
                            $affectedFields[] = $splitValue[1];

                            /* prepare self reference */
                            if ($splitValue[0] == $sourcePostType)
                                $this->after_processing[$splitValue[1]][$operation][] = $value;

                            /* check for inheritance from child to further children (recursive) */
                            $affectedFields = $this->get_affected_fields($splitValue[0],
                                $splitValue[1], $operation, $value, $affectedFields, $originalField);
                        }
                    }
            return $affectedFields;
        }


        /**
         * Remove a relationship from a post.
         *
         * @param int|string $targetPostId The post id to remove the relationship from.
         * @param string $fieldName The field name to be updated.
         * @param int|string $postIdToBeRemoved The post relationship to be removed.
         * @return void
         */
        private function remove_relationship($targetPostId, string $fieldName, $postIdToBeRemoved): void
        {
            /* 1. get current value of related post ------------------------------------------------------------------*/
            $field = $this->get_field($targetPostId, $fieldName);
            /* check if value is an array */
            $fieldValueIsArray = true;
            if (isset($field['type']) && $field['type'] == 'post_object' &&
                (!isset($field['multiple']) && !$field['multiple']))
                $fieldValueIsArray = false;

            /* make sure that current value is an integer array */
            $currentValues = maybe_unserialize(get_post_meta($targetPostId, $fieldName, true));
            if ($currentValues === '') $currentValues = [];
            if (!is_array($currentValues)) $currentValues = [$currentValues];
            if (!count($currentValues)) return; // nothing to delete
            $currentValues = array_map('intval', $currentValues);

            /* 2. get new value of related post and prepare for deletion ---------------------------------------------*/
            /* prepare deletion of relationship if current value is not part of the related post id */
            $newValues = [];
            foreach ($currentValues as $value) if ($value != $postIdToBeRemoved) $newValues[] = $value;

            /* make sure that value has the correct format */
            if (!count($newValues) && !$fieldValueIsArray) $newValues = '';
            elseif (!$fieldValueIsArray) $newValues = $newValues[0];
            elseif (count($newValues)) $newValues = array_map('strval', $newValues);

            /* 3. update the post metadata by deleting the relationship ----------------------------------------------*/
            update_post_meta($targetPostId, $fieldName, $newValues);
            update_post_meta($targetPostId, '_' . $fieldName, $field['key']);
        }


        /**
         * Add a new relationship to a post.
         *
         * @param int|string $targetPostId The post id to add the relationship to.
         * @param string $fieldName The field name to be updated.
         * @param int|string $postIdToBeAdded The post relationship to be added.
         * @return void
         */
        private function add_relationship($targetPostId, string $fieldName, $postIdToBeAdded): void
        {
            /* 1. get current value of related post ------------------------------------------------------------------*/
            $field = $this->get_field($targetPostId, $fieldName);
            if (!$field) return;// field not found attached to this post

            /* make sure that current value is an integer array */
            $value = maybe_unserialize(get_post_meta($targetPostId, $fieldName, true));
            if ($value == '') $value = [];
            if (!is_array($value)) $value = [$value];
            $value = array_map('intval', $value);

            /* 2. add relationship if maximum post amount not exceeded -----------------------------------------------*/
            $maxPosts = $this->get_max_post($field);
            $isArrayValue = !(($field['type'] == 'post_object' && !$field['multiple']));
            if (($maxPosts == 0 || count($value) < $maxPosts) && !in_array($postIdToBeAdded, $value)) {
                $value[] = $postIdToBeAdded;
            } /* 3. determine if relationships should be overwritten -------------------------------------------------*/
            elseif ($maxPosts > 0) {

                if (isset($this->overwrite[$fieldName]))
                    if ($this->overwrite[$fieldName]['overwrite']) {

                        /* check if first or last entry is to be removed */
                        $remove = $this->overwrite[$fieldName]['type'] == 'first' ?
                            array_shift($value) :
                            array_pop($value);

                        /* remove this relationship from post */
                        $this->remove_relationship(intval($remove), $fieldName, $targetPostId);
                        $value[] = $postIdToBeAdded;
                    }
            }

            /* make sure that current value is a string array */
            if (!$isArrayValue) $value = $value[0];
            else $value = array_map('strval', $value);

            /* 4. update the post metadata by deleting the relationship ----------------------------------------------*/
            update_post_meta($targetPostId, $fieldName, $value);
            update_post_meta($targetPostId, '_' . $fieldName, $field['key']);
        }


        /**
         * Return maximum post amount fo relationship field. The maximum post number for post objects is 1.
         *
         * @param array $field An array containing an acf field.
         * @return mixed Return maximum post number.
         */
        private function get_max_post(array $field)
        {
            if ($field['type'] == 'post_object' && !$field['multiple']) return 1;
            elseif ($field['type'] == 'relationship' && $field['max']) return $field['max'];
            return false;
        }


        /**
         * Get acf field from post with post id and field name from cache. If it does not exist, add to cache.
         *
         * @param int|string $postId The post id.
         * @param string $fieldKey The field key.
         * @return mixed Returns modified acf field as added to cache.
         */
        public function get_field($postId, string $fieldKey)
        {
            /* 1. check if field already in cache --------------------------------------------------------------------*/
            $inCache = false;
            $cache_key = 'get_field-' . $postId . '-' . $fieldKey;
            $cache = wp_cache_get($cache_key, 'acf-bi-relationships', false, $inCache);
            if ($inCache) return $cache;

            /* 2. get field from field group -------------------------------------------------------------------------*/
            /* loop through fields and check if searched field is relationship field */
            $field = false;
            foreach ($this->post_field_groups($postId) as $acfFieldGroup)
                foreach ($acfFieldGroup['fields'] as $acfField)
                    if ($acfField['key'] == $fieldKey && in_array($acfField['type'], ['relationship', 'post_object'])) {
                        $field = $acfField;
                        break 2;
                    }

            /* 3. update cache ---------------------------------------------------------------------------------------*/
            wp_cache_set($cache_key, $field, 'acf-bi-relationships');

            /* 4. return field ---------------------------------------------------------------------------------------*/
            return $field;
        }


        /**
         * Get acf field group from post with post id from cache. If it does not exist, add to cache.
         *
         * @param int|string $postId The post id.
         * @return mixed Returns modified acf field group as added to cache.
         */
        public function post_field_groups($postId)
        {
            /* 1. check if field already in cache --------------------------------------------------------------------*/
            $inCache = false;
            $cache = wp_cache_get('post_field_groups-' . $postId, 'acf-bi-relationships', false, $inCache);
            if ($inCache) return $cache;

            /* 2. prepare field group to add to cache-----------------------------------------------------------------*/
            $acfFieldGroups = acf_get_field_groups(['post_id' => $postId]);
            foreach ($acfFieldGroups as $key => $acfFieldGroup)
                $acfFieldGroups[$key]['fields'] = acf_get_fields($acfFieldGroup['key']);

            /* 3. update cache ---------------------------------------------------------------------------------------*/
            wp_cache_set('post_field_groups-' . $postId, $acfFieldGroups, 'acf-bi-relationships');

            /* 4. return field group ---------------------------------------------------------------------------------*/
            return $acfFieldGroups;
        }
    }


    /* instantiate */
    new ACF_Inheritance();

endif;