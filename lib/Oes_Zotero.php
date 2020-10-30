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

/*

https://api.zotero.org/groups/799391/items?format=json&itemKey=4DX7VUKT%2CHSF4S77S%2CMH38HU5G%2CJG2KJR9X%2C3MDG58D3%2CIIAM28D4%2CNP5CXAZJ%2CGCGXZ2ZI%2C49BXII8M%2CKUQCX6WF%2CEQUDX4XN%2CBXNPKPV3%2C7VGRSNGB%2CDNVVHKW8%2CIXDZ7WR5%2CDGC5DH63%2CXF3WQTN6%2C35GQN5D4%2CQCX7TUU4%2CVGWWS6EI%2CRAQN5UV2%2CETSASC93%2CRQWJHAQU%2CAPDPUMSI%2CD7IBKNKM%2CQPNJTZ45%2CJI658NIE%2CZD44AMI7%2C44SKUB73%2CV5UD9A6J%2CXE2F56QC%2CWBE9VAEE%2CZ47EBC8Z%2CD5NQUXQH%2C7DQK83NV%2CRFEDZHJ3%2CRM24TR7C%2CVJKDE9NG%2CIJ4V7GRF%2CUI6F6MFK%2CB3MI5MGU%2CXVBKN7ZE%2CZPHH8735%2C445EB2TD%2CEQDNZT8P%2C5GB7SUWE%2CI55PBDE7%2CVF567GED%2CZ6BKDX3E%2CT6KI36TJ&key=jlmZuelFHxJi6FP1ZgQhUu7L&style=https%3A%2F%2Fwww.zotero.org%2Fstyles%2Ffoerster-geisteswissenschaft
 */

class Oes_Zotero
{
    
    var $publicationStatus = Oes_General_Config::STATUS_PUBLISHED;
    
    var $zoteroPostType = false;
    
    var $dtmClass = false;

    var $collectionTaxonomy = false;

    static $zoteroCitationStyle = 'https://www.zotero.org/styles/technische-universitat-dresden-linguistik';

    const FETCH_BATCH_SIZE = 5;

    var $existingTitles = [];

    var $collectionsMapping = [];

    var $apikey;

    var $libraryType;

    var $libraryID;

    /**
     * @var Zotero_Library
     */
    var $connection;

    /**
     * Oes_Zotero constructor.
     * @param $apikey
     * @param $libraryType
     * @param $libraryID
     */
    public function __construct($apikey, $libraryType, $libraryID, $postType = '', $dtmClass = '')
    {
        $this->apikey = $apikey;
        $this->libraryType = $libraryType;
        $this->libraryID = $libraryID;
        $this->zoteroPostType = $postType;
        $this->dtmClass = $dtmClass;
    }

    function initLibrary()
    {

        if ($this->connection) {
            return $this->connection;
        }

        if (x_empty($this->dtmClass)) {
            throw new Exception("dtm class not set");
        }

        if (x_empty($this->apikey)) {
            throw new Exception("api key not set");
        }

        if (x_empty($this->libraryID)) {
            throw new Exception("library id not set");
        }

        if (x_empty($this->libraryType)) {
            throw new Exception("library type not set");
        }

        $this->connection =
            new Zotero_Library($this->libraryType, $this->libraryID, null, $this->apikey);

        return $this->connection;

    }

    static function matchTaxonomies($zotCollections)
    {

        $mapping = Oes_General_Config::ZOTERO_ISFWW_MAPPING;

        foreach ($zotCollections as $col) {

            $colItemKey = $col['itemKey'];

            $maps = $mapping[$colItemKey];

            if (is_array($maps)) {
                foreach ($maps as $termslug => $term) {
                    $taxonomy = $term[1];
                    if ($taxonomy == 'regions') {
                        $regions[] = $termslug;
//                    echo "add region: $termslug $pid\n";
                    } else if ($taxonomy == 'themes') {
                        $themes[] = $termslug;
//                        echo "add theme: $termslug $pid\n";
                    }

                }

            } else if ($maps == -1 || !empty($maps)) {
                throw new Exception("not importing $colItemKey\n");
            } else {

            }
        }

        return [$regions, $themes];

    }

