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
 * Class Oes_Mini_AMW_Dialog_Step
 * @property $id
 * @property $type
 * @property $back
 * @property $back_label
 * @property $cancel
 * @property $cancel_label
 * @property $next
 * @property $next_label
 * @property $final
 * @property $final_label
 * @property $is_final
 * @property $is_start
 * @property $form_part
 * @property $is_form_screen
 * @property $is_confirmation_screen
 * @property $is_html_screen
 * @property $pre_form_html
 * @property $post_form_html
 * @property $no_validate
 * @property $no_callback
 * @property $merge_on_back
 * @property $show_progress_bar
 */
class Oes_Mini_Dialog_Step extends Oes_Mini_DynamicData
{

    const attr_id = 'id';
    const attr_is_confirmation_screen = 'is_confirmation_screen';
    const attr_is_html_screen = 'is_html_screen';
    const attr_is_form_screen = 'is_form_screen';
    const attr_back = 'back';
    const attr_back_label = 'back_label';
    const attr_next = 'next';
    const attr_next_label = 'next_label';
    const attr_cancel  = 'cancel';
    const attr_cancel_label = 'cancel_label';
    const attr_final = 'final';
    const attr_final_label = 'final_label';
    const attr_form_part = 'form_part';
    const attr_pre_form_html = 'pre_form_html';
    const attr_post_form_html = 'post_form_html';
    const attr_no_validate = 'no_validate';
    const attr_merge_on_back = 'merge_on_back';
    const attr_no_callback = 'no_callback';
    const attr_is_start = 'is_start';
    const attr_is_final = 'is_final';
    const attr_show_progress_bar = 'show_progress_bar';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

}
