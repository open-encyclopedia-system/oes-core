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

class OesPersonShortCodeItem {

    var $id;
    var $name;
    var $name_last;
    var $with_genitiv;
    var $firstname;
    var $lastname;
    var $title;
    var $birth = "";
    var $death = "";
    var $isNew = false;
    var $label = "";
    var $pos;
    var $gnd = "";
    var $viaf = "";
    var $lccn = "";
    var $rameau = "";
    var $worldcat = "";

    var $historical_persons = [];

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
    public function getNameLast()
    {
        return $this->name_last;
    }

    /**
     * @param mixed $name_last
     */
    public function setNameLast($name_last)
    {
        $this->name_last = $name_last;
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
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getBirth()
    {
        return $this->birth;
    }

    /**
     * @param mixed $birth
     */
    public function setBirth($birth)
    {
        $this->birth = $birth;
    }

    /**
     * @return mixed
     */
    public function getDeath()
    {
        return $this->death;
    }

    /**
     * @param mixed $death
     */
    public function setDeath($death)
    {
        $this->death = $death;
    }

    /**
     * @return mixed
     */
    public function getGnd()
    {
        return $this->gnd;
    }

    /**
     * @param mixed $gnd
     */
    public function setGnd($gnd)
    {
        $this->gnd = $gnd;
    }

    /**
     * @return mixed
     */
    public function getViaf()
    {
        return $this->viaf;
    }

    /**
     * @param mixed $viaf
     */
    public function setViaf($viaf)
    {
        $this->viaf = $viaf;
    }

    /**
     * @return mixed
     */
    public function getLccn()
    {
        return $this->lccn;
    }

    /**
     * @param mixed $lccn
     */
    public function setLccn($lccn)
    {
        $this->lccn = $lccn;
    }

    /**
     * @return mixed
     */
    public function getRameau()
    {
        return $this->rameau;
    }

    /**
     * @param mixed $rameau
     */
    public function setRameau($rameau)
    {
        $this->rameau = $rameau;
    }

    /**
     * @return mixed
     */
    public function getWorldcat()
    {
        return $this->worldcat;
    }

    /**
     * @param mixed $worldcat
     */
    public function setWorldcat($worldcat)
    {
        $this->worldcat = $worldcat;
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

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    

}

class OesPersonShortcode {

    var $persons = [];
    var $curMaxSeq = 0;
    var $pos = 0;

    /**
     * @return array
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * @param array $persons
     */
    public function setPersons($persons)
    {
        $this->persons = $persons;
    }

    
    function doParseExistingPersonShortcodes($atts, $content = null) {

        if (empty($content)) {
            return $content;
        }

        if (empty($atts)) {
            $atts = [];
        }

        $rec = new OesPersonShortCodeItem();

        $withGenitiv = false;

        if (preg_match("@['´’]s@", $content)) {
            $withGenitiv = true;
        }

        $xLabel = str_replace("'s","",$content);
        $xLabel = str_replace("´s","",$xLabel);
        $xLabel = str_replace("’s","",$xLabel);
        $xLabel = str_replace("s'","s",$xLabel);
        $xLabel = str_replace("s´","s",$xLabel);
        $xLabel = str_replace("s’","s",$xLabel);
        $xLabel = str_replace("–","-",$xLabel);

        $xLabelParts = preg_split("@\s*\(@", $xLabel, 2);

        $name = $xLabelParts[0];

        $suffix = trim(str_replace(")", "", $xLabelParts[1]));

        list ($birth, $death) = preg_split("@ *- *@", $suffix);
        $birth = preg_replace("@[^0-9]@", "", $birth);
        $death = preg_replace("@[^0-9]@", "", $death);

        $firstname = "";
        $lastname = "";
        $title = "";

        if (stripos($name, ",")!==false) {
            $parts = preg_split("@,\s*@", $name);
            $lastname = array_shift($parts);
            if (!empty($parts)) {
                $firstname = array_shift($parts);
            }
            if (!empty($parts)) {
                $title = array_shift($parts);
            }
        } else {

            if (preg_match("@<[a-z]+>(.*?)</[a-z]+>@", $name, $matches)) {
                list ($firstname, $title) = preg_split("@<[a-z]+>.*?</[a-z]+>@", $name, 2);
                $lastname = $matches[1];
                $name_last = $lastname;
            } else {
                $parts = preg_split("@\s+@", $name);
                $lastname = array_pop($parts);
                $firstname = implode(" ", $parts);
                $name_last = $lastname;
            }

        }

        $name = strip_tags($name);

        $rec->id = $atts['id'];

        $firstname = preg_replace("@[”“'”]@","",$firstname);

        $rec->name = trim($name);
        $rec->firstname = trim($firstname);
        $rec->lastname = trim($lastname);
        $rec->title = trim($title);
        $rec->name_last = $name_last;
        $rec->name_last = $name_last;
        $rec->with_genitiv = $withGenitiv;
        $rec->is_locked = array_key_exists('locked', $atts);

        /**
         * wenn wir die variable nicht explizit abfragen ob gesetzt oder nicht,
         * dann setzten wir das attribute der instanz auf null. das führt dazu,
         * dass im sub_field des repeater felds ein vorheriger wert nicht
         * überschrieben wird.
         */
            $rec->birth = $birth;

            $rec->death = $death;

            $rec->gnd = $atts['gnd'];

            //        $rec->lccn = $atts['lccn'];
//        $rec->viaf = $atts['viaf'];
//        $rec->rameau = $atts['rameau'];
//        $rec->worldcat = $atts['worldcat'];

        if ($rec->id > $this->curMaxSeq) {
            $this->curMaxSeq = $rec->id;
        }

        if (empty($rec->id)) {
            $rec->setNew(true);
        }



        $rec->setLabel($content);

        $rec->setPos($this->pos);

        $this->persons[] = $rec;

        $this->pos++;

        return $content;

    }

    function writeBackShort($atts, $content = null) {

    }

    function reset()
    {
        $this->persons = [];
        $this->seq = 1;
    }

    function parseContent($content)
    {

        add_shortcode('person', array($this, 'doParseExistingPersonShortcodes'));

        $content = do_shortcode($content);

        remove_shortcode('person');

        return $this->persons;

    }

    function assignNewIds()
    {

        /**
         * @var OesPersonShortCodeItem $person
         */
        foreach ($this->persons as &$person)
        {
            if ($person->isNew()) {
                $this->curMaxSeq++;
                $person->setId($this->curMaxSeq);
            }
        }

    }

    function doWriteBackPersonItems($atts, $content)
    {

        if (empty($content)) {
            return $content;
        }

        $person = $this->persons[$this->pos];

        $id = $person->getId();

        $this->pos++;

        return "[person id=$id locked=1]$content\[/person\]";


    }

    function writeBackPersonItems($content)
    {

        $this->pos = 0;

        add_shortcode('person', array($this, 'doWriteBackPersonItems'));

        $content = do_shortcode($content);

        remove_shortcode('person');

        return $content;

    }

    /**
     * @param $content
     * @param array $list
     * @return string
     */
    function renderText($content, $list = [])
    {

        $this->pos = 0;


        $persons = [];

        foreach ($list as $x) {
            $persons[$x['id']] = $x;
        }

        $this->historical_persons = $persons;

        add_shortcode('person', array($this, 'renderPersonItem'));

        $content = do_shortcode($content);

        remove_shortcode('person');

        return $content;

    }

    function renderPersonItem($atts, $content) {

        if (empty($atts)) {
            return $content;
        }

        $att_person_id = $atts['id'];

        try {

            $item =
                x_lookup_entry_in_array($this->historical_persons,
                    $att_person_id);

            $with_genitiv = $item['with_genitiv'];

            $name = $item['name'];

            $historical_person = $item['historical_person'];

            $content = $name;

            if ($historical_person) {
                $person =
                    dtm_1418_person_base::init($historical_person->ID);
                $gnd = $person->gnd_identifier;
                $dob_year = $person->dob_year;
                $dod_year = $person->dod_year;
                $permalink = $person->get_permalink();
            } else {
                return $content;
                $gnd = $item['gnd'];
                $dob_year = $item['birth'];
                $dod_year = $item['death'];
            }

            $dates = $dob_year;
            if ($dod_year) {
                $dates .= "-$dod_year";
            }

            $content .= " ($dates)";

            

            return <<<EOD
<a href="$permalink">$content</a>
EOD;


        } catch (Exception $e) {

        }

        return $content;

    }

}

