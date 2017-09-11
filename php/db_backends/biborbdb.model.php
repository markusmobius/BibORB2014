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
 * File: biborbdb.model.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 * 
 * Description:
 *
 *      BibOrb database interface.
 */


// Bibtex Database manager
class BibORB_DataBase {
	
    // Should a BibTeX file be generated.
    var $generate_bibtex;
    
    // name of the bibliography
    var $biblio_name;
    // the biblio directory
    var $biblio_dir;
    
    // Sort method used to sort entries
    var $sort;
    // Sort order method (ascending/descending)
    var $sort_order;
    
    
    /**
        Constructor.
        $bibname -> name of the bibliography
        $genBibtex -> keep an up-to-date BibTeX file.
     */
    function BibORB_DataBase($bibname,$genBibtex = true){
        $this->biblio_name = $bibname;
        $this->biblio_dir = "./bibs/$bibname/";
        $this->generate_bibtex = $genBibtex;
    }
    
    /**
        Set the sort method.
     */
    function set_sort($sort){
        $this->sort = $sort;
    }
    
    /**
        Set the sort order. (ascending/descending)
     */
    function set_sort_order($sort_order){
        $this->sort_order = $sort_order;
    }
    
    /**
        Generate the path of the bib file.
    */
    function bibtex_file(){
        return $this->biblio_dir.$this->biblio_name.".bib";
    }
    
    /**
        Return the name of the bibliography.
    */
    function name(){
        return $this->biblio_name;
    }
	
    /**
        Return the directory containing uploaded papers/data.
    */
    function papers_dir(){
        return $this->biblio_dir."papers/";
    }
    
    /**
        Regenerate the bibtex file so that it exactly contains the data present
     in the database.
    */
    function update_bibtex_file(){

    }
    
    /**
        Reload the database according the bibtex file.
     */
    function reload_from_bibtex(){
        $bt = new BibTeX_Tools();
        $data = $bt->bibtex_file_to_xml($this->bibtex_file());
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$data[2]);
        fclose($fp);
    }
    
    /**
        Return an array of all BibTeX ids.
        Ids are sorted according the default $sort method and $sort_order.
     */
    function all_bibtex_ids(){
    }
    
    /**
        Return an array of BibTex ids of entries belonging to the group $groupname.
        If $groupname is null, it returns an array of entries that aren't
        associated with a group.
        Ids are sorted according the default $sort method and $sort_order.
    */
    function ids_for_group($groupname){
    }
    
    
    /**
        Return an XML representation of all entries of the database.
    */
    function all_entries(){
    }
    
    /**
        Get the XML representation of a given set of references.
        $anArray contains the list of BibTeX ids. No sort is applied and 
        references should be order in the same way as $anArray.
    */
    function entries_with_ids($anArray){
    }
    
    /**
        Get the XML representation of a given reference.
    */
    function entry_with_id($anID){
    }
    
    /**
        Add a new reference to the database
        $dataArray contains bibtex values.
        It is in charge of moving uploaded files to the right place and ensure
     that the id selected is not already defined.
        It returns an array that resumes the operation.
    */
    function add_new_entry($dataArray){
        $res['added'] = true; // if the add was successful or not
        $res['message'] = ""; // to store a message (error/success/warning)
        $res['id'] = $dataArray['id']; // the id of the reference added
        return $res;
    }
	
    /**
        Add entries by importing a BibTeX string.
    */
    function add_bibtex_entries($bibtex){
        $bt = new BibTeX_Tools();
        $data = $bt->bibtex_string_to_xml($bibtex);
        return $data[1];
    }
    
    /**
        Delete an entry from the database.
    */
    function delete_entry($bibtex_id){
    }
    
    /**
        Delete entries from the database.
    */
    function delete_entries($tabids){
        foreach($tabids as $id){
            $this->delete_entry($id);
        }
    }
    
    /**
        Update an entry.
        $dataArray contains the new values for each BibTeX field.
    */
    function update_entry($dataArray){
        $res['updated'] = true;
        $res['message'] = "";
        $res['id'] = $dataArray['id'];
        return $res;
    }
    
    /**
        Test if a bibtex key is already present in the database
        Returns TRUE/FALSE
    */
    function is_bibtex_key_present($bibtex_key){
    }
    
    /**
        Return an array containing groups present in the bibliography.
    */
    function groups(){
        $res = array();
        return $res; 
    }
	
    /**
        Add a set of entries to a group
    */
    function add_to_group($idArray,$group){
    }
    
    /**
        Reset groups of a set of entries
    */
    function reset_groups($idArray){
    }
    
    /**
        Search in given fields, a given value.
     $fields is an array containing the name of fields to look at.
     Returns an array of BibTeX ids.
    */
    function ids_for_search($value,$fields){
    }
    
    /**
        Advanced search function
     Returns an array of BibTeX ids.
     */
    function ids_for_advanced_search($searchArray){
    }

    /**
        XPath search
     Returns an array of BibTeX ids.
    */
    function ids_for_xpath_search($xpath_query){
    }
    
    /**
        Total number of references in the database
    */
    function count_entries(){ 
    }
    
    /**
        Count on-line available papers.
    */
    function count_epapers(){
    }
    
    /**
        Return a list of available types of papers (article, book, ....)
    */
    function entry_types(){
    }

    /**
     Change the type of a given entry
    */
    function change_type($id,$newtype){	
    }

    /**
     Change the bibtex key
    */
    function change_id($id,$newid){
    }
    
    /**
        Change the ownership status of a given entry
        Shelf mode
     */
    function change_ownership($id,$newownership){	
    }
    
    /**
        Change the read status of a given entry
        Shelf mode
     */
    function change_readstatus($id,$newreadstatus){	
    }
    
}


/**
 Create a new bibliography.
*/
function create_database($name,$description){
    // array to store messages or errors
    $resArray = array('message' => null,
                      'error' => null);
    return $resArray;
}

/**
    Delete a bibliography
 */
function delete_database($name){
    // message to display
    $res = "";
    return $res;
}

/**
    Get the name of recorded bibliographies.
 */
function get_databases_names(){
    $databases_names = array();
    return $databases_names;
}

?>
