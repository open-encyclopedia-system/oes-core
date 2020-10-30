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

/**
 * Class CrudSxData
 * @property $objId
 * @property $preDefinedData
 * @property $formData
 * @property $formDataFieldChoices
 * @property $dynFormId
 * @property $fromDbMappingTable
 * @property $toDbMappingTable
 * @property $crudMode
 * @property $submitBtnLabel
 * @property $dialogScreenTitle
 * @property $notificationTarget
 * @property $notificationObserverAppId
 * @property $notificationObserverFunctionName
 * @property $notificationObserverAppSessionId
 *
 *
 */
class CrudSxData extends SxData
{
    public function __construct($id)
    {
        parent::__construct($id);
    }

    /**
     * @return mixed
     */
    public function getFormDataFieldChoices()
    {
        return x_as_array($this->formDataFieldChoices);
    }

    /**
     * @param mixed $formDataFieldChoices
     */
    public function setFormDataFieldChoices(array $formDataFieldChoices): void
    {
        $this->formDataFieldChoices = $formDataFieldChoices;
    }

    /**
     * @return mixed
     */
    public function getCrudMode()
    {
        return $this->crudMode;
    }

    /**
     * @param mixed $crudMode
     */
    public function setCrudMode($crudMode): void
    {
        $this->crudMode = $crudMode;
    }



    /**
     * @return mixed
     */
    public function getNotificationTarget()
    {
        return $this->notificationTarget;
    }

    /**
     * @param mixed $notificationTarget
     */
    public function setNotificationTarget($notificationTarget): void
    {
        $this->notificationTarget = $notificationTarget;
    }


    function addFormDataKeyValue($key, $value, $fieldGroup = "dynform1")
    {

        $fieldKey = Oes_Mini_App::getPropOfFieldInFieldGroup($key, $fieldGroup);

        $this->formData[$fieldKey] = $value;

    }

    function getFormDataValue($name, $fieldGroup = "dynform1")
    {
        $fieldKey = Oes_Mini_App::getPropOfFieldInFieldGroup($name, $fieldGroup);

        return $this->formData[$fieldKey];

    }

    function getFormDataValueAsArray($name, $fieldGroup = "dynform1")
    {
        $fieldKey = Oes_Mini_App::getPropOfFieldInFieldGroup($name, $fieldGroup);

        return x_as_array($this->formData[$fieldKey]);

    }

    /**
     * @return mixed
     */
    public function getSubmitBtnLabel()
    {
        return $this->submitBtnLabel;
    }

    /**
     * @param mixed $submitBtnLabel
     */
    public function setSubmitBtnLabel($submitBtnLabel): void
    {
        $this->submitBtnLabel = $submitBtnLabel;
    }

    /**
     * @return mixed
     */
    public function getDialogScreenTitle()
    {
        return $this->dialogScreenTitle;
    }

    /**
     * @param mixed $dialogScreenTitle
     */
    public function setDialogScreenTitle($dialogScreenTitle): void
    {
        $this->dialogScreenTitle = $dialogScreenTitle;
    }

    /**
     * @return mixed
     */
    public function getNotificationObserverAppId()
    {
        return $this->notificationObserverAppId;
    }

    /**
     * @param mixed $notificationObserverAppId
     */
    public function setNotificationObserverAppId($notificationObserverAppId): void
    {
        $this->notificationObserverAppId = $notificationObserverAppId;
    }

    /**
     * @return mixed
     */
    public function getNotificationObserverFunctionName()
    {
        return $this->notificationObserverFunctionName;
    }

    /**
     * @param mixed $notificationObserverFunctionName
     */
    public function setNotificationObserverFunctionName($notificationObserverFunctionName): void
    {
        $this->notificationObserverFunctionName = $notificationObserverFunctionName;
    }

    /**
     * @return mixed
     */
    public function getNotificationObserverAppSessionId()
    {
        return $this->notificationObserverAppSessionId;
    }

    /**
     * @param mixed $notificationObserverAppSessionId
     */
    public function setNotificationObserverAppSessionId($notificationObserverAppSessionId): void
    {
        $this->notificationObserverAppSessionId = $notificationObserverAppSessionId;
    }


    /**
     * @return mixed
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * @param mixed $objId
     */
    public function setObjId($objId): void
    {
        $this->objId = $objId;
    }


    /**
     * @return mixed
     */
    public function getPreDefinedData()
    {
        return $this->preDefinedData;
    }

    /**
     * @param mixed $preDefinedData
     */
    public function setPreDefinedData($preDefinedData): void
    {
        $this->preDefinedData = $preDefinedData;
    }

    /**
     * @return mixed
     */
    public function getConvertedFormData()
    {

        $fields = Oes_Mini_App::lookupDynAcfForm();

        return Oes_Mini_App::convertKeyToFieldNamesInAcfFormData($this->formData, $fields);

    }

    /**
     * @return mixed
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * @param mixed $formData
     */
    public function setFormData($data): void
    {

        if (!is_array($data)) {
            throw new Exception("setFormData failed. empty data.");
        }

        $fields = Oes_Mini_App::lookupDynAcfForm();

        $res = [];

        foreach ($data as $key => $value) {
            Oes_Mini_App::addFieldValue($key, $value, $fields, $res);
        }

        $this->formData = $res;

    }

    /**
     * @return mixed
     */
    public function getDynFormId()
    {
        return $this->dynFormId;
    }

    /**
     * @param mixed $dynFormId
     */
    public function setDynFormId($dynFormId): void
    {
        $this->dynFormId = $dynFormId;
    }

    /**
     * @return mixed
     */
    public function getFromDbMappingTable()
    {
        return $this->fromDbMappingTable;
    }

    /**
     * @param mixed $fromDbMappingTable
     */
    public function setFromDbMappingTable($fromDbMappingTable): void
    {
        $this->fromDbMappingTable = $fromDbMappingTable;
    }

    /**
     * @return mixed
     */
    public function getToDbMappingTable()
    {
        return $this->toDbMappingTable;
    }

    /**
     * @param mixed $toDbMappingTable
     */
    public function setToDbMappingTable($toDbMappingTable): void
    {
        $this->toDbMappingTable = $toDbMappingTable;
    }


}
