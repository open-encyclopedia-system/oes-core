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

class Oes_Posts_Importer
{

    var $createnewposts = false;

    var $oldIdToNewIdMatching = [];

    var $missingPostParents = [];

    var $postDataRecords;

    /**
     * PostImporter constructor.
     * @param bool $createnewposts
     * @param $postDataRecords
     */
    public function __construct($postDataRecords, $createnewposts, $postid = null)
    {
        $this->createnewposts = $createnewposts;
        $this->postDataRecords = $postDataRecords;
        $this->selected_postid = $postid;

        /*
         * 
         */
        add_filter('acf/allow_unfiltered_html', function($allow_unfiltered_html) {
            return true;
        });

        $this->loadAllPostIds();
    }

    function loadAllPostIds()
    {
        $args1 = array(
            'post_type' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );
        $query = new WP_Query($args1);

        $posts = $query->posts;

        foreach ($posts as $pid) {
            $this->oldIdToNewIdMatching[$pid] = $pid;
        }

    }

    function import()
    {

        $total = count($this->postDataRecords);

        $list = ($this->postDataRecords);

        foreach ($list as $postid => $postRecord) {

//            echo "importing $postid\n";

            if ($this->selected_postid) {
                if ($postid != $this->selected_postid) {
//                    echo "skipping $this->selected_postid $postid\n";
                    continue;
                }
            }

            $post = $postRecord['post'];

            if ($this->exists($post)) {
//                echo "exists $postid\n";
                continue;
            }

            $count++;

            $metaRecords = $postRecord['meta'];
            $acfRecords = $postRecord['acf'];
            $acfRecordsRaw = $postRecord['acf_raw'];

            $postParentId = $post['post_parent'];

            $oldId = $postid;

            $newPostParentId = false;

            if ($postParentId) {
                if (!array_key_exists($postParentId, $this->oldIdToNewIdMatching)) {
                    echo "not importing due to parent is missing $postParentId\n";
                    $this->missingPostParents[$postParentId] = $postid;
                    continue;
                } else {
                    $newPostParentId = $this->oldIdToNewIdMatching[$postParentId];
                }
            }


            $insertargs = $post;

            if (!$this->createnewposts) {
                $insertargs['import_id'] = $post['ID'];
            }

            unset($insertargs['ID']);
            unset($insertargs['guid']);

//                if ($postParentId) {
//                    if ($newPostParentId) {
//                        $insertargs['post_parent'] = $newPostParentId;
//                    }
//                }

            $oldId = $postid;
            $postid = wp_insert_post($insertargs);
            $this->oldIdToNewIdMatching[$oldId] = $postid;

            unset ($this->missingPostParents[$oldId]);

//            Oe"imported $postid\n";

            unset ($this->missingPostParents[$postid]);

            $dtm = oes_dtm_form::init($postid);

            $dtmclass = get_class($dtm);
//
            $dtmFields = $dtmclass::$ACF_FIELDS;
//
//            foreach ($dtmFields as $dtmField)
//            {
//                $dtmFieldType = $dtmField['type'];
//                $dtmFieldName = $dtmField['name'];
//                if ($dtmFieldType == 'date_picker') {
//                    $dtmVal = $acfRecords[$dtmFieldName];
//                    if ($dtmVal) {
//                        echo $dtmFieldName, ":", $acfRecords[$dtmFieldName], "\n";
//                        $dtmDateTime = DateTime::createFromFormat('Ymd', $dtmVal);
//                        if ($dtmDateTime) {
//                            echo date("r", $dtmDateTime->getTimestamp()), "\n";
//                        } else {
//                            echo "invalid date value $dtmVal\n";
//                        }
//                    }
//                } else if ($dtmFieldType == 'relationship') {
//                    print_r($acfRecords[$dtmFieldName]);
//                } else if ($dtmFieldType == 'post_object') {
//                    print_r($acfRecords[$dtmFieldName]);
//                }
//            }
//
            if (is_array($acfRecords)) {

                foreach ($acfRecords as $key => $val) {
                    if (!array_key_exists($key, $dtmFields)) {
                        echo "not found field: $key\n";
                        continue;
                    }
                    // check if we have a date_picker or date_time_picker field here

                    $dtmField = $dtmFields[$key];
                    $dtmFieldType = $dtmField['type'];
                    if ($dtmFieldType == 'date_picker' || $dtmFieldType == 'date_time_picker') {
                        if (!empty($val)) {
                            if (preg_match('@[\./]@', $val)) {
                                $origval = $val;
                                $val = strtotime($val);
//                                Oes::debug("converted date/time value from $origval to $val");
                            }
                        }
                    }

                    

                    $dtm->{$key} = $val;
                    
                }

                $dtm->x_created = strtotime($post['post_date']);

                $dtm->save();

            }

            if ($dtm->post_type == 'attachment') {


                foreach ($metaRecords as $metakey => $metavalues) {

                    if (!startswith($metakey, "_wp_")) {
                        continue;
                    }

                    foreach ($metavalues as $metaval) {
                        $metaval = $metaval_orig = $metaval;
                        $metaval = maybe_unserialize($metaval);
//                        echo "updating: $metakey -> " . $metaval_orig . "\n";
//                        echo "updating: $metakey -> " . print_r($metaval, true) . "\n";
                        update_post_meta($postid, $metakey, $metaval);
                    }
                }

            }

            Oes::debug ("imported: $postid $count/$total");

            unset ($this->postDataRecords[$oldId]);

            if ($count > 100) {
//                break;
            }

            if ($this->selected_postid) {
                break;
            }
        }


        if (!empty($this->missingPostParents)) {
            echo "has missing post parents\n";
            print_r($this->missingPostParents);
        } else {
//            echo "has no missing post parents\n";
        }

    }

    function exists($post)
    {

        static $cache = [];

        $postType = $post['post_type'];

        $cached = $cache[$postType];

        $id = $post['ID'];

        if (!$cached) {
            $cached = x_values_as_keys(oes_wp_query_post_ids($postType));
            $cache[$postType] = $cached;
        }

        return array_key_exists($id, $cached);

    }


}
