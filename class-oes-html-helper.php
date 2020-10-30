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

oes_upload_vendor_autoload();

use PhpQuery\PhpQuery as phpQuery;

class Oes_Html_Helper
{

    public static function ConvertToEndNotes($html, $references)
    {

        $text_ctx = phpQuery::newDocument($html);

        $ref_ctx = phpQuery::newDocument($references);

        $refs = [];

        phpQuery::pq("li", $ref_ctx)->each(
            function ($v) use (&$refs) {

                $self = phpQuery::pq($v);

                $reftextN = phpQuery::pq(".reference-text", $v);

                $id = $self->attr("id");
                $text = $reftextN->text();

                $refs[$id] = $text;

            }
        );

        phpQuery::pq(".reference", $text_ctx)->each(

            function ($v) use (&$refs) {

                $self = phpQuery::pq("a", $v);

                $href = $self->attr("href");

                $href = str_replace('#', '', $href);

                $href = str_replace('cite-', 'cite_', $href);

                $reftext = $refs[$href];

                if (empty($reftext)) {
//                    $reftext = $refs[$href];
//                    if (empty($reftext)) {
                    $self->parent()->
                    replaceWith("");
                    return;
//?                    }
                }

                $self->parent()->
                replaceWith("[note]" . $reftext . "[/note]");

            }

        );

        return phpQuery::pq($text_ctx)->html();

    }

    public static function CleanHtml($text)
    {

        $text_ctx = phpQuery::newDocument($text);

        phpQuery::pq("h1,h2,h3,h4,h5,h6", $text_ctx)->each(

            function ($v) {

                $self = phpQuery::pq($v);

                $tag = $self->get()[0];

                $tagName = $tag->tagName;

                $text = $self->text();

                $self->replaceWith("<$tagName>$text</$tagName>");

            }
        );

        phpQuery::pq("a", $text_ctx)->each(

            function ($v) {

                $self = phpQuery::pq($v);

                $href = $self->attr('href');

                if ($href) {
                    $href = str_replace('_', '-', $href);
                    $self->attr('href', $href);
                }

            }
        );

        return phpQuery::pq($text_ctx)->html();

    }

    public static function ConvertLinksOfPersons($text)
    {

        $text_ctx = phpQuery::newDocument($text);

        phpQuery::pq("a", $text_ctx)->each(

            function ($v) {

                $self = phpQuery::pq($v);

                $tag = $self->get()[0];

                $tagName = $tag->tagName;

                $id = $self->attr("id");

                if (!startswith($id, "GND_")) {
                    return;
                }

                $gndid = str_replace("GND_", "", $id);

                $name = $self->text();

                $self->replaceWith("[person gnd=$gndid ]" . $name . '[/person]');

            }
        );

        return phpQuery::pq($text_ctx)->html();

    }

    /**
     * Build TOC structure out of parsed h2, h3, h4
     * tags found in html
     *
     * @param $html
     * @return Oes_Toc_Struct
     */
    public static function ParseToc($html)
    {
        $toc = new Oes_Toc_Struct($html);
        $toc->parse();
        return $toc;
    }


}

class Oes_Toc_Struct
{

    var $cur_level = 0;

    var $current;

    var $stack = [];

    var $parent = [];

    var $root = [];

    var $modified_text = false;

    var $children = [];

    var $html = false;

    var $toc = [];

    /**
     * Oes_Html_Helper constructor.
     * @param int $cur_level
     */
    public function __construct($html)
    {
        $this->html = $this->modified_text = $html;
    }


