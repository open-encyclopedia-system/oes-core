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

class AMS_Action
{
    var $id;
    var $params = [];
}

class AMS_Generic_Actions
{

    function addAuthor()
    {

    }

}

class AMS_DashB_Model_Dialog_State
{
    function openDialog()
    {
        $this->isOpen = true;
    }

    function closeDialog()
    {
        $this->isOpen = false;
    }


}

/**
 * Class AMS_AC_SubIssue_InviteUser_Details
 *
 * @property dtm_ams_dialog_config $createDialog
 * @property $role
 * @property $reminderDateRules
 * @property $reminderDateMessageTemplates
 *
 */
class AMS_AC_SubIssue_InviteUser_Details extends Oes_Mini_DynamicData
{

}

class AMS_AC_SubIssue_InviteUser
{
    /**
     * @var dtm_ams_issue_config
     */
    var $config;

    function start()
    {

        /**
         * @var AMS_AC_SubIssue_InviteUser_Details $details
         */
        $details = $this->config->getDetails(AMS_AC_SubIssue_InviteUser_Details::class);

        $createDialog = $details->createDialog;

        $dialogFormScreen = AMS_Rest_v1::getDialogFormScreen($createDialog->uid);

        $state->dialog = $dialogFormScreen;


    }

}

class AMS_Functions
{

    function createSubIssue()
    {

    }

}

/**
 * Class AMS_InviteAuthor_ActionController
 */

class UserTransformer
{

}

/**
 * Class User
 * @property $displayName
 * @property $firstname
 * @property $lastname
 * @property $email
 * @property $roles
 */
class User
{

}

/**
 *
 * @property $name
 * @property $email
 * @property $type TO,CC,BCC - default = TO
 */
class EmailRecipient
{

}

/**
 * Class AMS_InviteAuthor_ActionController
 * @property User $user
 * @property User[] $users
 * @property AMS_InviteAuthor_ActionController_Email $email
 */
class AMS_InviteAuthor_ActionController_Values extends Oes_Mini_DynamicData
{

}

/**
 * Class AMS_InviteAuthor_ActionController_Email
 * @property $recipients
 * @property $recipientsCC
 * @property $recipientsBCC
 * @property $subject
 * @property $body
 * @property $senderName
 */
class AMS_InviteAuthor_ActionController_Email
{

}

class AMS_InviteAuthor_ActionController extends AMS_Dialog_Base_Controller
{

    function run($action,$data)
    {

    }

    function gotoChooseUserScreen()
    {

    }

    function gotoFilloutEmailScreen()
    {

        $values = $this->values;

        $nameOfUserVar = $this->getVarName('fillOutEmailScreen.single','user');
        $nameOfUsersVar = $this->getVarName('fillOutEmailScreen.multiplea','users');


        $users = $values->{$nameOfUsersVar};

        foreach ($users as $user) {
            $recipient = new EmailRecipient();
            $recipient->name = $user->displayName;
            $recipient->email = $user->email;
            $recipient->type = 'TO';
        }

        //

        $idOfEmailTemplate = $this->getVarName('idOfEmailTemplate.single','idOfEmailTemplate');

        $emailTemplate = $this->lookupEmailTemplate($idOfEmailTemplate);

        $variableIds = $emailTemplate.getVariableIds();

        // zuerst holen wir uns die werte aus den formular werten

        // wir haben drei ValuesResolvers

        $variableHolder = new VariableHolder();

        $variableHolder->addVariableIds($variableIds);

        foreach ($variableHolder->listOpenVariables() as $varId)
        {

            $hasValue = $resolver->hasValueFor($varId);

            if ($hasValue) {
                continue;
            }

            $value = $resolver->getValueFor($varId);

            $variableHolder->setValueFor($varId,$value);

        }

        // danach aus dem issue
        // und was dann übrig geblieben ist, aus den projekteinstellungen

        $emailTemplate->substituteVariablesWithValue($variableHolder);

        // return variables from email templates

        $values->email->body = $emailTemplate->body;
        $values->email->subject = $emailTemplate->subject;

        $exchange->output->values = $values;

        return $formWithValues;


    }

    /**
     * Die Nutzerin hat in S1 einen User ausgewählt.
     */
    function fromStep1ToStep2()
    {



    }

    function fromStep2ToStep3()
    {

    }

    /**
     *
     */
    function fromStep3ToStep4()
    {

        // arbeite funktionen ab, die dieser transition zugeordnet sind



    }

}