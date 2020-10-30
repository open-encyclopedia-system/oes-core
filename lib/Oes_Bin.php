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

class Oes_Bin {

    static function generateDtmClasses($outputDir)
    {

        foreach (Oes_General_Config::$postTypeConfigFiles as $configFile)
        {
            include ($configFile);

            $formName = $config[Oes_General_Config::PT_CONFIG_ATTR_LABELS][Oes_General_Config::PT_CONFIG_ATTR_LABELS_SINGULAR];

            $formName = normalizeToSimpleSortAscii($formName);

            $classes[$formName] = [
                'post_type' => $config['post_type'],
                'form' => $configFile,
                'class' => $config['dtm_class'],
            ];
        }

        try {

            $builder = new Oes_DtmClassBuilder_Factory($classes,$outputDir);

            $builder->generateDtmClasses();

            $builder->addTransformerRelatedStuff($outputDir);

            $builder->exportInterfacesAndSkelClassesToDir($outputDir);

            $builder->exportDtmClassesToDir($outputDir);

        } catch (\Exception $e) {
            throw $e;
        }
    }

}