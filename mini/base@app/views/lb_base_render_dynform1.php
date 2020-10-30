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

$isEmpty = empty($model->formDataHtmlRendering);

if (!$isEmpty) {
    ?>
    <div class="mi-required-fields-block"><?php app_e('<span class="m-asterisk">*</span> Required fields are marked'); ?></div>
    <?php
}

?>
<div slot="errors"></div>
<?php

$ops->setCssClass('recommendBibliographicTitleModal');

echo $model->formDataHtmlRendering;

$dynFormTarget = $model->dynFormTarget;

if (!empty($dynFormTarget)) {
    $ops->setTargetApp($dynFormTarget['app']);
    $ops->setTargetSlot($dynFormTarget['slot']);
    $ops->setTargetSlotId($dynFormTarget['id']);
}


?>
<script>


    $(function () {

        $(".mi-acf-modal-form input:text, .mi-acf-modal-form textarea").first().focus();

        $(".mi-wizard-image-upload-with-preview [type='file']").on('change', function() {

            var t$ = $(this)

            var acfUploader$ = t$.closest('label')

            var imageHolder$ = $('.mi-file-upload-image-holder', acfUploader$)

            if (imageHolder$.length == 0)
            {
                imageHolder$ = $("<div/>")
                imageHolder$.addClass('mi-file-upload-image-holder')
                imageHolder$.prependTo(acfUploader$)
            }

            miniRun.showImagePreviewAfterFileSelect(t$, imageHolder$)

        })




        $("[name$='_lookup_button_opt]']").on('click', function () {

            var t$ = $(this)

            var type = $("[id*='__user_select_']").val()

            var identifier = $("[id$='_user_pid_opt']").val()
            if (!identifier) {
                identifier = $("[id$='_user_isbn_opt']").val()
                type = 'isbn'
            }

            if (!identifier) {
                identifier = ''
            }

            window.miniRun.do_action('lookupISBN', '<?php echo $ops->getAppid(); ?>', {
                type: type,
                identifier: identifier
            }, false, false, false)
        })
    })

</script>
