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
 * File: bibindex.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *      The aim of bibindex.php is to allows the consultation of a given bibliography.
 *
 *      If the user has not the adminstrator status (not logged in), he is only
 *  able to consult the bibliography. Otherwise, he may edit, add or modify entries
 *  in the bibliography.
 *
 *      Some basic operations on the bibliography are supported:
 *          * a basket allows to record a subset of the bibliography, you may
 *              - reset groups to which the entries of the basket belong,
 *              - add a group to each entries of the basket,
 *              - export the selection to BibTeX,
 *              - export the selection to a simple HTML output.
 *          * display all the entries present in the bibliography, ordered by BibTeX key,
 *          * display all entries belonging to a given group,
 *          * basic search engine (one word) over authors, titles, and keywords.
 *
 *      BibORB may be used to create a new bibliography, but also support importation of a
 *  well-formed BibTeX bibliography (update from BibTeX in the manager menu).
 *
 *      BibORB also support access to a given article in a given bibliography directly:
 *  'bibindex.php?mode=details&abstract=1&menu=0&bibname=example&id=idA', will display the
 *  article of ID idA of the bibliography 'example'. The article will be displayed with its
 *  abstract if defined and the BibORB menu will not be displayed.
 *
 *
 *      Concerning the method that is used to manipulate the bibliography, everything is done
 *  using XML/XSLT. Each time a modification is performed, the BibTeX file is updated by converting
 *  the XML file into BibTeX. For XSLT experts, there are some 'curious' XSLT stylesheet. This is
 *  mainly because I encountered problems using some transformations (xsl:copy and namespace) with
 *  the PHP XSLT processor, and also because I have not currently the time to investigate more. Any
 *  comments, solutions to deal with this will be welcomed...
 *
 */

/**
 * loads some functions
 */

require_once("config.php"); // globals definitions
require_once("php/functions.php"); // functions
require_once("php/basket.php"); // basket functions
require_once("php/biborbdb.php"); // database
require_once("php/xslt_processor.php"); // xslt processing
require_once("php/interface-bibindex.php"); // generate interface
require_once("php/auth.php"); // authentication
require_once("php/third_party/Tar.php"); // Create a tar.gz archive
require_once("php/error.php"); // error handling
require_once("php/i18n.php");       // load i18n functions

/**
 * Session
 */
session_cache_limiter('nocache');
session_name("SID");
session_start();

// Set the error_handler
set_error_handler("biborb_error_handler");

// remove slashes from variables
if(get_magic_quotes_gpc()) {
    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}


/**
 * i18n, choose default lang if not set up
 * Try to detect it from the session, browser or fallback to default.
 */
if(!array_key_exists('language',$_SESSION)){
    if( ($prefLang = get_pref_lang()) !== FALSE){
        if(!array_key_exists($prefLang,$available_locales))
            $prefLang = DEFAULT_LANG;
    }
    $_SESSION['language'] = $prefLang;
    load_i18n_config($_SESSION['language']);
}

/*
    Global variables to store an error message or a standard message.
 */
$error = null;
$message = null;

/*
    Display an error if there is no active bibtex database
 */
if(!array_key_exists('bibdb',$_SESSION) && !array_key_exists('bibname',$_GET)){
    trigger_error("Bibliography's name is not set!",ERROR);
}

/*
    If the basket doesn't exists, create it.
 */
if(!isset($_SESSION['basket'])){
    $_SESSION['basket'] = new Basket();
}

/*
    If the session variable 'bibdb' is not set, get the bibliography name from
    GET variables and create a new Biborb_Database.
 */
$update_auth = FALSE;
if(!array_key_exists('update_authorizations',$_SESSION)){
    $update_auth = TRUE;
}

if(array_key_exists('bibname',$_GET)){
    if(!array_key_exists('bibdb',$_SESSION)){
        $_SESSION['bibdb'] = new BibORB_Database($_GET['bibname'],GEN_BIBTEX);
        $_SESSION['bibdb']->set_BibORB_fields($GLOBALS['bibtex_entries']);
        $_SESSION['basket']->reset();
    }
    else if($_SESSION['bibdb']->name()!=$_GET['bibname']){
        $_SESSION['bibdb'] = new BibORB_Database($_GET['bibname'],GEN_BIBTEX);
        $_SESSION['bibdb']->set_BibORB_fields($GLOBALS['bibtex_entries']);
        $_SESSION['basket']->reset();
    }
    $update_auth = TRUE;
}

