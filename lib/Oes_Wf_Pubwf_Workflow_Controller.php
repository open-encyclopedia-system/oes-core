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

class Oes_Wf_Pubwf_Workflow_Controller extends Oes_Wf_Controller_Base
{

    use Workflow2_Controller;

    /**
     * @var Oes_User
     */
    var $user;

    /**
     * @var dtm_1418_pubwf_workflow_base
     */
    var $dtm;

    /**
     * Oes_Wf_Pubwf_Workflow_Controller constructor.
     * @param $dtm
     */
    public function __construct($dtm, $userid = null)
    {
        $this->dtm = $dtm;
        $this->user = Oes_User::init($userid);
    }


    function reloadDtm()
    {
        $this->dtm = dtm_1418_pubwf_workflow_base::init($this->dtm->ID);
    }

    static function load($id)
    {
        $dtm = dtm_1418_pubwf_workflow_base::init($id);
        return new Oes_Wf_Pubwf_Workflow_Controller($dtm);
    }

    static function createAndPopulate($articleid = null)
    {

        $workflow = dtm_1418_pubwf_workflow_base::create();

        $wf_article = dtm_1418_pubwf_article_base::create();

        if ($articleid) {

            $article = dtm_1418_article_base::init($articleid);

            $wf_article->title = $article->title;
            $wf_article->tax_regions = $article->tax_regions__ids;
            $wf_article->tax_themes = $article->tax_themes__ids;
            $wf_article->outline = $article->outline;
            $wf_article->article_type = $article->article_type;
            $wf_article->max_wordcount = $article->wordcount;
            $wf_article->article_classification_group = $article->u_article_classification_group;
            $wf_article->parent_regional_survey_article = $article->parent_regional_survey_article__ids;
            $wf_article->parent_thematic_survey_article = $article->parent_thematic_survey_article__ids;

            $wf_article->duedate = strtotime('+6MONTHS', time());

            $wf_article->article_section_editor = $article->article_section_editor__ids;
            $wf_article->article_discussion_moderator = $article->article_discussion_moderator__ids;

        }

        $wf_article->save();

        $workflow->article = $wf_article->ID;

        $workflow->eo_article = $article->ID;

        $workflow->save(true);

        return new Oes_Wf_Pubwf_Workflow_Controller($workflow);

    }


    function & add_author($firstname, $lastname, $email, $role_type, $roles_group)
    {
        $role = dtm_1418_pubwf_role_base::create();

        $role->role_type = $role_type;

        $role->firstname = $firstname;
        $role->lastname = $lastname;
        $role->email = $email;

        $role->save();

        /**
         * @var dtm_1418_pubwf_rolesgroup_base $rolesgroup
         */
        $rolesgroup = $this->dtm->{$roles_group . "__obj"};

        $roles = $rolesgroup->roles__array;

        $roles[] = $role->ID;

        $rolesgroup->roles = $roles;

        $rolesgroup->save();

        return $role;

    }

    /**
     * @param dtm_1418_pubwf_role_base $role
     * @param $duedate
     * @param $reminder1
     * @param $reminder2
     * @param $reminder3
     * @return dtm_1418_pubwf_request_base
     * @throws Exception
     */
    function & invite_role(&$role, $duedate, $reminder1, $reminder2, $reminder3)
    {

        $role->needs_invitation = true;

        $role->save();

        //

        $inv = dtm_1418_pubwf_request_base::create();

//        $inv->invitation_parent = $role->ID;

        $inv->workflow_parent = $this->dtm->ID;

        $inv->wf_status_user_change = dtm_1418_pubwf_request_base::state_pending;

        $inv->recipient_name = trim($role->firstname . " " . $role->lastname);

        $inv->recipient_address = $role->email;

        $inv->save();

        $dd = dtm_1418_pubwf_duedate_base::create();

        $dd->duedate = $duedate;
        $dd->reminder_1 = $reminder1;
        $dd->reminder_2 = $reminder2;
        $dd->reminder_3 = $reminder3;
        $dd->parent = $inv->ID;

        $dd->save();

        return $inv;

    }

    function change_state($state)
    {
        $this->dtm->wf_status_user_change = $state;
        $this->dtm->save();
    }

    function assignTo($profileid)
    {
        $this->dtm->assignee = $profileid;
    }

