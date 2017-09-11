<?php
/**
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
 * File: index.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *      This is the page that is initially loaded when accessing BibORB.
 *
 *      It offers the following functions:
 *          - authentication ($disable_authentication set to false)
 *          - add a new bibliography
 *                  (if $disable_authentication set to true
 *                   or registered user)
 *          - delete a bibliography
 *                  (move to a trash folder)
 *          - accessing the list of available bibliographies
 *
 */

require_once("config.php");          // load configuration variables
require_once("php/functions.php");   // load needed functions
require_once("php/biborbdb.php");    // load biborb database
require_once("php/interface-index.php");   // load function to generate the interface
require_once("php/auth.php");        // load authentication class
require_once("php/i18n.php");        // load i18n functions
require_once("php/error.php");       // load biborb error handler
//require_once("php/third_party/Cache/Lite/Output.php"); // cache system

/**
 *   Load the session
 */
// do not put web pages in browser cache
session_cache_limiter('nocache');
session_name("SID");
session_start();

// Set the error_handler for biborb
set_error_handler("biborb_error_handler");

/**
 * stripslashes
 */
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


/**
 * To store an error or a message. (mode=result)
 */
$error_or_message = array('error' => null,
                          'message' => null);

/**
 * Get a value for 'mode' in the $_GET array.
 * If not in $_GET, receive null
 * The 'mode' variable sets which page to display
 */
$mode = (array_key_exists('mode',$_GET) ? $_GET['mode'] : null);

/**
 * Set the access level
 * If authentication activated delegate to the Auth class.
 * Else set full power to any user.
 */
if(!DISABLE_AUTHENTICATION){
    // create a new Auth object if needed
    if(!array_key_exists('auth',$_SESSION))
        $_SESSION['auth'] = new Auth();

    if(!array_key_exists('user',$_SESSION))
        $_SESSION['user_is_admin'] = FALSE;

}
else{
    $_SESSION['user_is_admin'] = TRUE;
}

/**
 * Look in $_GET for an action to be performed
 */
if(isset($_GET['action'])){
    switch($_GET['action']){
        /*  Select the lang   */
        case 'select_lang':
            $_SESSION['language'] = $_GET['lang'];
            load_i18n_config($_SESSION['language']);
            break;

        /* Create a database  */
        case 'create':
            // check we have the authorization to modify
            if(!array_key_exists('user_is_admin',$_SESSION) || !$_SESSION['user_is_admin']){
                trigger_error("You are not authorized to create bibliographies!",ERROR);
            }

            $error_or_message = create_database($_GET['database_name'],
                                                $_GET['description']);
            break;

        /* Delete a database  */
        case 'delete':
            // check we have the authorization to modify
            if(!array_key_exists('user_is_admin',$_SESSION) || !$_SESSION['user_is_admin']){
                trigger_error("You are not authorized to delete bibliographies!",ERROR);
            }
            $error_or_message['message'] = delete_database($_GET['database_name']);
            break;

        /*  Logout  */
        case 'logout':
            $_SESSION['user_is_admin'] = FALSE;
            $_SESSION['user_can_add'] = FALSE;
            $_SESSION['user_can_modidy'] = FALSE;
            $_SESSION['user_can_delete'] = FALSE;
            unset($_SESSION['user']);
            unset($_SESSION['user_pref']);
            $_SESSION['language'] = DEFAULT_LANG;
            load_i18n_config($_SESSION['language']);
            break;

        default:
            break;
    }
}

/**
 * Look in $_POST for an action to be performed
 */
if(isset($_POST['action'])){
    switch($_POST['action']){
        /*  Login */
        case 'login':
            $login = $_SESSION["google"]["gmail"];
            // check missing values
            if($login==""){
                $error_or_message['error'] = msg("LOGIN_MISSING_VALUES");
                $mode = "login";
            }
            else{
                // check the user name
                $loggedin = $_SESSION['auth']->is_valid_user($login,"");
                if($loggedin){
                    // set user privileges
                    $_SESSION['user'] = $login;
                    $_SESSION['user_is_admin'] = $_SESSION['auth']->is_admin_user($login);
                    $_SESSION['user_pref'] = $_SESSION['auth']->get_preferences($login);
                    // select language
                    $_SESSION['language'] = $_SESSION['user_pref']['default_language'];
                    load_i18n_config($_SESSION['language']);
                    // redirect to the default database
                    if($_SESSION['user_pref']['default_database'] != ""){
                        header("Location:./bibindex.php?bibname=".$_SESSION['user_pref']['default_database']);
                    }
                }
                else {
                    $error_or_message['error'] = msg("LOGIN_WRONG_USERNAME_PASSWORD");
                    $mode = "login";
                }
            }
            break;

        /* Update user's preferences. */
        case 'update_preferences':
            $_SESSION['auth']->set_preferences($_POST,$_SESSION['user']);
            $_SESSION['user_pref'] = $_SESSION['auth']->get_preferences($_SESSION['user']);
            $_SESSION['language'] = $_SESSION['user_pref']['default_language'];
            load_i18n_config($_SESSION['language']);
            $mode = "preferences";
            $message = msg("Preferences updated.");
            break;

        default:
            break;
    }
}

/*********************************************** BEGINING OF THE HTML OUTPUT **/

/**
 * Select what to do according the value of 'mode'
 */
switch($mode){
	// This is the welcome page.
    case 'welcome':
        echo index_welcome();
        break;

	// List of available bibliograpies
    case 'select':
        echo index_select();
        break;

    // Add a new bibliography
    case 'add_database': echo index_add_database(); break;

	// Delete a bibliography
    case 'delete_database': echo index_delete_database(); break;

	// Little help on what is available for the administrator mode
    case 'manager_help':
        echo index_manager_help();
        break;

	// Login form
    case 'login': echo index_login(); break;

	// Generic page to display result of operations (add, delete, ...)
    case 'result': echo index_result(); break;

    // User's Preferences panel
    case 'preferences': echo index_preferences(); break;

	// By default, load the welcome page
    default: echo index_welcome(); break;
}

?>
