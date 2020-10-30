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

class Oes_Shortcode_Item {

    var $id;
    var $isNew = true;
    var $pos;
    var $atts;
    var $content;

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }



    /**
     * @return mixed
     */
    public function getAtts()
    {
        return $this->atts;
    }

    /**
     * @param mixed $atts
     */
    public function setAtts($atts)
    {
        $this->atts = $atts;
    }



    /**
     * @return mixed
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * @param mixed $pos
     */
    public function setPos($pos)
    {
        $this->pos = $pos;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->isNew = false;
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @param mixed $isNew
     */
    public function setNew($isNew)
    {
        $this->isNew = $isNew;
    }

}

class Oes_Shortcodes_Parser {

    var $items = [];
    var $curMaxSeq = 0;
    var $pos = 0;
    var $shortcode;

    /**
     * OesPersonShortcode constructor.
     * @param $shortcode
     */
    public function __construct($shortcode)
    {
        $this->shortcode = $shortcode;
    }


    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }


    function doParseShortcode($atts, $content = null) {

        if (empty($content)) {
            return $content;
        }

        if (empty($atts)) {
            $atts = [];
        }

        $rec = new Oes_Shortcode_Item();

        $id = $atts['id'];

        if ($id) {

            if ($id > $this->curMaxSeq) {
                $this->curMaxSeq = $id;
            }

            $rec->setId($id);

        }

        $rec->content = $content;

        $rec->atts = $atts;

        $rec->setPos($this->pos);

        $this->items[] = $rec;

        $this->pos++;

        return $content;

    }

    function reset()
    {
        $this->persons = [];
        $this->seq = 1;
    }

    function parse($content)
    {

        add_shortcode($this->shortcode, array($this, 'doParseShortcode'));

        $content = do_shortcode($content);

        remove_shortcode($this->shortcode);

        return $this->getItems();

    }

    function assignNewIds()
    {

        /**
         * @var OesPersonShortCodeItem $person
         */
        foreach ($this->items as &$item)
        {
            if ($item->isNew()) {
                $this->curMaxSeq++;
                $item->setId($this->curMaxSeq);
            }
        }

    }

    function doReplaceShortcode($atts, $content)
    {

        if (empty($content)) {
            return $content;
        }

        $item = $this->items[$this->pos];

        $id = $item->getId();

        $this->pos++;

        return "[".$this->shortcode." id=$id]".$content."[/".$this->shortcode."]";


    }

    function writeBack($content)
    {

        $this->pos = 0;

        add_shortcode($this->shortcode, array($this, 'doReplaceShortcode'));

        $content = do_shortcode($content);

        remove_shortcode($this->shortcode);

        return $content;

    }

}