    function assignManagingEditor($profileid)
    {
        $this->dtm->managing_editor = $profileid;
    }

    function assignProxyManagingEditor($profileid)
    {
        $this->dtm->proxy_managing_editor = $profileid;
    }

    function assignToMe()
    {

        $this->assignTo($this->user->profile_id);

        $managingeditor = $this->dtm->managing_editor__id;

        // condition 1: managing editor is not set
        if (empty($managingeditor)) {
            if ($this->user->has_role(Oes_General_Config::CONTRIBUTOR_ROLE_MANAGING_EDITOR)) {
                $this->assignManagingEditor($this->user->profile_id);
            }
        }

        $this->dtm->editor = $this->user->profile_id;

        $this->dtm->save();

    }

    function save()
    {
        $this->dtm->save();
    }

    function canAssignToMe()
    {

        $profileid = $this->user->profile_id;

        $isManagingEditor = $this->user->has_role([Oes_General_Config::CONTRIBUTOR_ROLE_MANAGING_EDITOR]);

        $isEditorialOffice = $this->user->has_role(Oes_General_Config::CONTRIBUTOR_ROLE_EDITORIAL_OFFICE);

        $hasProperRoles = $isEditorialOffice || $isManagingEditor;

        if (!$hasProperRoles) {
            return false;
        }

        $assignee = $this->dtm->editor__id;

        $managingeditor = $this->dtm->managing_editor__id;

        if (!empty($assignee)) {

            // editorial office can always assign to
            if ($isEditorialOffice) {
                return true;
            }


            // only if it's the managing editor of this workflow
            if ($isManagingEditor) {
                return $profileid == $managingeditor;
            }

        }

        // editorial office can always assign to
        if ($isEditorialOffice) {
            return true;
        }

        if ($isManagingEditor) {

            if (!empty($managingeditor)) {
                return $profileid == $managingeditor;
            }

            return true;

        }

        return false;


    }

    function canInviteProxyManagingEditor()
    {

        $managingeditor = $this->dtm->managing_editor__id;

        $cond1 =
            $this->user->has_role(Oes_General_Config::CONTRIBUTOR_ROLE_EDITORIAL_OFFICE);

        $cond2 = !empty($managingeditor) &&
            $managingeditor == $this->user->profile_id;

        return $cond1 || $cond2;
    }

    function isAssignedToMe()
    {
        return $this->dtm->editor__id == $this->user->profile_id;
    }

    function isCurrentManagingEditor()
    {
        $profileid = $this->user->profile_id;
        $managingeditor = $this->dtm->managing_editor__id;
        return $profileid == $managingeditor;
    }

    function hasEditorialOfficeRole()
    {
        return $this->user->has_role(Oes_General_Config::CONTRIBUTOR_ROLE_EDITORIAL_OFFICE);
    }

    function canAssignToProxyManagingEditor()
    {

        $profileid = $this->user->profile_id;

        $managingeditor = $this->dtm->managing_editor__id;

        $pme = $this->dtm->proxy_managing_editor__id;

        $hasPme = !empty($pme);

        if (!$hasPme) {
            return false;
        }

        $assignee = $this->dtm->editor__id;

        if ($this->isCurrentManagingEditor()) {
            return $assignee != $pme;
        } else if ($this->hasEditorialOfficeRole()) {
            return $assignee != $pme;
        }

        return false;

    }

    function createInvitation($type, $payload, $name, $email, $duedate,
                              $reminder1, $reminder2)
    {

        $inv = dtm_1418_pubwf_request_base::create();

        $inv->workflow_parent = $this->dtm->ID;

        $inv->wf_status_user_change = dtm_1418_pubwf_request_base::state_pending;

        $inv->recipient_name = $name;

        $inv->recipient_address = $email;

        $inv->type_of_request = $type;

        $inv->payload = $payload;

        $inv->save();

        $dd = dtm_1418_pubwf_duedate_base::create();

        $dd->duedate = $duedate;
        $dd->reminder_1 = $reminder1;
        $dd->reminder_2 = $reminder2;
        $dd->parent = $inv->ID;

        $dd->save();

    }

    function getInvitations($type, $phase)
    {

        $invs = x_filter_array_by_property($this->dtm->invitations__objs, dtm_1418_pubwf_request_base::attr_type_of_request, $type);

        $res = [];
        foreach ($invs as $inv) {
            $wf_status = $inv->wf_status;
            if (in_array($wf_status, $phase)) {
                $res[] = $inv;
            }
        }

        return $res;

    }

