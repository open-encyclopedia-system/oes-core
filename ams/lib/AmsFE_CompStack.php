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
 * Trait AmsFE_CompStack
 * @property AmsFE_Styles $styles
 * @property AmsFE_Attributes $attributes
 * @property AmsFE_Attributes $attribs
 */
trait AmsFE_CompStack
{

    /**
     * @var AmsFE_Component
     */
    var $parent_;

    var $children_ = [];

    var $name_;

    var $text_;

    var $value_;

    var $view = 'view';

    /**
     * @var AmsFE_Data
     */
    var $data_ = [];

    var $templateLiteral_;

    /**
     * @var AmsFE_Styles
     */
    var $styles_;

    /**
     * @var AmsFE_Attributes
     */
    var $attributes_;

    /**
     * @var AmsFE_Build
     */
    var $build_;

    function close()
    {
        $this->build_->closeComp($this);
    }

    public function __get($name)
    {

        if ($name == 'styles') {
            if (!$this->styles_) {
                $this->styles_ = new AmsFE_Styles();
            }
            return $this->styles_;
        }

        if ($name == 'data') {
            if (!$this->data_) {
                $this->data_ = new AmsFE_Data();
            }
            return $this->data_;
        }

        if ($name == 'attributes' || $name == 'attribs') {
            if (!$this->attributes_) {
                $this->attributes_ = new AmsFE_Attributes();
            }
            return $this->attributes_;
        }

    }


    function text($text)
    {
        $this->build_->text($text);
    }

    function value($value)
    {
        $this->build_->value($value);
    }

    function templateLiteral($text)
    {
        $this->build_->templateLiteral($text);
    }


    public function __toString()
    {
        return "__toString:: ".$this->name_;
    }

    //

    /**
     * @param AmsFE_CompStack $node
     * @param string $prefix
     */
    function dump($node,$prefix="")
    {

        if ($node->name_ == '__text') {
            echo $prefix,$node->text_,"\n";
            return;
        }

        echo "$prefix<";
        echo $node->name_;
        echo ">\n";

        foreach ($node->children_ as $child)
        {
            $node->dump($child,$prefix."  ");
        }

        echo "$prefix</";
        echo $node->name_;
        echo ">\n";

    }


}