    static function matchLanguageCodesToLabels($lang)
    {
        if (empty($lang)) {
            return $lang;
        }

        $orig_lang = $lang;

        $langmapping = Oes_General_Config::LANGUAGE_CODES_TO_ENGLISH_LABELS;

        $labels = [];

        if (array_key_exists($lang, $langmapping)) {

            $labels[] = $langmapping[$lang];

        } else {

            $lang = preg_split('@[ /\&;,]@', $lang);

            foreach ($lang as $lng) {
                list($lng,) = preg_split("@[_\-]@", $lng, 2);
                $lng = trim($lng);
                if (empty($lng)) {
                    continue;
                }
                if (!array_key_exists($lng, $langmapping)) {
                    error_log("language not found [$lng] $orig_lang");
                } else {
                    $labels[] = $langmapping[$lng];
                }
            }

        }

        $languagestr = implode(',', $labels);

        return $languagestr;

    }

    public function loadCollectionsMapping($mapping)
    {
        $this->collectionsMapping = $mapping;
    }

    public function loadExistingTitlesIntoCache($filePath = null)
    {

        $posts = oes_wp_query_posts($this->zoteroPostType
            ,
            null,
//            [
//            [
//                'key' => 'status',
//                'value' => $this->publicationStatus
//            ]
//        ]
            [
            'post_status' => ['draft', 'publish'],
            'fields' => 'ids',
        ]);

        $total = count($posts);

        $entries = [];

        if ($filePath && file_exists($filePath)) {
            $entries = json_decode(file_get_contents($filePath), true);
        } else {

            foreach ($posts as $pid) {

                $dtm = oes_dtm_form::init($pid);

                $itemKey1 = $dtm->zot_itemKey;
                $itemVersion1 = $dtm->zot_itemVersion;
                $zotTitle = $dtm->zot_title;
                $libraryId = $dtm->zot_libraryId;

                $normalizedTitle = normalizeToSimpleSortAscii($zotTitle);

                $count++;

                $entries[$normalizedTitle] = [$zotTitle, $zotTitle, $itemKey1, $libraryId, $pid];

                if (($count % 500) == 0) {
                    error_log("zotero: loading titles $count/$total");
                }

            }


        }

        $this->existingTitles = $entries;

    }

    function addToExistingTitles($zotTitle, $title, $key, $libraryId, $pid)
    {
        $normalizedTitle = normalizeToSimpleSortAscii($title);
        $this->existingTitles[$normalizedTitle] = [$zotTitle, $title, $key, $libraryId, $pid];
    }

    function checkIfTitleExists($title)
    {
        $normalizedTitle = normalizeToSimpleSortAscii($title);
        return (array_key_exists($normalizedTitle, $this->existingTitles));
    }

    function getExistingTitleEntry($title)
    {
        $normalizedTitle = normalizeToSimpleSortAscii($title);
        return $this->existingTitles[$normalizedTitle];
    }