    function getRunningAuthorInvitations()
    {
        return $this->getInvitations(Oes_AMW_General::INVITATION_TYPE_INVITE_AUTHOR, dtm_1418_pubwf_request_base::phase_pending);
    }


    /**
     * @param dtm_1418_pubwf_request_base $inv
     */
    function addAuthorAfterInvitation($inv)
    {

        $data = x_safe_json_decode($inv->payload, true);

        $contributor = dtm_1418_contributor_base::create();
        $contributor->profile_type = Oes_General_Config::CONTRIBUTOR_PROFILE_TYPE_PERSONAL;
        $contributor->firstname = $data['firstname'];
        $contributor->lastname = $data['lastname'];
        $contributor->email = $data['email'];
        $contributor->status = Oes_General_Config::STATUS_IN_PREPARATION;
        $contributor->roles = [Oes_General_Config::CONTRIBUTOR_ROLE_AUTHOR];
        $contributor->save();

        $authors = $this->dtm->authors__array;
        $authors[] = $contributor->ID;

        $this->dtm->authors = $authors;

        $this->dtm->save();

    }

    /**
     * @param $fileFormData
     * @return dtm_1418_pubwf_file_base|mixed
     * @throws Exception
     */
    function & uploadFileFromDialog($fileFormData)
    {

        /**
         * Array
         * (
         * [file] => Array
         * (
         * [name] => [Aaron_Bastani]_Fully_Automated_Luxury_Communism__(z-lib.org).pdf
         * [type] => application/pdf
         * [tmp_name] => /private/var/tmp/phpINaYZF
         * [error] => 0
         * [size] => 891965
         * [created] => 1561479358
         * [mimetype] => application/pdf
         * [filename] => 74d760e6b634982b0c0e4dad03278e51_[Aaron_Bastani]_Fully_Automated_Luxury_Communism__(z-lib.org).pdf
         * [id] => 74d760e6b634982b0c0e4dad03278e51
         * )
         *
         * [notes] =>
         * )
         */

        $savedFiles = [];

        try {

            $fileInfoId = $fileFormData['id'];

            Oes_Mini_App::getTempFileInfo($fileInfoId);

            $filename = $fileFormData['filename'];

            $filePath = Oes_Mini_App::getTempUploadDirPath($filename);

            $destinationFilePath = $this->getUploadDir($filename);

            $savedFiles[] = $destinationFilePath;

            copy($filePath, $destinationFilePath);

            $fileFormData['pubwf.id'] = $this->dtm->ID;
            $fileFormData['pubwf.file.type'] = $type;

            $jsonFilePath = $this->getUploadDir($fileInfoId . '.json');

            $savedFiles[] = $jsonFilePath;

            file_put_contents($jsonFilePath, json_encode($fileFormData));

            //

            $file = dtm_1418_pubwf_file_base::create();

            $file->mimetype = $fileFormData['mimetype'];
            $file->type = $fileFormData['type'];
            $file->type_label = $fileFormData['type_label'];
            $file->sender_name = $fileFormData['sender_name'];
            $file->sender_profile_id = $fileFormData['sender_profile_id'];
            $file->sender_email = $fileFormData['sender_email'];
            $file->sender_message = $fileFormData['sender_message'];
            $file->sender_role = $fileFormData['sender_role'];
            $file->notes = $fileFormData['notes'];
            $file->filesize = $fileFormData['size'];
            $file->fileinfo_id = $fileInfoId;
            $file->filename = $fileFormData['name'];
            $file->workflow_parent = $this->dtm->ID;
            $file->wf_status_user_change = Pubwf_File_Transformer::state_uploaded;
            $file->language = $fileFormData['language'];
            $file->is_revised_version = $fileFormData['is_revised_version'];
            $file->phase_id = $fileFormData['phase_id'];

            $file->save();

            $this->dtm->manuscript_most_recent = $file->ID;

            $this->dtm->save();

            return $file;

        } catch (Exception $e) {
            $this->dtm->manuscript_most_recent = null;
            $this->dtm->save();
            foreach ($savedFiles as $file) {
                unlink($file);
            }
        }


//        wp_get_upload_dir()

    }

