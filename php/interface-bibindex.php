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
 * File: interface-bibindex.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *      Functions to generate the interface (bibindex.php)
 *
 */

/********************************** BIBINDEX */

/**
 * bibindex_details()
 * Called when a given entry has to be displayed
 'bibindex.php?mode=details&abstract=1&menu=0&bibname=example&id=idA
 */

function bibindex_details()
{
    $html = bibheader();

    // get the bibname
    if(!array_key_exists('bibname',$_GET)){die("No bibliography name provided");}
    $bibdb = new BibORB_DataBase($_GET['bibname'],GEN_BIBTEX);

    // get the parameters
    $param = $GLOBALS['xslparam'];
    $param['bibname'] = $bibdb->name();
    $param['bibnameurl'] = $bibdb->xml_file();
    $param['display_basket_actions'] = "no";

    if(array_key_exists('abstract',$_GET)){
        $param['abstract'] = $_GET['abstract'] ? "true" : "false";
    }
    if(array_key_exists('basket',$_GET)){
        $param['display_basket_actions'] = $_GET['basket'] ? "true" : "no";
    }

    $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
    $xsl_content = load_file("./xsl/biborb_output_sorted_by_id.xsl");

    if(array_key_exists('bibids',$_GET)){
        // get the entries
        $bibids = explode(',',$_GET['bibids']);
		$xml_content = $bibdb->entries_with_ids($bibids);
		$content = $xsltp->transform($xml_content,$xsl_content,$param);
	}
	else if(array_key_exists('id',$_GET)){
		// get the selected entry
        $xml_content = $bibdb->entry_with_id($_GET['id']);
        $content = $xsltp->transform($xml_content,$xsl_content,$param);
	}
	// display the menu or not
    if(array_key_exists('menu',$_GET)){
        if($_GET['menu'] == 1){
            $html .= bibindex_menu($_GET['bibname']);
            $html .= main(null,$content);
        }
        else{
            $html .= $content;
        }
    }
    else{
        $html .= $content;
    }
    $html .= html_close();

    return $html;
}

/**
 * bibindex_login()
 * Display the login page
 */
function bibindex_login_old(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("INDEX_LOGIN_TITLE");
    $content = "<form id='login_form' action='bibindex.php' method='post' onsubmit='return validate_login_form(\"".$_SESSION['language']."\")' >";
    $content .= "<fieldset>";
    $content .= "<legend>Login</legend>";
    $content .= "<label for='login'>".msg("LOGIN_USERNAME").":</label>";
    $content .= "<input type='text' name='login' id='login' /><br/>";
    $content .= "<label for='password'>".msg("LOGIN_PASSWORD").":</label>";
    $content .= "<input type='password' id='password' name='mdp'/><br/>";
    $content .= "<input type='hidden' name='action' value='login'/>";
    $content .= "<input type='submit' value=\"".msg("Login")."\" class='submit'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $html .= main($title,$content,$GLOBALS['error']);
    $html .= html_close();
    return $html;
}

