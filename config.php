<?php
/**
 *
 * This file is part of BibORB
 *
 * Copyright (C) 2003-2005  Guillaume Gardey (ggardey@club-internet.fr)
 *
 * BibORB is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * BibORB is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */

/**
 *
 * File: config.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *      Globals configurations variables. See each for details.
 *
 */

/**
 * Version of Biborb
 */
define("BIBORB_XML_VERSION","1.1");
define("BIBORB_VERSION","1.0");
define("BIBORB_RELEASE_DATE","21 December 2013");

/**
 * Path where is install biborb
 * You shouldn't modify it
 */
define("BIBORB_PATH",realpath("./index.php"));

/**
 * Localization
 * Available: en_US, fr_FR, de_DE, it_IT
 */
define("DEFAULT_LANG",'en_US');

/**
 * Show available languages on BibORB pages
 * TRUE/FALSE
 */
define("DISPLAY_LANG_SELECTION",TRUE);

/**
 * If TRUE, this will disable authentification.
 * All users will have the administrator status
 */
define("DISABLE_AUTHENTICATION",FALSE);

/**
 * Authentication methods: mysql, files
 * Used if DISABLE_AUTHENTICATION = FALSE
 */
define("AUTH_METHOD",'files');

/**
 * Google authentication
 */
const CLIENT_ID = '415026457171-fefl20lmod71a1bddusses4srauu3vkb.apps.googleusercontent.com';
const CLIENT_SECRET = 'HWf2UOJ1H4jWeLLg9jmDc0XE';
const APPLICATION_NAME = "BibORB";


/**
 *  Should a confirmation be displayed when deleting entries
 */
define("WARN_BEFORE_DELETING",TRUE);

/**
 * Should the abstract be present for each entry.
 */
define("DISPLAY_ABSTRACT",FALSE);

/**
 * Should action be represented by icons or not.
 */
define("DISPLAY_IMAGES",TRUE);

/**
 *  Sould action be represented by text or not.
 */
define("DISPLAY_TEXT",FALSE);

/**
 * List of all possible fields in a BibTeX record.
 * The '_' is mandatory.
 */
$bibtex_entries = array(
    "id",
    "address",
    "annote",
    "author",
    "booktitle",
    "chapter",
    "crossref",
    "edition",
    "editor",
    "howpublished",
    "institution",
    "journal",
    "key",
    "month",
    "note",
    "number",
    "organization",
    "pages",
    "publisher",
    "school",
    "series",
    "title",
    "type",
    "volume",
    "year",
    "abstract",
    "keywords",
    "url",
    "urlzip",
    "pdf",
    "ad_url",
    "ad_pdf",
    "ad_urlzip",
    "groups",
    "website",
    "longnotes",
    "link",
    "own",
    "read"
);

/**
 * Choose which fields to save when exporting an entry to bibtex
 * By default all fields are exported
 */
$fields_to_export = array('author',
			  'address',
			  'annote',
			  'booktitle',
			  'chapter',
			  'crossref',
			  'edition',
			  'editor',
			  'howpublished',
			  'institution',
			  'journal',
			  'key',
			  'month',
			  'note',
			  'number',
			  'organization',
			  'pages',
			  'publisher',
			  'school',
			  'series',
			  'title',
			  'type',
			  'volume',
			  'year');

/**
 * The CSS style file to use.
 */
define("CSS_FILE","css/style.css");

/**
 * Display sort in all/group/search view
 * If no, displayed only on search
 * TRUE/FALSE
 */
define("DISPLAY_SORT",TRUE);

/**
 * Default sort method: ID,title,year
 * and order: ascending/descending
 */
define("DEFAULT_SORT","ID");
define("DEFAULT_SORT_ORDER","ascending");

/**
 * Max number of references by page.
 */
define("MAX_REFERENCES_BY_PAGE",10);

/**
 * Keep an up-to-date BibTeX file.
 * If true, each time a bibliography is modified, a BibTeX file is updated
 * in its 'bibs' directory.
 */
define("GEN_BIBTEX",TRUE);

/**
 *  Activate the shelf mode.
 *  Additional action will be available to set the ownership of a reference
 *  and its reading status(read, read next, not read)
 * value: TRUE/FALSE
 */
define("SHELF_MODE",TRUE);

/**
 * umask - Set the mask to use when creating files.
 *
 */
define("UMASK",0111);

/**
 * dmask - Set the mask to use when creating directories.
 */
define("DMASK",0000);

/**
 *  List of file types that can be uploaded
 */
$valid_upload_extensions = array('ps','pdf','gz','bz2','zip');
?>
