<?php

namespace OES\Popup;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Create the html representation of a note and prepare notes list.
 * (Called by shortcode)
 *
 * @param array $args Shortcode attributes (is mostly empty, unused).
 * @param string $content Content within the shortcode.
 *
 * @return string Return the html string representing a note.
 */
function render_single_note(array $args, string $content = ""): string
{
    /* get post id */
    global $oesNotes;
    $postID = $GLOBALS['post']->ID;

    /* get note number */
    $number = (!isset($oesNotes[$postID]) || count($oesNotes[$postID]['used_reference_numbers']) == 0) ?
        1 :
        max($oesNotes[$postID]['used_reference_numbers']) + 1;

    /* render content */
    $content = do_shortcode($content);

    /* replace paragraph characters */
    $content = str_replace(['<p>', '</p>'], ['', '<br /><br />'], $content);

    /* update class variable */
    if (!isset($oesNotes[$postID])) {

        if (!isset($GLOBALS['current_notes_post_number'])) $GLOBALS['current_notes_post_number'] = 0;

        $oesNotes[$postID] = [
            'notes_post_number' => $GLOBALS['current_notes_post_number'],
            'used_reference_numbers' => [$number],
            'notes' => [$number => $content]
        ];
        $GLOBALS['current_notes_post_number']++;
    } else {
        $oesNotes[$postID]['used_reference_numbers'][] = $number;
        $oesNotes[$postID]['notes'][$number] = $content;
    }

    /* replace note paragraphs */
    $content = str_replace(['<oesnotep>', '</oesnotep>'], ['', '<br/>'], $content);

    /* create note */
    return get_single_html(
        $number,
        '<sup id="popup' . $number . '">' . $number . '</sup>',
        $content,
        ['trigger' => 'oes-note',
            'popup' => 'oes_note_popup']);
}


/**
 * Create the html representation of the note list.
 *
 * @param mixed $args Shortcode attributes (is mostly empty, unused).
 * @param string $content Content within the shortcode.
 *
 * @return string Return the html string representing the note list.
 */
function get_rendered_notes_list($args, string $content = ""): string
{
    /* get global parameters */
    global $oesNotes;
    $postID = $GLOBALS['post']->ID;

    /* return early if no notes available */
    if (empty($oesNotes[$postID]) || !isset($oesNotes[$postID]['notes'])) return '';

    /* loop through notes */
    $content .= ($args['header'] ?? '') .'<ul class="oes-notes-list">';
    foreach ($oesNotes[$postID]['notes'] as $number => $note) {

        /* check for paragraphs */
        $modifiedNote = '';
        if (strpos($note, '<oesnotep>') || strpos($note, '<oesnotep>') === 0) {

            /* get text before and after paragraph */
            $prepareNote = preg_replace('/<oesnotep>/', 'OESSPLIT', $note, 1);
            $prepareNote = preg_replace('~</oesnotep>(?!.*</oesnotep>)~', 'OESSPLIT', $prepareNote);
            $prepareNoteSplit = preg_split('/(OESSPLIT)/', $prepareNote, 0);

            /* should contain three items BEFORE PARAGRAPHS AFTER */
            if (sizeof($prepareNoteSplit) == 3) {

                /* before text */
                if (!empty($prepareNoteSplit[0])) $modifiedNote .= '<div>' . $prepareNoteSplit[0] . '</div>';

                /* paragraphs */
                if (!empty($prepareNoteSplit[1])) {

                    $noteColumns = [];
                    $i = 0;
                    $paragraphs = preg_split('/(<oesnotep>|<\/oesnotep>)/',
                        $prepareNoteSplit[1],
                        0,
                        PREG_SPLIT_DELIM_CAPTURE);
                    if ($paragraphs) foreach ($paragraphs as $paragraph) {

                        if (empty($paragraph) || $paragraph == " ") continue;
                        elseif ($paragraph == "<oesnotep>") continue;
                        elseif ($paragraph == "</oesnotep>") continue;
                        elseif ($paragraph == "<br>") $i++;
                        else {
                            $noteColumns[$i][] = '<p>' . $paragraph . '</p>';
                            $i++;
                        }

                        /*if(empty($paragraph) || $paragraph == " ") continue;
                        elseif($paragraph == "<oesnotep>")$insidep = true;
                        elseif($paragraph == "</oesnotep>")$insidep = false;
                        elseif($paragraph == "<br>")$i++;
                        else{
                            if($insidep) $noteColumns[$i][] = '<p>' . $paragraph . '</p>';
                            else{
                                $i++;
                                $noteColumns[$i][] = '<p>' . $paragraph . '</p>';
                            }
                        }*/
                    }

                    /* put into columns */
                    if ($noteColumns) {
                        $modifiedNote .= '<div class="note-columns-' . sizeof($noteColumns) . ' row">';
                        foreach ($noteColumns as $noteContainer)
                            $modifiedNote .= '<div class="column">' . implode('', $noteContainer) . '</div>';
                        $modifiedNote .= '</div>';
                    }
                }

                /* after text */
                if (!empty($prepareNoteSplit[2])) $modifiedNote .= '<div>' . $prepareNoteSplit[2] . '</div>';
            }
        }

        $content .= sprintf('<li id="oes-note-list-%s"><span><a href="#popup%s">%s</a></span><div class="%s">%s</div></li>',
                $number,
                $number,
                $number,
                'note' . intval($number),
                empty($modifiedNote) ? $note : $modifiedNote);

    }
    $content .= '</ul>';

    return $content;
}


/**
 * Add replace variables.
 *
 * @param array $replace The current replace variables.
 */
function add_notes_replace_variable(array $replace): array
{
    $replace['<oesnote>'] = '[oes_popup type="note"]';
    $replace['</oesnote>'] = '[/oes_popup]';
    return $replace;
}


/**
 * Get html representation of end notes for the frontend.
 *
 * @param array $args Table parameter. Valid parameters are:
 *  'display-header'    : The header string. Default is 'End Notes'.
 *  'add-to-toc'        : Add header to table of contents. Default is true.
 *  'toc-level'         : If header is added to table of contents, define level. Default is 1.
 *
 * @return string Return the html table representation of note list.
 */
function get_html_notes(array $args = []): string
{
    /* only execute if notes exist */
    global $oesNotes, $oes_post;
    if (!$oes_post || !isset($oesNotes[$oes_post->object_ID])) return '';

    /* get header from options */
    $header = $args['header'] ?? '';
    if (!empty($header)) {

        $header = oes_generate_header_for_table_of_contents(
            $header,
            $args['level'] ?? 2,
            [
                'table-header-class' => 'oes-content-table-header' .
                    ($args['add-to-toc'] ? '' : ' oes-exclude-heading-from-toc'),
                'position' => 2
            ]);
    }

    $list = get_rendered_notes_list([
        'header' => $header
    ]);


    /**
     * Filters the notes list.
     *
     * @param string $list The notes list.
     */
    return apply_filters('oes/get_html_notes', $list);
}