<?php
/**
 *
 * This file is part of BibORB
 * 
 * Copyright (C) 2003-2005  Guillaume Gardey
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
 * File: functions.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
 * Licence: GPL
 * 
 * Description:
 *      
 *      Some generic functions
 */

/**
 * load variables and functions
 */
require_once("config.php");
require_once("php/utilities.php");
require_once("php/bibtex.php");


/**
 * Upload a file.	
 * If successful, return the name of the file, otherwise null.
 * Overwrite if the file is already present.
 *
 * @param $bibname The name of the bibliography
 * @param $type The ID of the file uploaded.
 * @param $id The BibTeX id of the paper.
 *
 * @return The name of the file uploaded on success. Null otherwise.
 * 
 * @author G. Gardey
 */
function upload_file($bibname,$type,$id)
{
    $res = null;
    $infofile = pathinfo($_FILES[$type]['name']);
    $extension = $infofile['extension'];
    $file = get_new_name($infofile['basename'],$id);
    $path = "./bibs/".$bibname."/papers/".$file;
    // If file already exists, delete it
    if(file_exists($path)){
        unlink($path);
    }
    // upload the file
    $is_uploaded = move_uploaded_file($_FILES[$type]["tmp_name"],$path);
    // change it to be readable/writable to the owner and readable for others
    if($is_uploaded){
        chmod($path, 0777 - UMASK );
        $res = $file;
    }
    return $res;
}

/**
 * Create the main panel in the BibORB HTML interface.
 *
 * @author G. Gardey
 */
function main($title,$content,$error = null,$message = null)
{
  $html = "<div id='main'>";
  if($title != null){$html .= "<h2 id='main_title'>$title</h2>";}
  if($error){$html .= "<div id='error'>$error</div>";}
  if($message){$html .= "<div id='message'>$message</div>";}
  if($content != null) {$html .= "<div id='content'>$content</div>";}
  $html .= "</div>";
  return $html;  
}


/**
 * Recursively delete a directory.
 * @param $dir The name of the directory.
 * @author G. Gardey
 */
function deldir($dir) {
    $current_dir = opendir($dir);
    while($entryname = readdir($current_dir)){
        if(is_dir("$dir/$entryname") and ($entryname != "." and $entryname!="..")){
            deldir("${dir}/${entryname}");
        }
        elseif($entryname != "." and $entryname!=".."){
            unlink("${dir}/${entryname}");
        }
    }
    closedir($current_dir);
    rmdir($dir);
}

/**
 * Remove accents of a string.
 */
function remove_accents($string){
    return strtr($string,
                "•µ¿¡¬√ƒ≈∆«»… ÀÃÕŒœ–—“”‘’÷ÿŸ⁄€‹›ﬂ‡·‚„‰ÂÊÁËÈÍÎÏÌÓÔÒÚÛÙıˆ¯˘˙˚¸˝ˇ",
                "YuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy");
}

/**
 * Generate the add all to basket div section for the BibORB html interface.
 * @param $ids The ids to add in the basket.
 * @param $mode The current display mode of BibORB.
 * @param $extraparam Some extraparam.
 * @author G. Gardey
 */

function add_all_to_basket_div($ids,$mode,$extraparam=null){
    $title = msg("Add all entries to the basket.");
    $html = "<div class='addtobasket' title='$title'>";
    $addalllink = "bibindex.php?mode=$mode&amp;action=add_to_basket&amp;id=";
    foreach($ids as $id){
        $addalllink .= "$id*";
    }
    if($extraparam){$addalllink .= "&amp;$extraparam";}
    $html .= "<a href='".$addalllink."'>";
    $html .= "<img src='./data/images/add.png' alt='add' />";
    $html .= "</a>";
    $html .= "</div>";
    return $html;
}

/**
 * Generate a XHTML div containing sort functions.
 * @param $selected_sort The sort selected.
 * @param $selected_order The sort order selected.
 * @param $mode The Biborb current display mode.
 * @param $misc An array containing additional values for the form.
 */