function bibindex_login(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("INDEX_LOGIN_TITLE");

    require_once 'src/Google_Client.php';
    require_once 'src/contrib/Google_Oauth2Service.php';
    $client = new Google_Client();
    $oauth2 = new Google_Oauth2Service($client);
    $authUrl = $client->createAuthUrl();
    $_SESSION['returnpoint']="bibindex.php";
    $content="<a class=\"login\" href=\"$authUrl\">Google Account Login</a>";
    
    $html .= main($title,$content,$GLOBALS['error']);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_logout()
 * Change admin mode to user and redirect to welcome page
 */
function bibindex_logout(){
    unset($_SESSION['user']);
    bibindex_welcome();
}

/**
 * bibindex_menu($bibname)
 * Create the menu for the bibliography $bibname.
 */
function bibindex_menu($bibname)
{
    $html = "<div id='menu'>";
    // title
    $html .= "<span id='title'>BibORB</span>";
    // name of the current bibliography
    $html .= "<span id='bibname'>".$bibname."</span>";
    $html .= "<ul>";
    // first menu item => Select a bibliography
    $html .= "<li><a href='index.php?mode=select'>".msg("BIBINDEX_MENU_SELECT_BIB")."</a>";
    $html .= "<ul>";
    // jump to a given bibliography
    $avbibs = get_databases_names();
    $html .= "<li>";
    $html .= "<form id='choose_bib' action='bibindex.php'>";
    $html .= "<fieldset>";
    $html .= "<select onchange='javascript:change_db(this.value)'>";
    foreach($avbibs as $key=>$bib){
        if($key == $bibname){
            $html .= "<option selected='selected' value='$key'>$bib</option>";
        }
        else{
            $html .= "<option value='$key'>$bib</option>";
        }
    }
    $html .= "</select><br/>";
    $html .= "<noscript><div><input class='submit' type='submit' value='Go'/></div></noscript>";
    $html .= "</fieldset>";
    $html .= "</form>";
    $html .= "</li>";
    $html .= "</ul></li>";

    // second item
    // -> Display
    //      | -> All
    //      | -> by group
    //      | -> browse
    //      | -> search
    $html .= "<li><a title='".msg("BIBINDEX_MENU_DISPLAY_HELP")."' href='bibindex.php?mode=display'>".msg("BIBINDEX_MENU_DISPLAY")."</a>";
    $html .= "<ul>";
    $html .= "<li><a title='".msg("BIBINDEX_MENU_DISPLAY_ALL_HELP")."' href='bibindex.php?mode=displayall'>".msg("BIBINDEX_MENU_DISPLAY_ALL")."</a></li>";
    $html .= "<li><a title='".msg("BIBINDEX_MENU_DISPLAY_BY_GROUP_HELP")."'href='bibindex.php?mode=displaybygroup'>".msg("BIBINDEX_MENU_DISPLAY_BY_GROUP")."</a></li>";
    $html .= "<li><a title='".msg("BIBINDEX_MENU_BROWSE_HELP")."'href='bibindex.php?mode=browse&amp;start=0'>".msg("BIBINDEX_MENU_BROWSE")."</a></li>";
    $html .= "<li><a title='".msg("BIBINDEX_MENU_DISPLAY_SEARCH_HELP")."' href='bibindex.php?mode=displaysearch'>".msg("BIBINDEX_MENU_DISPLAY_SEARCH")."</a></li>";
//    $html .= "<li><a title='".msg("BIBINDEX_MENU_DISPLAY_ADVANCED_SEARCH_HELP")."' href='bibindex.php?mode=displayadvancedsearch'>".msg("BIBINDEX_MENU_DISPLAY_ADVANCED_SEARCH")."</a></li>";
    $html .= "</ul>";
    $html .= "</li>";
    $html .= "<li><a title='".msg("BIBINDEX_MENU_TOOLS_HELP")."' href='bibindex.php?mode=displaytools'>".msg("BIBINDEX_MENU_TOOLS")."</a><ul><li/></ul></li>";
    // third menu item
    // -> Basket
    //      | -> Display basket
    //      | -> Modify groups (if admin)
    //      | -> Export to bibtex
    //      | -> Export to XML
    //      | -> Reset basket
    $html .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_HELP")."' href='bibindex.php?mode=basket'>".msg("BIBINDEX_MENU_BASKET")."</a>";
    $html .= "<ul>";
    $html .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_DISPLAY_HELP")."' href='bibindex.php?mode=displaybasket'>".msg("BIBINDEX_MENU_BASKET_DISPLAY")."</a></li>";
    if($_SESSION['user_can_modify'] || DISABLE_AUTHENTICATION){
        $html .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_GROUP_HELP")."' class='admin' href='bibindex.php?mode=groupmodif'>".msg("BIBINDEX_MENU_BASKET_GROUP")."</a></li>";
    }
    $html .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_EXPORT_HELP")."' href='bibindex.php?mode=exportbasket'>".msg("BIBINDEX_MENU_BASKET_EXPORT")."</a></li>";
    /*
    $html .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_BIBTEX_HELP")."' href='bibindex.php?mode=exportbaskettobibtex'>".msg("BIBINDEX_MENU_BASKET_BIBTEX")."</a></li>";
    $html .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_HTML_HELP")."' href='bibindex.php?mode=exportbaskettohtml'>".msg("BIBINDEX_MENU_BASKET_HTML")."</a></li>";
     */
    $html .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_RESET_HELP")."' href='bibindex.php?mode=".$GLOBALS['mode']."&amp;action=resetbasket";
	if($GLOBALS['mode'] == "displaybygroup" && array_key_exists('group',$_GET)){
		$html  .= "&amp;group=".$_GET['group'];
	}
	if($GLOBALS['mode'] == "displaysearch"){
		if(array_key_exists('search',$_GET)){
			$html .= "&amp;search=".$_GET['search'];
		}
		if(array_key_exists('author',$_GET)){
			$html .= "&amp;author=".$_GET['author'];
		}
		if(array_key_exists('title',$_GET)){
			$html .= "&amp;title=".$_GET['title'];
		}
		if(array_key_exists('keywords',$_GET)){
			$html .= "&amp;search=".$_GET['keywords'];
		}	
	}
	$html .= "'>".msg("BIBINDEX_MENU_BASKET_RESET")."</a></li>";
    $html .= "</ul>";
    $html .= "</li>";

    // fourth menu item
    // -> Manager
    //      | -> Login (if not admin and authentication enabled
    //      | -> Add an entry (if admin)
    //      | -> Update from BibTeX (if admin)
    //      | -> Import a bibtex file (if admin)
    //      | -> Preferences
    //      | -> Logout (if admin and authentication disabled
    $html .= "<li><a title='".msg("BIBINDEX_MENU_ADMIN_HELP")."' href='bibindex.php?mode=manager'>".msg("BIBINDEX_MENU_ADMIN")."</a>";
    $html .= "<ul>";
    if(!array_key_exists('user',$_SESSION) && !DISABLE_AUTHENTICATION){
        $html .= "<li><a title=\"".msg("BIBINDEX_MENU_ADMIN_LOGIN_HELP")."\" href='bibindex.php?mode=login'>".msg("BIBINDEX_MENU_ADMIN_LOGIN")."</a></li>";
    }
    if($_SESSION['user_can_add']){
        $html .= "<li><a title='".msg("BIBINDEX_MENU_ADMIN_ADD_HELP")."' class='admin' href='bibindex.php?mode=addentry'>".msg("BIBINDEX_MENU_ADMIN_ADD")."</a></li>";
    }
    if($_SESSION['user_is_admin'] && GEN_BIBTEX){
        $html .= "<li><a title=\"".msg("BIBINDEX_MENU_ADMIN_UPDATE_HELP")."\" class='admin' href='bibindex.php?mode=update_xml_from_bibtex'>".msg("BIBINDEX_MENU_ADMIN_UPDATE")."</a></li>";
    }
    if($_SESSION['user_can_add']){
        $html .= "<li><a title='".msg("BIBINDEX_MENU_ADMIN_IMPORT_HELP")."' class='admin' href='bibindex.php?mode=import'>".msg("BIBINDEX_MENU_ADMIN_IMPORT")."</a></li>";
    }
    if(array_key_exists('user',$_SESSION)){
        $html .= "<li>";
        $html .= "<a href='index.php?mode=preferences' title='".msg("BIBINDEX_MENU_ADMIN_PREF_HELP")."'>".msg("BIBINDEX_MENU_ADMIN_PREF")."</a>";
        $html .= "</li>";
    }
    if(array_key_exists('user',$_SESSION) && !DISABLE_AUTHENTICATION){
        $html .= "<li><a title='".msg("BIBINDEX_MENU_ADMIN_LOGOUT_HELP")."' href='bibindex.php?mode=welcome&amp;action=logout'>".msg("BIBINDEX_MENU_ADMIN_LOGOUT")."</a></li>";
    }
    $html .= "</ul>";
    $html .= "</li>";
    $html .= "</ul>";

    if(DISPLAY_LANG_SELECTION && !array_key_exists("user",$_SESSION)){
        $html .= "<form id='language_form' action='bibindex.php' method='get'>";
        $html .= "<fieldset>";
        $html .= "<label for='lang'>".msg("Language:")."</label>";
        $html .= lang_html_select($_SESSION['language'],'lang','javascript:change_lang(this.value)');
        $html .= "<input type='hidden' name='action' value='select_lang'/>";
        $html .= "<noscript><div><input class='submit' type='submit' value='".msg("Select")."'/></div></noscript>";
        $html .= "</fieldset>";
        $html .= "</form>";
    }
    $html .= "</div>";

  return $html;
}

/**
 * bibheader()
 * Create the HTML header
 */
function bibheader($inbody = NULL)
{
  $html = html_header("BibORB - ".$_SESSION['bibdb']->fullname(),CSS_FILE,NULL,$inbody);
  return $html;
}


/**
 * This is the default Welcome page.
 */
function bibindex_welcome()
{
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = "BibORB: ". $_SESSION['bibdb']->fullname();
    $content = "";
    //$content = msg("This is the bibliography").": <strong>".$_SESSION['bibdb']->name()."</strong>.<br/>";
    if(array_key_exists('user',$_SESSION) && !DISABLE_AUTHENTICATION){
        $content .= msg("You are logged as").": <em>".$_SESSION['user']."</em>.";
/*
        $content .= "<br/>";
        $content .= "Allowed to add entry: ".($_SESSION['user_can_add'] ? "YES" : "NO");
        $content .= "<br/>";
        $content .= "Allowed to modify entry: ".($_SESSION['user_can_modify'] ? "YES" : "NO");
        $content .= "<br/>";
        $content .= "Allowed to delete entry: ".($_SESSION['user_can_delete'] ? "YES" : "NO");
*/
    }
	$nb = $_SESSION['bibdb']->count_entries();
	$nbpapers = $_SESSION['bibdb']->count_epapers();
	
	$content  .= "<h3>".msg("Statistics")."</h3>";
    $content  .= "<table>";
	$content  .= "<tbody>";
	$content  .= "<tr>";
	$content  .= "<td>".msg("Number of recorded articles").":</td>";
	$content  .= "<td><strong>$nb</strong></td>";
	$content  .= "</tr>";
	$content  .= "<tr>";
	$content  .= "<td>".msg("On-line available publications").":</td>";
	$content  .= "<td><strong>$nbpapers</strong></td>";
	$content  .= "</tr>";
	$content  .= "</tbody>";
    $content  .= "</table>";

    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}


/**
 * bibindex_operation_result()
 * Display error or message
 */
function bibindex_operation_result(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BibORB message");
    $html .= main($title,null,$GLOBALS['error'],$GLOBALS['message']);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_display_help()
 * Display a small help on items present in the 'display' menu
 */

function bibindex_display_help(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BIBINDEX_DISPLAY_HELP_TITLE");
    $content = load_localized_file("display_help.txt");
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * Display all entries in the bibliography.
 */
function bibindex_display_all(){
    $title = msg("BIBINDEX_DISPLAY_ALL_TITLE");
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());

    // store the ids in session if we come from an other page.
    // the bibtex keys are retreived from the database the first time that display_all is called
    if(!isset($_GET['page'])){
    	// split the array so that we display only $GLOBALS['max_ref']
        $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->all_bibtex_ids(),$GLOBALS['max_ref']);
        // go to the first page
        $_GET['page'] = 0;
    }

    $flatids = flatten_array($_SESSION['ids']);

    if(count($flatids)>0){
    	// get the data of the references to display
        $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
        // set up XSLT parameters
        $param = $GLOBALS['xslparam'];
        $param['bibindex_mode'] = $_GET['mode'];
        $param['basketids'] = $_SESSION['basket']->items_to_string();
        $param['extra_get_param'] = "page=".$_GET['page'];
        // generate HTML render of references
        $content = biborb_html_render($entries,$param);

        // create the header: sort function + add all to basket
        $start = "<div class='result_header'>";
        // display sort if needed
        if($GLOBALS['display_sort'] == "yes"){
            $start = sort_div($GLOBALS['sort'],$GLOBALS['sort_order'],$_GET['mode'],null).$start;
        }
        $start .= add_all_to_basket_div($flatids,$_GET['mode'],"sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."&amp;page=".$_GET['page']);
        $start .= "</div>";

        // create a nav bar to display entries
        $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),"displayall","sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."page=".$_GET['page']);
        $content = $start.$content;
    }
    else{
        $content = msg("No entries.");
    }
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
} // end bibindex_display_all


/**
 * bibindex_display_by_group()
 * Display entries by group
 */
function bibindex_display_by_group(){

	$group = array_key_exists('group',$_GET) ? $_GET['group'] : null;
    if(isset($_GET['orphan'])){$group=null;}

    $title = msg("BIBINDEX_DISPLAY_BY_GROUPS_TITLE");
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());

    // create a form with all groups present in the bibliography

    $main_content = "<form id='display_by_group_form' method='get' action='bibindex.php'>";
    $main_content .="<fieldset>";
    $main_content .= "<input type='hidden' name='bibname' value='".$_SESSION['bibdb']->name()."'/>";
    $main_content .= "<input type='hidden' name='mode' value='displaybygroup'/>";
    $main_content .= "<label for='group'>".msg("Available groups").":</label> ";
    $main_content .= xhtml_select('group',1,$_SESSION['bibdb']->groups(),$group);
    // if shelf_mode on, create a popup to display which references to display
    if($GLOBALS['display_shelf_actions'] == "yes"){
        $main_content .= read_status_html_select("read_status_grp",(isset($_GET['read_status_grp']) ? $_GET['read_status_grp'] : 'any'));
        $main_content .= ownership_html_select("ownership_grp",(isset($_GET['ownership_grp']) ? $_GET['ownership_grp'] : 'any'));
    }
    $main_content .= "&nbsp;<input class='submit' type='submit' value='".msg("Display")."'/>";
    $main_content .= "</fieldset>";
    $main_content .= "</form>";

    // create a form to display orphans references
    // if shelf_mode on, create a popup to display which references to display
    $main_content .= "<form id='group_orphan_form' method='get' action='bibindex.php'>";
    $main_content .= "<fieldset>";
    $main_content .= "<input type='hidden' name='bibname' value='".$_SESSION['bibdb']->name()."'/>";
    $main_content .= "<input type='hidden' name='mode' value='displaybygroup'/>";
    $main_content .= "<input type='hidden' name='orphan' value='1'/>";
    $main_content .= "<label>".msg("Entries associated with no group:")."</label>";
    // if shelf_mode on, create a popup to display which references to display
    if($GLOBALS['display_shelf_actions'] == "yes"){
        $main_content .= read_status_html_select("read_status_orphans",(isset($_GET['read_status_orphans']) ? $_GET['read_status_orphans'] : 'any'));
        $main_content .= ownership_html_select("ownership_orphans",(isset($_GET['ownership_orphans']) ? $_GET['ownership_orphans'] : 'any'));
    }
    $main_content .= "&nbsp;<input type='submit' class='submit' value='".msg("Orphans")."'/>";
    $main_content .= "</fieldset>";
    $main_content .= "</form>";

    if($GLOBALS['display_shelf_actions'] == "yes"){
        if(isset($_GET['read_status_orphans'])){
            $_SESSION['bibdb']->set_read_status($_GET['read_status_orphans']);
        }
        if(isset($_GET['ownership_orphans'])){
            $_SESSION['bibdb']->set_ownership($_GET['ownership_orphans']);
        }
        if(isset($_GET['read_status_grp'])){
            $_SESSION['bibdb']->set_read_status($_GET['read_status_grp']);
        }
        if(isset($_GET['ownership_grp'])){
            $_SESSION['bibdb']->set_ownership($_GET['ownership_grp']);
        }
    }
    // store the ids in session if we come from an other page.
    if(!isset($_GET['page'])){
        $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->ids_for_group($group),$GLOBALS['max_ref']);
        $_GET['page'] = 0;
    }

    $flatids = flatten_array($_SESSION['ids']);
    $nb = count($flatids);

    // if the group is defined, display the entries matching it
    if(($group || isset($_GET['orphan'])) && $nb>0){
        $param = $GLOBALS['xslparam'];
        $param['group'] = $group;
        $param['basketids'] = $_SESSION['basket']->items_to_string();
        $param['bibindex_mode'] = "displaybygroup";

        // display orphans
        if(isset($_GET['orphan'])){
            if(isset($_GET['read_status_orphans'])){
                $param['extra_get_param'] = "read_status_orphans=".$_GET['read_status_orphans']."&amp;";
            }
            if(isset($_GET['ownership_orphans'])){
                $param['extra_get_param'] = "ownership=".$_GET['ownership_orphans']."&amp;";
            }
            $param['extra_get_param'] = "orphan=1&page=".$_GET['page'];
            $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
        }
        else{
            if(isset($_GET['read_status_grp'])){
                $param['extra_get_param'] = "read_status_orphans=".$_GET['read_status_grp']."&amp;";
            }
            if(isset($_GET['ownership_grp'])){
                $param['extra_get_param'] = "ownership=".$_GET['ownership_grp']."&amp;";
            }
            $param['extra_get_param'] = "group=$group&page=".$_GET['page'];
            $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
        }

        if($nb == 1){
            if(!isset($_GET['orphan'])){
                $main_content .= sprintf(msg("An entry for the group %s."),$group);
            }
            else{
                $main_content .= sprintf(msg("1 orphan."));
            }
        }
        else{
            if(!isset($_GET['orphan'])){
                $main_content .= sprintf(msg("%d entries for the group %s."),$nb,$group);
            }
            else{
                $main_content .= sprintf(msg("%d orphans."),$nb);
            }
        }

        if(isset($_GET['orphan'])){
            $extraparam = "orphan=1&amp;sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."&amp;page=".$_GET['page'];
        }
        else{
            $extraparam = "group=$group&amp;sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."&amp;page=".$_GET['page'];
        }

        // create the header
        $start = "<div style='result_header'>";
        if(isset($_GET['orphan'])){
            $extra['orphan'] = 1;
        }
        else{
            $extra['group'] = $group;
        }

        if($GLOBALS['display_sort'] == "yes"){
            $start = sort_div($GLOBALS['sort'],$GLOBALS['sort_order'],$_GET['mode'],$extra).$start;
        }
        $start .= add_all_to_basket_div($flatids,$_GET['mode'],$extraparam);
        $start .= "</div>";

        // create a nav bar to display entries
        $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),$_GET['mode'],$extraparam);

        $main_content .= "<br/><br/>".$start;
        $main_content .= biborb_html_render($entries,$param);
    }
    else{
        if(!isset($_GET['orphan'])){
            if(isset($group)){
                $main_content .= sprintf(msg("No entry for the group %s."),$group);
            }
        }
        else{
            $main_content .= sprintf(msg("No orphan."));
        }
    }
    $html .= main($title,$main_content);
    $html .= html_close();

    return $html;
}

