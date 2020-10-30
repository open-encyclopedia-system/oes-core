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


function oes_wp_logout($callback)
{
    add_action("wp_logout", function () use ($callback) {
        wp_set_current_user(0);
        if ($callback) {
            $callback();
        }
    });
    wp_logout();

}

function oes_get_user_main_profile_id($userid = false)
{
    oes_get_id_of_post(oes_get_main_profile($userid));
}

function oes_get_user_main_profile($userid = false)
{


    if (!$userid) {
        if (!is_user_logged_in()) {
            return false;
        }
        $userid = get_current_user_id();
    }

    $user = oes_get_user($userid);

    $main_profile = oes_acf_value($user, "main_profile");

    if (!$main_profile) {
        throw new Exception(
            "main profile not set for user $userid" . print_r($user, true));
    }

    return oes_get_post($main_profile);

}

function oes_get_all_user_profiles($userid = false)
{

    if (!is_user_logged_in()) {
        return false;
    }

    if (!$userid) {
        $userid = get_current_user_id();
    }

    $user = oes_get_user($userid);

    $list = [];

    $user_profiles = oes_acf_value($user, "user_profiles");

    if (is_array($user_profiles)) {
        foreach ($user_profiles as $po) {
            $list[] = oes_get_post($po);
        }
    }

    return $list;

}

/**
 * @param $valid
 * @param $value
 * @param $name
 * @param $field
 * @param $input
 * @return bool|string
 */
function oes_validate_signup_password($valid, $value, $name, $field, $input)
{

    static $password1;

    if (empty($value)) {
        return $valid;
    }

    $errors = [];

//    if (mb_strlen($value)<8) {
//        return "Password must be at least 8 characters in length including a letter and a digit.";
//    }

    if (!preg_match("/(?i)^(?=.*[a-z])(?=.*\d).{8,}$/", $value)) {
        return __("Password must be at least 8 characters in length including a letter and a digit.", "oes");
    }

    if ($name == 'password1') {
        $password1 = $value;
        return true;
    }

    if ($name == 'password2') {
        if (strcmp($password1, $value) === 0) {
            return true;
        } else {
            return __("Password and repeated password must match.", "oes");
        }
    }

    return $valid;

}

function oes_activate_account($email, $token, $signin = false)
{

    $signuprec = false;

    $found = oes_query_posts(Oes_General_Config::S_SIGNUP, [
        'relation' => 'AND',
        ['key' => 'email', 'value' => $email],
        ['key' => 'token', 'value' => $token]
    ], function ($post_id, $post, $pos, $payload) use (&$signuprec) {
        $signuprec = oes_get_maybe_post($post_id, true);
    });

    if (empty($found)) {
        return false;
    }

    $name = oes_acf_value($signuprec, 'name');

    list ($firstname, $lastname) = preg_split('@\s+@', $name);

    if (empty($lastname)) {
        $lastname = $firstname;
    }

    $nickname = $firstname;

    $nicename = $name;

    $userpro = null;

    $isRegistered = false;

    $newProfileId = false;

    add_action('user_register', function ($user_id)
    use (&$signuprec, $name, $firstname, $lastname, $email, &$isRegistered, &$newProfileId) {

        $userpro['type_of_profile'] = 'individual';
        $userpro['display_name'] = $name;
        $userpro['firstname'] = $firstname;
        $userpro['lastname'] = $lastname;
        $userpro['creator'] = $user_id;
        $userpro['owner'] = $user_id;
        $userpro['email_address'] = $email;
        $userpro['last_confirmed_email_address'] = $email;
        $userpro['email_confirmed_by'] = $user_id;
        $userpro['email_last_confirmed_on'] = time();
        $userpro['is_main_profile'] = true;
        $userpro['status'] = "Activated";

        //        $userpro['users'] = [
//            ['user' => $user_id, 'created_at' => time(), 'state' => 'Active', 'role'=>'Admin']
//        ];


        $userpro_id = oes_acf_save_post_and_values('eo_user_profile', "$name ($email)", $userpro);

        if (!$userpro_id) {
            $isRegistered = false;
        } else {
            $isRegistered = true;
            $newProfileId = $userpro_id;
        }

        oes_acf_save_values($signuprec['ID'], ['activated' => time(),
            'activated_ip' => x_get_remote_addr()]);

        oes_set_current_profile($newProfileId, $user_id);

        oes_set_main_profile($newProfileId, $user_id);

    });

    $email = oes_acf_value($signuprec, 'email_address');

    $userid = wp_insert_user([
        'user_pass' => oes_acf_value($signuprec, 'password1'),
        'user_login' => oes_acf_value($signuprec, 'email_address'),
        'user_email' => oes_acf_value($signuprec, 'email_address'),
        'locale' => 'en',
        'role' => 'editorial_office',
        'show_admin_bar_front' => false,
        'user_nicename' => $nicename,
        'nickname' => $nickname,
        'first_name' => $firstname,
        'last_name' => $lastname,
        'display_name' => $name]);

    if (is_wp_error($userid) || !$isRegistered) {
        error_log("oes_activate_account[fail] " . print_r($userid, true));
        throw new Exception("Activation failed");
    }


    $contributor['name'] = $name;
    $contributor['firstname'] = $firstname;
    $contributor['lastname'] = $lastname;
    $contributor['email'] = $email;
    $contributor['roles'] = [
        ['role' => 'Editorial Office', 'added_at' => time()],
        ['role' => 'Managing Editor', 'added_at' => time()]
    ];

    $newcontributorid = OES_Factory::create("contributors", $name, $contributor);

    oes_acf_update_field("contributor", $newcontributorid->ID(), "user_" . $userid);

    return [$userid, $newProfileId];

}

