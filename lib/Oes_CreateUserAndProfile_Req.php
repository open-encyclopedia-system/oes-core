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

class Oes_CreateUserAndProfile_Req {

    var $in_pwd;
    var $in_email;
    var $in_username;
    var $in_locale = 'en';
    var $in_role = 'subscriber';
    var $in_firstName, $in_lastName, $in_displayName, $in_nickName, $in_userNicename;
    var $in_showAdminBarFront = false;

    var $userCreateCallback = null;

    function setCallbackOnUserCreate($callback)
    {
        $this->userCreateCallback = $callback;
    }

    function createUserAndProfile()
    {

        if ($this->userCreateCallback) {
            add_action('user_register',$this->userCreateCallback);
        }

        $userid = wp_insert_user([
            'user_pass' => $this->in_pwd,
            'user_login' => $this->in_username,
            'user_email' => $this->in_email,
            'locale' => $this->in_locale,
            'role' => $this->in_role,
            'show_admin_bar_front' => $this->in_showAdminBarFront?'true':'false',
            'use_ssl' => true,
            'user_nicename' => $this->in_userNicename,
            'nickname' => $this->in_nickName,
            'first_name' => $this->in_firstName,
            'last_name' => $this->in_lastName,
            'display_name' => $this->in_displayName]);

        $this->out_userid = $userid;

        if ($this->userCreateCallback) {
            remove_action('user_register',$this->userCreateCallback);
        }

        return $userid;

    }


}