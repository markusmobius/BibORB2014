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
 * File: biborbdb.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *      BibOrb database class.
 *      Provides a class to access the database recorded in XML
 *      When a request is done, the result is given as an xml string (bibtex).
 *      The interface uses this xml to generate an HTML output.
 *
 *
 */

require_once("php/xslt_processor.php"); //xslt processor
require_once("php/bibtex.php"); //parse bibtex string

// list of sort attributes
$sort_values = array('author','title','ID','year','dateAdded','lastDateModified');

//Bibtex Database manager
class BibORB_DataBase {
	
    // Should a BibTeX file be generated.
    var $generate_bibtex;

    // name of the bibliography
    var $biblio_name;

    // the biblio directory
    var $biblio_dir;

    // list of BibTeX fields relevant for BibORB
    var $biborb_fields;

    // the changelog file;
    var $changelog;

    // Sort method used to sort entries
    var $sort;
    var $sort_values = array('author','title','ID','year','dateAdded','lastDateModified');

    // Sort order method (ascending/descending)
    var $sort_order;
    var $sort_order_values = array('ascending','descending');

    // Read status
    var $read_status;
    var $read_status_values = array('any','notread','readnext','read');

    // Ownership
    var $ownership;
    var $ownership_values = array('any','notown','borrowed','buy','own');

