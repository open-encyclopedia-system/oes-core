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

<style>

    /*.mi-form-errors {*/
    /*    !*background: #f0f0f0;*!*/
    /*    !*padding: 0px;*!*/
    /*    !*list-style: none;*!*/
    /*    color: #bc121d;*/
    /*    margin: 1rem 0;*/
    /*    list-style-type: square;*/
    /*}*/

    /*.mi-form-errors li {*/
    /*    margin: .5rem 0;*/
    /*}*/

</style><?php

if (!empty($errors)) {
    ?>
    <ul class="mi-form-errors"><?php
    foreach ($errors as $msg) {
        ?>
        <li><?php echo($msg); ?></li><?php
    } ?></ul><?php
}

?><script>
    jQuery('#mi-acf-modal-form .m-body').scrollTop(0)
</script>