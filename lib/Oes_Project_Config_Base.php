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

abstract class Oes_Project_Config_Base {


    var $projectPluginBaseDir;

    var $websiteLanguage = false;

    /**
     * @return string
     */
    public function getWebsiteLanguage()
    {
        return $this->websiteLanguage;
    }

    /**
     * @param string $websiteLanguage
     */
    public function setWebsiteLanguage(string $websiteLanguage): void
    {
        $this->websiteLanguage = $websiteLanguage;
    }

    

    /**
     * Oes_Project_Config_Base constructor.
     * @param $projectPluginBaseDir
     */
    public function __construct($projectPluginBaseDir)
    {
        $this->projectPluginBaseDir = $projectPluginBaseDir;
    }


    abstract function getAllPostTypes();

    final function getProjectPluginBaseDir() {
        return $this->projectPluginBaseDir;
    }

    abstract function getMainHtmlAppId();

    function getZoteroApiKey() {
        throw new Exception("Zotero API key not set for this project.");
    }

    /**
     * @return Oes_CptSettingsPageDefs[]
     */
    function getCptSettingsPageDefs()
    {
        return [];
    }

}


class Oes_CptSettingsPageDefs
{

    const DEFAULT_PARENT_POST_TYPE = "page";

    var $slug, $label;
    var $parentPostType = self::DEFAULT_PARENT_POST_TYPE;
    var $menuTitle = null;

    /**
     * Oes_CptSettingsPageDefs constructor.
     * @param $id
     * @param $label
     */
    public function __construct($id, $label, $parentPostType = self::DEFAULT_PARENT_POST_TYPE, $menuTitle = null)
    {
        $this->slug = $id;
        $this->label = $label;
        $this->parentPostType = $parentPostType;
        $this->menuTitle = $menuTitle;
    }

}