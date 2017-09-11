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
    File: auth.mysql.php
    Author: Guillaume Gardey (ggardey@club-internet.fr)
    Licence: GPL

    Description:

        The following definition of Auth uses a mysql database to store the
    authorizations.

    The database is organized as follows:

        biborb_users(id,login,password,name,firstname,admin)
            password is stored using the md5 function
            admin: Y if the user is an admin, N otherwise

        biborb_auth(user_id,db_name,access)
            user_id: a valid biborb_users id
            db_name: the bibliography's name or '*' to set authorizations for
                     all databases
            access: a 3 characters field (add|modify|delete)
                       111 == add modify and delete,
                       100 == add no modify no delete ...

    use the "_anonymous_" user to set default privileges for unauthentified users.

*/

/**
    The database configuration
*/
$host = "localhost";
$dbuser = "mmobius";
$pass = "mmobius";
$db = "biborb";
$table = "biborb_users";
$auth_table = "biborb_auth";
$pref_table = "user_preferences";


/**
    Class Auth: a genreic class to check authorizations.
    This implementation of Auth uses a MySQL database.
*/
class Auth
{
    var $host;          // database host
    var $dbuser;        // a valid user for the database
    var $pass;          // its password
    var $dbname;        // name of the database containing BibORB tables
    var $users_table;   // name of the table containing users data.
    var $users_auth;    // name of the table containing authorizations data.
    var $user_preferences_table;

    /**
        Constructor
     */
    function Auth(){
        $this->host = $GLOBALS['host'];
        $this->dbuser = $GLOBALS['dbuser'];
        $this->pass = $GLOBALS['pass'];
        $this->dbname = $GLOBALS['db'];
        $this->users_table = $GLOBALS['table'];
        $this->users_auth = $GLOBALS['auth_table'];
        $this->user_preferences_table = $GLOBALS['pref_table'];
    }