function oes_create_new_institutional_profile($name, $ownerid)
{

    $values['owner'] = $ownerid;
    $values['creator'] = $ownerid;
    $values['roles'] = "Institution";
    $values['type_of_profile'] = "Institutional";
    $values['users'] = [['user' => $ownerid, 'role' => 'Admin',
        'created_at' => time(), 'state' => 'Active']];

    return oes_acf_save_post_and_values("profile", "$type: $name", $values);

}


function oes_get_current_user($refresh = false)
{

//    global $user;

//    if (isset($user) && !$refresh) {
//        return $user;
//    }

    if (!is_user_logged_in()) {
        $user = false;
        return false;
    }

    $user = oes_get_user(get_current_user_id());

    return $user;

}

function oes_get_current_profile()
{
    return oes_get_user_main_profile();
}

function oes_get_current_profile_id()
{

    static $profileid;

    if ($profileid) {
        return $profileid;
    }

    $profile = oes_get_current_profile();

    if (!$profile) {
        throw new Exception("Missing profile");
    }

    $profileid = $profile['ID'];

    return $profileid;
}

function oes_get_current_user_profiles()
{
    $user = oes_get_current_user();
    return [$user];
}

/**
 * @param bool $user
 * @param array $acf_relationship_fields
 * @return array|bool|int
 */
function oes_get_user($user = false, $acf_relationship_fields = ['last_used_profile', 'user_profiles', 'main_profile'])
{

    if (empty($user)) {
        $user = get_current_user_id();
    }

    $user_id = $user;

    if (is_object($user) && $user instanceof WP_User) {
        $user_id = $user->ID;
        $userdata = get_object_vars($user->data);
    } else if (is_array($user) && array_key_exists('ID', $user)) {
        $user_id = $user['ID'];
        $userdata = $user;
    } else if (is_numeric($user)) {
        $user = get_userdata($user);
        if (!is_object($user) || is_wp_error($user)) {
            return $user;
        }
        $userdata = get_object_vars($user->data);
    } else {
        return $user;
    }

    if (array_key_exists('__id', $userdata)) {
        return $userdata;
    }

    $userdata['__id'] = genRandomString(4);

//    if ($with_meta) {
    $fields = oes_get_user_meta($user_id, $acf_relationship_fields);

    $usermeta = get_user_meta($user_id);

    foreach (['nickname', 'last_name', 'first_name', 'description', 'locale', 'email_address'] as $field) {

        if (!array_key_exists($field, $usermeta)) {
            continue;
        }

        $value = $usermeta[$field];

        if (empty($value)) {
            continue;
        }

        $fields[$field] = $value[0];

    }

    $fields['url'] = $userdata['user_url'];
    $fields['display_name'] = $userdata['display_name'];
    $fields['nicename'] = $userdata['user_nicename'];
    $fields['status'] = $userdata['user_status'];
    $fields['deleted'] = $userdata['deleted'];
    $fields['spam'] = $userdata['spam'];
    $fields['registered'] = strtotime($userdata['user_registered']);

    $userdata['meta'] = $fields;
//    }

    return $userdata;

}

