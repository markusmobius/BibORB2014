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
    File: auth.model.php
    Author: Guillaume Gardey (ggardey@club-internet.fr)
    Licence: GPL

    Description:
        Implementation of Auth using files.

            bib_users.txt contains information about registered users
                ex:
                    userA:passwordA,1
                    userB:passwordB,0

            => userA is an administrator, userB not.

            bib_access.txt contains authorizations
                ex:
                    :userC*adm,userD*m
                    bib1:userA*adm,userB*a
                    bib2:*m


            userA can add (a), delete (d) or modify (m) references
            userB can only add new references.
            userC gets all privileges on all bibliographies
            userD gets only modification privilege on all bibliographies
            all anonym users can modify references of bibliography bib2

    Lines starting with # are considered as comments
*/

/**
 * Class Auth: a genreic class to check authorizations.
 *
 */
class Auth
{
    var $f_users;
    var $f_access;

    /**
     *   Constructor
     */
    function Auth(){
        $this->f_users = "./data/auth_files/bib_users.txt";
        $this->f_access = "./data/auth_files/bib_access.txt";
    }

    /**
     * Is the login/password valid?
     * Returns TRUE/FALSE
     */
    function is_valid_user($user,$pass){
        $content = file($this->f_users);
        foreach($content as $line){
            $line = trim($line);
            if($line != '' && $line[0] != '#'){
                if(preg_match("/(\S*)\s*:\s*(\S*),([01])$/",$line,$match)){
                    if($match[1] == $user){
                        //return crypt($pass,$match[2]) == $match[2];
                        return $pass == $match[2];
                    }
                }
            }
        }
        return FALSE;
    }

    /**
     * Is the user an administrator?
     * Returns TRUE/FALSE
     */
    function is_admin_user($user){
        $content = file($this->f_users);
        foreach($content as $line){
            $line = trim($line);
            if($line != '' && $line[0] != '#'){
                $line=explode(",",$line);
                $match=explode(":",$line[0]);
                if ($match[0]==$user){
                    return ($line[1] == '1');
                }
            }
        }
        return false;
    }

    /**
     * Can the user delete entries?
     * Returns TRUE/FALSE
     */
    function can_delete_entry($user, $database_name){
        $users = $this->registered_users_for_bibliography($database_name);
        if(array_key_exists($user,$users)){
            return strstr($users[$user],'d');
        }
    }

    /**
     * Can the user add entries?
     * Return TRUE/FALSE
     */
    function can_add_entry($user, $database_name){
        $users = $this->registered_users_for_bibliography($database_name);
        if(array_key_exists($user,$users)){
            return strstr($users[$user],'a');
        }
    }

    /**
     * Can the user update entries?
     * Return TRUE/FALSE
     */
    function can_modify_entry($user, $database_name){
        $users = $this->registered_users_for_bibliography($database_name);
        if(array_key_exists($user,$users)){
            return strstr($users[$user],'m');
        }
    }

    /**
     * Get the list of users associated to a bibliography.
     */
    function registered_users_for_bibliography($bibname){
        $content = file($this->f_access);
        $users = array();
        foreach($content as $line){
            $line = trim($line);
            if($line != "" && $line[0] != '#'){
                //match for all bibliographies
                if(preg_match("/:(.*)/",$line,$match)){
                    $data = explode(',',$match[1]);
                    foreach($data as $user){
                        $tab = explode('*',$user);
                        $users[$tab[0]] = $tab[1];
                    }
                }
                // match for a given bibliography
                else if(preg_match("/(.*)\s*:\s*(.*)/",$line,$match)){
                    if($match[1] == $bibname){
                        $data = explode(',',$match[2]);
                        foreach($data as $user){
                            $tab = explode('*',$user);
                            $users[$tab[0]] = $tab[1];
                        }
                    }
                }
            }
        }
        return $users;
    }

