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
 * File: interface-index.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *      Functions to generate the interface (index.php)
 *
 */

/**
 * Create the page for authentication
 */
function index_login(){
    $html = html_header("Biborb",CSS_FILE);
    $html .= index_menu();
    $title = msg("INDEX_MENU_LOGIN_TITLE");

    require_once 'src/Google_Client.php';
    require_once 'src/contrib/Google_Oauth2Service.php';
    $client = new Google_Client();
    $oauth2 = new Google_Oauth2Service($client);
    $authUrl = $client->createAuthUrl();
    $_SESSION['returnpoint']="index.php";
    $content="<a class=\"login\" href=\"$authUrl\">Google Account Login</a>";

    $html .= main($title,$content,$GLOBALS['error_or_message']['error']);
    $html .= html_close();

    return $html;
}

/**
 * Display the welcome page
 * The text is loaded from ./data/index_welcome.txt
 */
function index_welcome(){
    $html = html_header("Biborb",CSS_FILE);
    $title = "BibORB 2014: BibTeX On-line References Browser";
    $content = load_localized_file("index_welcome.txt");
    // get the version and the date
    $content = str_replace('$biborb_version',BIBORB_VERSION,$content);
    $content = str_replace('$date_release',BIBORB_RELEASE_DATE,$content);
    $html .= index_menu();
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * Create the page to add a new bibliography.
 */
function index_add_database(){
    $html = html_header("Biborb",CSS_FILE);
    $title = msg("INDEX_CREATE_BIB_TITLE");
    // create the form to create a new bibliography
    $content = "<form method='get' action='index.php' id='f_bib_creation' onsubmit='return validate_bib_creation(\"".$_SESSION['language']."\")'>";
    $content .= "<fieldset>";
    $content .= "<input type='hidden' name='mode' value='result'/>";
    $content .= "<label for='database_name'>".msg("INDEX_CREATE_BIBNAME").":</label>";
    $content .= "<input type='text' name='database_name' id='database_name'/><br/>";
    $content .= "<label for='description'>".msg("INDEX_CREATE_DESCRIPTION").":</label>";
    $content .= "<input type='text' name='description' id='description'/><br/>";
    $content .= "<input type='hidden' name='action' value='create'/>";
    $content .= "<input class='submit' type='submit' value='".msg("Create")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";

    $html .= index_menu();
    $html .= main($title,$content);
    $html .= html_close();

    return $html;
}

/**
 * Display the bibliographies in a combo box to select which one to delete.
 */
function index_delete_database(){
    $html = html_header("Biborb",CSS_FILE);
    $title = msg("INDEX_DELETE_BIB_TITLE");

    // get all bibliographies and create a form to select which one to delete
    $databases = get_databases_names();
    $content = "<form method='get' action='index.php' id='f_delete_database'>";
    $content .= "<fieldset>";
    $content .= "<input type='hidden' name='mode' value='result'/>";
    $content .= "<select name='database_name'>";

    foreach($databases as $key=>$name){
        if($key != ".trash"){
            $content .= "<option value='$key'>$name</option>";
        }
    }

    $content .= "</select>";
    $content .= "<input type='hidden' name='action' value='delete'/>";
    $content .= "&nbsp;<input class='submit' type='submit' value='".msg("Delete")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";

    $html .= index_menu();
    $html .= main($title,$content);
    $html .= html_close();

    return $html;
}

/**
 * Display an help for the manager submenu. This help is loaded from a file.
 */
function index_manager_help(){
    $html = html_header("Biborb",CSS_FILE);
    $title = msg("INDEX_MANAGER_HELP_TITLE");
    $content = load_localized_file("index_manager_help.txt");
    $html .= index_menu();
    $html .= main($title,$content);
    $html .= html_close();

    return $html;
}

/**
 * Generic page to display the result of an operation.
 * Will only display information recorded into $error_or_message
 */
function index_result(){
    $html = html_header("Biborb",CSS_FILE);
    $html .= index_menu();
    $html .= main(msg("INDEX_RESULTS_TITLE"),null,
                  $GLOBALS['error_or_message']['error'],
                  $GLOBALS['error_or_message']['message']);
    $html .= html_close();

    return $html;
}

/**
 * List of available bibliographies.
 */
function index_select(){
    $html = html_header("Biborb",CSS_FILE);
    $title = msg("INDEX_AVAILABLE_BIBS_TITLE");
    $html .= index_menu();

    // get all bibliographies and create an array
    $databases = get_databases_names();
    $content = "<table id='available_bibliographies'>";
    $content .= "<thead>";
    $content .= "<tr>";
    $content .= "<th>".msg("INDEX_AVAILABLE_BIBS_COL_BIBNAME")."</th>";
    $content .= "<th>".msg("INDEX_AVAILABLE_BIBS_COL_BIBDESCRIPTION")."</th>";
    $content .= "</tr>";
    $content .= "</thead>";
    $content .= "<tbody>";

    foreach($databases as $name=>$fullname){
        // do not parse the trash directory
        if($name != ".trash"){
            $description = load_file("./bibs/$name/description.txt");
            $content .= "<tr>";
            $content .= "<td><a class='bibname' href='./bibindex.php?mode=welcome&amp;bibname=$name'>$fullname</a></td>";
            $content .= "<td><span class='bib_description'>$description</span></td>";
            $content .= "</tr>";
        }
    }
    $content .= "</tbody></table>";

    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * Create the menu for each page generated. It is placed into a <div> tag of ID 'menu'.
 */
function index_menu(){
    // start of the div tag
    $html = "<div id='menu'>";
    // title to display => use ID 'title'
    $html .= "<span id='title'>BibORB</span>";
    // no bibliography currently displayed
    $html .= "<span id='bibname'></span>";

    // First menu item:
    // -> Welcome
    //      | -> Available bibliographies
    $html .= "<ul>";
    $html .= "<li><a title=\"".msg("INDEX_MENU_WELCOME_HELP")."\" href='index.php?mode=welcome'>".msg("INDEX_MENU_WELCOME")."</a>";
    $html .= "<ul>";
    $html .= "<li><a title='".msg("INDEX_MENU_BIBS_LIST_HELP")."' href='index.php?mode=select'>".msg("INDEX_MENU_BIBS_LIST")."</a></li>";
    $html .= "</ul></li>";

    // Second menu item:
    // -> Manager
    //      | -> Login              (if not administrator)
    //      | -> Add a bibliography (if administrator)
    //      | -> Delete a bibliography (if administrator)
    //      | -> Logout     (if administrator and $disable_authentication set to false)
    $html .= "<li><a title='".msg("INDEX_MENU_MANAGER_HELP")."' href='index.php?mode=manager_help'>".msg("INDEX_MENU_MANAGER")."</a>";
    $html .= "<ul>";
    if(!DISABLE_AUTHENTICATION && !array_key_exists('user',$_SESSION)){
        $html .= "<li><a title=\"".msg("INDEX_MENU_LOGIN_HELP")."\" href='index.php?mode=login'>".msg("INDEX_MENU_LOGIN")."</a></li>";
    }
    if($_SESSION['user_is_admin']){
        $html .= "<li><a title='".msg("INDEX_MENU_ADD_BIB_HELP")."' class='admin' href='index.php?mode=add_database'>".msg("INDEX_MENU_ADD_BIB")."</a></li>";
        $html .= "<li><a title='".msg("INDEX_MENU_DELETE_BIB_HELP")."' class='admin' href='index.php?mode=delete_database'>".msg("INDEX_MENU_DELETE_BIB")."</a></li>";
    }
    if(array_key_exists('user',$_SESSION)){
        $html .= "<li>";
        $html .= "<a href='index.php?mode=preferences' title='".msg("INDEX_MENPREFERENCES_HELP")."' >".msg("INDEX_MENU_PREFERENCES")."</a>";
        $html .= "</li>";
    }
    if(!DISABLE_AUTHENTICATION && array_key_exists('user',$_SESSION)){
        $html .= "<li><a title='".msg("INDEX_MENU_LOGOUT_HELP")."' href='index.php?mode=welcome&amp;action=logout'>".msg("INDEX_MENU_LOGOUT")."</a></li>";
    }
    $html .= "</ul>";
    $html .= "</li>";
    $html .= "</ul>";

    // Display language selection if needed & and if the user is not logged in.
    if(DISPLAY_LANG_SELECTION && !array_key_exists("user",$_SESSION)){
        $html .= "<form id='language_form' action='index.php' method='get'>";
        $html .= "<fieldset>";
        $html .= "<label for='lang'>".msg("Language:")."</label>";
        $html .= lang_html_select($_SESSION['language'],'lang','javascript:change_lang_index(this.value)');
        $html .= "<input type='hidden' name='action' value='select_lang'/>";
        $html .= "<noscript><div><input class='submit' type='submit' value='".msg("Select")."'/></div></noscript>";
        $html .= "</fieldset>";
        $html .= "</form>";
    }
    $html .= "</div>";

    return $html;
}
/**
 * Display preferences.
 */
function index_preferences(){
    $html = html_header("Biborb",CSS_FILE);
    $html .= index_menu();
    if(isset($GLOBALS['message'])){
        $html .= main(msg("PREFERENCES_TITLE"),pref_content(),null,$GLOBALS['message']);
    }
    else{
        $html .= main(msg("PREFERENCES_TITLE"),pref_content());
    }
    $html .= html_close();

    return $html;
}

/**
 * Preferences panel
 * Generate the HTML content for the preference panel.
 */
function pref_content(){
    // load the preferences of the current user
    $pref = $_SESSION['auth']->get_preferences($_SESSION['user']);

    $content = "<form id='preferences' method='post' action='index.php'>";
    $content .= "<fieldset>";
    $content .= "<legend>".msg("Preferences")."</legend>";
    $content .= "<table>";

    // CSS File
    //$content .= "<tr>";
    //$content .= "<td>".msg("Select a CSS stype.")."</td>";
    //$content .= "<td><select name='css_file'>";
    //if($pref['css_file']=='style.css'){
    //    $content .= "<option value='style.css' selected='selected'>".msg("Default")."</option>";
    //}
    //$content .= "</select></td>";
    //$content .= "</tr>";

    // Default language
    $content .= "<tr>";
    $content .= "<td>".msg("Select your language.")."</td>";
    $content .= "<td>".lang_html_select($pref['default_language'],'default_language')."</td>";
    $content .= "</tr>";

    // Default database
    $content .= "<tr>";
    $content .= "<td>".msg("Select the default database to open once logged in.")."</td>";
    $names = get_databases_names();
    $content .= "<td><select name='default_database'>";
    foreach($names as $key=>$name){
        if($pref['default_database'] == $name){
            $content .= "<option selected='selected' value='$key'>$name</option>";
        }
        else{
            $content .= "<option value='$key'>$name</option>";
        }
    }
    $content .= "</select></td>";
    $content .= "<tr/>";

    // Display images
    $content .= "<tr>";
    $content .= "<td>".msg("Display icons commands.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='display_images' value='yes' ".($pref['display_images'] == "yes" ? "checked='checked'" : "" ).">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='display_images' value='no' ".($pref['display_images'] == "no" ? "checked='checked'" : "" ).">".msg("No")."</input>";
    $content .= "</td></tr>";

    // Display text
    $content .= "<tr>";
    $content .= "<td>".msg("Display text commands.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='display_txt' value='yes' ".($pref['display_txt'] == "yes" ? "checked='checked'" : "" ).">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='display_txt' value='no' ".($pref['display_txt'] == "no" ? "checked='checked'" : "" ).">".msg("No")."</input>";
    $content .= "</td></tr>";

    // Display abstract
    $content .= "<tr>";
    $content .= "<td>".msg("Display abstract.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='display_abstract' value='yes' ".($pref['display_abstract'] == "yes" ? "checked='checked'" : "" ).">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='display_abstract' value='no' ".($pref['display_abstract'] == "no" ? "checked='checked'" : "" ).">".msg("No")."</input>";
    $content .= "</td></tr>";

    // Warn before deleting
    $content .= "<tr>";
    $content .= "<td>".msg("Warn before deleting.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='warn_before_deleting' value='yes' ".($pref['warn_before_deleting'] == "yes" ? "checked='checked'" : "" ).">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='warn_before_deleting' value='no' ".($pref['warn_before_deleting'] == "no" ? "checked='checked'" : "" ).">".msg("No")."</input>";
    $content .= "</td></tr>";

    // Display sort forms
    $content .= "<tr>";
    $content .= "<td>".msg("Display sort functions.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='display_sort' value='yes' ".($pref['display_sort'] == "yes" ? "checked='checked'" : "").">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='display_sort' value='no' ".($pref['display_sort'] == "no" ? "checked='checked'" : "").">".msg("No")."</input>";
    $content .= "</td></tr>";

    // Sort id
    $content .= "<tr>";
    $content .= "<td>".msg("Default sort attribute.")."</td>";
    $content .= "<td>";
    $content .= "<select name='default_sort'>";
    foreach($GLOBALS['sort_values'] as $sortval){
        if($pref['default_sort'] == $sortval){
            $content .= "<option selected='selected' value='$sortval'>".msg("$sortval")."</option>";
        }
        else{
            $content .= "<option value='$sortval'>".msg("$sortval")."</option>";
        }
    }
    $content .= "</select>";
    $content .= "</td>";
    $content .= "</tr>";

    // sort order
    $content .= "<tr>";
    $content .= "<td>".msg("Default sort order.")."</td>";
    $content .= "<td>";
    $content .= "<select name='default_sort_order'>";
    if($pref['default_sort_order'] == 'ascending'){
        $content .= "<option selected='selected' value='ascending'>".msg("ascending")."</option>";
    }
    else{
        $content .= "<option value='ascending'>".msg("ascending")."</option>";
    }
    if($pref['default_sort_order'] == 'descending'){
        $content .= "<option selected='selected' value='descending'>".msg("descending")."</option>";
    }
    else{
        $content .= "<option value='descending'>".msg("descending")."</option>";
    }
    $content .= "</select>";
    $content .= "</td>";
    $content .= "</tr>";

    // max ref by pages
    $content .= "<tr>";
    $content .= "<td>".msg("Number of references by page.")."</td>";
    $content .= "<td><input size='3' name='max_ref_by_page' value='".$pref['max_ref_by_page']."'/></td>";
    $content .= "</tr>";

    // shelf mode
    $content .= "<tr>";
    $content .= "<td>".msg("Display shelf actions.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='display_shelf_actions' value='yes' ".($pref['display_shelf_actions'] == "yes" ? "checked='checked'" : "" ).">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='display_shelf_actions' value='no' ".($pref['display_shelf_actions'] == "no" ? "checked='checked'" : "" ).">".msg("No")."</input>";
    $content .= "</td></tr>";

    $content .= "</table>";
    $content .= "<input type='hidden' name='action' value='update_preferences'/>";
    $content .= "<div style='text-align:center'>";
    $content .= "<input class='submit' type='submit' value='".msg("Update")."'/>";
    $content .= "</div>";
    $content .= "</fieldset>";
    $content .= "</form>";
    return $content;
}
?> 