    public function import($importcount = 1000000, $isUserContent = false, $userProfile = null)
    {

        $this->initLibrary();
        

        $error_reporting = error_reporting(E_ALL & ~E_NOTICE);

        ini_set('max_execution_time', 10000);
        ini_set('memory_limit', '6G');

        $isRealImport = hasparam('real') || hasparam('step1');

        // check existing zotero entries

        $postids = oes_wp_query_post_ids($this->zoteroPostType, [
            [
                'key' => 'zot_libraryId',
                'value' => $this->libraryID
            ]
        ], [
            'post_status' => ['draft', 'publish']
        ]);

        error_log("count posts " . count($postids));

//        $args = [
//            'post_type' => $wissenBasisPostType,
//            'posts_per_page' => -1,
//
//        ];
//
//        $r = new WP_Query($args);

        global $post;

        $curItemVersions = [];

        $existingWissensbasisEntriesWhichCanBeDeleted = array();

        $totalPostCount = $r->post_count;


        x_error_log("# fetching");

        list ($curItemVersions,
            $existingWissensbasisEntriesWhichCanBeDeleted,
            $idByItemKey) = x_apcFetch("zotero.local.version.3.$libraryID", function () use (&$postids, $totalPostCount) {

            $curItemVersions = [];
            
            foreach ($postids as $pid) {

                $count1++;

                $itemKey1 = get_field('zot_itemKey', $pid);
                $itemVersion1 = get_field('zot_itemVersion', $pid);

//                    $itemKey1 = $bib->zot_itemKey;
//                    $itemVersion1 = $bib->zot_itemVersion;

                if ($count1 % 500 == 0) {
                    x_error_log("loading post $count1/$totalPostCount $itemKey1 $itemVersion1");
                }

                $curItemVersions[$itemKey1] = $itemVersion1;
                $existingWissensbasisEntriesWhichCanBeDeleted[$itemKey1][] = $pid;
                $idByItemKey[$itemKey1] = $pid;

            }

            return [$curItemVersions, $existingWissensbasisEntriesWhichCanBeDeleted, $idByItemKey];

        }, 3600 * 1, true);


//        $libraryType = 'group'; //user or group
//        $libraryType = 'user'; //user or group
//        $libraryID = 20877;
//        $librarySlug = '';
//        $apiKey = 'H09jnrSSD2hXdXZSyQIhFvG6';
//        $collectionKey = '';



        $toBeDeleted = [];

        $itemVersionByKey = $curItemVersions;

        $fetchedVersions = $this->connection->fetchItemVersions();

        $fetchedVersionsTmp = $fetchedVersions;

        $toBeUpdated = array();

        $toBeCreated = array();

        $toBeLoaded = array();

        x_error_log("# fetched versions " . count($fetchedVersions));

        x_error_log("# current items in db " . count($curItemVersions));

        foreach ($fetchedVersions as $k => $f) {

            $found = $curItemVersions[$k];

            unset($existingWissensbasisEntriesWhichCanBeDeleted[$k]);

            if (empty($found)) {
                $toBeCreated[$k] = $k;
            } else {
                unset($toBeDeleted[$k]);
                if ($f != $found) {
                    $toBeUpdated[$k] = $f;
                }
            }

        }


        // cache time


        $items = array();

        $sliceOffset = 0;

//        IlkersGoodies::$DISABLE_AUTO_PURGE = true;

        $totalNum = 0;

        x_error_log("to be loaded: " . count($toBeUpdated));
        x_error_log("to be created: " . count($toBeCreated));
        x_error_log("to be deleted: " . count($existingWissensbasisEntriesWhichCanBeDeleted));
//        x_error_log("to be deleted (no more existing in zotero): " . count($existingWissensbasisEntriesWhichCanBeDeleted));
//        x_error_log("to be deleted (no more existing in zotero): " . print_r($existingWissensbasisEntriesWhichCanBeDeleted, true));

        if (is_array($existingWissensbasisEntriesWhichCanBeDeleted))

            foreach ($existingWissensbasisEntriesWhichCanBeDeleted as
                     $itemKey_1 => $listOf_wbEntryId_1) {

                foreach ($listOf_wbEntryId_1 as $wbEntryId_1) {
                    try {
                        $deletedtm = oes_dtm_form::init($wbEntryId_1);
                        $deletedtm->trash();
                    } catch (Exception $e) {

                    }
                }

            }


//        $existingItems = [];

        $this->connection->setCacheTtl(0);

        if (!empty($toBeUpdated)) {

            $items = $this->connection->fetchItemsInJson(
                array_keys($toBeUpdated), self::$zoteroCitationStyle, $importcount);

        }

        $count = 0;
        $total = count($items);

        foreach ($items as $pos => $item) {

            $itemKey = $item['key'];

            $id = $idByItemKey[$itemKey];

            if (empty($id)) {
                throw new Exception("id of existing item $itemKey not found");

            }

            try {
                $this->create_bib_entry($item, $id, $this->libraryID, $count, $total);
            } catch (Exception $e) {
                error_log("error in updating $id " . $e->getMessage());
            }

            $count++;

            x_error_log("updating $bib->zot_title $count/$total");

//            if ($count == 50) {
//                break;
//            }

        }

//        file_put_contents(__DIR__ . "/db/items.json", json_encode($items));

        $totalNum = 0;

        $this->connection->setCacheTtl(7 * 24 * 60 * 60);

        if (!empty($toBeCreated)) {

            $items = $this->connection->fetchItemsInJson(
                array_keys($toBeCreated),
                '',
//                'https://www.zotero.org/styles/foerster-geisteswissenschaft',
                $importcount);

        }

        $toBePurged = array();

        $count = 0;
        $total = count($items);

        $countBibCreate = 0;

//        file_put_contents(__DIR__."/db/items.json",json_encode($items));

        foreach ($items as $pos => $item) {

            $itemkey = $item['key'];

            if (array_key_exists($itemkey, $toBeUpdated)) {
                continue;
            }

            try {
                $bib = $this->create_bib_entry($item, null, $libraryID, $count, $total, $isUserContent, $userProfile);
            } catch (Exception $e) {
                error_log("error in creating " . $e->getMessage());
                continue;
            }

            $count++;

            if ($count == $importcount) {
                break;
            }

        }

    }

