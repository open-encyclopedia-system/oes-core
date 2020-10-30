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

class Oes_DtmInterface
{

    var $fields;

    var $formClass;

    var $formName;

    var $formDef;

    var $taxonomyByFieldName = [];

    /**
     * @var Oes_ClassDefinition
     */
    var $interfaceDef;

    /**
     * Oes_DtmInterface constructor.
     * @param $fields
     * @param $formClass
     * @param $formName
     * @param $formDef
     */
    public function __construct($fields, $formClass, $formName, $formDef)
    {
        $this->fields = $fields;
        $this->formClass = $formClass;
        $this->formName = $formName;
        $this->formDef = $formDef;
    }


    /**
     * @return Oes_ClassDefinition
     */
    function createInterface()
    {

        ob_start();

        ?>

        /**
        *
        <?php

        $fieldKeys = [];

        foreach ($this->fields as $fieldKey => $fieldData) {

            $fieldDataType = $fieldData['type'];

            if ($fieldDataType == 'tab') {
                continue;
            }

            $fieldName = $fieldData['name'];

            if (!empty($fieldName)) {
                $fieldKey = $fieldName;
            } else if (is_numeric($fieldKey)) {
                $fieldKey = $fieldData['key'];
            }

//            else if (array_key_exists('key', $fieldData)) {
//                $fieldKey = $fieldData['key'];
//            }



            if (empty($fieldKey)) {
                print_r($fieldData);
                throw new Exception("$fieldKey / $fieldName $this->formName ");
            }
            $fieldKeys[] = $fieldKey;

        }


        foreach ($fieldKeys as $fieldKey) {

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
        */<?php

        $bodyCommentBlock = ob_get_clean();

        $interf = new Oes_ClassDefinition();

        $interf->setIsInterfaceDefinition(true);

        $interf->setNameOfClass($this->formName);

        $interf->setClassCommentBlock($bodyCommentBlock);

        foreach ($fieldKeys as $fieldKey) {

            $attrvalue = var_export($fieldKey, true);

            $interf->addConstant("attr_" . $fieldKey, $attrvalue);

        }

        return $interf;

    }


}
