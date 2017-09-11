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
        This file defines the Auth class to provide a basic user management
    system. Auth is designed to be flexible so that you can customize it to 
    your needs.

        4 levels of authorizations are available: add entries, edit/update
    entries, delete entries, admin. Only the 'admin' level is authorized to 
    create or delete bibliographies, it also allows the user to add, edit and
    delete entries. The add/edit/delete authorizations are defined for each
    bibliographies.
        
        Authorizations are accessed by Biborb using the following functions:
            - is_valid_user($user,$pass)
                Check the login.
            - can_add_entry($user,$biblio)
                Check if $user can add entry to the bibliography named
                $biblio.
            - can_delete_entry($user,$biblio)
                Check if $user can delete entries from the bibliography named
                $biblio.
            - can_modify_entry($user,$biblio)
                Check if $user can edit and update entries from the bibliography
                named $biblio
            - is_admin_user($user)
                Check if $user is an admin user.
            
        It is then easy to redefine these methods to match your needs (other 
    databases, ldap, postgres, xml databases....)
*/


/**
    Class Auth: a genreic class to check authorizations.
*/
class Auth
{
    /**
        Constructor
     */
    function Auth(){
    }
    
    /**
        Is the login/password valid?
        Returns TRUE/FALSE
     */
    function is_valid_user($user,$pass){
    }
    
    /**
        Is the user an administrator?
        Returns TRUE/FALSE
     */
    function is_admin_user($user){
    }
    
    /**
        Can the user delete entries?
        Returns TRUE/FALSE
     */
    function can_delete_entry($user, $database_name){
    }
    
    /**
        Can the user add entries?
        Return TRUE/FALSE
     */
    function can_add_entry($user, $database_name){
    }
    
    /**
        Can the user update entries?
        Return TRUE/FALSE
     */
    function can_modify_entry($user, $database_name){
    }
    
    /**
        Return an array containing preferences for a given user.
     */
    function get_preferences($user){
    }
    
    /**
        Set the preferences for a given user.
     */
    function set_preferences($pref,$user){
    }
}

?>