    function parse($html = null)
    {

        if (empty($html)) {
            $html = $this->html;
        }


        foreach (['h5' => 'h6', 'h4' => 'h5', 'h3' => 'h4', 'h2' => 'h3', 'h1' => 'h2'] as $tagsource => $tagtarget) {

            $html = str_replace('<' . $tagsource, '<' . $tagtarget, $html);
            $html = str_replace('</' . $tagsource, '</' . $tagtarget, $html);

            $tagsource = strtoupper($tagsource);

            $tagtarget = strtoupper($tagtarget);

            $html = str_replace('<' . $tagsource, '<' . $tagtarget, $html);
            $html = str_replace('</' . $tagsource, '</' . $tagtarget, $html);

        }

        $toc = [];


        $text_ctx = phpQuery::newDocument($html);

        $pos = 0;

        phpQuery::pq("*", $text_ctx)->each(

            function ($v) use (&$toc, &$pos) {

                if (@x_empty($v->tagName)) {
                    echo "ouch\n";
                    return;
                }

                $self = phpQuery::pq($v);

                $tag = $self->get()[0];

                $tagName = $tag->tagName;

                if ($tagName != 'h2' && $tagName != 'h3' && $tagName != 'h4') {
                    return;
                }

                $level = intval(preg_replace("@[^0-9]@", "", $tagName));

                $pos++;

                $text = $self->text();

                $transtext = transliterator_transliterate("Greek-Latin", $text);

                $normalizedtext = normalizeToSimpleSortAsciiWithSpace($transtext);

                $normalizedtext = str_replace(" ", "_", $normalizedtext);

                $tagid = "toc_$normalizedtext";

                $self->attr("id", $tagid);

                $self->html($text);

//                $tagid = 'toc_' . $pos;

                $toc[$tagid] =
                    ['id' => $tagid,
                        'tag' => $tagName,
                        'level' => $level,
                        'title' => $text,
                        'text' => $text];


            }
        );

        $this->toc = $toc;

        $this->root =
            ['label' => 'ROOT', 'is_root' => true, 'id' => 'toc_0'];

        $this->parent = $this->root;

        foreach ($this->toc as $it) {

            $it_level = $it['level'];

            $it_id = $it['id'];

            if (!$this->cur_level) {
                $this->current = $it;
                $this->cur_level = $it_level;
            }

            $cur_level = $this->cur_level;

            if ($it_level > $cur_level) {
                $this->stack[] = $this->parent;
                $this->parent = $this->current;
            } else if ($it_level < $cur_level) {
                $diff_level = $cur_level - $it_level;
                for ($i = 0; $i < $diff_level; $i++) {
                    $this->parent = array_pop($this->stack);
                }
            }

            $parent_id = $this->parent['id'];

            $this->children[$parent_id][] = $it_id;

            $this->cur_level = $it_level;

            $this->current = $it;

        }

        foreach ($this->stack as $st) {
            $this->parent = array_pop($this->stack);
        }

        $this->toc['toc_0'] = $this->root;

        foreach ($this->children as $parentid => $children) {
            $this->toc[$parentid]['children'] = $children;
        }

        $this->root = $this->toc['toc_0'];

        $this->modified_text = phpQuery::pq($text_ctx)->html();

    }

    function walk_ul(&$buffer = null, $skip_ul = false)
    {
        $this->build_ul($this->get_root(),
            0, "", $buffer, $skip_ul);
    }

    public function build_ul($it = null,
                             $level = 1,
                             $tocnumberprefix = "",
                             &$buffer = null, $skip_ul = false)
    {

        $children = $it['children'];

        if (empty($children)) {
            return;
        }

        if (!$skip_ul) {
            oes_html_open_tag("ul", false, false, $buffer);
        }


        $sublevel = 0;

        foreach ($children as $child) {
            $sublevel++;
            $subit = $this->find_by_id($child);
            oes_html_open_tag("li", "toc-li toclevel-$level toc-li-$level", "", $buffer);
            $this->build_li($subit, $sublevel, $tocnumberprefix, $buffer);
            oes_html_close_tag("li", $buffer);

        }

        if (!$skip_ul) {
            oes_html_close_tag("ul", $buffer);
        }


    }

    function find_by_id($id)
    {
        return $this->toc[$id];
    }

    public function build_li($it,
                             $level = 1,
                             $tocnumberprefix = "", &$buffer = null, $skip_ul = false)
    {


        $nodeid = $it['id'] . '-back';

        $nodelabel = $it['title'];
        $nodenumber = $tocnumberprefix . $level;

        $nodeurl = $it['id'];;

        $nodehtml = <<<EOD
<span class="tocnumber">$nodenumber</span>
<span class="toctext">$nodelabel</span>
EOD;

        $nodeahref = <<<EOD
<a id="$nodeid" class="toc-node-link toc-node-link-$level" href="#$nodeurl">$nodehtml</a>
EOD;

        oes_echo_or_buffer($nodeahref, $buffer);

        $tocnumberprefix = $level . ".";

        $this->build_ul($it, $level, $tocnumberprefix, $buffer);

    }

    function get_root()
    {
        return $this->toc['toc_0'];
    }

}

function oes_echo_or_buffer($str, &$buffer = false)
{
    if (is_array($buffer)) {
        $buffer[] = $str;
    } else {
        echo $str;
    }
}

function oes_html_open_tag($tag, $classes = "", $id = "", &$buffer = [])
{

    if ($classes) {
        $classes = <<<EOD
classes="$classes"
EOD;
    }

    if ($id) {
        $id = <<<EOD
id="$id"
EOD;
    }

    $str = <<<EOD
<$tag $classes $id>
EOD;

    oes_echo_or_buffer($str, $buffer);

}


function oes_html_single_tag($tag, $classes = "", $id = "", &$buffer)
{

    $str = <<<EOD
<$tag $classes $id/>
EOD;

    oes_echo_or_buffer($str, $buffer);

}

function oes_html_close_tag($tag, &$buffer)
{
    oes_echo_or_buffer("</$tag>", $buffer);
}

function oes_the_content($content = null, $more_link_text = null, $strip_teaser = false)
{
    if (!isset($content)) {
        $content = get_the_content($more_link_text, $strip_teaser);
    }
    $content = apply_filters('the_content', $content);
    $content = str_replace(']]>', ']]&gt;', $content);
    echo $content;
}