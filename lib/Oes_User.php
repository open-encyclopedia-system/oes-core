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

class Oes_User
{

    /**
     * Oes_User constructor.
     * @param $userid
     */
    public function __construct($userid)
    {
        $this->userid = $userid;
        $this->userdata = get_userdata($userid);
        $this->user = new Oes_Mini_DynamicData(get_fields("user_$userid"));
        $this->user_profiles = x_as_array($this->user->user_profiles);
        $this->user_profile_ids = oes_get_ids_of_posts(x_as_array($this->user->user_profiles));


        $this->isadmin = oes_is_current_user_admin($this->userdata);
    }

    var $userid;
    var $userdata;
    var $user;
    var $profile_id;
    /**
     * @var dtm_1418_contributor_base
     */
    var $profile;

    function is_author_of_article($article)
    {

        if (!($article instanceof dtm_1418_article_version_base)) {
            $article = dtm_1418_article_version_base::init($article);
        }

        foreach ($article->article_author__ids as $id) {
            if (in_array($id, $this->user_profile_ids)) {
                return true;
            }
        }

        return false;

    }

    /**
     * @param WP_Comment $comment
     */
    function can_approve_comment($comment)
    {
        if ($this->isadmin) {
            return true;
        }
        return $this->is_author_of_article($comment->comment_post_ID);
    }

    /**
     * @param WP_Comment $comment
     */
    function can_add_comment($postid)
    {
        return true;
//        return comments_open($postid);
    }

    /**
     * @param WP_Comment $comment
     */
    function can_reply_comment($comment)
    {

        if ($this->isadmin) {
            return true;
        }

        if (!$comment->comment_approved) {
            return false;
        }

        return true;

    }

    /**
     * @param WP_Comment $comment
     */
    function can_delete_comment($comment)
    {
        if ($this->isadmin) {
            return true;
        }
        $com = dtm_1418_comment_base::init($comment->comment_ID);
        return in_array($com->creatorprofile__id, $this->user_profile_ids);
    }

    /**
     * @param WP_Comment $comment
     */
    function can_reject_comment($comment)
    {
        if ($this->isadmin) {
            return true;
        }
        return $this->is_author_of_article($comment->comment_post_ID);
    }

    /**
     * @param null $userid
     * @return Oes_User
     * @throws Exception
     */
    static function init($userid = null)
    {

        static $cache = [];

        $iscurrent = false;

        if (!$userid) {
            if (is_user_logged_in()) {
                $iscurrent = true;
                $userid = get_current_user_id();
            } else {
                throw new Exception("user is not logged in");
            }
        }

        if (array_key_exists($userid, $cache)) {
            return $cache[$userid];
        }

        $user = new Oes_User($userid);

        if ($iscurrent) {
            $user->profile_id = oes_get_current_profile_id();
            $user->profile = dtm_1418_contributor_base::init(oes_get_current_profile_id());
        }


        $cache[$userid] = $user;

        return $user;

    }

    static function getDisplayName($userid = null)
    {
        $user = self::init($userid);
        return $user->profile->u_name;
    }

    function has_role($role)
    {
        if (!is_array($role)) {
            $roles = [$role];
        } else {
            $roles = $role;
        }

        $cur_roles = $this->profile->roles__array;

        $ret = array_intersect($roles, $cur_roles);

        return !empty($ret);
        
    }

    static function signIn($username, $password, &$errors)
    {

        if (!isset($errors)) {
            $errors = [];
        }

        if (empty($password)) {
            $errors[] = 'Password is empty.';
        }

        if (empty($username)) {
            $errors[] = 'Username is empty.';
        }


        $credentials = [
            'user_login' => $username,
            'user_password' => $password
        ];

        $user = wp_signon($credentials);

        if (empty($user) || is_wp_error($user)) {
            /**
             * @var WP_Error $wperr
             */
            $wperr = $user;
            $errors[] = "Sign-in failed. Incorrect username/password."; // . $wperr->get_error_message();

        } else {

            $userID = $user->ID;

            wp_set_current_user($userID, $username);
            wp_set_auth_cookie($userID, true);

            do_action('wp_login', $username);

//            $errors[] = print_r($user, true);

            if (!is_user_logged_in()) {
                $errors[] = "Still not logged in.";
            }

        }


        if (!empty($errors)) {
            error_log("signin failed " . print_r($errors, true));
            return false;
        }

        return true;

    }

    static function setCurrentUser($id)
    {
        $user = dtm_user::init_user($id);
        wp_set_current_user($id, $user->display_name);
    }

    static function setCurrentUserDirect($id,$name)
    {
        wp_set_current_user($id, $name);
    }

    static function lookupUserIdByEmail($email)
    {
        $wpuser = get_user_by('email', $email);
        if (!$wpuser) {
            throw new Exception("user not found ($email)");
        }
        return $wpuser->ID;
    }

}