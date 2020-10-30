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

function oes_send_html_mail($to, $from_name, $from_email, $subject, $message, $headers = [], $attachments = [], $sender = null)
{
    if (!isset($sender)) {
        $sender = $from_email;
    }

    add_action("phpmailer_init", function (&$phpmailer) use ($sender) {
        /**
         * @var PHPMailer $phpmailer
         */
        $phpmailer->Sender = $sender;
    });

    //


    $myheaders = [];
    $myheaders[] = "From: $from_name <$from_email>";
    $myheaders[] = "Content-Type: text/html; charset=UTF-8";

    if (is_array($headers)) {
        if ($headers['bcc']) {
            $myheaders[] = "Bcc: " . $headers['bcc'];
        }
        if ($headers['cc']) {
            $myheaders[] = "Cc: " . $headers['cc'];
        }
    }

    wp_mail($to, $subject, $message, $myheaders, $attachments);

}

function oes_send_text_mail($to, $from_name, $from_email, $subject, $message, $headers = [], $attachments = [], $sender = null)
{
    if (!isset($sender)) {
        $sender = $from_email;
    }

    add_action("phpmailer_init", function (&$phpmailer) use ($sender) {
        /**
         * @var PHPMailer $phpmailer
         */
        $phpmailer->Sender = $sender;
//        $phpmailer->SMTPDebug = 2;
    });

    //


    $myheaders = [];
    $myheaders[] = "From: $from_name <$from_email>";
    $myheaders[] = "Content-Type: text/plain; charset=UTF-8";

    if (is_array($headers)) {
        if ($headers['bcc']) {
            $myheaders[] = "Bcc: " . $headers['bcc'];
        }
    }

    wp_mail($to, $subject, $message, $myheaders, $attachments);

}