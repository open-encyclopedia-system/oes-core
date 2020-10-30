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

class Oes_DtmClassBuilder
{

    /**
     * @var Oes_ClassDefinition
     */
    var $classDef;

    function createInterface($formName, $formDef)
    {

        ?>

        /**
        *
        <?php

        $varToPostTypesMapping = [];
        $varToPostTypesMapping2 = [];

        foreach ($fields as $fieldKey => $fieldData) {

            if (array_key_exists('name', $fieldData)) {
                $fieldKey = $fieldData['name'];
            } else if (array_key_exists('key', $fieldData)) {
                $fieldKey = $fieldData['key'];
            }

            $fieldDataType = $fieldData['type'];

            if ($fieldDataType == 'tab') {
                continue;
            }

            if ($fieldDataType == 'taxonomy') {

                $taxonomy = $fieldData['taxonomy'];

                if (!array_key_exists('multiple', $fieldData)) {
                    $fieldData['multiple'] = 1;
                }

                if (empty($taxonomy)) {
                    throw new Exception("taxonomy missing $fieldKey/$formName");
                }

                $taxonomyByFieldName[$formClass][$fieldKey] =
                    $fieldData;

            }

            $isRelationship = in_array($fieldDataType, Oes_General_Config::LIST_OF_ACF_RELATIONSHIP_TYPES);


            if ($isRelationship) {

                $postTypes = $fieldData['post_type'];

                if ($fieldDataType == 'gallery') {
                    $postTypes = ['attachment'];
                } else if ($fieldDataType == 'file') {
                    $postTypes = ['attachment'];
                } else if ($fieldDataType == 'image') {
                    $postTypes = ['attachment'];
                }

                $remoteFieldKey = $fieldData['remote_name'];

                $fieldKey2 = $fieldKey;

                if (!empty($remoteFieldKey)) {
                    $fieldKey2 = $fieldKey . '#' . $remoteFieldKey;
                }

                $varToPostTypesMapping2[$fieldKey2] = $postTypes;
                $varToPostTypesMapping[$fieldKey] = $postTypes;

            }


            ?>
            * @property $<?php echo $fieldKey, "\n"; ?>
            * @property $<?php echo $fieldKey, "__html\n"; ?>
            * @property $<?php echo $fieldKey, "__float\n"; ?>
            * @property $<?php echo $fieldKey, "__int\n"; ?>
            * @property $<?php echo $fieldKey, "__id\n"; ?>
            * @property $<?php echo $fieldKey, "__ids\n"; ?>
            * @property $<?php echo $fieldKey, "__objs\n"; ?>
            * @property $<?php echo $fieldKey, "__obj\n"; ?>
            * @property $<?php echo $fieldKey, "__terms\n"; ?>
            * @property $<?php echo $fieldKey, "__term\n"; ?>
            * @property $<?php echo $fieldKey, "__array\n"; ?>
            *
            <?php

        }

        $relationshipsByPostType[$formClass][] = $varToPostTypesMapping2;

        ?>
        * @property ID
        * @property post_title
        * @property post_excerpt
        * @property post_content
        * @property post_status
        * @property post_date
        * @property post_date_gmt
        * @property comment_status
        * @property post_name
        */

        <?php

        ?>

        interface <?php echo $formName; ?> {

        <?php

        foreach ($fields as $fieldKey => $fieldData) {

            if (array_key_exists('name', $fieldData)) {
                $fieldKey = $fieldData['name'];
            } else if (array_key_exists('key', $fieldData)) {
                $fieldKey = $fieldData['key'];
            }

            $type = $fieldData['type'];

            if ($type == 'tab') {
                continue;
            }

            ?>
            const attr_<?php echo $fieldKey, " = \"$fieldKey\";\n"; ?><?php

        }

        ?>


        } /* end of interface <?php echo $formName; ?>

        <?php

    }


}