/*
    Set the authorization levels
 */
if(!DISABLE_AUTHENTICATION){
    if(!array_key_exists('auth',$_SESSION)){
        $_SESSION['auth'] = new Auth(AUTH_CRYPT);
    }

    if($update_auth){
        if(!array_key_exists('user',$_SESSION)){
            $_SESSION['user_is_admin'] = FALSE;
        }
        else{
            $_SESSION['user_is_admin'] = $_SESSION['auth']->is_admin_user($_SESSION['user']);
        }
        if(!array_key_exists('user',$_SESSION)){
            $_SESSION['user_can_add'] = $_SESSION['auth']->can_add_entry("",$_SESSION['bibdb']->name());
        }
        else{
            $_SESSION['user_can_add'] = $_SESSION['auth']->can_add_entry($_SESSION['user'],$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
        }

        if(!array_key_exists('user',$_SESSION)){
            $_SESSION['user_can_delete'] = $_SESSION['auth']->can_delete_entry("",$_SESSION['bibdb']->name());
        }
        else{
            $_SESSION['user_can_delete'] = $_SESSION['auth']->can_delete_entry($_SESSION['user'],$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
        }

        if(!array_key_exists('user',$_SESSION)){
            $_SESSION['user_can_modify'] = $_SESSION['auth']->can_modify_entry("",$_SESSION['bibdb']->name());
        }
        else{
            $_SESSION['user_can_modify'] = $_SESSION['auth']->can_modify_entry($_SESSION['user'],$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
        }
    }
}
else{
    $_SESSION['user_can_delete'] = TRUE;
    $_SESSION['user_can_add'] = TRUE;
    $_SESSION['user_can_modify'] = TRUE;
    $_SESSION['user_is_admin'] = TRUE;
}

$_SESSION['update_authorizations'] = FALSE;


// user preferences
if(array_key_exists('user_pref',$_SESSION)){
    $max_ref = $_SESSION['user_pref']['max_ref_by_page'];
}
else{
    $max_ref = MAX_REFERENCES_BY_PAGE;
}

//abstract
if(array_key_exists('user_pref',$_SESSION)){
    $abst = $_SESSION['user_pref']['display_abstract'] == "yes";
}
else{
    $abst = array_key_exists('abstract',$_GET) ? $_GET['abstract'] : DISPLAY_ABSTRACT;
}

// sort
$display_sort = DISPLAY_SORT;
$sort = DEFAULT_SORT;
$sort_order = DEFAULT_SORT_ORDER;

// sort order
if(array_key_exists('user_pref',$_SESSION)){$display_sort = $_SESSION['user_pref']['display_sort'];}
// sort ID
if(array_key_exists('sort',$_GET)){$sort = $_GET['sort'];}
else if(array_key_exists('sort',$_POST)){$sort = $_POST['sort'];}
else if(array_key_exists('user_pref',$_SESSION)){$sort = $_SESSION['user_pref']['default_sort'];}
// sort order
if(array_key_exists('sort_order',$_GET)){$sort_order = $_GET['sort_order'];}
else if(array_key_exists('sort_order',$_POST)){$sort_order = $_POST['sort_order'];}
else if(array_key_exists('user_pref',$_SESSION)){$sort_order = $_SESSION['user_pref']['default_sort_order'];}

$_SESSION['bibdb']->set_sort($sort);
$_SESSION['bibdb']->set_sort_order($sort_order);

$display_images = DISPLAY_IMAGES;
$display_txt = DISPLAY_TEXT;
$display_shelf_actions = SHELF_MODE;
if(array_key_exists('user_pref',$_SESSION)){
    $display_images = ($_SESSION['user_pref']['display_images'] == "yes");
    $display_txt = $_SESSION['user_pref']['display_txt'] == "yes";
    $display_shelf_actions = $_SESSION['user_pref']['display_shelf_actions'] == "yes";
}

// global XSL parameters
$xslparam = array(  'bibname' => $_SESSION['bibdb']->name(),
                    'bibnameurl' => $_SESSION['bibdb']->xml_file(),
                    'display_images' => $display_images,
                    'display_text' => $display_txt,
                    'abstract' => $abst,
                    'display_add_all'=> 'true',
                    'sort' => $sort,
                    'sort_order' => $sort_order,
                    'can_modify' => $_SESSION['user_can_modify'] || $_SESSION['user_is_admin'],
                    'can_delete' => $_SESSION['user_can_delete'] || $_SESSION['user_is_admin'],
                    'shelf-mode' => $display_shelf_actions,
                    'biborb_xml_version' => BIBORB_XML_VERSION);

/**
 * Action are given by GET/POST method.
 * Analyse the URL to do the corresponding action.
 */

// GET action
if(isset($_GET['action'])){
    switch($_GET['action']){

        /*
            Select the GUI language
         */
        case 'select_lang':
            $_SESSION['language'] = $_GET['lang'];
            load_i18n_config($_SESSION['language']);
            break;

        /*
            Add an item to the basket
         */
        case 'add_to_basket':
            if(!isset($_GET['id'])){
                trigger_error("Trying to add a null value in basket!",ERROR);
            }
            else{
                $_SESSION['basket']->add_items(explode("*",$_GET['id']));
            }
        break;
	
        /*
            Delete an entry from the basket
         */
        case 'delete_from_basket':
            if(!isset($_GET['id'])){
                trigger_error("Trying to remove a null value from basket!",ERROR);
            }
            else{
                $_SESSION['basket']->remove_item($_GET['id']);
            }
            break;
	
        /*
            Reset the basket
         */
        case 'resetbasket':
            $_SESSION['basket']->reset();
            break;

        /*
            Delete an entry from the database
         */
        case 'delete':
            // check that there is an id
            if(!isset($_GET['id'])){
                trigger_error("BibTeX key not set. Can not remove a reference from the database.",ERROR);
            }
            // check we have the authorization to delete
            if(!array_key_exists('user_can_delete',$_SESSION) || !$_SESSION['user_can_delete']){
                trigger_error("You are not authorized to delete references!",ERROR);
            }
            $confirm = FALSE;
            if(array_key_exists('confirm_delete',$_GET)){
                $confirm = (strcmp($_GET['confirm_delete'],msg("Yes")) == 0);
            }

            $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");		
            // save the bibtex entry to show which entry was deleted
            $xml_content = $_SESSION['bibdb']->entry_with_id($_GET['id']);
            $bibtex = $xsltp->transform($xml_content,load_file("./xsl/xml2bibtex.xsl"));
            if(!WARN_BEFORE_DELETING || $confirm){		
                // delete it
                $_SESSION['bibdb']->delete_entry($_GET['id']);
                // update message
                $message = sprintf(msg("The following entry was deleted: <pre>%s</pre>"),$bibtex);
                // if present, remvove entries from the basket
                $_SESSION['basket']->remove_item($_GET['id']);
                $_GET['mode'] = "operationresult";
            }
            else if(array_key_exists('confirm_delete',$_GET) && strcmp($_GET['confirm_delete'],msg("No")) == 0){
                $_GET['mode'] = "welcome";
            }
            else {
                $theid = $_GET['id'];
                $message = sprintf(msg("Delete this entry? <pre>%s</pre>"),$bibtex);
                $message .= "<form action='bibindex.php' method='get' style='margin:auto;'>";
                $message .= "<fieldset style='border:none;text-align:center'>";
                $message .= "<input type='hidden' name='action' value='delete'/>";
                $message .= "<input type='hidden' name='id' value='$theid'/>";
                $message .= "<input type='submit' name='confirm_delete' value='".msg("No")."'/>";
                $message .= "&nbsp;";
                $message .= "<input type='submit' name='confirm_delete' value='".msg("Yes")."'/>";
                $message .= "</fieldset>";
                $message .= "</form>";

                $_GET['mode'] = "operationresult";
            }
            $xsltp->free();		
            break;
	
        /*
            Add entries in the basket to a given group
         */
        case 'add':
            if(isset($_GET['groupvalue'])){
                $gval = htmlentities(trim($_GET['groupvalue']));
            }
            if(isset($_GET['newgroupvalue'])){
                $gval = htmlentities(trim($_GET['newgroupvalue']));
            }
            if(!isset($gval)){
                trigger_error(msg("No group specified!"),ERROR);
            }
            else if($gval != ""){
                $_SESSION['bibdb']->add_to_group($_SESSION['basket']->items,$gval);
            }
            break;

        /*
         * Reset the group field of entries in the basket.
         */
        case 'reset':
            // check we have the authorization to modify
            if(!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify']){
                trigger_error("You are not authorized to modify references!",ERROR);
            }
            $_SESSION['bibdb']->reset_groups($_SESSION['basket']->items);
            break;

        /*
         * Logout
         */
        case 'logout':
            $_SESSION['user_can_add'] = FALSE;
            $_SESSION['user_can_delete'] = FALSE;
            $_SESSION['user_can_modify'] = FALSE;
            $_SESSION['user_is_admin'] = FALSE;
            unset($_SESSION['user']);
            unset($_SESSION['user_pref']);
            break;

        /*
         * Change the BibTeX type of an entry
         */
        case 'update_type':
            // check we have the authorization to modify
            if(!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify']){
                trigger_error("You are not authorized to modify references!",ERROR);
            }
            $_SESSION['bibdb']->change_type(htmlentities($_GET['id']),htmlentities($_GET['bibtex_type']));
            $_GET['mode']='update';
            break;

        /*
         * Change the BibTeX key of a reference
         */
        case 'update_key': // update the BibTeX key of a reference
            // check we have the authorization to modify
            if(!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify']){
                trigger_error("You are not authorized to modify references!",ERROR);
            }
            $oldid = htmlentities($_GET['id']);
            $newid = htmlentities($_GET['bibtex_key']);
            if(!$_SESSION['bibdb']->is_bibtex_key_present($newid)){
                $_SESSION['bibdb']->change_id($oldid,$newid);
                $_GET['mode'] = 'update';
                $_GET['id'] = $newid;
                // change the value in the basket
                $_SESSION['basket']->remove_item($oldid);
                $_SESSION['basket']->add_item($newid);
            }
            else{
                $error = sprintf(msg("BibTeX key <code>%s</code> already exists."),$newid);
                $_GET['mode'] = 'operationresult';
            }
            break;

        case 'delete_basket':

            // check we have the authorization to delete
            if(!array_key_exists('user_can_delete',$_SESSION) || !$_SESSION['user_can_delete']){
                trigger_error("You are not authorized to delete references!",ERROR);
            }

            $confirm = FALSE;
            if(array_key_exists('confirm_delete',$_GET)){
                $confirm = (strcmp($_GET['confirm_delete'],msg("Yes"))==0);
            }
            $ids_to_remove = $_SESSION['basket']->items;
            $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
            $xml_content = $_SESSION['bibdb']->entries_with_ids($ids_to_remove);

            if(!WARN_BEFORE_DELETING || $confirm){
                $_SESSION['bibdb']->delete_entries($ids_to_remove);
                // update message
                $bibtex = $xsltp->transform($xml_content,load_file("./xsl/xml2bibtex.xsl"));
                $message = sprintf(msg("The following entries were deleted: <pre>%s</pre>"),$bibtex);
                $_SESSION['basket']->reset();
                $_GET['mode'] = "operationresult";
            }
            else if(array_key_exists('confirm_delete',$_GET) && strcmp($_GET['confirm_delete'],msg("No")) == 0){
                $_GET['mode'] = "welcome";
            }
            else{
                $html_entries = biborb_html_render($xml_content,$GLOBALS['xslparam']);
                $message = msg("Delete the following entries?");
                $message .= $html_entries;
                $message .= "<form action='bibindex.php' method='get' style='margin:auto;'>";
                $message .= "<fieldset style='border:none;'>";
                $message .= "<input type='hidden' name='action' value='delete_basket'/>";
                $message .= "<input type='submit' name='confirm_delete' value='".msg("No")."'/>";
                $message .= "<input type='submit' name='confirm_delete' value='".msg("Yes")."'/>";
                $message .= "</fieldset>";
                $message .= "</form>";
        		  $_GET['mode'] = "operationresult";
            }
            $xsltp->free();
            break;

        /*
            Shelf mode: update the owner ship
         */
        case 'update_ownership':
            // check we have the authorization to modify
            if(!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify']){
                trigger_error("You are not authorized to modify references!",ERROR);
            }
            $_SESSION['bibdb']->change_ownership($_GET['id'], $_GET['ownership']);
            break;

        /*
            Shelf mode: update the read status of a reference
         */
        case 'update_readstatus':
            // check we have the authorization to modify
            if(!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify']){
                trigger_error("You are not authorized to modify references!",ERROR);
            }
            $_SESSION['bibdb']->change_readstatus($_GET['id'], $_GET['readstatus']);
            break;

        /*
            Add a browse item.
         */
        case 'add_browse_item':
            if(array_key_exists('type',$_GET) && array_key_exists('value',$_GET)){
                $theType = htmlentities($_GET['type']);
                $theValue = htmlentities($_GET['value']);
                $found = false;
                $cpt = 0;
                if(array_key_exists('browse_history',$_SESSION)){
                    for($cpt=0;$cpt<count($_SESSION['browse_history']) && !$found; $cpt++){
                        $found = $_SESSION['browse_history'][$cpt]['type'] == $theType;
                    }
                }

                if($found){
                    $_SESSION['browse_history'][$cpt-1]['value'] = $theValue;
                    $_GET['start'] = $cpt;
                    array_splice($_SESSION['browse_history'],$cpt);
                    /*for($i=$cpt;$i<count($_SESSION['browse_history']);$i++){
                        unset($_SESSION['browse_history'][$i]);
                    }*/
                    $_SESSION['browse_ids'] = $_SESSION['bibdb']->all_bibtex_ids();
                    for($i=0;$i<count($_SESSION['browse_history']);$i++){
                        $_SESSION['browse_ids'] = $_SESSION['bibdb']->filter($_SESSION['browse_ids'],$theType,$theValue);
                    }
                }
                else{
                    $_SESSION['browse_history'][] = array('type'=>$theType,'value'=>$theValue);
                    $_SESSION['browse_ids'] = $_SESSION['bibdb']->filter($_SESSION['browse_ids'],$theType,$theValue);
                }
                $_GET['start'] = count($_SESSION['browse_history']);
            }
            break;

        default:
            break;
    }
}

// analyse POST
if(isset($_POST['action'])){
    switch($_POST['action']){
        /*
         * Add an entry to the database
         */
        case 'add_entry':
            // check we have the authorization to modify
            if(!array_key_exists('user_can_add',$_SESSION) || !$_SESSION['user_can_add']){
                trigger_error("You are not authorized to add references!",ERROR);
            }
            if(isset($_POST['ok'])){
                $res = $_SESSION['bibdb']->add_new_entry($_POST);
                if($res['added']){
                    $message = msg("ENTRY_ADDED_SUCCESS")."<br/>";
                    $entry = $_SESSION['bibdb']->entry_with_id($res['id']);
                    $param = $GLOBALS['xslparam'];
                    $param['bibindex_mode'] = "displaybasket";
                    $param['mode'] = "user";
                    $message .= biborb_html_render($entry,$param);
                    $error = $res['message'];
                }
                else{
                    $error = $res['message'];
                }
            }
            else{
                $_GET['mode'] = 'welcome';
            }
            break;

        /*
         * Update a reference
         */
        case 'update_entry':
            if(isset($_POST['ok'])){
                // check we have the authorization to modify
                if(!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify']){
                    trigger_error("You are not authorized to modify references!",ERROR);
                }
                $res = $_SESSION['bibdb']->update_entry($_POST);
                if($res['updated']){
                    $message = msg("The following entry was updated:")."<br/>";
                    $entry = $_SESSION['bibdb']->entry_with_id($res['id']);
                    $param = $GLOBALS['xslparam'];
                    $param['bibindex_mode'] = "displaybasket";
                    $param['mode'] = "user";
                    $message .= biborb_html_render($entry,$param);
                    $error = $res['message'];
                }
                else{
                    $error = $res['message'];
                }
            }
            else{
                $_GET['mode'] = 'welcome';
            }
            break;
	
        /*
            Import bibtex entries.
        */
        case 'import':
            // check we have the authorization to modify
            if(!array_key_exists('user_can_add',$_SESSION) || !$_SESSION['user_can_add']){
                trigger_error("You are not authorized to add references!",ERROR);
            }
            // Error if no value given
            if((!array_key_exists('bibfile',$_FILES) || !file_exists($_FILES['bibfile']['tmp_name'])) && !array_key_exists('bibval',$_POST)){
                trigger_error("Error, no bibtex data provided!",ERROR);
            }
            else{
                // get bibtex data from $_POST or $_FILES
                if(array_key_exists('bibval',$_POST)){
                    $bibtex_data = explode("\n",$_POST['bibval']);
                }
                else{
                    $bibtex_data= file($_FILES['bibfile']['tmp_name']);
                }
                // add the new entry
                $res = $_SESSION['bibdb']->add_bibtex_entries($bibtex_data);

                if(count($res['added']) > 0 && count($res['added']) <= 20){
                    $entries = $_SESSION['bibdb']->entries_with_ids($res['added']);
                    $param = $GLOBALS['xslparam'];
                    $param['bibindex_mode'] = "displaybasket";
                    $param['mode'] = "admin";
                    $formated = biborb_html_render($entries,$param);
                    if(count($res['added']) == 1){
                        $message = msg("The following entry was added to the database:");
                    }
                    else if(count($res['added']) > 1){
                        $message = msg("The following entries were added to the database:");
                    }
                    $message .= $formated;
                }
                else{
                    $message .= sprintf(msg("%d entries were added to the database."),count($res['added']));
                }


                if(count($res['notadded']) != 0){
                    $error = msg("Some entries were not imported. Their BibTeX keys were already present in the bibliography. ");
                    $error .= "<br/>";
                    $error .= msg("BibTeX keys in conflict: ");
                    $lg = count($res['notadded']);
                    for($i=0;$i<$lg;$i++){
                        $error .= $res['notadded'][$i];
                        $error .= ($i!=$lg-1 ? ", " : ".");
                    }
                }
            }
            break;
	
        /*
            Login
        */
        case 'login':
            $login = $_SESSION["google"]["gmail"];
            if($login==""){
                $error = msg("LOGIN_MISSING_VALUES");
                $_GET['mode'] = 'login';
            }
            else {
                $loggedin = $_SESSION['auth']->is_valid_user($login,"");
                if($loggedin){
                    $_SESSION['user'] = $login;
                    $login_success = "welcome";
                    $_SESSION['user_is_admin'] = $_SESSION['auth']->is_admin_user($login);
                    $_SESSION['user_can_add'] = $_SESSION['auth']->can_add_entry($login,$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
                    $_SESSION['user_can_delete'] = $_SESSION['auth']->can_delete_entry($login,$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
                    $_SESSION['user_can_modify'] = $_SESSION['auth']->can_modify_entry($login,$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
                }
                else {
                    $error = msg("LOGIN_WRONG_USERNAME_PASSWORD");
                    $_GET['mode'] = 'login';
                }
            }
            break;
	
        /*
         * Export the basket to bibtex
         */
        case 'export':
            if($_SESSION['basket']->count_items() != 0){
                // basket not empty -> processing
                // get entries
                $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['basket']->items);
                $bt = new BibTeX_Tools();
                $tab = $bt->xml_to_bibtex_array($entries);
                header("Content-Type: text/plain");
                echo $bt->array_to_bibtex_string($tab,$GLOBALS['fields_to_export']);
                exit();
            }
            else{
                $_GET['mode'] = 'displaybasket';
            }
            break;

        /**
         * Select which export format for basket
         */
        case 'export_basket':
            switch($_POST['export_format']){
                case 'bibtex':
                    $_GET['mode'] = 'exportbaskettobibtex';
                    break;
                case 'ris':
                    $_GET['mode'] = 'exportbaskettoris';
                    break;
                case 'html':
                    $_GET['mode'] = 'exportbaskettohtml';
                    break;
                case 'docbook':
                    $_GET['mode'] = 'exportbaskettodocbook';
                    break;
                default:
                    $_GET['mode'] = 'welcome';
                    break;
            }
            break;

            /**
             * Export all references.
             */
        case 'export_all':
            $bt = new BibTeX_Tools();
            $_GET['mode'] = "displaytools";
            $entries = $bt->xml_to_bibtex_array($_SESSION['bibdb']->all_entries());
            $filename = $_SESSION['bibdb']->name();
            switch($_POST['export_format']){
                case 'bibtex':
                    $filename .= ".bib";
                    $content = $bt->array_to_bibtex_string($entries,$GLOBALS['fields_to_export']);
                    break;
                case 'ris':
                    $filename .= ".ris";
                    $content = $bt->array_to_RIS($entries);
                    break;
                case 'docbook':
                    $filename .= ".xml";
                    $content = $bt->array_to_DocBook($entries);
                    break;
                default:
                    trigger_error("Unknown export format.",ERROR);
                    break;
            }
            header("Content-Type:text/plain");
            header("Content-Disposition:attachment;filename=$filename");
            echo $content;
            break;

        /*
         * Get BibTeX references from .aux LaTeX file.
         */
        case 'bibtex_from_aux':
            $bibtex_keys = bibtex_keys_from_aux($_FILES['aux_file']['tmp_name']);
            $xmldata = $_SESSION['bibdb']->entries_with_ids($bibtex_keys);
            $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
            $param = $GLOBALS['xslparam'];
            $param['fields_to_export'] = implode(".",$fields_to_export);

            header("Content-disposition: attachment; filename=".$_FILES['aux_file']['name'].".bib");
            header("Content-Type: application/force-download");
            echo $xsltp->transform($xmldata,load_file("./xsl/xml2bibtex_advanced.xsl"),$param);
            $xsltp->free();
            die();

            /*
                Create an archive of a given bibliography.
             */
        case 'get_archive':
            // move to bibs
            chdir("./bibs");
            // tar name
            $tar_name = $_SESSION['bibdb']->name().".tar.gz";
            // delete it if it already exists
            if(file_exists($tar_name)){ unlink($tar_name);}
            // create the archive
            $tar = new Archive_Tar($tar_name,"gz");
            $tar->create($_SESSION['bibdb']->name()) or trigger_error("Failed to create an archive of the Bibliography", FATAL);
            // Save as...
            header("Content-disposition: attachment; filename=".$tar_name);
            header("Content-Type: application/octed-stream");
            readfile($tar_name);
            die();

        default:
            break;
    }
}


/**
 * Select what to do according to the mode given in parameter.
 */
if(isset($login_success)){
    $mode = "welcome";
}
else if(array_key_exists('mode',$_GET)){
    $mode = $_GET['mode'];
}
else if(array_key_exists('mode',$_POST)){
    $mode = $_POST['mode'];
}
else {
    $mode = "welcome";
}

switch($mode) {
    // Welcome page
    case 'welcome': echo bibindex_welcome(); break;

    // Generice page to display operations results
    case 'operationresult': echo bibindex_operation_result(); break;

    // Help on the display menu item
    case 'display': echo bibindex_display_help(); break;

    // Display all entries
    case 'displayall': echo bibindex_display_all(); break;

    // Display by group
    case 'displaybygroup': echo bibindex_display_by_group(); break;

    // Display search page
    case 'displaysearch': echo bibindex_display_search(); break;
    case 'displayadvancedsearch': echo bibindex_display_advanced_search(); break;
    case 'displayxpathsearch': echo bibindex_display_xpath_search();break;

    // Help on the basket menu item
    case 'basket': echo bibindex_basket_help(); break;

    // Display the basket
    case 'displaybasket': echo bibindex_display_basket(); break;

    // Display the page to modify groups of entries in the basket
    case 'groupmodif':
        if($_SESSION['basket']->count_items() != 0){
            echo bibindex_basket_modify_group();
        }
        else{
            echo bibindex_display_basket();
        }
        break;

    // Help on the Manager Menu
    case 'manager': echo bibindex_manager_help(); break;

    // Add a new entry
    case 'addentry':echo bibindex_entry_to_add(); break;

    // Select the type of the new entry to add
    case msg("Select"):
        echo bibindex_add_entry($_GET['type']); break;

    // Update an entry
    case 'update': echo bibindex_update_entry(); break;

    // Login page
    case 'login': echo bibindex_login(); break;

    // Logout
    case 'logout': echo bibindex_logout(); break;

    // Update the XML file according to values present in the BibTeX file.
    case 'update_xml_from_bibtex':
        $_SESSION['bibdb']->reload_from_bibtex();
        echo bibindex_welcome();
        break;

    // Mode to access directly to an article
    case 'details': echo bibindex_details(); break;

    // Import references
    case 'import': echo bibindex_import(); break;

    // Export the basket to bibtex
    case 'exportbaskettobibtex': echo bibindex_export_basket_to_bibtex(); break;

    // Page to select which export
    case 'exportbasket':
        // Display export selection form if some entries in the basket
        if($_SESSION['basket']->count_items() != 0){
            echo bibindex_export_basket();
        }
        else{
            echo bibindex_display_basket();
        }
        break;

    // Export the basket to RIS format
    case 'exportbaskettoris':
        $bt = new BibTeX_Tools();
        $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['basket']->items);
        $tab = $bt->xml_to_bibtex_array($entries);
        header("Content-Type: text/plain");
        echo $bt->array_to_RIS($tab);
        break;

    case 'exportbaskettodocbook':
        $bt = new BibTeX_Tools();
        $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['basket']->items);
        $tab = $bt->xml_to_bibtex_array($entries);
        header("Content-Type: text/plain");
        echo $bt->array_to_DocBook($tab);
        break;

    // bibtex of a given entry
    case 'bibtex':
        $bt = new BibTeX_Tools();
        $entries = $_SESSION['bibdb']->entry_with_id($_GET['id']);
        $tab = $bt->xml_to_bibtex_array($entries);
        header("Content-Type: text/plain");
        echo $bt->array_to_bibtex_string($tab,$GLOBALS['fields_to_export']);
        break;

    // Export the basket to html
    case 'exportbaskettohtml': echo bibindex_export_basket_to_html();break;


    // Display Tools
    case 'displaytools': echo bibindex_display_tools();break;

    // Browse mode
    case 'browse':
        if(isset($_GET['start'])){
            if($_GET['start'] == 0){
                unset($_SESSION['ids']);
                unset($_SESSION['browse_history']);
                $_SESSION['browse_ids'] = $_SESSION['bibdb']->all_bibtex_ids();
                // extract values from the database
                // save them into session
                $_SESSION['misc']['years'] = $_SESSION['bibdb']->get_values_for('year');
                $_SESSION['misc']['groups'] = $_SESSION['bibdb']->get_values_for('group');
                $_SESSION['misc']['series'] = $_SESSION['bibdb']->get_values_for('series');
                $_SESSION['misc']['journals'] = $_SESSION['bibdb']->get_values_for('journal');
                $_SESSION['misc']['authors'] = $_SESSION['bibdb']->get_values_for('author');
            }
            if(isset($_SESSION['browse_history'])){
                for($i=0;$i<$_GET['start'];$i++){
                    $_SESSION['browse_ids'] = $_SESSION['bibdb']->filter($_SESSION['browse_ids'],$_SESSION['browse_history'][$i]['type'],$_SESSION['browse_history'][$i]['value']);
                }
                for($i=$_GET['start'];$i<count($_SESSION['browse_history']);$i++){
                    unset($_SESSION['browse_history'][$i]);
                }
            }
        }
        echo bibindex_browse();break;

    // By default
    default: echo bibindex_welcome(); break;
}

?>
