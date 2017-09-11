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
 * File: auth.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 *    Redirect to the php source matching the authentication method
 *    defined in the configuration file of BibORB.
 */

require_once("config.php");
if(!DISABLE_AUTHENTICATION){
    switch(AUTH_METHOD){
        // Use file authentication system
        case 'files':
            require_once("php/auth_backends/auth.file.php");
            break;

        // Use mysql authentication system
        case 'mysql':
            require_once("php/auth_backends/auth.mysql.php");
            break;
    }
}
?>