    function create_bib_entry($item, $id = null, $libraryID, $pos = 0, $total = 0, $isUserContent = false, $userProfile = null)
    {

        $isUpdate = false;

        $dtmClass = $this->dtmClass;

        if ($id) {
            $bib = $dtmClass::init($id);
            $isUpdate = true;
        } else {
            $bib = $dtmClass::create();
            $bib->post_status = 'publish';
        }

        $itemKey = $item['key'];

        $itemBib = $item['bib'];

        $bib->post_name = $itemKey;

        $bib->zot_libraryId = $this->libraryID;

        $itemCitation = $item['citation'];

        $zotJSON = $item['data'];

        $zotType = $zotJSON['itemType'];

        if ($zotType == 'note') {
            throw new Exception("note not imported");
        }

        if ($zotType == 'attachment') {
            throw new Exception("attachment not imported");
        }

        //

        $zotCollections = $zotJSON['collections'];

        $zotTags = $zotJSON['tags'];

        if (is_array($zotCollections)) {
            $res = [];
            foreach ($zotCollections as $col) {
                $res[] = ['itemKey' => $col];
            }
            $zotJSON['collections'] = $res;
        }

        //

//        Oes_Dtm_EoArticle::matchArticlesInBibliographyTitle($bib, $zotTags);

        $zotTitle = $zotJSON['title'];

        /*
                if (preg_match('@, (\d+) volumes@', $zotTitle, $matches)) {
                    $zotJSON['numberOfVolumes'] = $matches[1];
                    $zotTitle = preg_replace("@, (\d+) volumes@", "", $zotTitle);
                    $zotJSON['title'] = $zotTitle;
        //            x_error_log("title # volumes $matches[1]");
                }

                if (preg_match('@, volume (\d+)@', $zotTitle, $matches)) {
                    $zotJSON['volume'] = $matches[1];
                    $zotTitle = preg_replace("@, volume (\d+)@", "", $zotTitle);
                    $zotJSON['title'] = $zotTitle;
        //            x_error_log("title # volume $matches[1]");
                }

        */

        if (preg_match('@ \((\d+) ed\.?\)@', $zotTitle, $matches)) {
            $zotJSON['edition'] = $matches[1];
            $zotTitle = preg_replace("@ \(\d+ ed\.\)@", "", $zotTitle);
            $zotJSON['title'] = $zotTitle;
//            x_error_log("edition $matches[1]");
        }


        //

        foreach ($zotJSON as $fieldName => $fieldValue) {
            $bib->{"zot_$fieldName"} = $fieldValue;
        }

        $zotLanguage = $zotJSON['language'];

//        if (!empty($zotLanguage)) {
//            $bib->zot_language =
//        }

        $contributors_as_authors = [];
        $contributors_as_editors = [];


        //        $zotCreators = $zotJSON['creators'];
//
//        if (!is_array($zotCreators)) {
//            x_error_log("no creators in $itemKey $zotTitle");
//        } else {
//            foreach ($zotCreators as $creator) {
//
//                $type = $creator['creatorType'];
//
//                if ($type != 'author' && $type != 'editor') {
//                    continue;
//                }
//
//                $name = $creator['name'];
//
//                if (empty($name)) {
//                    $lastname = $creator['lastName'];
//                    $firstname = $creator['firstName'];
//                    $name = trim("$firstname $lastname");
//                }
//
//                if (empty($name)) {
//                    continue;
//                }
//
//                $dupname = x_str_duplicate_match($name);
//
//                try {
//
//                    $contributorid =
//                        $this->lookupArticleBySimpleName($dupname);
//
//                    x_error_log("*** name $name found ($dupname)");
//
//                    if ($type == 'author') {
//                        $contributors_as_authors[$contributorid] = $contributorid;
//                    } else if ($type == 'editor') {
//                        $contributors_as_editors[$contributorid] = $contributorid;
//                    }
//
//                } catch (Exception $e) {
//
//                    x_error_log("name $name not found ($dupname)");
//
//                }
//
//
//            }
//        }
//
//        $bib->u_matching_contributor_as_bib_author = $contributors_as_authors;
//
//        $bib->u_matching_contributor_as_bib_editor = $contributors_as_editors;

        $bib->zot_bibliography = $itemBib;

        $bib->zot_citation = $itemCitation;

        $bib->zot_import = 1;

        $bib->zot_style = self::$zoteroCitationStyle;

        $isDuplicate = false;

        $existingItemBib = null;

        if (!$isUpdate) {

            $isDuplicate = $this->checkIfTitleExists($zotTitle);

            if ($isUserContent) {


                if ($isDuplicate) {

                    // if it's a duplicate we will save the pid of the existing
                    // title
                    $existingItem = $this->getExistingTitleEntry($zotTitle);

                    $existingItemPid = null;

                    if ($existingItem) {
                        $existingItemPid = $existingItem[4];
                    }

                    $bib->status = Oes_General_Config::STATUS_DUPLICATE_CONFIRMED;

                    $bib->duplicate_bib = $existingItemPid;


                } else {
                    $bib->status = $this->publicationStatus;
                }

            } else if ($isDuplicate) {

                $existingItem = $this->getExistingTitleEntry($zotTitle);

                if ($existingItem) {

                    $existingItemPid = $existingItem[4];

                    $existingItemBib = $this->dtmClass::init($existingItemPid);

                    $existingItemBib->status = Oes_General_Config::STATUS_DUPLICATE_CONFIRMED;

                    $existingItemBib->save();

                }

                $bib->status = $this->publicationStatus;

            } else {
                $bib->status = $this->publicationStatus;
            }

        }


//        try {
//            list ($regions, $themes) = Oes_Zotero::matchTaxonomies($bib->zot_collections);
//        } catch (Exception $e) {
//            $bib->status = Oes_General_Config::STATUS_ON_HOLD;
//        }
//
//        $bib->user_regions = $regions;
//        $bib->user_themes = $themes;

//        $bib->zot_language = Oes_Zotero::matchLanguageCodesToLabels($bib->zot_language);
//        $bib->zot_language = Oes_Zotero::matchLanguageCodesToLabels($bib->zot_language);


        $bib->x_is_user_content = $isUserContent;

        if ($isUserContent && $userProfile) {
            /**
             * @var \dtm_1418_contributor_base $userProfile
             */
            $bib->uploaded_by_profile = $userProfile;
            $bib->uploaded_by_date = time();
            $bib->uploaded_by_name = $userProfile->name_listing;
            $bib->uploaded_by_email = $userProfile->email;
        }

        $bib->save();

        $id = $bib->ID();

        if ($existingItemBib) {
            // previously existing title uploaded by title was now marked as a duplicate
            // so we have to save the id of the new title with the previous existing entity
            $existingItemBib->duplicate_bib = $id;
            $existingItemBib->save();
        }

        $this->addToExistingTitles($zotTitle, $zotTitle, $itemKey, $libraryID, $id);

        $str = <<<EOD
<a target="_blank" href="/wp-admin/post.php?post=$id&action=edit">$itemCitation</a>
EOD;

        if ($isUpdate) {
            $str = "Updated[$pos/$total]: $str";
        } else {
            $str = "Created[$pos/$total]: $str";
        }

        x_error_log($str);

        return $bib;

    }