function oes_get_userprofiles_of_wpuser($wpuserid = false)
{
    $profile = oes_get_user_main_profile($wpuserid);

    return [$profile];

    if (!$wpuserid) {
        $wpuser = wp_get_current_user();
        $wpuserid = $wpuser->ID;
    }

    $profiles = [];

    $args = [
        'meta_key' => 'display_name',
        'orderby' => 'meta_value',
        'order' => 'DESC'
    ];

    oes_query_posts("eo_user_profile", array(
        'relation' => 'AND',
        array(
            'key' => 'owner',
            'value' => $wpuserid
        ),
        array(
            'key' => 'type_of_profile',
            'value' => "individual"
        )
    ), function ($id, $post, $pos, $payload = "") use (&$profiles) {

        $entry = oes_get_maybe_post($id, true);

        $profiles[$entry['ID']] = $entry;

    }, $wpuserid, false, -1, "meta_value", "ASC", $args);

    oes_query_posts("eo_user_profile", array(
        'relation' => 'AND',
        array(
            'key' => 'owner',
            'value' => $wpuserid
        ),
        array(
            'key' => 'type_of_profile',
            'value' => "institutional"
        )
    ), function ($id, $post, $pos, $payload = "") use (&$profiles) {

        $entry = oes_get_maybe_post($id, true);

        $profiles[$entry['ID']] = $entry;

    }, $wpuserid, false, -1, "meta_value", "ASC", $args);

    return $profiles;

}

function oes_isarticleauthor2($post_id = false, $profiles = false)
{

}

function oes_isarticleauthor($post_id = false, $profiles = false)
{

    if (!$post_id) {
        $post = get_post();
        $post_id = $post->ID;
    }

    //

    $post = oes_get_maybe_post($post_id, true);

    $mostrecenteditionID =
        oes_acf_value($post, "most_recent_edition");

    if (empty($mostrecenteditionID)) {
        return false;
    }

    $article = oes_get_maybe_post($mostrecenteditionID, true);

    if (!$profiles) {

        $profiles = oes_get_all_user_profiles();

        if (empty($profiles)) {
            return false;
        }

    } else if (is_array($profiles) && array_key_exists('ID', $profiles) && $profiles['post_type'] == Oes_1418_Config::EO_CONTRIBUTOR) {

        $profiles = [$profiles];

    } else if (is_numeric($profiles)) {

        $profiles = [oes_get_post($profiles)];

    } else if (is_object($profiles) && $profiles instanceof WP_Post && $profiles->post_type == Oes_1418_Config::EO_CONTRIBUTOR) {
        $profiles = [oes_get_post($profiles)];
    }

    $authors = oes_acf_value($article, "article_author");

//    if (oes_is_current_user_admin()) {
//        return true;
//    }

    if (is_array($authors)) {

        /**
         * @var EO_Contributor $author1
         */
        foreach ($authors as $author1) {

            /**
             * @var EO_Contributor $contributor
             */
            foreach ($profiles as $profile) {

                if (oes_equals_posts($profile, $author1)) {
                    return true;
                }
            }
        }

    }

    return false;


}

function oes_fe_activateUser()
{

    $currentuser = oes_get_current_user(true);


}

