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

if (empty($layer)) {
    $layer = 2;
}

$ops->setCssClass("m-active");


/**
 * @var Oes_Mini_Wizard_Screen $screen
 */
$screen = $model->wizardScreen;

$cssClass = $screen->getCssClass();

?>
<style>

    .mi-wizard-dialog {
        background: transparent;
        color: black;
        position: relative;
        position: absolute;
        /*top: 80px;*/
        top: 0px;
        left: 50%;
        width: 50%;
        height: calc(100% - 0px);
    }

    .mi-wizard-dialog.mi-add-comment-wizard {
        right: 0px;
        left: auto;
    }

    .single-eo_article .mi-wizard-dialog {
        top: 0px;
        height: calc(100% - 0px);
    }

    .mi-wizard-dialog .m-title-panel {
        height: 80px;
        /*position: absolute;*/
        /*top: 0px;*/
        /*left: 0px;*/
        width: 100%;
        background: rgba(255,255,255,.99);
        padding: 10px 2rem;
        font-size: 18px;
        display: flex;
        align-items: center;
        color: #0063aa;
        font-weight: bold;
        text-transform: uppercase;
        justify-content: space-between;
    }

    .mi-wizard-dialog .m-title-panel .m-help-link {
        color: #0063aa;
        font-weight: normal;
        font-size: 16px;
    }


    .mi-wizard-dialog .m-body {
        position: absolute;
        top: 80px;
        height: calc(100% - 160px);
        left: 0;
        width: 100%;
        background: rgba(255,255,255,.99);
        overflow: auto;
        /*padding: 60px 2rem 30px 2rem;*/
        padding: 0px 2rem 30px 2rem;
    }

    .mi-wizard-dialog .m-buttons-panel {
        /*background: #ddd;*/
        height: 80px;
        display: flex;
        justify-content: flex-end;
        /*padding: 5px 30px;*/
        position: absolute;
        bottom: 0px;
        left: 0px;
        width: 100%;
        z-index: 2;
        padding: 10px 2rem;
        /*background: rgba(255, 255, 255, .99);*/
        background: #f0f0f0;
    }

    .mi-wizard-dialog .m-buttons-panel button {
        margin: 0 0 0 1rem;
        min-width: 200px;
    }

    .mi-wizard-dialog .m-choose-type-acf-field {
        width: 30% !important;
        margin: 0 auto;
    }

    .mi-wizard-dialog.m-layer-3 {
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
        /*width: */
        margin: 0 auto;
    }

    .mi-wizard-dialog .acf-fields textarea {
        /*padding: 1rem;*/
        /*font-size: 1rem;*/
        /*line-height: 1.8rem;*/
        /*font-family: 'Noto';*/
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

    .mi-wizard-dialog .m-acf-field-bottom-sticky {
        background: white;
        position: sticky;
        bottom: 0px;
        padding-bottom: 20px;
    }

    .mi-wizard-dialog .m-acf-field-top-sticky {
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

</style>
<div id="<?php echo $formId; ?>" class="mi-wizard-dialog m-layer-<?php echo $layer; ?> <?php html($cssClass); ?>">
    <div style="position: relative; height: 100%;">
    <div class="m-title-panel"><?php html($screen->title); ?><a href="#" class="m-help-link">HELP</a></div>
    <div class="form-wrapper">
        <!--        <form id="sample-form">-->
        <!--            <div>-->
        <!--                <label for="year">Enter at least four characters (required):</label>-->
        <!--                <input id="year" type="text" minlength="4" required="">-->
        <!--            </div>-->
        <!--        </form>-->
        <form action="/" class="mi-acf-modal-form" id="mi-acf-modal-form" autocomplete="false">
            <h1>LKER</h1>
            <button type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;"
                    tabindex="-1">SUBMIT</button>
            <div slot="wizard-body" slot-id="<?php echo $layer; ?>"
                 class="m-body m-acf-modal"><?php ?></div>
            <div class="m-buttons-panel">
                <?php $screen->renderButtons(function ($name, $label, $active, $action, $attr = []) use ($ops) {

                    $cssClass = 'm-wizard-button-' . $name;

                    if (!$active) {
                        $cssClass = "m-disabled";
                    }

                    $attr['class'] .= ' ' . $cssClass;

                    ?>
                    <button <?php acf_esc_atts_e($attr); ?><?php if ($action) {
                        $ops->on_click($action);
                    } ?> ><?php html($label); ?></button><?php
                }) ?>
            </div>
        </form>
    </div>
    </div>
</div>
<script>

    acf.data.nonce = <?php echo json_encode(wp_create_nonce('acf_nonce')); ?>;

    var acfModalForm$ = jQuery('#<?php html($formId) ?>')

    acf.do_action('append', acfModalForm$);

</script>