    static function syncWithZotero($groupid = false,$key = false, $postType,$dtmClass,$importCount=10)
    {

        ini_set("output_buffering", "Off");

        $zot = new Oes_Zotero($key,'group',$groupid,$postType,$dtmClass);

//        $zot->loadExistingTitlesIntoCache();


        ?>
        <h2>Synchronizing Zotero Library: <?php echo $postType; ?></h2>
        <pre><?php

            //$zot->import("group", "163113", "jlmZuelFHxJi6FP1ZgQhUu7L", $importcount);
            $zot->import($importCount, $isUserContent, $userProfile);

            ?></pre>
        <h3>Synchronizing Zotero entries finished, cleaning up …</h3>

        <?php add_action("oes/dtm/resolve_done", function () {
        ?><h2>Clean up finished.</h2><?php
    }, 10, 0);
    }

    public static function buildCreatorName($o, $firstNameFirst = false)
    {

        $lastName = $o['lastName'];

        $firstName = $o['firstName'];

        $name = $o['name'];

        $two_fields = $o['two_fields'];

        if (!empty($name)) {
            return $name;
        }
        $parts = [];

        if ($firstNameFirst) {

            if ($firstName) {
                $parts[] = $firstName;
            }

            if ($lastName) {
                $parts[] = $lastName;
            }

            $name_str = implode(" ", $parts);

        } else {

            if ($lastName) {
                $parts[] = $lastName;
            }

            if ($firstName) {
                $parts[] = $firstName;
            }

            $name_str = implode(", ", $parts);

        }

        return $name_str;

    }

