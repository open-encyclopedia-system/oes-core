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
$ops->setCssClass("m-active");
?>
<style>
    .mi-warning-dialog {
        width: 400px;
        min-height: 200px;
        max-height: 400px;
        background: #f0f0f0;
        color:black;
        padding: 10px;
    }
</style>
<div class="mi-warning-dialog">
    <div><?php echo $model->warningMessage; ?></div>
    <div><button <?php $ops->on_click('close_dialog');?> >CLOSE</button></div>
</div>
