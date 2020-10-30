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

//include (__DIR__."/AmsFE_CompStack.php");
//include (__DIR__."/AmsFE_Styles.php");
//include (__DIR__."/AmsFE_Attributes.php");
//include (__DIR__."/AmsFE_Component.php");

/**
 * Class AmsFE_Build
 * @property AmsFE_Component $NextSteps
 * @property AmsFE_Component $SubIssues
 * @property AmsFE_Component $div
 * @property AmsFE_Component $span
 * @property AmsFE_Component $table
 * @property AmsFE_Component $tbody
 * @property AmsFE_Component $thead
 * @property AmsFE_Component $tr
 * @property AmsFE_Component $td
 * @property AmsFE_Component $a
 * @property AmsFE_Component $button
 * @property AmsFE_Component $link
 * @property AmsFE_Component $br
 * @property AmsFE_Component $h1
 * @property AmsFE_Component $h2
 * @property AmsFE_Component $h3
 * @property AmsFE_Component $h4
 * @property AmsFE_Component $h5
 * @property AmsFE_Component $h6
 * @property AmsFE_Component $b
 * @property AmsFE_Component $i
 * @property AmsFE_Component $__text
 */
class AmsFE_Build
{

    use AmsFE_CompStack;

    /**
     * AmsFE_Component constructor.
     * @param $name_
     */
    public function __construct()
    {
        $this->name_ = "__ROOT__";
        $this->build_ = $this;
        $this->current_ = $this;
    }

    /**
     * @var AmsFE_Component
     */
    var $current_;

    /**
     * @var string
     */
    var $currentName_;

    public function __get($name)
    {
        if ($this->current_->name_ == $name) {
            return $this->current_;
        }
        return $this->push($name);
    }


    function push($name)
    {
        static $seqid;
        $seqid++;

        $comp = new AmsFE_Component($name,$this);
        $comp->ID = $seqid;

        $this->current_->children_[] = $comp;

        $comp->parent_ = $this->current_;
        $this->current_ = $comp;

        return $comp;

    }

    /**
     * @param AmsFE_Component $comp
     */
    function closeComp($comp)
    {

        if ($this->current_->ID !== $comp->ID) {
            throw new Exception('comp mismatch: '.$this->current_->ID.'/'.$comp->current_->ID);
        }

        $this->current_ = $this->current_->parent_;

    }

    function text($str)
    {
        $node = $this->push("__text");
        $node->text_ = $str;
        $node->close();
    }

    function value($value)
    {
        $node = $this->push("___value");
        $node->value_ = $value;
        $node->close();
    }

    function templateLiteral($str)
    {
        $node = $this->push("___templateLiteral");
        $node->templateLiteral_ = $str;
        $node->close();
    }

}