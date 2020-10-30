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
 * Class Oes_Mini_Screen_App
 */
class Oes_Mini_Screen_App extends Oes_Mini_App
{

    var $screenLayerId = 1;

    /**
     * @return int
     */
    public function getScreenLayerId(): int
    {
        return $this->screenLayerId;
    }

    /**
     * @param int $screenLayerId
     */
    public function setScreenLayerId(int $screenLayerId): void
    {
        $this->screenLayerId = $screenLayerId;
    }

    function doBuildModel()
    {
        parent::doBuildModel();
        $this->model->setScreenLayerId($this->screenLayerId);
    }

    function actDoNewModel()
    {
        return new Oes_Mini_Screen_Model();
    }

    function closeScreen($id=false)
    {
        if (empty($id)) {
            $id = $this->getScreenLayerId();
        }
        $this->clearTargetSlot('mye#mye-layer@'.$id);
    }

}

class Oes_Mini_Screen_Model extends Oes_Mini_App_Model
{

    var $screenLayerId;

    /**
     * @return mixed
     */
    public function getScreenLayerId()
    {
        return $this->screenLayerId;
    }

    /**
     * @param mixed $screenLayerId
     */
    public function setScreenLayerId($screenLayerId): void
    {
        $this->screenLayerId = $screenLayerId;
    }

}