    /**
        Is the login/password valid?
        Returns TRUE/FALSE
     */
    function is_valid_user($user,$pass){
        // connection to the users database
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.",ERROR);
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.",ERROR);
        }
        else{
            // Get ($user,$pass) record
            $query = sprintf("SELECT login, password from %s WHERE login='%s' AND password='%s'",
                             $this->users_table,
                             mysql_real_escape_string($user),
                             md5(mysql_real_escape_string($pass)));
            $result = mysql_query($query,$connect) or trigger_error("Invalid SQL Request",ERROR);
            return (mysql_num_rows($result)>0);
        }
    }

    /**
        Is the user an administrator?
        Returns TRUE/FALSE
     */
    function is_admin_user($user){
        //connection to the users database
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.",ERROR);
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
             trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.",ERROR);
        }
        else{
            // get $admin value for $user
            $query = sprintf("SELECT admin FROM %s WHERE login='%s'",
                             $this->users_table,
                             mysql_real_escape_string($user));
            $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);

            if(mysql_num_rows($result) != 0){
                $row = mysql_fetch_assoc($result);
                return ($row['admin'] == 'Y');
            }
            else{
                return FALSE;
            }
        }
    }

    /**
        Can the user delete entries?
        Returns TRUE/FALSE
     */
    function can_delete_entry($user, $database_name){
        //connection to the users database
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.");
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.");
        }
        else{
            $user = ($user == "" ? "_anonymous_" : $user);
            // get records where $id = $user
            $query = sprintf("SELECT id FROM %s WHERE login='%s'",
                             $this->users_table,
                             mysql_real_escape_string($user));
            $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);
            if(mysql_num_rows($result) != 0){
                $row = mysql_fetch_assoc($result);
                $id = $row['id'];

                // look for *
                $query = sprintf("SELECT access FROM %s WHERE user_id='%s' AND db_name='*'",$this->users_auth,$id);
                $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);
                if(mysql_num_rows($result) != 0){
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[2] == '1';
                }
                else{
                    $query = sprintf("SELECT access FROM %s WHERE user_id='%s' AND db_name='%s'",
                                     $this->users_auth,
                                     $id,
                                     mysql_real_escape_string($database_name));
                    $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[2] == '1';
                }
            }
            else{
                return FALSE;
            }
        }
    }

    /**
        Can the user add entries?
        Return TRUE/FALSE
     */
    function can_add_entry($user, $database_name){
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.",ERROR);
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.",ERROR);
        }
        else{
            $user = ($user == "" ? "_anonymous_" : $user);

            $query = sprintf("SELECT id FROM %s WHERE login='%s'",
                             $this->users_table,
                             mysql_real_escape_string($user));
            $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);
            if(mysql_num_rows($result) != 0){
                $row = mysql_fetch_assoc($result);
                $id = $row['id'];

                $query = sprintf("SELECT access FROM %s WHERE user_id='%s' AND db_name='*'",$this->users_auth,$id);
                $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);

                if(mysql_num_rows($result) != 0){
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[0] == '1';
                }
                else{
                    $query = sprintf("SELECT access FROM %s WHERE user_id='%s' AND db_name='%s'",
                                     $this->users_auth,
                                     $id,
                                     mysql_real_escape_string($database_name));
                    $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[0] == '1';
                }
            }
            else{
                return FALSE;
            }
        }
    }

    /**
        Can the user update entries?
        Return TRUE/FALSE
     */
    function can_modify_entry($user, $database_name){
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.",ERROR);
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.",ERROR);
        }
        else{
            $user = ($user == "" ? "_anonymous_" : $user);

            $query = sprintf("SELECT id FROM %s WHERE login='%s'",
                             $this->users_table,
                             mysql_real_escape_string($user));
            $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);

            if(mysql_num_rows($result) != 0){
                $row = mysql_fetch_assoc($result);
                $id = $row['id'];

                $query = sprintf("SELECT access FROM %s WHERE user_id='%s' AND db_name='*'",$this->users_auth,$id);
                $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);

                if(mysql_num_rows($result) != 0){
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[1] == '1';
                }
                else{
                    $query = sprintf("SELECT access FROM %s WHERE user_id='%s' AND db_name='%s'",
                                     $this->users_auth,
                                     $id,
                                     mysql_real_escape_string($database_name));
                    $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[1] == '1';
                }
            }
            else{
                return FALSE;
            }
        }
    }
    /**
        Return an array containing preferences for a given user.
     */
    function get_preferences($user){
        //connection to the users database
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.",ERROR);
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.",ERROR);
        }
        else{
            $pref = array();
            // get the user_id
            $query = sprintf("SELECT id FROM %s WHERE login='%s'",
                             $this->users_table,
                             mysql_real_escape_string($user));
            $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);
            $row = mysql_fetch_assoc($result);
            $id = $row['id'];
            // get pref for this user
            $query = sprintf("SELECT * FROM %s WHERE user_id='%s'",
                             $this->user_preferences_table,
                             $id);
            $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);

            if(mysql_num_rows($result) != 0){
                // pref exist for this user
                $row = mysql_fetch_assoc($result);
                $pref['css_file'] = $row['css_file'];
                $pref['default_language'] = $row['default_language'];
                $pref['default_database'] = $row['default_database'];
                $pref['display_images'] = $row['display_images'] == 'Y' ? "yes" : "no";
                $pref['display_txt'] = $row['display_txt'] == 'Y' ? "yes" : "no";
                $pref['display_abstract'] = $row['display_abstract'] == 'Y' ? "yes" : "no";
                $pref['warn_before_deleting'] = $row['warn_before_deleting'] == 'Y' ? "yes" : "no";
                $pref['display_sort'] = $row['display_sort'] == 'Y' ? "yes" : "no";
                $pref['default_sort'] = $row['default_sort'];
                $pref['default_sort_order'] = $row['default_sort_order'];
                $pref['max_ref_by_page'] =$row['max_ref_by_page'];
                $pref['display_shelf_actions'] = $row['display_shelf_actions'] == 'Y' ? "yes" : "no";
            }
            else{
                //default preferences
                $pref['css_file'] = "style.css";
                $pref['default_language'] = "en_US";
                $pref['default_database'] = "none";
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
    }

    /**
        Set the preferences for a given user.
     */
    function set_preferences($pref,$user){
        //connection to the users database
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.",ERROR);
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            trigger_error("Unable to connect to the database!<br/>Check your MySQL configuration.");
        }
        else{
            // get the user_id
            $query = sprintf("SELECT id FROM %s WHERE login='%s'",
                             $this->users_table,
                             mysql_real_escape_string($user));
            $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.",ERROR);
            $row = mysql_fetch_assoc($result);
            $id = $row['id'];
            // get pref for this user
            $query = "SELECT * FROM ".$this->user_preferences_table." WHERE user_id='$id'";
            $result = mysql_query($query,$connect) or trigger_error("Invalid SQL request.");
            // create the record if doesn't already exist
            if(mysql_num_rows($result) == 0){
                $query = "INSERT INTO `".$this->user_preferences_table."` (user_id) VALUES ('$id');";
                mysql_query($query,$connect) or trigger_error("Invalid SQL request.");
            }
            $query = "UPDATE ".$this->user_preferences_table." SET ";
            $query .= "css_file='".(array_key_exists("css_file",$pref) ? addslashes($pref['css_file']) : "style.css")."',";
            $query .= "default_language='".(array_key_exists("default_language",$pref) ? addslashes($pref['default_language']) : "en_US")."',";
            $query .= "default_database='".(array_key_exists("default_database",$pref) ? addslashes($pref['default_database']) : "")."',";
            $query .= "display_images='".(array_key_exists("display_images",$pref) ? ($pref['display_images'] == "yes" ? "Y" : "N") : "Y")."',";
            $query .= "display_txt='".(array_key_exists("display_txt",$pref) ? ($pref['display_txt'] == "yes" ? "Y" : "N") : "N")."',";
            $query .= "display_abstract='".(array_key_exists("display_abstract",$pref) ? ($pref['display_abstract'] == "yes" ? "Y" : "N") : "N")."',";
            $query .= "warn_before_deleting='".(array_key_exists("warn_before_deleting",$pref) ? ($pref['warn_before_deleting'] == "yes" ? "Y" : "N") : "Y")."',";
            $query .= "display_sort='".(array_key_exists("display_sort",$pref) ? ($pref['display_sort'] == "yes" ? "Y" : "N") : "N")."',";
            $query .= "default_sort='".(array_key_exists("default_sort",$pref) ? addslashes($pref['default_sort']) : "ID")."',";
            $query .= "default_sort_order='".(array_key_exists("default_sort_order",$pref) ? addslashes($pref['default_sort_order']) : "ascending")."',";
            $query .= "max_ref_by_page='".(array_key_exists("max_ref_by_page",$pref) ? addslashes($pref['max_ref_by_page']) : "10")."',";
            $query .= "display_shelf_actions='".(array_key_exists("display_shelf_actions",$pref) ? ($pref['display_shelf_actions'] == "yes" ? "Y" : "N") : "N")."' ";

            $query .= "WHERE user_id='$id' LIMIT 1";
            mysql_query($query,$connect) or trigger_error("Invalid SQL request.");
        }
    }
}

?>
