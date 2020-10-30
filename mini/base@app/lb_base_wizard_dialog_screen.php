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

$formId = "acf-form-" . genRandomString();


/**
 * @var Oes_Mini_Wizard_Screen $screen
 */
$screen = $model->wizardScreen;

$addDialogScreenCssClasses = $screen->additionalCssClasses;

$ops->setCssClass("m-active $addDialogScreenCssClasses");

$target = $model->wizardScreenTarget;

if (!empty($target)) {
    $ops->setTargetApp($target['app']);
    $ops->setTargetSlot($target['slot']);
    $ops->setTargetSlotId($target['id']);
}

$cssClass = $screen->getCssClass();

?>
<style>

    .mi-dialog-screen {
        background: #f0f0f0;
        color: black;
        /* position: relative; */
        /* position: absolute; */
        /* top: 80px; */
        /* left: 0px; */
         width: 100%;
        height: 100%;
        border-radius: 15px;
        /* border: 1px solid red; */
        position: relative;
        overflow: hidden;
    }



    .mi-dialog-screen .m-title-panel {
        height: 40px;
        position: absolute;
        top: 0px;
        left: 0px;
        width: 100%;
        color: white;
        background: #0063aa;
        padding: 11px 20px;
        font-size: 16px;
        line-height: 18px;
    }

    .mi-dialog-screen .m-body {
        position: absolute;
        top: 40px;
        height: calc(100% - 100px);
        left: 0;
        width: 100%;
        background: white;
        overflow: auto;
        padding: 20px 20px 10px 20px;
    }

    .mi-dialog-screen .m-buttons-panel {
        /*background: #ddd;*/
        height: 60px;
        display: flex;
        justify-content: center;
        padding: 5px 0;
        position: absolute;
        bottom: 0px;
        left: 0px;
        width: 100%;
        z-index: 2;
        padding: 10px 0;
        background: rgba(240,240,240,.95);
    }

    .mi-dialog-screen .m-buttons-panel button {
        margin: 0 20px;
        height: fit-content;
        padding: 10px 20px;
        display: block;
    }

    .mi-dialog-screen .m-buttons-panel button.m-wizard-button-cancel {
        background: none;
        color: #0063aa;
        border: 0;
    }

    .mi-dialog-screen .m-choose-type-acf-field {
        width: 30% !important;
        margin: 0 auto;
    }

    .mi-dialog-screen.m-layer-3 {
        left: auto;
        right: 0;
    }

    .mi-signin-wizard {
        top: auto;
        left: auto;
        height: 500px;
        width: 360px;
        /*max-height: 80vh;*/
    }

    .mi-register-wizard {
        top: auto;
        left: auto;
        height: 750px;
        width: 500px;
        max-height: 100vh;
    }

    .mi-reset_password-wizard {
        top: auto;
        left: auto;
        height: 670px;
        width: 500px;
    }

    .mi-hidden-btn {
        opacity: .5;
    }

    .acf-basic-uploader {
        width: 100%;
    }

    .mi-file-upload-image-holder {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 1rem;
    }

    .mi-file-upload-image-holder img {
        max-width: 100%;
        max-height: 400px;
        margin: 0 auto;
        display: block;
    }

    .mi-add-to-collection-wizard ul.acf-radio-list li input[type="checkbox"], .mi-add-to-collection-wizard ul.acf-checkbox-list li input[type="checkbox"], .mi-add-to-collection-wizard ul.acf-radio-list li input[type="radio"], .mi-add-to-collection-wizard ul.acf-checkbox-list li input[type="radio"] {
        /*margin: 5px 10px 10px 10px;*/
        display: block;
        margin: 2px 13px 0 0;
    }

    .mi-add-to-collection-wizard .acf-checkbox-list li label {
        font-size: 18px;
        /* margin: -2px; */
        display: flex !important;
        justify-content: flex-start;
        align-items: flex-start;
    }

    .mi-add-to-collection-wizard .acf-checkbox-list li label .m-small {
        font-size: 12px;
        text-transform: uppercase;
    }


    .mi-add-to-collection-wizard ul.acf-radio-list, ul.acf-checkbox-list {
        margin: 20px 0 0;
    }

    .mi-add-to-collection-wizard .acf-fields {
        width: 75%;
        margin: 0 auto;
    }

    .mi-dialog-screen .acf-fields textarea {
        padding: 1rem;
        font-size: 1rem;
        line-height: 1.8rem;
        font-family: 'Noto';
        max-height: calc(100vh - 140px - 200px);
        /*height: 100vh;*/
    }

    .acf-field label .m-acf-label-button {
        background: none;
        border: 0;
        color: blue;
        /* background: blue; */
        line-height: 0px;
        font-weight: bold;
        border-radius: 45px;
        color: black;
        font-size: 14px;
        text-decoration: underline;
        margin: 0;
        padding: 0;
        text-align: right;
        margin-left: 10px;
    }

    .mi-dialog-screen .m-acf-field-bottom-sticky {
        background: white;
        position: sticky;
        bottom: 0px;
        padding-bottom: 20px;
    }

    .mi-dialog-screen .m-acf-field-top-sticky {
        background: white;
        position: sticky;
        top: 0px;
        padding-bottom: 20px;
        z-index: 2;
    }

    .mi-add-to-collection-wizard {
        /*width: 480px !important;*/
        /*top: 0px !important;*/
        /*height: 100vh !important;*/
        /*left: 0px;*/
    }

    .mi-manage-collection-wizard {
        /*width: 480px;*/
        left: 50%;
        /*width: calc(100vw - 480px);*/
        /*top: 0px !important;*/
        /*height: 100vh !important;*/
    }

    .mi-centered-text {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        width: 100%;
    }

</style>
<div id="<?php echo $formId; ?>" class="mi-dialog-screen m<?php html($cssClass); ?>">
    <div class="m-title-panel"><?php html($screen->title); ?></div>
    <div class="form-wrapper">
        <script>
            jQuery(function() {
                jQuery('form')
            })
        </script>
        <form action="/" class="mi-acf-modal-form" autocomplete="false">
            <input id="username" style="opacity: 0;position: absolute;" type="text" name="fakeusernameremembered">
            <input id="password" style="opacity: 0;position: absolute;" class="cp-password_stub" type="password" name="fakepasswordremembered">
            <button type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1">SUBMIT</button>
            <div slot="wizard-dialog-body"
                 class="m-body m-acf-modal"><div class="mi-centered-text"><?php echo $screen->body; ?></div></div>
            <div class="m-buttons-panel">
                <?php $screen->renderButtons(function ($name, $label, $active, $action, $attr = []) use ($ops) {

                    $cssClass = 'm-wizard-button-' . $name;

                    if (!$active) {
                        $cssClass = "m-disabled";
                    }

                    $attr['class'] .= ' '.$cssClass;

                    ?>
                    <button <?php acf_esc_atts_e($attr); ?> <?php if ($action) {
                        $ops->on_click($action);
                    } ?> ><?php html($label); ?></button><?php
                }) ?>
            </div>
        </form>
    </div>
</div>
<script>

    acf.data.nonce = <?php echo json_encode(wp_create_nonce('acf_nonce')); ?>;

    var acfModalForm$ = jQuery('#<?php html($formId) ?>')

    acf.do_action('append', acfModalForm$);

</script>