function oes_fe_switchUserProfile()
{

    $currentuser = oes_get_current_user(true);

    if (!$currentuser) {
        return false;
    }

    $redirectToP = rparam('redirect_to');
    $profileP = rparam('profile', '');

    if (empty($profileP)) {
        return false;
    }

    $ret = oes_set_current_profile($profileP, $currentuser['ID']);

    if ($redirectToP) {
        redirect($redirectToP);
    }

    return true;

}

function oes_is_main_profile($profile, $userid = false)
{

    if (!$userid) {
        $userid = get_current_user_id();
    }

    $user = oes_get_user($userid);

    if (is_numeric($profile)) {
        $profileid = $profile;
    } else {
        $profileid = $profile['ID'];
    }

    return $user['meta']['main_profile']['ID'] == $profileid;

}

function oes_is_a_user_profile($profile)
{
    if (is_numeric($profile)) {
        $profileid = $profile;
    } else {
        $profileid = $profile['ID'];
    }

    $userprofiles = oes_get_userprofiles_of_wpuser($userid);

    $profileMatched = false;

    if (is_array($userprofiles)) {
        foreach ($userprofiles as $userpro) {
            if ($userpro['ID'] == $profileid) {
                $profileMatched = true;
                break;
            }
        }
    }

    return $profileMatched;

}

function oes_set_current_profile($profileP, $userid)
{

    $userprofiles = oes_get_userprofiles_of_wpuser($userid);

    $profileMatched = false;

    if (is_array($userprofiles)) {
        foreach ($userprofiles as $userpro) {
            if ($userpro['ID'] == $profileP) {
                $profileMatched = true;
            }
        }
    }

    if (!$profileMatched) {
        return false;
    }

    oes_acf_update_field('last_used_profile', $profileP, 'user_' . $userid);

    return true;

}

function oes_get_main_profile($userid)
{
    $user = oes_get_user($userid);
    return $user['meta']['main_profile'];
}

function oes_set_main_profile($profileP, $userid)
{

    $userprofiles = oes_get_userprofiles_of_wpuser($userid);

    $profileMatched = false;

    if (is_array($userprofiles)) {
        foreach ($userprofiles as $userpro) {
            if ($userpro['ID'] == $profileP) {
                $profileMatched = true;
            }
        }
    }

    if (!$profileMatched) {
        return false;
    }

    oes_acf_update_field('main_profile', $profileP, 'user_' . $userid);

    return true;

}

/**
 * Determines if the query is for the blog homepage.
 *
 * The blog homepage is the page that shows the time-based blog content of the site.
 *
 * is_home() is dependent on the site's "Front page displays" Reading Settings 'show_on_front'
 * and 'page_for_posts'.
 *
 * If a static page is set for the front page of the site, this function will return true only
 * on the page you set as the "Posts page".
 *
 * @since 1.5.0
 *
 * @see is_front_page()
 * @global WP_Query $wp_query Global WP_Query instance.
 *
 * @return bool True if blog view homepage, otherwise false.
 */
function oes_is_admin()
{

//    /**
//     * @var WP_Query $wp_query
//     */
//    global $wp_query;
//
//    if (!isset($wp_query)) {
//        _doing_it_wrong(__FUNCTION__, __('Conditional query tags do not work before the query is run. Before then, they always return false.'), '3.1.0');
//        return false;
//    }

    if (stripos($_SERVER['REQUEST_URI'], '/wp-admin') !== false) {
        return true;
    }

}

function oes_get_userdata($userid)
{
    $user = get_userdata($userid);
    if (!$user) {
        throw new Exception("user not found $userid");
    }
    return get_object_vars($user->data);
}

function oes_update_user_property($userid, $new_value, $property)
{

    if (!$userid) {
        throw new Exception("Bad user id $userid");
    }

    $userdata = oes_get_userdata($userid);

    if ($userdata[$property] == $new_value) {
        return false;
    }

    $userdata[$property] = $new_value;
    add_filter("send_email_change_email", "oes_return_false");
    wp_update_user($userdata);
    remove_filter("send_email_change_email", "oes_return_false");

    return true;

}