/**
 * bibindex_display_search
 * display the search interface
 */
function bibindex_display_search(){

    $searchvalue = array_key_exists('search',$_GET) ? trim(htmlentities(remove_accents($_GET['search']))) :"";

    $title = msg("BIBINDEX_SIMPLE_SEARCH_TITLE");
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());

    // search tabs
    $main_content = "<div id='search_tabs'>";
    $main_content .= "<ul id='tabnav'>";
    $main_content .= "<li><a class='active' href='bibindex.php?mode=displaysearch'>".msg("Simple Search")."</a></li>";
    $main_content .= "<li><a href='bibindex.php?mode=displayadvancedsearch'>".msg("Advanced Search")."</a></li>";
    $main_content .= "<li><a href='bibindex.php?mode=displayxpathsearch'>".msg("XPath Search")."</a></li>";
    $main_content .= "</ul>";
    $main_content .= "</div>";

    $main_content .= "<form id='simple_search_form' class='search_content' action='bibindex.php' method='get' style='text-align:center'>";
    $main_content .= "<fieldset>";
    $main_content .= "<input type='hidden' name='mode' value='displaysearch' />";
    $main_content .= "<input name='search' value='".$searchvalue."' />&nbsp;";
    $main_content .= msg("Sort by:")."&nbsp;<select name='sort'>";

    $main_content .= "<option value='ID' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
    }
    if($sort == 'ID'){
        $main_content .="selected='selected'";
    }
    $main_content .= ">ID</option>";

    $main_content .= "<option value='title' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
    }
    if($sort == 'title'){
        $main_content .="selected='selected'";
    }
    $main_content .= ">".msg("Title")."</option>";

    $main_content .= "<option value='year' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
    }
    if($sort == 'year'){
        $main_content .="selected='selected'";
    }
    $main_content .= ">".msg("Year")."</option>";
    $main_content .= "</select>&nbsp;";
    $main_content .= "&nbsp;<input class='submit' type='submit' value='".msg("Search")."' /><br/>";
    $main_content .= "<table>";
    $main_content .= "<tbody>";
    $main_content .= "<tr>";
    $main_content .= "<td><input type='checkbox' name='author' value='1'";
    if(array_key_exists('author',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= " />".msg("Author")."</td>";
    $main_content .= "<td><input type='checkbox' name='title' value='1' ";
    if(array_key_exists('title',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= "/>".msg("Title")."</td>";
    $main_content .= "<td><input type='checkbox' name='keywords' value='1' ";
    if(array_key_exists('keywords',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= "/>".msg("Keywords")."</td>";
    $main_content .= "</tr><tr>";
    $main_content .= "<td><input type='checkbox' name='journal' value='1'";
    if(array_key_exists('journal',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= " />".msg("Journal")."</td>";
    $main_content .= "<td><input type='checkbox' name='editor' value='1'";
    if(array_key_exists('editor',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= " />".msg("Editor")."</td>";
    $main_content .= "<td><input type='checkbox' name='year' value='1'";
    if(array_key_exists('year',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= " />".msg("Year")."</td>";
    $main_content .= "</tr></tbody></table>";

    $main_content .= "<br/>";


    $main_content .= "</fieldset>";
    $main_content .= "</form>";

    if($searchvalue){
        $fields =array();
        $extra_param ="search=$searchvalue";

        if(array_key_exists('author',$_GET)){
            array_push($fields,'author');
                $extra_param .= "&author=1";
        }
        if(array_key_exists('title',$_GET)){
            array_push($fields,'title');
                $extra_param .= "&title=1";
        }
        if(array_key_exists('keywords',$_GET)){
            array_push($fields,'keywords');
                $extra_param .= "&keywords=1";
        }
        if(array_key_exists('editor',$_GET)){
            array_push($fields,'editor');
                $extra_param .= "&editor=1";
        }
        if(array_key_exists('journal',$_GET)){
            array_push($fields,'journal');
                $extra_param .= "&journal=1";
        }
        if(array_key_exists('year',$_GET)){
            array_push($fields,'year');
                $extra_param .= "&year=1";
        }
        if(array_key_exists('sort',$_GET)){
            array_push($fields,'sort');
            $extra_param .= "&sort=".$_GET['sort'];
        }
        if(array_key_exists('sort_order',$_GET)){
            $extra_param .= "&sort_order=".$_GET['sort_order'];
        }
	
        // store the ids in session if we come from an other page.
        if(!isset($_GET['page'])){
            $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->ids_for_search($searchvalue,$fields),$GLOBALS['max_ref']);
            $_GET['page'] = 0;
        }
        $flatids = flatten_array($_SESSION['ids']);
        if(count($flatids)>0){
            $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);

            $nb = count($flatids);
            $param = $GLOBALS['xslparam'];
            $param['bibindex_mode'] = $_GET['mode'];
            $param['basketids'] = $_SESSION['basket']->items_to_string();
            $extra_param .= "&page=".$_GET['page'];
            $param['extra_get_param'] = $extra_param;

            // add all
            $start = "<div class='result_header'>";
            $start .= add_all_to_basket_div($flatids,$_GET['mode'],$extra_param);
            $start .= "</div>";

            // create a nav bar to display entries
            $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),$_GET['mode'],$extra_param);

            if($nb==1){
                $main_content .= sprintf(msg("One match for %s"),$searchvalue).$start;
                $main_content .= biborb_html_render($entries,$param);
            }
            else if($nb>1) {
                $main_content .= sprintf(msg("%d matches for %s."),$nb,$searchvalue).$start;
                $main_content .= biborb_html_render($entries,$param);
            }
        }
        else{
            $main_content .= sprintf(msg("No match for %s."),$searchvalue);
        }
    }
    $html .= main($title,$main_content);
    $html .= html_close();

    return $html;
}

/**
 Display advanced search
*/
function bibindex_display_advanced_search(){

    $bibtex_fields = array('author','booktitle','edition','editor','journal','publisher','series','title','year');

    $biborb_fields = array('abstract','keywords','groups','longnotes');

    $extraparam = "";

    // if the result of a search is being displayed, hide the search form
    // display a link to show it again
    $content = "";
    if(array_key_exists('searched',$_GET)){
        $extraparam .= "searched=1&";
        $content .= "<div style='float:right;'>";
        $content .= "<script type='text/javascript'><!--
    document.write(\"<a class='cleanref' href=\\\"javascript:toggle_element(\'search_form\')\\\">";
        $content .= msg("Display/ Hide search form")."</a>\");\n--></script>";
        $content .= "<noscript><pre> </pre></noscript>";
        $content .= "</div>";
    }

    // search tabs
    $content .= "<div id='search_tabs'>";
    $content .= "<ul id='tabnav'>";
    $content .= "<li><a href='bibindex.php?mode=displaysearch'>".msg("Simple Search")."</a></li>";
    $content .= "<li><a class='active' href='bibindex.php?mode=displayadvancedsearch'>".msg("Advanced Search")."</a></li>";
    $content .= "<li><a href='bibindex.php?mode=displayxpathsearch'>".msg("XPath Search")."</a></li>";
    $content .= "</ul>";
    $content .= "</div>";

    $content .= "<div id='search_form' class='search_content'>";
    $content .= "<form action='bibindex.php' method='get'>";
    $content .= "<fieldset>";
    $content .= "<em>".msg("Connector:")." </em>";
    $content .= "<select name='connector'>";

    if(array_key_exists('connector',$_GET)){
        $extraparam .= "connector=".$_GET['connector']."&";
        if(!strcmp($_GET['connector'],'and')){
            $content .= "<option value='and' selected='selected'>".msg("and")."</option>";
            $content .= "<option value='or'>".msg("or")."</option>";
        }
        else{
            $content .= "<option value='and'>".msg("and")."</option>";
            $content .= "<option value='or' selected='selected'>".msg("or")."</option>";
        }
    }
    else{
        $extraparam = "connector=and&";
        $content .= "<option value='and' selected='selected'>".msg("and")."</option>";
        $content .= "<option value='or'>".msg("or")."</option>";
    }
    $content .= "</select> ";
    $content .= "<em>".msg("Sort by:")." </em>";
    $content .= "<select name='sort' >";
    $content .= "<option value='year' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
        $extraparam .= "sort=".$_GET['sort']."&";
    }
    else{
        $extraparam .= "sort=year&";
    }

    if($sort == 'year'){
        $content .="selected='selected'";
    }
    $content .= ">".msg("Year")."</option>";

    $content .= "<option value='ID' ";
    if($sort == 'ID'){
        $content .="selected='selected'";
    }
    $content .= ">ID</option>";

    $content .= "<option value='title' ";
    if($sort == 'title'){
        $content .="selected='selected'";
    }
    $content .= ">".msg("Title")."</option>";
    $content .= "</select><br/>";
	$content .= "<strong>".msg("BibTeX Fields")."</strong><br/>";

    foreach($bibtex_fields as $field){
        $content .= "<label>".msg($field)."</label>";
        if(array_key_exists($field,$_GET)){
            $thefield = trim(htmlentities(remove_accents($_GET[$field])));
            $content .= "<input name='$field' value='".$thefield."'/>";
            $extraparam .= "$field=".$thefield."&";
        }
        else{
            $content .= "<input name='$field'/>";
        }
        $content .= "<br/>";
    }
    $content .= "<strong>".msg("BibORB Fields")."</strong><br/>";
    foreach($biborb_fields as $field){
        $content .= "<label>".msg($field)."</label>";
        if(array_key_exists($field,$_GET)){
            $thefield = trim(htmlentities(remove_accents($_GET[$field])));
            $content .= "<input name='$field' value='".$thefield."'/>";
            $extraparam .= "$field=".$thefield."&";
        }
        else{
            $content .= "<input name='$field'/>";
        }
        $content .= "<br/>";
    }

    $content .= "<input type='hidden' name='mode' value='displayadvancedsearch'/>";
    $content .= "<input type='hidden' name='searched' value='1'/>";
    $content .= "<input class='submit' type='submit' value='".msg("Search")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $content .= "</div><br/>";

    $searchArray = array();
    foreach($bibtex_fields as $val){
        if(array_key_exists($val,$_GET) && trim(htmlentities($_GET[$val])) != ''){
            $searchArray['search_'.$val]=trim(htmlentities(remove_accents($_GET[$val])));
        }
    }

    foreach($biborb_fields as $val){
        if(array_key_exists($val,$_GET) && trim(htmlentities($_GET[$val])) != ''){
            $searchArray['search_'.$val]=trim(htmlentities(remove_accents($_GET[$val])));
        }
    }
    if(array_key_exists('connector',$_GET)){
        $searchArray['search_connector'] = $_GET['connector'];
    }
    $main_content = "";
    if(count($searchArray) > 1){
        // store the ids in session if we come from an other page.
        if(!isset($_GET['page'])){
            $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->ids_for_advanced_search($searchArray),$GLOBALS['max_ref']);
            $_GET['page'] = 0;
        }
        $flatids = flatten_array($_SESSION['ids']);
        if(count($flatids)>0){
            $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
            $nb = count($flatids);
            $param = $GLOBALS['xslparam'];
            $param['bibindex_mode'] = 'displayadvancedsearch';
            $param['basketids'] = $_SESSION['basket']->items_to_string();
            $extraparam .= "page=".$_GET['page'];
            $param['extra_get_param'] = $extraparam;

            // add all
            $start = "<div class='result_header'>";
            $start .= add_all_to_basket_div($flatids,$_GET['mode'],$extraparam);
            $start .= "</div>";
            // create a nav bar to display entries
            $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),$_GET['mode'],$extraparam);

            if($nb==1){
                $main_content = msg("One match.").$start;
                $main_content .= biborb_html_render($entries,$param);
            }
            else if($nb>1) {
                $main_content = sprintf(msg("%d matches."),$nb).$start;
                $main_content .= biborb_html_render($entries,$param);
            }
        }
        else{
            $main_content = msg("No match.");
        }
    }

    $title = msg("BIBINDEX_ADVANCED_SEARCH_TITLE");

    // hide the search form if some results are being displayed
    if(array_key_exists('searched',$_GET)){
        $html = bibheader("onload='javascript:toggle_element(\"search_form\")'");
    }
    else{
        $html = bibheader();
    }
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $html .= main($title,$content.$main_content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_basket_help()
 * Display a small help on items present in the 'basket' menu
 */

function bibindex_basket_help(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BIBINDEX_BASKET_HELP_TITLE");
    $content = load_localized_file("basket_help.txt");
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_display_basket()
 * display entries present in the basket
 */
function bibindex_display_basket(){
    $title = msg("BIBINDEX_BASKET_DISPLAY_TITLE");
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $content = null;
    $error = null;
    $message = null;


    $param = $GLOBALS['xslparam'];
    $param['bibindex_mode'] = $_GET['mode'];
    $param['basketids'] = $_SESSION['basket']->items_to_string();

    // store the ids in session if we come from an other page.
    if(!isset($_GET['page'])){
        $_SESSION['ids'] = array_chunk($_SESSION['basket']->items,$GLOBALS['max_ref']);
        $_GET['page'] = 0;
    }
    $nb = $_SESSION['basket']->count_items();
    if($nb>0){
        $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
        // create a nav bar to display entries
        $start = create_nav_bar($_GET['page'],count($_SESSION['ids']),"displaybasket","page=".$_GET['page']);
        $param['extra_get_param'] = "page=".$_GET['page'];
        $main_content = biborb_html_render($entries,$param);
        $content = "<div>";
        if($nb == 1){
            $content = msg("An entry in the basket.");
        }
        else{
            $content = sprintf(msg("%d entries in the basket."),$nb);
        }
        if($_SESSION['user_can_delete']){
            $content .= "<div style='float:right;display:inline;'>";
            $content .= "<a class='admin' href='bibindex.php?mode=operation_result&action=delete_basket'>";
            $content .= msg("Delete all from database");
            $content .= "</a>";
            $content .= "</div>";
        }
        $content .= "</div>".$start;
        $content .= $main_content;
    }
    else{
        $message = msg("No entry in the basket.");
    }

    $html .= main($title,$content,$error,$message);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_basket_modify_group
 * Display the page to modify groups of entries in the basket
 */
function bibindex_basket_modify_group(){
    $title = msg("BIBINDEX_BASKET_GROUPS_MANAGE_TITLE");
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());

    // reset groups
    $main_content = "<form id='reset_groups' action='bibindex.php' method='get'>";
    $main_content .= "<fieldset>";
	$main_content .= "<input type='hidden' name='mode' value='groupmodif'/>";
    $main_content .= "<input type='hidden' name='action' value='reset'/>";
	$main_content .= "<input class='submit' type='submit' value='".msg("Reset")."'/> &nbsp;".msg("Reset the groups field of each entry in the basket. ");
	$main_content .= "</fieldset>";
	$main_content .= "</form>";
	$main_content .= "<br/>";
	$main_content .= msg("Add all entries in the basket to a group:");
    $main_content .= "<br/>";
    $main_content .= "<br/>";

    //  create a new group
	$main_content .= "<form id='add_new_group' action='bibindex.php' method='get' onsubmit='return validate_add_group(\"".$_SESSION['language']."\")'>";
	$main_content .= "<fieldset>";
	$main_content .= "<input type='hidden' name='mode' value='groupmodif'/>";
	$main_content .= "<label for='newgroupvalue'>".msg("New group:")."</label> <input name='newgroupvalue' id='newgroupvalue' class='longtextfield'/>";
    $main_content .= "<input type='hidden' name='action' value='add'/>";
	$main_content .= "&nbsp;<input class='submit' type='submit' value='".msg("Add")."'/>";
	$main_content .= "</fieldset>";
	$main_content .= "</form><br/>";
    $groups = $_SESSION['bibdb']->groups();

    // display available groups if at least one exists
    if(count($groups)>0){
        $main_content .= "<form id='add_group' action='bibindex.php' method='get'>";
        $main_content .= "<fieldset>";
        $main_content .= "<input type='hidden' name='mode' value='groupmodif'/>";
        $main_content .= "<label for='groupvalue'>".msg("Existing group:")."</label>";
        $main_content .= xhtml_select('groupvalue',1,$groups,"",null,null,"longtextfield");
        $main_content .= "<input type='hidden' name='action' value='add'/>";
        $main_content .= "&nbsp;<input class='submit' type='submit' value='".msg("Add")."'/>";
        $main_content .= "</fieldset>";
        $main_content .= "</form>";
    }

    $html .= main($title,$main_content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_manager_help()
 * Display a small help on items present in the 'manager' menu
 */
function bibindex_manager_help(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BIBINDEX_ADMIN_HELP_TITLE");
    $content = load_localized_file("manager_help.txt");
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_entry_to_add
 * display the page to select which type of entry to add
 */
function bibindex_entry_to_add(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BIBINDEX_SELECT_NEW_ENTRY_TITLE");
    $types = xhtml_select('type',1,$_SESSION['bibdb']->entry_types(),"");
	$content = "<form id='select_entry_type' method='get' action='bibindex.php'>";
	$content .= "<fieldset>";
	$content .= "<label for='mode'>".msg("Select an entry type: ")."</label>".$types;
    $content .= "&nbsp;<input class='submit' type='submit' name='mode' id='mode' value='".msg("Select")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
	
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_add_entry
 * Display a form to edit the value of each BibTeX fields
 */
function bibindex_add_entry($type){
	
    // xslt transformation
    $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
    $param = $GLOBALS['xslparam'];
    $xml_content = load_file("./xsl/model.xml");
    $xsl_content = load_file("./xsl/model.xsl");
    $param = array("typeentry"=>$type);
    $fields = $xsltp->transform($xml_content,$xsl_content,$param);
    $xsltp->free();

    // get the list of available groups
    $glist = $_SESSION['bibdb']->groups();
    array_push($glist,"");
    $groups=xhtml_select("groupslist",1,$glist,"","addGroup()");
	$fields = str_replace("#XHTMLGROUPSLIST",$groups,$fields);

    // read status and ownership
    $readstatus = read_status_html_select("read","notread");
    $ownership = ownership_html_select("own","notown");
    $fields = str_replace("#XHTMLREADSTATUS",$readstatus,$fields);
    $fields = str_replace("#XHTMLOWNERSHIP",$ownership,$fields);

    $html = bibheader("");
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BIBINDEX_ADD_ENTRY_TITLE");
// a tabbed interface
    $content = "<div>";
    $content .= "<ul id='tabnav'>";
    $content .= "<li><a id='tab_required_ref' class='active' href='javascript:toggle_tab_edit(\"required_ref\")'>".msg("BIBORB_OUTPUT_REQUIRED_FIELDS")."</a></li>";
    $content .= "<li><a id='tab_optional_ref' href='javascript:toggle_tab_edit(\"optional_ref\")'>".msg("BIBORB_OUTPUT_OPTIONAL_FIELDS")."</a></li>";
    $content .= "<li><a id='tab_additional_ref' href='javascript:toggle_tab_edit(\"additional_ref\")'>".msg("BIBORB_OUTPUT_ADDITIONAL_FIELDS")."</a></li>";
    $content .= "</ul>";
    $content .= "</div>";

    $content .= "<form method='post' action='bibindex.php' enctype='multipart/form-data' onsubmit='return validate_new_entry_form(\"".$_SESSION['language']."\")' id='f_bibtex_entry'>";
	$content .= "<fieldset class='clean'>";
	$content .= "<input name='___type' value='$type' type='hidden'/>";
    $content .= "</fieldset>";
	$content .= eval_php($fields);
	$content .= "<fieldset class='clean'>";
	$content .= "<input type='hidden' name='mode' value='operationresult'/>";
	$content .= "<input class='submit' type='submit' name='cancel' value='".msg("Cancel")."'/>&nbsp;";
	$content .= "<input class='submit' type='submit' name='ok' value='".msg("Add")."'/>";
    $content .= "<input type='hidden' name='action' value='add_entry'/>";
	$content .= "</fieldset>";
	$content .= "</form>";

    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_update_entry
 * Display a form to modify fields of an entry
 */
function bibindex_update_entry(){

	// get the entry
	$entryxml = $_SESSION['bibdb']->entry_with_id($_GET['id']);
    $bt = new BibTeX_Tools();
    $entry = $bt->xml_to_bibtex_array($entryxml);
    $entry = $entry[0];
    $thetype = $entry['___type'];
    $theid = $_GET['id'];

    // get existent types
	$types = $_SESSION['bibdb']->entry_types();

	// xslt transformation
	$xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
	$param = $GLOBALS['xslparam'];
	$param['id'] = $theid;
	$param['modelfile'] = "file:///".str_replace('\\', '/', realpath("./xsl/model.xml"));
	$param['update'] = "true";
    $param['type'] = $thetype;
    $fields = $xsltp->transform($entryxml,load_file("./xsl/xml2htmledit.xsl"),$param);
	$xsltp->free();

    // get existent groups
	$glist = $_SESSION['bibdb']->groups();
    // put the groups HTML select in the form
	array_push($glist,"");
	$groups = xhtml_select("groupslist",1,$glist,"","addGroup()");
	$fields = str_replace("#XHTMLGROUPSLIST",$groups,$fields);

    // read status and ownership
    $readstatus = read_status_html_select("read",(isset($entry['read']) ? $entry['read'] : 'notread'));
    $ownership = ownership_html_select("own",(isset($entry['own']) ? $entry['own'] : 'notown'));
    $fields = str_replace("#XHTMLREADSTATUS",$readstatus,$fields);
    $fields = str_replace("#XHTMLOWNERSHIP",$ownership,$fields);

	$listtypes = xhtml_select('bibtex_type',1,$types,$thetype);

    // form to update the type
	$content = "<form method='get' action='bibindex.php' class='f_default_form'>";
    $content .= "<fieldset>";
    $content .= "<label>".msg("BibTeX type:")."</label>&nbsp;";
    $content .= $listtypes;
    $content .= "<input type='hidden' name='action' value='update_type'/>";
    $content .= "&nbsp;<input class='submit' type='submit' value='".msg("Update")."'/>";
    $content .= "<input type='hidden' name='id' value='$theid'/>";
    $content .= "</fieldset>";
    $content .= "</form>";

    // form to update the bibtex key
    $content .= "<form method='get' id='new_bibtex_key' action='bibindex.php' class='f_default_form' onsubmit='return validate_new_bibtex_key(\"".$_SESSION['language']."\")' >";
    $content .= "<fieldset>";
    $content .= "<label>".msg("BibTeX Key:")."</label>";
    $content .= "&nbsp;<input name='bibtex_key' value='$theid'/>";
    $content .= "<input type='hidden' name='action' value='update_key'/>";
    $content .= "&nbsp;<input class='submit' type='submit' value='".msg("Update")."'/>";
    $content .= "<input type='hidden' name='id' value='$theid'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $content .= "<br/>";

    // a tabbed interface
    $content .= "<div>";
    $content .= "<ul id='tabnav'>";
    $content .= "<li><a id='tab_required_ref' class='active' href='javascript:toggle_tab_edit(\"required_ref\")'>".msg("BIBORB_OUTPUT_REQUIRED_FIELDS")."</a></li>";
    $content .= "<li><a id='tab_optional_ref' href='javascript:toggle_tab_edit(\"optional_ref\")'>".msg("BIBORB_OUTPUT_OPTIONAL_FIELDS")."</a></li>";
    $content .= "<li><a id='tab_additional_ref' href='javascript:toggle_tab_edit(\"additional_ref\")'>".msg("BIBORB_OUTPUT_ADDITIONAL_FIELDS")."</a></li>";
    $content .= "</ul>";
    $content .= "</div>";


    // form to update the different fields
    $content .= "<form method='post' action='bibindex.php' enctype='multipart/form-data' name='fields' id='f_bibtex_entry'>";
    $content .= eval_php($fields);
    $content .= "<fieldset class='clean'>";
    $content .= "<input class='submit' type='submit' name='cancel' value='".msg("Cancel")."'/>";
    $content .= "<input type='hidden' name='action' value='update_entry'/>";
    $content .= "&nbsp;<input class='submit' type='submit' name='ok' value='".msg("Update")."'/>";
    $content .= "<input type='hidden' name='mode' value='operationresult'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
	
	// create the HTML page
	$html = bibheader("onload='javascript:toggle_element(\"additional\")'");
	$html .= bibindex_menu($_SESSION['bibdb']->fullname());
	$title = msg("BIBINDEX_UPDATE_ENTRY_TITLE");
	$html .= main($title,$content);
	$html .= html_close();
	echo $html;
}

/**
 * bibindex_import
 * Interface to import references (bibtex file or textfields)
 */
function bibindex_import(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BIBINDEX_IMPORT_TITLE");

    // general help message
    $content = msg("BIBINDEX_IMPORT_HELP");
    $content .= "<br/><br/>";

    // import from a BibTeX file
//    $content .= "<h3 style='padding:0;margin:0'>".msg("BIBINDEX_IMPORT_FILE_TITLE")."</h3>";
    $content .= "<form method='post' action='bibindex.php' enctype='multipart/form-data'>";
    $content .= "<fieldset style='border:solid 1px navy;' title='".msg("File")."'>";
    $content .= "<legend style='font-weight:bold;color:navy'>".msg("File")."</legend>";
    $content .= "<div style='text-align:left;'>";
    $content .= msg("BIBINDEX_IMPORT_FILE_DESC")."&nbsp;";
    $content .= "<input type='file' name='bibfile'/>";
    $content .= "<input type='hidden' name='mode' value='operationresult'/>";
    $content .= "<input type='hidden' name='action' value='import'/>&nbsp;";
    $content .= "<input class='submit' type='submit' value='".msg("Import")."'/>";
    $content .= "</div>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $content .= "<br/>";

    // import from a BibTeX string
//    $content .= "<h3 style='padding:0;margin:0'>".msg("BIBINDEX_IMPORT_TXT_TITLE")."</h3>";
    $content .= "<form method='post' action='bibindex.php'>";
    $content .= "<fieldset style='border:solid 1px navy;text-align:center;' title='BibTeX'>";
    $content .= "<legend style='font-weight:bold;color:navy'>BibTeX</legend>";
    $content .= "<div style='text-align:left'>".msg("BIBINDEX_IMPORT_TXT_DESC")."</div>";
    $content .= "<textarea name='bibval' cols='55' rows='15'></textarea>";
    $content .= "<input type='hidden' name='mode' value='operationresult'/>";
    $content .= "<div style='text-align:center'>";
    $content .= "<input type='hidden' name='action' value='import'/>";
    $content .= "<input class='submit' type='submit' value='".msg("Import")."'/>";
    $content .= "</div>";
    $content .= "</fieldset>";
    $content .= "</form>";
	
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}


/**
 * function bibindex_export_basket
 *
 */
function bibindex_export_basket(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BIBINDEX_EXPORT_BASKET");
    $content = "<span class='emphit'>".msg("")."</span>";
    // create the form to select which fields to export
    $content .= "<form action='bibindex.php' method='post'>";
    $content .= "<fieldset style='border:none;'>";
    $content .= "<div style='text-align:center'>";
    $content .= msg("Select an export format:")."&nbsp;";
    $content .= "<select size='1' name='export_format'>";
    $content .= "<option value='bibtex'>BibTeX</option>";
    $content .= "<option value='ris'>RIS</option>";
    $content .= "<option value='html'>HTML</option>";
    $content .= "<option value='docbook'>DocBook</option>";
    $content .= "</select>";
    $content .= "<input type='hidden' name='action' value='export_basket'/>";
    $content .= "<input type='submit' value='".msg("Select")."'/>";
    $content .= "</div>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}



/**
 * bibindex_export_basket_to_bibtex
 */
function bibindex_export_basket_to_bibtex(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BIBINDEX_EXPORT_TO_BIBTEX_TITLE");
    $content = "<span class='emphit'>".msg("Select fields to include in the exported BibTeX:")."</span>";
    // create the form to select which fields to export
    $content .= "<form action='bibindex.php' method='post'>";
    $content .= "<fieldset style='border:solid 1px navy;'>";
    $content .= "<legend style='font-weight:bold;color:navy;'>".msg("Available BibTeX fields")."</legend>";
    $content .= "<table>";
    $content .= "<tbody>";
    $cpt = 0;
    for($i=0;$i<count($GLOBALS['bibtex_entries']);$i++){
        if(strcmp($GLOBALS['bibtex_entries'][$i],'id') != 0){
            $field = $GLOBALS['bibtex_entries'][$i];
            if($cpt == 0){
                $content .= "<tr>";
            }
            $content .= "<td title='$field'><input type='checkbox' name='$field'";
            if(!(array_search($field,$GLOBALS['fields_to_export']) === false)){
                $content .= " checked='checked' ";
            }
            $content .= " />".msg($field)."</td>";
            $cpt++;
            if($cpt == 6){
                $content .= "</tr>";
                $cpt = 0;
            }
        }
    }
    if($cpt != 0){
        while($cpt != 6){
            $cpt++;
            $content .= "<td/>";
        }
        $content .= "</tr>";
    }

    $content .= "</tbody>";
    $content .= "</table>";
    $content .= "<div style='text-align:center'>";
    $content .= "<input type='hidden' name='action' value='export'/>";
    $content .= "<input type='submit' value='".msg("Export")."'/>";
    $content .= "</div>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}

/**
 * bibindex_export_basket_to_html
 */
function bibindex_export_basket_to_html(){

	if($_SESSION['basket']->count_items() != 0){
		// basket not empty -> processing
		// get entries
		$entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['basket']->items);
		
		// xslt transformation
		$xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
		$param = $GLOBALS['xslparam'];
		// hide basket actions
		$param['display_basket_actions'] = 'no';
		// hide edition/delete
		$param['mode'] = 'user';
		$content = $xsltp->transform($entries,load_file("./xsl/simple_html_output.xsl"),$param);
		$xsltp->free();
		
		// HTML output
		$html = html_header(null,CSS_FILE,null);
		$html .= $content;
		$html .= html_close();
		echo $html;
	}
	else{
		echo bibindex_display_basket();
	}
}


/**

*/
function bibindex_display_xpath_search()
{
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BIBINDEX_XPATH_SEARCH_TITLE");

    //tabs
    $content = "<div id='search_tabs'>";
    $content .= "<ul id='tabnav'>";
    $content .= "<li><a href='bibindex.php?mode=displaysearch'>".msg("Simple Search")."</a></li>";
    $content .= "<li><a href='bibindex.php?mode=displayadvancedsearch'>".msg("Advanced Search")."</a></li>";
    $content .= "<li><a class='active' href='bibindex.php?mode=displayxpathsearch'>".msg("XPath Search")."</a></li>";
    $content .= "</ul>";
    $content .= "</div>";
    $content .= "<div class='search_content'>";
    $content .= "<h4 class='tool_name'>".msg("TOOL_XPATH_TITLE")."</h4>";
    $content .= "<div class='tool_help'>";
    $content .= msg("TOOL_XPATH_HELP");
    $content .= "</div>";
    $content .= "<form class='tool_form' method='get' action='bibindex.php' id='xpath_form' onsubmit='return validate_xpath_form(\"".$_SESSION['language']."\")'>";
    $content .= "<fieldset>";
    $content .= "<textarea cols='50' rows='5' name='xpath_query'>";
    if(array_key_exists('xpath_query',$_GET)){
        $content .= $_GET['xpath_query'];
    }
    else{
        $content .= "contains(*/bibtex:author, 'someone') and */bibtex:year=2004";
    }
    $content .= "</textarea><br/>";
    $content .= "<input type='hidden' name='mode' value='displayxpathsearch'/>";
    $content .= "<input type='submit' class='submit' value='".msg("Search")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $content .= "</div>";

    // execute an Xpath query
    if(array_key_exists("xpath_query",$_GET)){
        // store the ids in session if we come from an other page.
        if(!isset($_GET['page'])){
            $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->ids_for_xpath_search(htmlentities($_GET['xpath_query'])),$GLOBALS['max_ref']);
            $_GET['page'] = 0;
        }
        $flatids = flatten_array($_SESSION['ids']);
        if(count($flatids)>0){
            $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);

            $nb = count($flatids);
            $param = $GLOBALS['xslparam'];
            $param['bibindex_mode'] = "displayxpathsearch";
            $param['basketids'] = $_SESSION['basket']->items_to_string();
            $extraparam = "xpath_query=".urlencode($_GET['xpath_query']);
            $extraparam .= "&page=".$_GET['page'];
            // add all
            $start = "<div class='result_header'>";
            $start .= add_all_to_basket_div($flatids,"displayxpathsearch",$extraparam);
            $start .= "</div>";
            // create a nav bar to display entries
            $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),"displayxpathsearch",$extraparam);
            $param['extra_get_param'] = $extraparam;
            if($nb==1){
                $content .= msg("One match.").$start;
                $content .= biborb_html_render($entries,$param);
            }
            else if($nb>1) {
                $content .= sprintf(msg("%d matches."),$nb).$start;
                $content .= biborb_html_render($entries,$param);
            }
        }
        else{
            $content .= msg("No match.");
        }
    }
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}


/**
 * Display the Tool page. Mis functions over the current bibliographies.
 */
function bibindex_display_tools(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BIBINDEX_TOOLS_TITLE");

    // Export the whole bibliography
    $content = "<h4 class='tool_name'>".msg("TOOL_EXPORT_TITLE")."</h4>";
    $content .= "<div class='tool_help'>";
    $content .= msg("TOOL_EXPORT_HELP");
    $content .= "</div>";
    $content .= "<form class='tool_form' method='post' ' action='bibindex.php'  id='export_form'>";
    $content .= "<fieldset>";
    $content .= "<select size='1' name='export_format'>";
    $content .= "<option value='bibtex'>BibTeX</option>";
    $content .= "<option value='ris'>RIS</option>";
    $content .= "<option value='docbook'>DocBook</option>";
    $content .= "</select>";
    $content .= "<input type='hidden' name='action' value='export_all'/>";
    $content .= "<input type='submit' value='".msg("Select")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";


    // Get BibTeX references according to a .aux LaTeX file.
    $content .= "<h4 class='tool_name'>".msg("TOOL_AUX2BIBTEX_TITLE")."</h4>";
    $content .= "<div class='tool_help'>";
    $content .= msg("TOOL_AUX2BIBTEX_HELP");
    $content .= "</div>";
    $content .= "<form class='tool_form' method='post' enctype='multipart/form-data' action='bibindex.php'  onsubmit='return validate_bibtex2aux_form(\"".$_SESSION['language']."\")' id='bibtex2aux_form'>";
    $content .= "<fieldset>";
    $content .= "<input type='file' name='aux_file'/>";
    $content .= "<input type='hidden' name='action' value='bibtex_from_aux'/>";
    $content .= "&nbsp;<input type='submit' class='submit' value='".msg("Download")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";

    // Get a full copy of the bibliography (references + attached documents)
    $content .= "<h4 class='tool_name'>".msg("TOOL_GET_ARCHIVE_TITLE")."</h4>";
    $content .= "<div class='tool_help'>";
    $content .= msg("TOOL_GET_ARCHIVE_HELP");
    $content .= "</div>";
    $content .= "<form class='tool_form' method='post' action='bibindex.php'>";
    $content .= "<fieldset>";
    $content .= "<input type='hidden' name='action' value='get_archive'/>";
    $content .= "<input type='submit' class='submit' value='".msg("Download")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";

    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}

/**
 * Browse the bibliography using filters
 */
function bibindex_browse(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->fullname());
    $title = msg("BIBINDEX_BROWSE_TITLE");

    // filter history
    $content = "<div class='browse_history'>";
    $content .= " &nbsp;&#187;&nbsp;<a href='./bibindex.php?mode=browse&amp;start=0'>Start</a>";
    if(array_key_exists('browse_history',$_SESSION)){
        $cpt = 1;
        foreach($_SESSION['browse_history'] as $hist){
            $content .= "&nbsp;&#187;&nbsp;<a href='./bibindex.php?mode=browse&amp;start=$cpt'>".$hist['value']."</a>";
        }
    }
    $content .= "</div>";
    if(!isset($_GET['type']))
        $_GET['type'] = 'year';

    // filters available
    $content .= "<div class='browse'>";
    $content .= "<ul id='tabnav'>";
    $content .= "<li><a id='tab_years' ".($_GET['type'] == 'year' ? "class='active'": "")." href=\"javascript:display_browse('years');\">".msg("Years")."</a></li>";
    $content .= "<li><a id='tab_authors' ".($_GET['type'] == 'authors' ? "class='active'": "")." href=\"javascript:display_browse('authors');\">".msg("Authors")."</a></li>";
    $content .= "<li><a id='tab_series' ".($_GET['type'] == 'series' ? "class='active'": "")." href=\"javascript:display_browse('series');\">".msg("Series")."</a></li>";
    $content .= "<li><a id='tab_journals' ".($_GET['type'] == 'journals' ? "class='active'": "")." href=\"javascript:display_browse('journals');\">".msg("Journals")."</a></li>";
    $content .= "<li><a id='tab_groups' ".($_GET['type'] == 'groups' ? "class='active'": "")." href=\"javascript:display_browse('groups');\">".msg("Groups")."</a></li>";
    $content .= "</ul>";
    $content .= "</div>";

    $content .= "<div class='browse_items'>";

    // years
    // years are listed by decades
    $content .= "<ul id='years' style='display:".($_GET['type'] != 'year' ? "none": "block").";'>".msg("Existing years:");
    for($i=0;$i<count($_SESSION['misc']['years']);$i++){
        $year = $_SESSION['misc']['years'][$i];
        if(!isset($oldyear)){
            $oldyear = $year;
            $content .= "<li><a href='bibindex.php?mode=browse&action=add_browse_item&type=year&value=$year'>$year</a>";
        }
        else{
            if( floor($oldyear/10) != floor($year/10)){
                $content .= "</li><li>";
            }
            else{
                $content .= ", ";
            }
            $content .= "<a href='bibindex.php?mode=browse&action=add_browse_item&type=year&value=$year'>$year</a>";
        }
        $oldyear = $year;
        if($i==count($_SESSION['misc']['years'])-1){
            $content .= "</li>";
        }

    }
    $content .= "</ul>";

    // authors are listed by alphabetic order
    // one list item by letter of the alphabet
    $content .= "<ul id='authors' style='display:".($_GET['type'] != 'authors' ? "none": "block").";'>".msg("Existing authors:");

    for($i=0;$i<count($_SESSION['misc']['authors']);$i++){
        $author = remove_accents($_SESSION['misc']['authors'][$i]);
        if(!isset($oldauthor)){
            $oldauthor = $author;
            $content .= "<li><a href='bibindex.php?mode=browse&action=add_browse_item&type=author&value=$author'>$author</a>";
        }
        else{
            if($author[0] != $oldauthor[0]){
                $content .= "</li><li>";
            }
            else{
                $content .= ", ";
            }
            $content .= "<a href='bibindex.php?mode=browse&action=add_browse_item&type=author&value=$author'>$author</a>";
            $oldauthor = $author;
        }

        if($i == count($_SESSION['misc']['authors'])-1){
            $content .= "</li>";
        }
    }
    $content .= "</ul>";

    // series
    $content .= "<ul id='series' style='display:".($_GET['type'] != 'series' ? "none": "block").";'>".msg("Existing series:");
    foreach($_SESSION['misc']['series'] as $serie){
        $content .= "<li><a href='bibindex.php?mode=browse&action=add_browse_item&type=series&value=$serie'>$serie</a></li>";
    }
    $content .= "</ul>";

    // journal
    $content .= "<ul id='journals' style='display:".($_GET['type'] != 'journals' ? "none": "block").";'>".msg("Existing journals:");
    foreach($_SESSION['misc']['journals'] as $journal){
        $content .= "<li><a href='bibindex.php?mode=browse&action=add_browse_item&type=journal&value=$journal'>$journal</a></li>";
    }
    $content .= "</ul>";

    // groups
    $content .= "<ul id='groups' style='display:".($_GET['type'] != 'groups' ? "none": "block")."';>".msg("Existing groups:");
    foreach($_SESSION['misc']['groups'] as $group){
        $content .= "<li><a href='bibindex.php?mode=browse&action=add_browse_item&type=group&value=$group'>$group</a></li>";
    }
    $content .= "</ul>";
    $content .= "</div>";

    // store the ids in session if we come from an other page.
    // the bibtex keys are retreived from the database the first time that display_all is called
    if(!isset($_GET['page'])){
    	// split the array so that we display only $GLOBALS['max_ref']
        $_SESSION['ids'] = array_chunk($_SESSION['browse_ids'],$GLOBALS['max_ref']);
        // go to the first page
        $_GET['page'] = 0;
    }

    $flatids = flatten_array($_SESSION['ids']);

    if(isset($_GET['page']) || $_GET['start']>0){
        if(count($flatids)>0){
            // get the data of the references to display
            $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
            // set up XSLT parameters
            $param = $GLOBALS['xslparam'];
            $param['bibindex_mode'] = $_GET['mode'];
            $param['basketids'] = $_SESSION['basket']->items_to_string();
            $param['extra_get_param'] = "page=".$_GET['page'];
            $html_content = biborb_html_render($entries,$param);

            // create the header: sort function + add all to basket
            $start = "<div class='result_header'>";
            /*if(DISPLAY_SORT){
                $start = sort_div($GLOBALS['sort'],$GLOBALS['sort_order'],$_GET['mode'],null).$start;
            }*/
            $start .= add_all_to_basket_div($flatids,$_GET['mode'],"sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."&amp;page=".$_GET['page']);
            $start .= "</div>";

            // create a nav bar to display entries
            $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),"browse","sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."page=".$_GET['page']);
            $content .= $start.$html_content;
        }
        else{
            $content .= msg("No entries.");
        }
    }



    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}

/**
 * Generate the HTML biborb code of entries.
 * @param $entries Entries to render
 * @param $options Some option for the rendering
 */
function biborb_html_render($entries,$options)
{
    // init an XSLT processor
    $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
    // interpret LaTeX code in HTML
    $entries = latex_macro_to_html($entries);
    // do the transformation
    $content = $xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$options);
    // replace localized string
    $content = replace_localized_strings($content);
    $xsltp->free();
    return $content;
}



?> 