    /**
        Constructor.
        $bibname -> name of the bibliography
        $genBibtex -> keep an up-to-date BibTeX file. Save in the $bibname
                      directory with name $bibname.tex.
     */
    function BibORB_DataBase($bibname,$genBibtex = true){
        $this->biblio_name = $bibname;
        $this->biblio_dir = "./bibs/$bibname/";
        $this->changelog = "./bibs/$bibname/changelog.txt";
        $this->generate_bibtex = $genBibtex;
        $this->read_status = 'any';
        $this->ownership = 'any';

        // check the version of biborb files
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content = $this->all_entries();
        $xsl_content = <<< XSLT_END
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:bibtex="http://bibtexml.sf.net/" version="1.0">
    <xsl:output method="text" encoding="iso-8859-1"/>
    <xsl:template match="/bibtex:file">
            <xsl:value-of select="@version"/>
    </xsl:template>
</xsl:stylesheet>
XSLT_END;

        $val = trim($xsltp->transform($xml_content,$xsl_content));
        $xsltp->free();
        if($val != "1.1"){
            // * add the first author lastname in a separate field
            // to sort by author
            // * set date added and last modified to yesterday
            $xml = $this->all_entries();
            // load all data in an array form
            $bt = new BibTeX_Tools();
            $pc = new PARSECREATORS();
            $entries = $bt->xml_to_bibtex_array($xml);

            for($i=0;$i<count($entries);$i++){
                // get the name of the first author
                if(array_key_exists('author',$entries[$i])){
                    $creatorArray = $pc->parse($entries[$i]['author']);
                    $entries[$i]['lastName'] = $creatorArray[0][2];
                }
                // set dateAdded and lastDateModified attributes
                if(!array_key_exists('dateAdded',$entries[$i]))
                    $entries[$i]['dateAdded'] = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
                if(array_key_exists('lastDateModified',$entries[$i]))
                    $entries[$i]['lastDateModified'] = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));

                // change own= {yes} to own = {own}
                if(array_key_exists('own',$entries[$i]))
                    $entries[$i]['own'] = ( $entries[$i]['own'] == "yes" ? "own" : $entries[$i]['own']);
            }
            // convert to XML
            $data = $bt->entries_array_to_xml($entries);
            // save the new xml file
            rename($this->xml_file(),$this->xml_file().".save");
            $fp = fopen($this->xml_file(),"w");
            fwrite($fp,$data[2]);
            fclose($fp);
        }
    }

    function set_BibORB_fields($tab)
    {
        $this->biborb_fields = $tab;
    }

    function fullname()
    {
        if(file_exists($this->biblio_dir."/fullname.txt")){
            return load_file($this->biblio_dir."/fullname.txt");
        }
        else{
            return $this->biblio_name;
        }
    }


    /**
        Set the sort method.
     */
    function set_sort($sort){
        if(array_search($sort,$this->sort_values) === FALSE){
            $sort = 'ID';
        }
        $this->sort = $sort;
    }

    /**
        Set the sort order. (ascending/descending)
     */
    function set_sort_order($sort_order){
        if(array_search($sort_order,$this->sort_order_values) === FALSE){
            $sort_order = 'ascending';
        }
        $this->sort_order = $sort_order;
    }

    /**
        Set the read status.
        When querying the database, only references of the given $read_status
        will be output.
     */
    function set_read_status($status){
        if(array_search($status,$this->read_status_values) === FALSE){
            $status = 'notread';
        }
        $this->read_status = $status;
    }

    /**
        Set the ownership
        When querying the database, only references of the given $ownership
        will be output.
     */
    function set_ownership($val){
        if(array_search($val,$this->ownership_values) === FALSE){
            $status = 'notown';
        }
        $this->ownership = $val;
    }

    /**
        Generate the path of the xml file.
    */
    function xml_file(){
        return $this->biblio_dir.$this->biblio_name.".xml";
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
        Update the .bib file wrt the .xml file.
        Only used in this class.
    */
    function update_bibtex_file(){
        // Load all the database and transform it into a bibtex string
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content = $this->all_entries();
        $xsl_content = load_file("./xsl/xml2bibtex.xsl");
        $bibtex = $xsltp->transform($xml_content,$xsl_content);
        $xsltp->free();

        // write the bibtex file
        $fp = fopen($this->bibtex_file(),"w");
        fwrite($fp,$bibtex);
        fclose($fp);
    }

    /**
        Reload the database according the bibtex file.
     */
    function reload_from_bibtex(){
        // load the bibtex file and transform it to XML
        $bt = new BibTeX_Tools();
        $data = $bt->bibtex_file_to_xml($this->bibtex_file());
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$data[2]);
        fclose($fp);
    }

    /**
        Return an array of all BibTeX ids.
        The entries are sorted using $this->sort and $this->sort_order
     */
    function all_bibtex_ids(){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content = $this->all_entries();
        $xsl_content = load_file("./xsl/extract_ids.xsl");
        $xsl_content = str_replace("XPATH_QUERY","//bibtex:entry",$xsl_content);
        $param = array('sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res = $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }

    /**
        Return a sorted array of BibTex ids of entries belonging to the group $groupname.
        If $groupname is null, it returns a sorted array of entries that aren't
        associated with a group.
    */
    function ids_for_group($groupname){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content = $this->all_entries();
        $xsl_content = load_file("./xsl/extract_ids.xsl");
        // generate the apropriate XSLT request
        if($groupname){
            // return entries associated to a given group
            $xpath = '//bibtex:entry[(.//bibtex:group)=$group';
            if($this->read_status != 'any'){
                $xpath .= ' and (.//bibtex:read)=\''.$this->read_status.'\'';
            }
            if($this->ownership != 'any'){
                $xpath .= ' and (.//bibtex:own)=\''.$this->ownership.'\'';
            }
            $xpath .= ']';
        }
        else{
            // return 'orphan' entries
            $xpath = '//bibtex:entry[not(.//bibtex:group)';
            if($this->read_status != 'any'){
                $xpath .= ' and (.//bibtex:read)=\''.$this->read_status.'\'';
            }
            if($this->ownership != 'any'){
                $xpath .= ' and (.//bibtex:own)=\''.$this->ownership.'\'';
            }
            $xpath .= ']';
        }
        $xsl_content = str_replace("XPATH_QUERY",$xpath,$xsl_content);
        // do the transformation
        $param = array('group'=>$groupname,
                       'sort' => $this->sort,
                       'sort_order' => $this->sort_order,
                       'biborb_xml_version' => BIBORB_XML_VERSION);
        $res =  $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }


    /**
        Get all entries in the database
    */
    function all_entries(){
        return load_file($this->xml_file());
    }

    /**
        Get a set of entries.
    */
    function entries_with_ids($anArray){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xsl_content = load_file("./xsl/entries_with_ids.xsl");
        //transform the array into an xml string
        $xml_content = "<?xml version='1.0' encoding='iso-8859-1'?>";
        $xml_content = "<listofids>";
        foreach($anArray as $item){
            $xml_content .= "<id>$item</id>";
        }
        $xml_content .= "</listofids>";
        $param = array('bibnameurl' => $this->xml_file());
        $res = $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return $res;
    }

    /**
        Get an entry
    */
    function entry_with_id($anID){
        return $this->entries_with_ids(array($anID));
    }

    /**
        Add a new entry to the database
        $dataArray contains bibtex values
    */
    function add_new_entry($dataArray){
        $res = array(   'added'=>false,
                        'message'=>"");
	
        $bibid = trim($dataArray['id']);
        // check if the entry is already present
        $inbib = $this->is_bibtex_key_present($bibid);

        // error, ID already exists or empty value
        if( $inbib|| strlen($bibid) == 0 || $bibid == null){
            if($inbib){
                $res['message'] = msg("BibTeX ID already present, select a different one.");
                $res['message'] .= "<div style='text-align:center'><a href='javascript:history.back()'>".msg("Back")."</a></div>";
            }
            else{
                $res['message'] = msg("Null BibTeX ID for an entry not allowed.");
                $res['message'] .= "<div style='text-align:center'><a href='javascript:history.back()'>".msg("Back")."</a></div>";
            }
        }
        else{
            // upload files if they are present
            if(array_key_exists('up_url',$_FILES) && file_exists($_FILES['up_url']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_url']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['url'] = upload_file($this->biblio_name,'up_url',$dataArray['id']);
                }
                else{
                    $res['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_url']['name']);
                    $res['message'] .= "<br/>";
                }
            }

            if(array_key_exists('up_urlzip',$_FILES) && file_exists($_FILES['up_urlzip']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_urlzip']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['urlzip'] = upload_file($this->biblio_name,'up_urlzip',$dataArray['id']);
                }
                else{
                    $res['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_urlzip']['name']);
                    $res['message'] .= "<br/>";
                }
            }

            if(array_key_exists('up_pdf',$_FILES) && file_exists($_FILES['up_pdf']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_pdf']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['pdf'] = upload_file($this->biblio_name,'up_pdf',$dataArray['id']);
                }
                else{
                    $res['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_pdf']['name']);
                    $res['message'] .= "<br/>";
                }
            }
	
            // add the new entry
            $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
            $bt = new BibTeX_Tools();

            // extract real bibtex values from the array
            $bibtex_val = $bt->extract_bibtex_data($dataArray,$this->biborb_fields);
 
            // get first lastname author
            if(array_key_exists('author',$bibtex_val)){
                $pc = new PARSECREATORS();
                $authors = $pc->parse($bibtex_val['author']);
                $bibtex_val['lastName'] = $authors[0][2];
            }
            // set its type
            $bibtex_val['___type'] = $dataArray['add_type'];
            // date added
            $bibtex_val['dateAdded'] = date("Y-m-d");
            // date modified
            $bibtex_val['lastDateModified'] = date("Y-m-d");
            // convert to xml
            $data = $bt->entries_array_to_xml(array($bibtex_val));
            $xml = $data[2];
            $xsl = load_file("./xsl/add_entries.xsl");
            $param = array('bibname' => $this->xml_file(),
                           'biborb_xml_version' => BIBORB_XML_VERSION);
            $result = $xsltp->transform($xml,$xsl,$param);
            $xsltp->free();
	
            // save xml
            $fp = fopen($this->xml_file(),"w");
            fwrite($fp,$result);
            fclose($fp);

            // update bibtex file
            if($this->generate_bibtex){$this->update_bibtex_file();}
	
            $res['added'] = true;
            $res['message'] .= "";
            $res['id'] = $dataArray['id'];
        }
        return $res;
    }
	
    /**
        Add entries. $bibtex is a bibtex string.
        Only entries which bibtex key is not present in the database are added.
        It returns an array:
            array( 'added' => array of successfuly added references
                   'notadded' => array of references notadded due to bibtex key conflicts
                )
    */
    function add_bibtex_entries($bibtex){
        // the array to return
        $res = array('added' => array(),
                     'notadded' => array());
        //open the database file in append mode
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $bt = new BibTeX_Tools();
        $xsl = load_file("./xsl/add_entries.xsl");
        $param = array('bibname' => $this->xml_file(),
                       'biborb_xml_version' => BIBORB_XML_VERSION);

        // entries to add
        $entries_to_add = $bt->get_array_from_string($bibtex);

        // bibtex key present in database
        $dbids = $this->all_bibtex_ids();
        $xml_to_add = "";

        // iterate and add ref which id is not present in the database
        foreach($entries_to_add as $entry){
            if(array_search($entry['id'],$dbids) === FALSE){
                if(array_key_exists('author',$entry)){
                    $pc = new PARSECREATORS();
                    $authors = $pc->parse($entry['author']);
                    $entry['lastName'] = $authors[0][2];
                }
                $entry['dateAdded'] = date("Y-m-d");
                $entry['lastDateModified'] = date("Y-m-d");
                $xml_to_add .= $bt->entry_array_to_xml($entry);
                $res['added'][] = $entry['id'];
            }
            else{
                $res['notadded'][] = $entry['id'];
            }
        }


        $xml_content = "<?xml version='1.0' encoding='ISO-8859-1'?>";
        $xml_content .= "<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/' version='".BIBORB_XML_VERSION."' >";
        $xml_content .= $xml_to_add;
        $xml_content .= "</bibtex:file>";
        $result = $xsltp->transform($xml_content,$xsl,$param);
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$result);
        fclose($fp);
        $xsltp->free();


        // update bibtex file
        if($this->generate_bibtex){$this->update_bibtex_file();}

        return $res;
    }

    /**
        Delete an entry from the database
    */
    function delete_entry($bibtex_id){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content = $this->all_entries();
        $xsl_content = load_file("./xsl/delete_entries.xsl");
        $param = array('id'=>$bibtex_id,
                       'biborb_xml_version' => BIBORB_XML_VERSION);
        $newxml = $xsltp->transform($xml_content,$xsl_content,$param);

        // detect all file corresponding to this id.
        $ar = opendir($this->papers_dir());
        $tab = array();
        while($file = readdir($ar)) {
            $inf = pathinfo($file);
            if(strcmp(substr($inf['basename'],0,strlen($bibtex_id)+1),$bibtex_id.".")==0){
                array_push($tab,$file);
            }
        }

        foreach($tab as $file){
            if(file_exists($this->papers_dir().$file)){
                unlink($this->papers_dir().$file);
            }
        }

        // update the xml file.
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$newxml);
        fclose($fp);

        //update the bibtex file.
        if($this->generate_bibtex){$this->update_bibtex_file();}
    }

    /**
        Delete entries from the database
    */
    function delete_entries($tabids){
        foreach($tabids as $id){
            $this->delete_entry($id);
        }
    }

    /**
        Update an entry.
    */
    function update_entry($dataArray){
        $res = array('updated'=>false,
                     'message'=>"");

        // check if the id value is null
        if($dataArray['id'] == null){
            $res['updated'] = false;
            $res['message'] = msg("Null BibTeX ID for an entry not allowed.");
        }
        else{
            if(array_key_exists('up_url',$_FILES) && file_exists($_FILES['up_url']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_url']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['url'] = upload_file($this->biblio_name,'up_url',$dataArray['id']);
                }
                else{
                    $res['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_url']['name']);
                    $res['message'] .= "<br/>";
                }
            }

            if(array_key_exists('up_urlzip',$_FILES) && file_exists($_FILES['up_urlzip']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_urlzip']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['urlzip'] = upload_file($this->biblio_name,'up_urlzip',$dataArray['id']);
                }
                else{
                    $res['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_urlzip']['name']);
                    $res['message'] .= "<br/>";
                }
            }

            if(array_key_exists('up_pdf',$_FILES) && file_exists($_FILES['up_pdf']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_pdf']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['pdf'] = upload_file($this->biblio_name,'up_pdf',$dataArray['id']);
                }
                else{
                    $res['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_pdf']['name']);
                    $res['message'] .= "<br/>";
                }
            }

            $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
            $bt = new BibTeX_Tools();
            $bibtex_val = $bt->extract_bibtex_data($dataArray,$this->biborb_fields);
            if(array_key_exists('author',$bibtex_val)){
                $pc = new PARSECREATORS();
                $authors  = $pc->parse($bibtex_val['author']);
                $bibtex_val['lastName'] = $authors[0][2];
            }
            $bibtex_val['___type'] = $dataArray['type_ref'];
            $bibtex_val['lastDateModified'] = date("Y-m-d");
            $data = $bt->entries_array_to_xml(array($bibtex_val));
            $xml = $data[2];

            $xsl = load_file("./xsl/update_xml.xsl");
            $param = array('bibname' => $this->xml_file(),
                           'biborb_xml_version' => BIBORB_XML_VERSION);
            $result = $xsltp->transform($xml,$xsl,$param);
            $xsltp->free();

            $fp = fopen($this->xml_file(),"w");
            fwrite($fp,$result);
            fclose($fp);
            // update bibtex file
            if($this->generate_bibtex){$this->update_bibtex_file();}

            $res['updated'] = true;
            $res['message'] .= "";
            $res['id'] = $dataArray['id'];
        }
        return $res;
    }

    /**
        Test if a key is already present in the database
    */
    function is_bibtex_key_present($bibtex_key){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $content = load_file($this->xml_file());
        $xsl = load_file("./xsl/search_entry.xsl");
        $param = array('id' => $bibtex_key);
        $result = $xsltp->transform($content,$xsl,$param);
        return (substr_count($result,"true") > 0);
    }

    /**
        Return an array containing groups present in the bibliography.
    */
    function groups(){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        // Get groups from the xml bibtex file
        $xml_content = load_file($this->xml_file());
        $xsl_content = load_file("./xsl/group_list.xsl");
        $group_list = $xsltp->transform($xml_content,$xsl_content);
        $xsltp->free();

        // Remove doublons
        $group_list = preg_split('/[,~]/',$group_list);
        foreach($group_list as $key=>$value){
            if(trim($value) == ""){
                unset($group_list[$key]);
            }
            else{
                $group_list[$key] = trim($value);
            }
        }
        $list = array_unique($group_list);
        sort($list);
        //print_r($list);die();

        return $list;
    }
	
    /**
        Add a set of entries to a group
    */
    function add_to_group($idArray,$group){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        // create an xml string containing id present in the basket
        $xml_content = "<?xml version='1.0' encoding='iso-8859-1'?>";
        $xml_content = "<listofids>";
        foreach($idArray as $item){
            $xml_content .= "<id>$item</id>";
        }
        $xml_content .= "</listofids>";
        $xsl_content = load_file("./xsl/addgroup.xsl");

        $param = array( 'bibname' => $this->xml_file(),
                        'group' => $group,
                        'biborb_xml_version' => BIBORB_XML_VERSION);
        // new xml file
        $result = $xsltp->transform($xml_content,$xsl_content,$param);

        // update the xml file
        $xsl_content = load_file("./xsl/update_xml.xsl");
        $result = $xsltp->transform($result,$xsl_content,$param);
        $xsltp->free();

        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$result);
        fclose($fp);
    }

    /**
        Reset groups of a set of entries
    */
    function reset_groups($idArray){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        // create an xml string containing id present in the basket
        $xml_content = "<?xml version='1.0' encoding='iso-8859-1'?>";
        $xml_content = "<listofids>";
        foreach($idArray as $item){
            $xml_content .= "<id>$item</id>";
        }
        $xml_content .= "</listofids>";
        $xsl_content = load_file("./xsl/resetgroup.xsl");
        $param = array( 'bibname' => $this->xml_file(),
                        'biborb_xml_version' => BIBORB_XML_VERSION);
        $result = $xsltp->transform($xml_content,$xsl_content,$param);

        // update the xml file
        $xsl_content = load_file("./xsl/update_xml.xsl");
        $result = $xsltp->transform($result,$xsl_content,$param);
        $xsltp->free();

        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$result);
        fclose($fp);
    }

    /**
     Search in given fields, a given value
    */
    function search_entries($value,$fields){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content =$this->all_entries();
        $xsl_content = load_file("./xsl/search.xsl");
        $param = array( 'bibname' => $this->xml_file());
        foreach($fields as $val){
            $param[$val] = "1";
        }
        $param['search'] = $value;
        $result = $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return $result;
    }

    function ids_for_search($value,$fields){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content = $this->search_entries($value,$fields);
        $xsl_content = load_file("./xsl/extract_ids.xsl");
        $xsl_content = str_replace("XPATH_QUERY",'//bibtex:entry',$xsl_content);
        $param = array('sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res =  $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }

    /**
        Advanced search function
    */
    function advanced_search_entries($searchArray){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content =$this->all_entries();
        $xsl_content = load_file("./xsl/advanced_search.xsl");
        $param = array( 'bibname' => $this->xml_file());
        foreach($searchArray as $key => $val){
            $param[$key] = $val;
        }
	
        $result = $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return $result;
    }

    function ids_for_advanced_search($searchArray){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content = $this->advanced_search_entries($searchArray);
        $xsl_content = load_file("./xsl/extract_ids.xsl");
        $xsl_content = str_replace("XPATH_QUERY",'//bibtex:entry',$xsl_content);
        $param = array('sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res =  $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }

    /**
        XPath search
     */
    function xpath_search($xpath_query){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content = $this->all_entries();
        $xsl_content = load_file("./xsl/xpath_query.xsl");
        $xsl_content = str_replace("XPATH_QUERY",$xpath_query,$xsl_content);
        $param = array( 'bibname' => $this->xml_file());
        $result = $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return $result;
    }

    function ids_for_xpath_search($xpath_query){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content = $this->all_entries();
        $xsl_content = load_file("./xsl/extract_ids.xsl");
        $xsl_content = str_replace("XPATH_QUERY","//bibtex:entry[$xpath_query]",$xsl_content);
        $param = array('sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res =  $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }

    /**
        Total number of entries
    */
    function count_entries(){
        $allentries = $this->all_entries();
        return substr_count($allentries,"<bibtex:entry ");
    }

    /**
        Count on-line available papers.
    */
    function count_epapers(){
        $allentries = $this->all_entries();
        $pdf = substr_count($allentries,"<bibtex:pdf>");
        $urlzip = substr_count($allentries,"<bibtex:urlzip>");
        $url = substr_count($allentries,"<bibtex:url>");
	
        return $url+$urlzip+$pdf;
    }

    /**
        Return a list of available types of papers
    */
    function entry_types(){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content = load_file("./xsl/model.xml");
        $xsl_content = load_file("./xsl/get_all_bibtex_types.xsl");
        $result = $xsltp->transform($xml_content,$xsl_content);
        $xsltp->free();
	
        return explode(" ",trim($result));
    }

    /**
        Change the type of a given entry
    */
    function change_type($id,$newtype){	
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        // get the entry
        $xml_content = $this->entry_with_id($id);
        // get its type
        $oldtype = trim($xsltp->transform($xml_content,load_file("./xsl/get_bibtex_type.xsl")));
        // replace it
        $xml_content = str_replace("bibtex:$oldtype","bibtex:$newtype",$xml_content);
        // update the xml
        $xsl = load_file("./xsl/update_xml.xsl");
        $param = array('bibname' => $this->xml_file(),
                       'biborb_xml_version' => BIBORB_XML_VERSION);
        $result = $xsltp->transform($xml_content,$xsl,$param);
        $xsltp->free();
        // save it
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$result);
        fclose($fp);
        // update bibtex file
        if($this->generate_bibtex){$this->update_bibtex_file();}
    }

    /**
        Change the bibtex key
    */
    function change_id($id,$newid){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        // get the entry
        $xml_content = $this->entry_with_id($id);

        $path = $this->papers_dir();
        // change the name of recorded files if necessary
        preg_match("/\<bibtex:url\>(.*)\<\/bibtex:url\>/",$xml_content,$matches);
        if(count($matches)>0){
            $oldname = $matches[1];
            $newname = get_new_name($oldname,$newid);
            rename($path.$oldname,$path.$newname);
            $xml_content = str_replace("<bibtex:url>$oldname</bibtex:url>","<bibtex:url>$newname</bibtex:url>",$xml_content);
        }
        preg_match("/\<bibtex:urlzip\>(.*)\<\/bibtex:urlzip\>/",$xml_content,$matches);
        if(count($matches)>0){
            $oldname = $matches[1];
            $newname = get_new_name($oldname,$newid);
            rename($path.$oldname,$path.$newname);
            $xml_content = str_replace("<bibtex:urlzip>$oldname</bibtex:urlzip>","<bibtex:urlzip>$newname</bibtex:urlzip>",$xml_content);
        }
        preg_match("/\<bibtex:pdf\>(.*)\<\/bibtex:pdf\>/",$xml_content,$matches);
        if(count($matches)>0){
            $oldname = $matches[1];
            $newname = get_new_name($oldname,$newid);
            rename($path.$oldname,$path.$newname);
            $xml_content = str_replace("<bibtex:pdf>$oldname</bibtex:pdf>","<bibtex:pdf>$newname</bibtex:pdf>",$xml_content);
        }
        // update the xml
        $xsl = load_file("./xsl/update_xml.xsl");
        $param = array('bibname' => $this->xml_file(),
                       'biborb_xml_version' => BIBORB_XML_VERSION);
        $result = $xsltp->transform($xml_content,$xsl,$param);
        $xsltp->free();
         // save it
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$result);
        fclose($fp);

        // replace by the new id in the xml file
        $xml_content = str_replace("id=\"$id\"","id=\"$newid\"",load_file($this->xml_file()));
        // save it
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$xml_content);
        fclose($fp);

        // update bibtex file
        if($this->generate_bibtex){$this->update_bibtex_file();}
    }


    /**
        Change the ownership of a given entry
        Shelf mode
     */
    function change_ownership($id,$newownership){	
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
                                          // get the entry
        $xml_content = $this->entry_with_id($id);
        //$oldownership = trim($xsltp->transform($xml_content,load_file("./xsl/get_bibtex_ownership.xsl")));
        // replace it
        if (strpos($xml_content, "<bibtex:own>") === false){
            $xml_content = str_replace("</bibtex:title>","</bibtex:title><bibtex:own>$newownership</bibtex:own>",$xml_content);
        }else{
            $xml_content = preg_replace("/\<bibtex\:own>.*\<\/bibtex\:own\>/", "<bibtex:own>$newownership</bibtex:own>", $xml_content);
        }
        // update the xml
        $xsl = load_file("./xsl/update_xml.xsl");
        $param = array('bibname' => $this->xml_file(),
                       'biborb_xml_version' => BIBORB_XML_VERSION);
        $result = $xsltp->transform($xml_content,$xsl,$param);
        $xsltp->free();
        // save it
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$result);
        fclose($fp);
        // update bibtex file
        if($this->generate_bibtex){$this->update_bibtex_file();}
    }

    /**
        Change the read status of a given entry
        Shelf mode
     */
    function change_readstatus($id,$newreadstatus){	
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
                                          // get the entry
        $xml_content = $this->entry_with_id($id);
        //$oldownership = trim($xsltp->transform($xml_content,load_file("./xsl/get_bibtex_ownership.xsl")));
        // replace it
        if (strpos($xml_content, "<bibtex:read>") === false){
            $xml_content = str_replace("</bibtex:title>","</bibtex:title><bibtex:read>$newreadstatus</bibtex:read>",$xml_content);
        }else{
            $xml_content = preg_replace("/\<bibtex\:read>.*\<\/bibtex\:read\>/", "<bibtex:read>$newreadstatus</bibtex:read>", $xml_content);
        }
        // update the xml
        $xsl = load_file("./xsl/update_xml.xsl");
        $param = array('bibname' => $this->xml_file(),
                       'biborb_xml_version' => BIBORB_XML_VERSION);
        $result = $xsltp->transform($xml_content,$xsl,$param);
        $xsltp->free();
        // save it
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$result);
        fclose($fp);
        // update bibtex file
        if($this->generate_bibtex){$this->update_bibtex_file();}
    }

    /**
     * Get all different values for a specific field in the database
     */
    function get_values_for($field){
        if($field == 'author'){
            $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
            $xml_content = $this->all_entries();
            $xsl_content = load_file("./xsl/extract_field_values.xsl");
            $param = array('field' => 'author');
            $res = $xsltp->transform($xml_content,$xsl_content,$param);
            $authors = remove_null_values(explode('|',$res));
            $xsltp->free();
            $pc = new PARSECREATORS();
            $author = array();
            foreach($authors as $eAuthors){
                $creators = $pc->parse($eAuthors);
                foreach($creators as $creator){
                    if(!in_array($creator[2],$author)){
                        $author[] = $creator[2];
                    }
                }
            }
            sort($author);
            return $author;
        }
        else{
            $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
            $xml_content = $this->all_entries();
            $xsl_content = load_file("./xsl/extract_field_values.xsl");
            $param = array('sort' => $this->sort,
                           'sort_order' => $this->sort_order,
                           'field' => $field);
            $res = $xsltp->transform($xml_content,$xsl_content,$param);
            $xsltp->free();
            $tab = array_values(remove_null_values(explode('|',$res)));
            natcasesort($tab);

            return $tab;
        }
    }

    /**
        Select among ids, entries that match $fied=$value
     */
    function filter($ids, $field, $value){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"ISO-8859-1");
        $xml_content = $this->entries_with_ids($ids);
        if($field == 'author'){
            $xpath_query = "contains(translate(.//bibtex:$field,\$ucletters,\$lcletters),translate('$value',\$ucletters,\$lcletters))";
        }
        else{
            $xpath_query = ".//bibtex:$field='$value'";
        }
        $xsl_content = load_file("./xsl/extract_ids.xsl");
        $xsl_content = str_replace("XPATH_QUERY","//bibtex:entry[$xpath_query]",$xsl_content);
        $param = array('sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res = $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }

}


/**
 * Create a new bibliography.
 * @param $name The name of the bibliography.
 * @param $description A short description of the bibliography.
 */
function create_database($name,$description){
    // array to store messages or errors
    $resArray = array('message' => null,
                      'error' => null);

    // get name of existing databases
    $databases_names = get_databases_names();
    $fullname = $name;
    // check it is not a pathname
    if(!ereg('^[^./][^/]*$', $name)){
        $resArray['error'] = msg("Invalid name for bibliography!");
    }
    else if($name != null){
        // Create the new bibliography if it doesn't exist.
        if(!in_array($name,array_values($databases_names))){
            $name = remove_accents($name);
            $name = str_replace(array(' ','\'','"','\\'),'_',$name);
            umask(DMASK);
            // create directory
            $res = mkdir("./bibs/$name");
            if($res){
                $resArray['message'] = msg("BIB_CREATION_SUCCESS");
            }
            else{
                $resArray['message'] = msg("BIB_CREATION_ERROR");
            }
            // papers directory
            mkdir("./bibs/$name/papers");

            // xml and bibtex files
            umask(UMASK);
            copy("./data/template/template.bib","./bibs/$name/$name.bib");
            copy("./data/template/template.xml","./bibs/$name/$name.xml");

            // description
            $fp = fopen("./bibs/$name/description.txt","w");
            fwrite($fp,htmlentities($description));
            fclose($fp);

            // full-name
            $fp = fopen("./bibs/$name/fullname.txt","a+");
            fwrite($fp,$fullname);
            fclose($fp);

            // init xml file
            $xml = load_file("./bibs/$name/$name.xml");
            $xml = str_replace("template",$name,$xml);
            $fp = fopen("./bibs/$name/$name.xml","w");
            fwrite($fp,$xml);
            fclose($fp);

        }
        else{
            $resArray['error'] = msg("BIB_EXISTS");
        }
    }
    else {
        $resArray['error'] = msg("BIB_EMPTY_NAME");
    }
    return $resArray;
}

/**
 * Delete a bibliography
 * @param $name The name of a bibliography.
 */
function delete_database($name){
    //check if $name is a valid bibliography name
    $dbnames = get_databases_names();
    if(!in_array($name,array_keys($dbnames))){
        trigger_error("Wrong database name: $name",ERROR);
    }
    // create .trash folder if it does not exit
    if(!file_exists("./bibs/.trash")){
        $oldmask = umask();
        umask(DMASK);
        mkdir("./bibs/.trash");
        umask($oldmask);
    }
    $fullname = $dbnames[$name];
    // save the bibto .trash folder
    rename("bibs/$name","bibs/.trash/$name-".date("Ymd")) or trigger_error("Error while moving $fullname to .trash folder",ERROR);
    // result message
    $res = sprintf(msg("Database %s moved to trash."),$fullname)."<br/>";
    $res .= sprintf(msg("Remove %s to definitively delete it."),"<code>./bibs/.trash/$name-".date("Ymd")."</code>");
    return $res;
}


/**
 * Get the name of recorded bibliographies.
 * It returns an array which keys are the bibliography's file system name
 * and values are the bibliography's real name.
 * @return An array
 */
function get_databases_names(){
    $dir = opendir("./bibs/");
    $databases_names = array();
    while($file = readdir($dir)){
        if(is_dir("./bibs/".$file) && $file != '.' && $file != '..' && $file[0] != '.') {
            if(file_exists("./bibs/$file/fullname.txt")){
                $name = load_file("./bibs/$file/fullname.txt");
            }
            else{
                $name = $file;
            }
            $databases_names[$file] = $name;
        }
    }
    return $databases_names;
}

/**
 * Extract ids from the xml
 */
function extract_ids_from_xml($xmlstring) {
    preg_match_all("/id=['|\"](.*)['|\"]/U",$xmlstring,$matches);
    return array_unique($matches[1]);
}
?>
