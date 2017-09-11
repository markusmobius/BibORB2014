<?php
/**
 * This file is part of BibORB
 *
 * Copyright (C) 2005  Guillaume Gardey (ggardey@club-internet.fr)
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
    File: i18n.php
    Author: Guillaume Gardey (ggardey@club-internet.fr)
    Licence: GPL

*/

// list of available locales
$available_locales = array('fr_FR' => 'Français',
                           'en_US' => 'English',
                           'it_IT' => 'Italiano',
                           'de_DE' => 'Deutsch');

/**
 * Load a localized strings for a lang.
 * Load it into $_SESSION['$language'] as an array $msgid => $msgstr
 * @param $language Language code
 */
function load_i18n_config($language = 'en_US') {
    /*
     setlocale(LC_ALL,$language);
     putenv("LANG=$language");
     setlocale(LC_MESSAGES, $language);
     $domain = 'biborb';
     bindtextdomain($domain,'./locale');
     textdomain($domain);
     */
    // As gettext not correctly supported on both php config
    // I parse the good .po file and load it into an array
    // load only if necessary

    // check $language is a valid lang (set to en_US if not)
    if(!array_key_exists($language,$GLOBALS['available_locales'])){
        $language = 'en_US';
    }
    //  load localized data
    $i18nfile = "./locale/".$language."/LC_MESSAGES/biborb.po";
    $i18nfile = file_exists($i18nfile) ? file($i18nfile) : file($default);

    // Parse data
    $lines_count = count($i18nfile);
    $current_line = 0;
    $key = null;
    while($current_line < $lines_count){
        $line = trim($i18nfile[$current_line]);
        if(preg_match("/msgid \"(.*)\"/",$line,$matches)){
            if($key){
                $_SESSION[$language][$key] = (trim($translation) == "" ? $key : $translation);
            }
            $key = $matches[1];
            $translation = "";
        }
        else if(preg_match("/msgstr \"(.*)\"/",$line,$matches)){
            $translation .= $matches[1];
        }
        else if(preg_match("/\"(.*)\"/",$line,$matches)){
            $translation .= $matches[1];
        }
        $current_line++;
    }
    if($key){
        $_SESSION[$language][$key] = ($translation == "" ? $key : $translation);
    }
}

/**
 * Translate a localized string
 * If $string doesn't exists, $string is returned.
 *  msg get the language configuration from $_SESSION
 */
function msg($string){
    // should return _($string) if gettext well supported
    return (array_key_exists($string, $_SESSION[$_SESSION['language']]) ? $_SESSION[$_SESSION['language']][$string] : $string);
}

/**
 * Load a localized text file.
 */
function load_localized_file($filename)
{
    $default = "./locale/en_US/$filename";
    $i18nfile = "./locale/".$_SESSION['language']."/".$filename;
    if(file_exists($i18nfile)){
        return load_file($i18nfile);
    }
    else{
        return load_file($default);
    }
}

/**
 * Parse a string and replace with localized data
 */
function replace_localized_strings($string)
{
    // ensure localisation is set up
    load_i18n_config($_SESSION['language']);
    // get all key to translate
    preg_match_all("(BIBORB_OUTPUT\w+)",$string,$matches);
    $keys = array_unique($matches[0]);
    // get the localized value for each element and replace it
    foreach($keys as $val){
        $string = str_replace($val,msg("$val"),$string);
    }
    return $string;
}


/**
 * Generate a HTML Select tag containing locales name.
 * On click call javascript to change the language
 */
function lang_html_select($lang,$name,$onchange = false)
{
    if($onchange){
        $res = "<select name='$name' id='$name' onchange='javascript:change_lang_index(this.value)'>";
    }
    else{
        $res = "<select name='$name' id='$name'>";
    }
    foreach($GLOBALS['available_locales'] as $locale=>$name){
        if($lang == $locale){
            $res .= "<option selected='selected' value='$locale' >".$name."</option>";
        }
        else{
            $res .= "<option value='$locale'>".$name."</option>";
        }
    }
    $res .= "</select>";
    return $res;
}

/**
 * Get prefered language from HTTP request.
 */
function get_pref_lang()
{
    if(array_key_exists('HTTP_ACCEPT_LANGUAGE',$_SERVER)){
        $preferedLanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $tab = preg_split('/,/',$preferedLanguages);
        $tab1 = preg_split('/-/',$tab[0]);
	if(count($tab1)==1){
		return $tab1[0];
	}
	else{
        	return $tab1[0]."_".strtoupper($tab1[1]);
	}
    }
    return FALSE;
}


?>