function sort_div($selected_sort,$selected_order,$mode,$misc){
    // ensure the localization is set up
    load_i18n_config($_SESSION['language']);

    $html = "<div class='sort'>";
    $html .= msg("Sort by:");
    $html .= "&nbsp;<form method='get' action='bibindex.php'>";
    $html .= "<fieldset>";
    $html .= "<select name='sort' size='1'>";
    
    foreach($_SESSION['bibdb']->sort_values as $sort_val){
        if($selected_sort == $sort_val){
            $html .= "<option value='$sort_val' selected='selected'>".msg("$sort_val")."</option>";
        }
        else {
            $html .= "<option value='$sort_val'>".msg("$sort_val")."</option>";
        }
    }

    $html .= "</select>&nbsp;";
    $html .= "<input type='hidden' name='mode' value='$mode'/>";
    if($misc){
        foreach($misc as $key=>$val){
            $html .= "<input type='hidden' name='$key' value='$val'/>";
        }
    }
    $html .= "<select name='sort_order'>";
    if($selected_order=='ascending'){
        $html .= "<option value='ascending' selected='selected'>".msg("ascending")."</option>";
    }
    else{
        $html .= "<option value='ascending'>".msg("ascending")."</option>";
    }
    if($selected_order=='descending'){
        $html .= "<option value='descending' selected='selected'>".msg("descending")."</option>";
    }
    else{
        $html .= "<option value='descending'>".msg("descending")."</option>";
    }
    $html .= "</select>&nbsp;";
    $html .= "<input type='submit' value='".msg("Sort")."'/>";
    $html .= "</fieldset>";
    $html .= "</form>";
    $html .= "</div>";
    
    return $html;
}

/**
 * Analyze a .dot aux file and return an array of bibtex ids
 * @param $auxfile A .aux LaTeX file.
 * @return An array of BibTeX keys.
 */
function bibtex_keys_from_aux($auxfile){
    $lines = load_file($auxfile);
    preg_match_all("/citation{(.*)}/i",$lines,$res);
    return $res[1];
}

/*
    Create the nav bar
*/
function create_nav_bar($current_page,$max_page,$mode,$extraparam=null){
    $html = "";
    if($max_page>1){
        $html .= "<div id='nav_bar'>";
        if($extraparam != null){
            $extraparam = "&amp;".$extraparam;
        }
        // left arrows to display if this isn't the first page
        if($current_page != 0){
            $html .= "<a href='bibindex.php?mode=$mode$extraparam&amp;page=0'><img src='data/images/stock_first-16.png' alt='First' title='First'/></a>";
            $html .= "<a href='bibindex.php?mode=$mode$extraparam&amp;page=".($current_page-1)."'><img src='data/images/stock_left-16.png' alt='Previous' title='Previous'/></a>";
        }
        
        // computes which index to display
        $nb = 10;
        if($current_page-$nb<0){
            $start_index = 0;
        }
        else if($current_page==$max_page-1){
            $start_index = max($max_page-2*$nb ,0);
        }
        else{
            $start_index = max($current_page - $nb-1,0);
        }
        
        // if $start_index is not 0 display dots
        if($start_index != 0){
            $html .= "&nbsp;...&nbsp;";
        }
        
        // output the numbered navigation bar
        for($i=$start_index;$i<$max_page && $i<$start_index+2*$nb ;$i++){
            if($current_page==$i){
                $html .= "<a id='current_page' href='bibindex.php?mode=$mode$extraparam&amp;page=$i'>".($i+1)."</a>";
            }
            else{
                $html .= "<a class='num_page' href='bibindex.php?mode=$mode$extraparam&amp;page=$i'>".($i+1)."</a>";
            }
        }
        
        // if the last page number is not displayed, output dots.
        if($i != $max_page){
            $html .= "&nbsp;...&nbsp;";
        }
        
        // right arrows to display if this isn't the last page
        if($current_page != $max_page-1){
            $html .= "<a href='bibindex.php?mode=$mode$extraparam&amp;page=".($current_page+1)."'><img src='data/images/stock_right-16.png' alt='Next' title='Next'/></a>";
            $html .= "<a href='bibindex.php?mode=$mode$extraparam&amp;page=".($max_page-1)."'><img src='data/images/stock_last-16.png' alt='Last' title='Last'/></a>";
        }
        $html .= "</div>";
    }
    return $html;
}

?>