    const attr_zot_itemType = 'zot_itemType';
    const attr_zot_title = 'zot_title';
    const attr_zot_bookTitle = 'zot_bookTitle';
    const attr_zot_creators = 'zot_creators';
    const attr_zot_series = 'zot_series';
    const attr_zot_seriesNumber = 'zot_seriesNumber';
    const attr_zot_volume = 'zot_volume';
    const attr_zot_numberOfVolumes = 'zot_numberOfVolumes';
    const attr_zot_edition = 'zot_edition';
    const attr_zot_place = 'zot_place';
    const attr_zot_publisher = 'zot_publisher';
    const attr_zot_date = 'zot_date';
    const attr_zot_pages = 'zot_pages';
    const attr_zot_language = 'zot_language';
    const attr_zot_caseName = 'zot_caseName';
    const attr_zot_nameOfAct = 'zot_nameOfAct';
    const attr_zot_subject = 'zot_subject';
    const attr_zot_dictionaryTitle = 'zot_dictionaryTitle';
    const attr_zot_programTitle = 'zot_programTitle';
    const attr_zot_blogTitle = 'zot_blogTitle';
    const attr_zot_code = 'zot_code';
    const attr_zot_reportNumber = 'zot_reportNumber';
    const attr_zot_reporter = 'zot_reporter';
    const attr_zot_distributor = 'zot_distributor';
    const attr_zot_presentationType = 'zot_presentationType';
    const attr_zot_letterType = 'zot_letterType';
    const attr_zot_manuscriptType = 'zot_manuscriptType';
    const attr_zot_mapType = 'zot_mapType';
    const attr_zot_publicationTitle = 'zot_publicationTitle';
    const attr_zot_committee = 'zot_committee';
    const attr_zot_billNumber = 'zot_billNumber';
    const attr_zot_videoRecordingFormat = 'zot_videoRecordingFormat';
    const attr_zot_forumTitle = 'zot_forumTitle';
    const attr_zot_encyclopediaTitle = 'zot_encyclopediaTitle';
    const attr_zot_thesisType = 'zot_thesisType';
    const attr_zot_artworkMedium = 'zot_artworkMedium';
    const attr_zot_websiteTitle = 'zot_websiteTitle';
    const attr_zot_country = 'zot_country';
    const attr_zot_proceedingsTitle = 'zot_proceedingsTitle';
    const attr_zot_versionNumber = 'zot_versionNumber';
    const attr_zot_reporterVolume = 'zot_reporterVolume';
    const attr_zot_university = 'zot_university';
    const attr_zot_postType = 'zot_postType';
    const attr_zot_artworkSize = 'zot_artworkSize';
    const attr_zot_episodeNumber = 'zot_episodeNumber';
    const attr_zot_seriesTitle = 'zot_seriesTitle';
    const attr_zot_interviewMedium = 'zot_interviewMedium';
    const attr_zot_scale = 'zot_scale';
    const attr_zot_codeNumber = 'zot_codeNumber';
    const attr_zot_reportType = 'zot_reportType';
    const attr_zot_websiteType = 'zot_websiteType';
    const attr_zot_audioFileType = 'zot_audioFileType';
    const attr_zot_issue = 'zot_issue';
    const attr_zot_conferenceName = 'zot_conferenceName';
    const attr_zot_publicLawNumber = 'zot_publicLawNumber';
    const attr_zot_audioRecordingFormat = 'zot_audioRecordingFormat';
    const attr_zot_assignee = 'zot_assignee';
    const attr_zot_genre = 'zot_genre';
    const attr_zot_codeVolume = 'zot_codeVolume';
    const attr_zot_court = 'zot_court';
    const attr_zot_issuingAuthority = 'zot_issuingAuthority';
    const attr_zot_dateEnacted = 'zot_dateEnacted';
    const attr_zot_meetingName = 'zot_meetingName';
    const attr_zot_system = 'zot_system';
    const attr_zot_docketNumber = 'zot_docketNumber';
    const attr_zot_firstPage = 'zot_firstPage';
    const attr_zot_codePages = 'zot_codePages';
    const attr_zot_numPages = 'zot_numPages';
    const attr_zot_patentNumber = 'zot_patentNumber';
    const attr_zot_documentNumber = 'zot_documentNumber';
    const attr_zot_institution = 'zot_institution';
    const attr_zot_network = 'zot_network';
    const attr_zot_url = 'zot_url';
    const attr_zot_accessDate = 'zot_accessDate';
    const attr_zot_label = 'zot_label';
    const attr_zot_studio = 'zot_studio';
    const attr_zot_filingDate = 'zot_filingDate';
    const attr_zot_company = 'zot_company';
    const attr_zot_section = 'zot_section';
    const attr_zot_programmingLanguage = 'zot_programmingLanguage';
    const attr_zot_dateDecided = 'zot_dateDecided';
    const attr_zot_session = 'zot_session';
    const attr_zot_legislativeBody = 'zot_legislativeBody';
    const attr_zot_applicationNumber = 'zot_applicationNumber';
    const attr_zot_runningTime = 'zot_runningTime';
    const attr_zot_history = 'zot_history';
    const attr_zot_seriesText = 'zot_seriesText';
    const attr_zot_priorityNumbers = 'zot_priorityNumbers';
    const attr_zot_ISSN = 'zot_ISSN';
    const attr_zot_journalAbbreviation = 'zot_journalAbbreviation';
    const attr_zot_issueDate = 'zot_issueDate';
    const attr_zot_ISBN = 'zot_ISBN';
    const attr_zot_references = 'zot_references';
    const attr_zot_DOI = 'zot_DOI';
    const attr_zot_legalStatus = 'zot_legalStatus';
    const attr_zot_archive = 'zot_archive';
    const attr_zot_archiveLocation = 'zot_archiveLocation';
    const attr_zot_callNumber = 'zot_callNumber';
    const attr_zot_libraryCatalog = 'zot_libraryCatalog';
    const attr_zot_note = 'zot_note';
    const attr_zot_import = 'zot_import';
    const attr_zot_itemKey = 'zot_itemKey';
    const attr_zot_itemVersion = 'zot_itemVersion';
    const attr_zot_libraryId = 'zot_libraryId';
    const attr_zot_parentItem = 'zot_parentItem';
    const attr_zot_dateAdded = 'zot_dateAdded';
    const attr_zot_dateModified = 'zot_dateModified';
    const attr_zot_tags = 'zot_tags';
    const attr_zot_unmatched_tags = 'zot_unmatched_tags';
    const attr_zot_collections = 'zot_collections';
    const attr_zot_style = 'zot_style';
    const attr_zot_bibliography = 'zot_bibliography';
    const attr_zot_citation = 'zot_citation';
    const attr_citation_key = 'citation_key';
    const attr_zot_extraKeyValues = 'zot_extraKeyValues';

    const ZOT_ITEM_TYPE_LABELS_DE = [
        'attachment' => 'Anhang',
        'blogPost' => 'Blogartikel',
        'book' => 'Buch',
        'bookSection' => 'Buchteil',
        'document' => 'Dokument',
        'thesis' => 'Doktorarbeit',
        'encyclopediaArticle' => 'Enzyklopädie-Artikel',
        'film' => 'Film',
        'thesis' => 'Hochschulschrift',
        'conferencePaper' => 'Konferenzbeitrag',
        'journalArticle' => 'Journal-Artikel',
        'magazineArticle' => 'Magazin-Artikel',
        'manuscript' => 'Manuskript',
        'note' => 'Notiz',
        'presentation' => 'Präsentation',
        'radioBroadcast' => 'Radiosendung',
        'webpage' => 'Webbeitrag',
        'website' => 'Webseite',
        'dictionaryEntry' => 'Wörterbucheintrag',
        'newspaperArticle' => 'Zeitungsartikel',
        'journalArticle' => 'Zeitschriftenartikel',
    ];

}