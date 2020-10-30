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

class Oes_ClassAttribute
{

    var $name;

    var $valueAsFormattedString;

    var $static = false;

    var $constant = false;

    var $comment = "";

    function export()
    {

        if (!empty($this->comment)) {
            echo $this->comment,"\n";
        }
        if ($this->isConstant()) {
            echo "const ";
        } else if ($this->isStatic()) {
            echo "static \$";
        } else {
            echo "var \$";
        }

        echo $this->name, " = ";

        echo $this->valueAsFormattedString, ";\n";

    }

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->static;
    }

    /**
     * @param bool $static
     */
    public function setStatic(bool $static): void
    {
        $this->static = $static;
    }

    /**
     * @return bool
     */
    public function isConstant(): bool
    {
        return $this->constant;
    }

    /**
     * @param bool $constant
     */
    public function setConstant(bool $constant): void
    {
        $this->constant = $constant;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }




    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getValueAsFormattedString()
    {
        return $this->valueAsFormattedString;
    }

    /**
     * @param mixed $valueAsFormattedString
     */
    public function setValueAsFormattedString($valueAsFormattedString): void
    {
        $this->valueAsFormattedString = $valueAsFormattedString;
    }


}