    function getUploadDir($filename = '')
    {
        static $dir;
        if ($dir) {
            return $dir . DIRECTORY_SEPARATOR . $filename;
        }
        $uploadDir = wp_get_upload_dir();
        $dir = $uploadDir['basedir'] . DIRECTORY_SEPARATOR . '/ams/' . $this->dtm->ID . '/';
        @mkdir($dir, 0777, true);
        return $dir . DIRECTORY_SEPARATOR . $filename;
    }


    /**
     * @param $referee
     * @param bool $assigned
     * @return dtm_1418_pubwf_reviewreport_base
     * @throws Exception
     */
    function addReviewReport($referee, $assigned = false)
    {
        throw new Exception("addReviewReport");

        $report = dtm_1418_pubwf_reviewreport_base::create();
        $report->referee = $referee;
        if ($assigned) {
            $report->wf_status = Pubwf_ReviewReport_Transformer::state_assigned;
        }
        $report->resolution = Oes_AMW_General::AMW_REVIEW_RESOLUTION_PENDING;
        $report->post_title = 'Review report: ' . $referee->name . ' / ' . $this->dtm->post_title;
        $report->save();
        return $report;
    }

    function & startInitialCheck()
    {

        /**
         * @var dtm_1418_pubwf_inicheck_base $prev_inicheck
         */
        $prev_inicheck = $this->dtm->initialcheck_most_recent__obj_no_exception;

        $inicheck = dtm_1418_pubwf_inicheck_base::create();
        $inicheck->workflow_parent = $this->dtm->ID;
        $inicheck->wf_status_user_change = Pubwf_IniCheck_Transformer::state_open;

        $inicheck->post_title = 'Initial check: ' . $this->dtm->post_title . ' ' . date('Y-m-d H:i');

        if ($prev_inicheck) {
            $inicheck->payload = $prev_inicheck->payload;
            $revision_no = $prev_inicheck->revision_no;
            $revision_no++;
            $inicheck->revision_no = $revision_no;
            $inicheck->prev_resolution = $prev_inicheck->resolution;
            $inicheck->prev_inicheck = $prev_inicheck->ID;
        } else {
            $inicheck->revision_no = "1";
        }

        $inicheck->post_title = 'Initial check: ' . $this->dtm->post_title . ' (' . $inicheck->revision_no . ') ' . date('Y-m-d H:i');

        $inicheck->save();

        $this->dtm->initialcheck_most_recent = $inicheck->ID;

        $this->dtm->save();

    }

    function clearUploaded1Draft()
    {
        $this->dtm->manuscript_most_recent = null;
        $this->dtm->save();
    }

}


trait Workflow2_Controller
{


    /**
     * @param $duedate
     * @param $reminders
     * @return dtm_1418_pubwf_duedate_base
     * @throws Exception
     */
    function create_duedate($duedate, $reminders)
    {
        $dtm = dtm_1418_pubwf_duedate_base::create();
        $dtm->duedate = $duedate;
        $pos = 0;
        foreach ($reminders as $rem) {
            $pos++;
            $dtm->{"reminder_" . $pos} = $rem;
        }

        $dtm->workflow_parent = $this->dtm->ID;

        $dtm->save();

        return $dtm;

    }

    /**
     * @param $type
     * @param $recip_name
     * @param $recip_email
     * @param $due_date
     * @param $reminder_dates
     * @return dtm_1418_pubwf_request_base
     * @throws Exception
     */
    function create_request($type, $recip_name, $firstname, $lastname, $recip_email, $due_date, $reminder_dates)
    {

        $req = dtm_1418_pubwf_request_base::create();
        $req->type = $type;
        $req->recipient_name = $recip_name;
        $req->recipient_email = $recip_email;
        $req->recipient_firstname = $firstname;
        $req->recipient_lastname = $lastname;

        $duedate_dtm = $this->create_duedate($due_date, $reminder_dates);

        $req->due_date = $duedate_dtm->ID;

        $req->wf_status_user_change = dtm_1418_pubwf_request_base::state_pending;

        $req->workflow_parent = $this->dtm->ID;

        $req->save();

        return $req;

    }


    function start_process_peer_review()
    {

    }

    function start_process_initial_check()
    {

    }

    function update_status_initial_check()
    {

    }

    function update_status_peer_review()
    {

    }

    function update_status_request()
    {

    }

    function upload_file($type, $filepath)
    {

    }


}