    /**
     * Get the preferences of a user.
     * @return An array of preferences for the user $user.
     */
    function get_preferences($user){
        $prefFile="none";
        if (!is_array($user)){
            $prefFile = "./data/auth_files/pref_".$user.".txt";
        }
        $pref = array();
        if(file_exists($prefFile)){
            $lines = file($prefFile);
            foreach($lines as $line){
                if(preg_match("/css_file:(.*)/",$line,$match)){
                    $pref['css_file'] = $match[1];
                }
                if(preg_match("/default_language:(.*)/",$line,$match)){
                    $pref['default_language'] = $match[1];
                }
                if(preg_match("/default_database:(.*)/",$line,$match)){
                    $pref['default_database'] = $match[1];
                }
                if(preg_match("/display_images:(.*)/",$line,$match)){
                    $pref['display_images'] = $match[1];
                }
                if(preg_match("/display_txt:(.*)/",$line,$match)){
                    $pref['display_txt'] = $match[1];
                }
                if(preg_match("/display_abstract:(.*)/",$line,$match)){
                    $pref['display_abstract'] = $match[1];
                }
                if(preg_match("/warn_before_deleting:(.*)/",$line,$match)){
                    $pref['warn_before_deleting'] = $match[1];
                }
                if(preg_match("/display_sort:(.*)/",$line,$match)){
                    $pref['display_sort'] = $match[1];
                }
                if(preg_match("/default_sort:(.*)/",$line,$match)){
                    $pref['default_sort'] = $match[1];
                }
                if(preg_match("/default_sort_order:(.*)/",$line,$match)){
                    $pref['default_sort_order'] = $match[1];
                }
                if(preg_match("/max_ref_by_page:(.*)/",$line,$match)){
                    $pref['max_ref_by_page'] = (int)$match[1];
                }
                if(preg_match("/display_shelf_actions:(.*)/",$line,$match)){
                    $pref['display_shelf_actions'] = $match[1];
                }
            }
        }
        else{
            //default preferences
            $pref['css_file'] = "style.css";
            $pref['default_language'] = "en_US";
            $pref['default_database'] = "";
            $pref['display_images'] = "yes";
            $pref['display_txt'] = "no";
            $pref['display_abstract'] = "no";
            $pref['warn_before_deleting'] = "yes";
            $pref['display_sort'] = "yes";
            $pref['default_sort'] = "ID";
            $pref['default_sort_order'] = "ascending";
            $pref['max_ref_by_page'] = "10";
            $pref['display_shelf_actions'] = "no";
            $this->set_preferences($pref,$user);
        }

        return $pref;
    }

    /**
        Set the preferences for a given user.
     */
    function set_preferences($pref,$user){
        $prefTxt = "";
        $prefTxt .= "css_file:".(array_key_exists("css_file",$pref) ? $pref['css_file'] : "style.css")."\n";
        $prefTxt .= "default_language:".(array_key_exists("default_language",$pref) ? $pref['default_language'] : "en_US")."\n";
        $prefTxt .= "default_database:".(array_key_exists("default_database",$pref) ? $pref['default_database'] : "")."\n";
        $prefTxt .= "display_images:".(array_key_exists("display_images",$pref) ? $pref['display_images'] : "yes")."\n";
        $prefTxt .= "display_txt:".(array_key_exists("display_txt",$pref) ? $pref['display_txt'] : "no")."\n";
        $prefTxt .= "display_abstract:".(array_key_exists("display_abstract",$pref) ? $pref['display_abstract'] : "no")."\n";
        $prefTxt .= "warn_before_deleting:".(array_key_exists("warn_before_deleting",$pref) ? $pref['warn_before_deleting'] : "yes")."\n";
        $prefTxt .= "display_sort:".(array_key_exists("display_sort",$pref) ? $pref['display_sort'] : "yes")."\n";
        $prefTxt .= "default_sort:".(array_key_exists("default_sort",$pref) ? $pref['default_sort'] : "ID")."\n";
        $prefTxt .= "default_sort_order:".(array_key_exists("default_sort_order",$pref) ? $pref['default_sort_order'] : "ascending")."\n";
        $prefTxt .= "max_ref_by_page:".(array_key_exists("max_ref_by_page",$pref) ? $pref['max_ref_by_page'] : "10")."\n";
        $prefTxt .= "display_shelf_actions:".(array_key_exists("display_shelf_actions",$pref) ? $pref['display_shelf_actions'] : "no")."\n";

        $fp = fopen("./data/auth_files/pref_".$user.".txt",'w');
        fwrite($fp,$prefTxt);
        fclose($fp);
    }

}

?>