function oes_post_profile_update($post)
{

    if (!$post) {
        return;
    }

    $type = oes_acf_value($post, 'type_of_profile');
    $owner = oes_acf_value($post, 'owner');
    $owner = oes_maybe_id_if_array($owner);

    $sysusers = [];

    $displayName = "";

    $isUserProfile = false;


    if ($type == 'Institutional') {

        $users = oes_acf_value($post, 'users');
        $displayName = oes_acf_value($post, 'name_of_institution');

        $users = x_as_array($users);

        foreach ($users as $usr) {
            $userid = oes_maybe_id_if_array($usr['user']);
            $user_with_role = $userid . '_' . $usr['role'];
            $sysusers[$userid] = ['user' => oes_maybe_id_if_array($usr['user']), 'user_with_role' => $user_with_role, 'role' => $usr['role']];
        }

    } else {

        $users = oes_acf_value($post, 'users');

        $users = x_as_array($users);

        if (is_array($users)) {
            foreach ($users as $usr) {
                $userid = oes_maybe_id_if_array($usr['user']);
                $user_with_role = $userid . '_' . $usr['role'];
                $sysusers[$userid] = ['user' => oes_maybe_id_if_array($usr['user']), 'user_with_role' => $user_with_role, 'role' => $usr['role']];
            }
        }

        $isUserProfile = true;
        $firstname = oes_acf_value($post, 'firstname');
        $lastname = oes_acf_value($post, 'lastname');
        $displayName = trim($firstname . " " . $lastname);

        $ownerid = oes_maybe_id_if_array($owner);

        $sysusers[$ownerid] = ['user' => $owner,
            'user_with_role' => $owner . "_Admin",
            'role' => 'Admin'];

    }


    $owneruser = oes_get_user($owner);

    $email_address = oes_acf_value($post, "email_address");

    if (oes_is_main_profile($post, $owneruser['ID'])) {

        try {

            if ($isUserProfile) {
                oes_update_user_property($owneruser['ID'], $firstname, 'first_name');
                oes_update_user_property($owneruser['ID'], $lastname, 'last_name');
            }

            oes_update_user_property($owneruser['ID'], $displayName, 'display_name');

            oes_update_user_property($owneruser['ID'],
                $email_address, 'user_email');


            //            update_field('last_confirmed_email_address', oes_acf_value($post, "email_address"), $post['ID']);
//            update_field('email_confirmed_by', get_current_user_id(), $post['ID']);
//            update_field('email_last_confirmed_on', time(), $post['ID']);

        } catch (Exception $e) {
            error_log($e->getMessage());
        }

    }

    oes_acf_update_field('display_name', $displayName, $post['ID']);
    wp_update_post(['ID' => $post['ID'], 'post_title' => $displayName . " ($email_address)"]);
    oes_acf_update_field('sys_users', $sysusers, $post['ID']);

}


/**
 * @param WP_User $user
 * @return bool
 */
function oes_is_current_user_admin($user = null)
{

    global $current_user;

    if (!$user) {
        $user = $current_user;
    } else {
        if (is_wp_error($user)) {
            return false;
        }
    }

    $roles = $user->roles;
//

    if (oes_has_admin_roles($roles)) {
        return true;
    }

    return false;

}

function oes_has_current_user_role($role)
{

    global $current_user;

    if (!$user) {
        $user = $current_user;
    } else {
        if (is_wp_error($user)) {
            return false;
        }
    }

    $roles = $user->roles;

    if (is_array($role)) {
        $intersect = array_intersect($roles, $role);
        return !empty($intersect);
    } else {
        return in_array($role, $roles);
    }

}

function oes_has_admin_roles($roles)
{
    if (empty($roles)) {
        return false;
    }
    if (!is_array($roles)) {
        return false;
    }
    $intersect = array_intersect($roles, Oes_General_Config::EO_ADMIN_ROLES);
    return !empty($intersect);
}