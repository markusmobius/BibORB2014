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
 * File: bibtex.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *      This file defines the BibTeX_Tools class. It provides functions to
 *   deal with bibtex data:
 *          * parse a string/file (using PARSEENTRIES from bibliophile.sf.net)
 *          * convert to xml
 *          * convert to RIS
 *          * convert to DocBook
 */

require_once("php/third_party/bibtexParse/PARSEENTRIES.php");
require_once("php/third_party/bibtexParse/PARSECREATORS.php");
require_once("php/utilities.php");

/**
 * A class to transform, parse BibTeX references.
 *
 * @author G. Gardey
 */
class BibTeX_Tools
{
    // The following variables are specific to DocBook importation
    // XML parser
    var $xp;
    var $entries;
    var $currentEntry;
    var $currentAuthor;
    var $currentTag;
    var $currentTitle;



    /**
     * Return an array of entries.
     * $string is a BibTeX string
     */
    function get_array_from_string($string){
        $bibtex_parser = new PARSEENTRIES();
        $bibtex_parser->loadBibtexString($string);
        $bibtex_parser->expandMacro = TRUE;
        $bibtex_parser->extractEntries();
        $res = $bibtex_parser->returnArrays();
        $entries = $res[2];
        $this->bibtex_import_post_traitment($entries);
        return $entries;
    }

    /**
     * Return an array of entries
     * $filename is a BibTeX file
     */
    function get_array_from_file($filename){
        $bibtex_parser = new PARSEENTRIES();
        $bibtex_parser->openBib($filename);
        $bibtex_parser->extractEntries();
        $bibtex_parser->expandMacro = TRUE;
        $bibtex_parser->closeBib();
        $res = $bibtex_parser->returnArrays();
        $this->bibtex_import_post_traitment($entries);
        return $res[2];
    }

    /**
     * Convert an array representation of an entry in XML.
     * @param $tab An array (field => value).
     * @return An XML string.
     */
    function entry_array_to_xml($tab){
        $xml = "<bibtex:entry id='".$tab['id']."'>";
        $xml .= "<bibtex:".$tab['___type'].">";
        foreach($tab as $key => $value){
            if($key != 'groups' && $key!= '___type' && $key != 'id'){
                $xml .= "<bibtex:".$key.">";
                $xml .= trim(myhtmlentities($value));
                $xml .= "</bibtex:".$key.">";
            }
            else if($key == 'groups') {
                $xml .= "<bibtex:groups>";
                $groupvalues = preg_split('/,/',$value);
                foreach($groupvalues as $gr){
                    $xml .= "<bibtex:group>";
                    $xml .= trim(myhtmlentities($gr));
                    $xml .= "</bibtex:group>";
                }
                $xml .= "</bibtex:groups>";
            }
        }
        $xml .= "</bibtex:".$tab['___type'].">";
        $xml .= "</bibtex:entry>";
        return $xml;
    }

    /**
     * Convert an array of entries to XML.
     * Return: array(number of entries, array of ids, xml string)
     */
    function entries_array_to_xml($tab){
        $ids = array();
        $xml_content = "<?xml version='1.0' encoding='ISO-8859-1'?>";
        $xml_content .= "<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/' version='".BIBORB_XML_VERSION."' >";
        foreach($tab as $entry){
            $xml_content .= $this->entry_array_to_xml($entry);
            array_push($ids,$entry['id']);
        }
        $xml_content .= "</bibtex:file>";
        return array(count($tab),$ids,$xml_content);
    }

    /**
     * Convert a bibtex string to xml.
     * Return: array(number of entries, array of ids, xml string)
     */
    function bibtex_string_to_xml($string){
        $entries = $this->get_array_from_string($string);
        return $this->entries_array_to_xml($entries);
    }

    /**
     * Convert a bibtex file to xml.
     * Return: array(number of entries, array of ids, xml string)
     */
    function bibtex_file_to_xml($filename){
        $entries = $this->get_array_from_file($filename);
        return $this->entries_array_to_xml($entries);
    }

    /**
     * Convert a XML string to an array
     */
    function xml_to_bibtex_array($xmlstring){
        // result
        $res = array();
        $xml = str_replace("\n","",$xmlstring);
        // match all entries
        preg_match_all("/<bibtex:entry id=['|\"](.*)['|\"]>(.*)<\/bibtex:entry>/U",$xml,$entries,PREG_PATTERN_ORDER);
        for($i=0;$i<count($entries[1]);$i++){
            $entry = $entries[2][$i];

            $ref_tab = array('id'=> $entries[1][$i]);
            // get the bibtex type
            preg_match("/<bibtex:(.[^>]*)>(.*)<\/bibtex:(.[^>]*)>/",$entry,$matches);
            $ref_tab['___type'] = $matches[1];

            // get groups value
            $bibtex_fields = $matches[2];
            preg_match("/<bibtex:groups>(.*)<\/bibtex:groups>/U",$bibtex_fields,$groups);
            if(isset($groups[1])){
                preg_match_all("/<bibtex:group>(.*)<\/bibtex:group>/U",$groups[1],$group);
                $ref_tab['groups'] = implode(',',$group[1]);
                $bibtex_fields = str_replace($groups[0],"",$bibtex_fields);

            }
            preg_match_all("/<bibtex:(.[^>]*)>(.*)<\/bibtex:(.[^>]*)>/U",$bibtex_fields,$fields);
            // analyse each fields
            for($j=0;$j<count($fields[1]);$j++){
                $ref_tab[$fields[1][$j]]=trim($fields[2][$j]);
            }
            $res[] = $ref_tab;
        }

        return $res;
    }

    /**
     * Convert an array to bibtex
     * @param $tab An array of references
     * @param $fields_to_export Array of fields to export
     * @return A bibtex formated string.
     */
    function array_to_bibtex_string($tab,$fields_to_export){
        $export = "";
        foreach($tab as $entry){
            $entry_exported = "";
            $export .= "@".$entry['___type']."{".$entry['id'];
            foreach($fields_to_export as $field){
                if(array_key_exists($field,$entry)){
                    $export .= ",\n";
                    $export .= "\t".$field." = {".$entry[$field]."}";
                }
            }
            $export .= "\n}\n";
        }
        return $export;
    }

    /**
     * Export an array of references to a RIS formated string.
     * @param $tab An array of references.
     * @return A RIS formated string.
     */
    function array_to_RIS($tab){
        $ris_type_translate = array('article'       => 'JOUR',
                                    'book'          => 'BOOK',
                                    'booklet'       => 'BOOK',
                                    'inbook'        => 'CHAP',
                                    'incollection'  => 'JOUR',
                                    'inproceedings' => 'JOUR',
                                    'manual'        => 'BOOK',
                                    'masterthesis'  => 'THES',
                                    'misc'          => 'GEN',
                                    'phdthesis'     => 'THES',
                                    'proceedings'   => 'CONF',
                                    'techreport'    => 'RPRT',
                                    'unpublished'   => 'UNPB');
        $pc = new PARSECREATORS();
        $export = "";
        foreach($tab as $entry){
            $export .= sprintf("TY  - %s\n",$ris_type_translate[$entry['___type']]);
            // authors
            if(array_key_exists('author',$entry)){
                $authors = $pc->parse($entry['author']);
                foreach($authors as $author){
                    $export .= sprintf("A1  - %s, %s\n",$author[2],$author[0]);
                }
            }

            // title
            if(array_key_exists('title',$entry)){
                $export .= sprintf("T1  - %s\n",$entry['title']);
            }

            // journal
            if(array_key_exists('journal',$entry)){
                $export .= sprintf("JO  - %s\n",$entry['journal']);
            }

            // volume
            if(array_key_exists('volume',$entry)){
                $export .= sprintf("VL  - %s\n",$entry['volume']);
            }

            // number
            if(array_key_exists('number',$entry)){
                $export .= sprintf("IS  - %s\n",$entry['number']);
            }

            // start/end page
            if(array_key_exists('pages',$entry)){
                $pages = preg_split('/-/',$entry['pages']);
                $pages = remove_null_values($pages);
                if(isset($pages[0])){
                    $export .= sprintf("SP  - %s\n",$pages[0]);
                }
                if(isset($pages[1])){
                    $export .= sprintf("EP  - %s\n",$pages[1]);
                }
            }

            // series
            if(array_key_exists('series',$entry)){
                $export .= sprintf("T3  - %s\n",$entry['series']);
            }

            // editor
            if(array_key_exists('editor',$entry)){
                $editors = $pc->parse($entry['editor']);
                foreach($editors as $editor){
                    $export .= sprintf("A3  - %s, %s\n",$editor[2],$editor[0]);
                }
            }

            // year
            if(array_key_exists('year',$entry)){
                $export .= sprintf("Y1  - %s\n",$entry['year']);
            }

            // pusblisher
            if(array_key_exists('publisher',$entry)){
                $export .= sprintf("PB  - %s\n",$entry['publisher']);
            }

            // address
            if(array_key_exists('address',$entry)){
                $export .= sprintf("AD  - %s\n",$entry['address']);
            }

            // note
            if(array_key_exists('note',$entry)){
                $export .= sprintf("N1  - %s\n",$entry['note']);
            }

            // abstract
            if(array_key_exists('abstract',$entry)){
                $export .= sprintf("N2 - %s\n",$entry['abstract']);
            }

            // keywords
            if(array_key_exists('keywords',$entry)){
                $keywords = preg_split('/,/',$entry['keywords']);
                foreach($keywords as $keyword){
                    $export .= sprintf("KW  - %s\n",$keyword);
                }
            }

            // url
            if(array_key_exists('url',$entry)){
                $export .= sprintf("UR  - %s\n",$entry['url']);
            }

            // pdf
            if(array_key_exists('pdf',$entry)){
                $export .= sprintf("L1  - %s\n",$entry['pdf']);
            }

            $export .= "ER  - \n";
            $export .= "\n";
        }
        return $export;
    }

    /**
     * Import RIS references.
     * @param A RIS string.
     * @return An array of references.
     */
    function RIS_to_array($ris)
    {
        $entries = array();
        $ris_type_translate = array('JOUR' => 'article',
                                    'BOOK' => 'book',
                                    'CHAP' => 'inbook',
                                    'GEN'  => 'misc',
                                    'THES' => 'phdthesis',
                                    'CONF' => 'proceedings',
                                    'RPRT' => 'techreport',
                                    'UNPB' => 'unpublished');
        foreach($ris as $line){
            echo $line;

            // type
            if(preg_match('/TY\s*-\s*(.*)\s*/',$line,$matches)){
                $entry = array();
                $entry['___type'] = $ris_type_translate[trim($matches[1])];
            }
            //author
            if(preg_match('/A1\s*-\s*(.*)\s*/',$line,$matches)){
                $authors = preg_split('/,/',$matches[1]);
                if(isset($entry['author'])){
                    $entry['author'] .= " and ";
                }
                else{
                    $entry['author'] = "";
                }
                $entry['author'] .= " ".$authors[1]." ".$authors[0];
            }
            //title
            if(preg_match('/T1\s*-\s*(.*)\s*/',$line,$matches)){
                $entry['title'] = $matches[1];
            }
            // journal
            if(preg_match('/JO\s*-\s*(.*)\s*/',$line,$matches)){
                $entry['journal'] = $matches[1];
            }
            // volume
            if(preg_match('/VL\s*-\s*(.*)\s*/',$line,$matches)){
                $entry['volume'] = $matches[1];
            }
            // number
            if(preg_match('/IS\s*-\s*(.*)\s*/',$line,$matches)){
                $entry['number'] = $matches[1];
            }
            // pages
            if(preg_match('/(SP|EP)\s*-\s*(.*)\s*/',$line,$matches)){
                $entry[$matches[1]] = $matches[2];
            }
            // series
            if(preg_match('/T3\s*-\s*(.*)\s*/',$line,$matches)){
                $entry['series'] = $matches[1];
            }
            // editor
            if(preg_match('/A3\s*-\s*(.*)\s*/',$line,$matches)){
                $authors = preg_split('/,/',$matches[1]);
                if(isset($entry['editor'])){
                    $entry['editor'] .= " and ";
                }
                else{
                    $entry['editor'] = "";
                }
                $entry['editor'] .= " ".$authors[1]." ".$authors[0];
            }
            // year
            if(preg_match('/Y1\s*-\s*(.*)\s*/',$line,$matches)){
                $entry['year'] = $matches[1];
            }
            // publisher
            if(preg_match('/BP\s*-\s*(.*)\s*/',$line,$matches)){
                $entry['publisher'] = $matches[1];
            }
            // address
            if(preg_match('/AD\s*-\s*(.*)\s*/',$line,$matches)){
                $entry['address'] = $matches[1];
            }
            // note
            if(preg_match('/N1\s*-\s*(.*)\s*/',$line,$matches)){
                $entry['note'] = $matches[1];
            }
            // abstract
            if(preg_match('/N2\s*-\s*(.*)\s*/',$line,$matches)){
                $entry['abstract'] = $matches[1];
            }
            // keywords
            if(preg_match('/KW\s*-\s*(.*)\s*/',$line,$matches)){
                if(isset($entry['keywords'])){
                    $entry['keywords'] = ", ";
                }
                else{
                    $entry['keywords'] = "";
                }
                $entry['keywords'] .= $matches[1];
            }
            // url
            if(preg_match('/UR\s*-\s*(.*)\s*/',$line,$matches)){
                $entry['url'] = $matches[1];
            }
            // end
            if(preg_match('/ER\s*-\s*(.*)\s*/',$line,$matches)){
                if(isset($entry['author']))
                    $entry['author'] = trim(preg_replace("/\s+/"," ",$entry['author']));
                if(isset($entry['editor']))
                    $entry['editor'] = trim(preg_replace("/s+/"," ",$entry['editor']));
                if(isset($entry['SP'])){
                    $entry['pages'] = $entry['SP'];
                    unset($entry['SP']);
                }
                if(isset($entry['EP'])){
                    if(isset($entry['page'])){
                        $entry['pages'] .= "--";
                    }
                    else{
                        $entry['pages'] = "";
                    }
                    $entry['pages'] .= $entry['EP'];
                    unset($entry['EP']);
                }
                $entries[] = $entry;
            }
        }

        return $entries;
    }


    /**
     * Export an array of references to DocBook.
     * @param $tab An array of entries.
     * @return A DocBook string.
     */
    function array_to_DocBook($tab){
        $pc = new PARSECREATORS();
        $export = "<?xml version='1.0'?>\n";
        $export .= "<bibliography>\n";
        foreach($tab as $entry){
            $export .= sprintf("\t<biblioentry xreflabel='%s' id='%s'>\n",$entry['id'],$entry['id']);
            $export .= sprintf("\t\t<abbrev>%s</abbrev>\n",$entry['id']);
            // authors
            if(array_key_exists('author',$entry)){
                $authors = $pc->parse($entry['author']);
                $export .= "\t\t<authorgroup>\n";
                foreach($authors as $author){
                    $export .= "\t\t\t<author>\n";
                    $export .= "\t\t\t\t<firstname>".$author[0]."</firstname>\n";
                    $export .= "\t\t\t\t<othername role='mi'>".$author[1]."</othername>\n";
                    $export .= "\t\t\t\t<surname>".$author[2]."</surname>\n";
                    $export .= "\t\t\t</author>\n";
                }
                $export .= "\t\t</authorgroup>\n";
            }
            // title
            if(array_key_exists('title',$entry)){
                $type = $entry['___type'];
                if($type != 'article' && $type != 'book' && $type != 'journal'){
                    $type = 'article';
                }
                $export .= sprintf("\t\t<citetitle pubwork='%s'>%s</citetitle>\n",$type,$entry['title']);
            }
            // journal
            if(array_key_exists('jounrnal',$entry)){
                $export .= sprintf("\t\t<citetitle pubwork='%s'>%s</citetitle>\n",'journal',$entry['journal']);
            }
            // publisher
            if(array_key_exists('publisher',$entry)){
                $export .= sprintf("\t\t<publisher>\n\t\t\t<publishername>%s</publishername>\n\t\t</publisher>\n",$entry['publisher']);
            }
            // volume
            if(array_key_exists('volume',$entry)){
                $export .= sprintf("\t\t<volumenum>%s</volumenum>\n",$entry['volume']);
            }
            // year
            if(array_key_exists('year',$entry)){
                $export .= sprintf("\t\t<pubdate>%s</pubdate>\n",$entry['year']);
            }
            // pages
            if(array_key_exists('pages',$entry)){
                $export .= sprintf("\t\t<artpagenums>%s</artpagenums>\n",$entry['pages']);
            }
            // number
            if(array_key_exists('number',$entry)){
                $export .= sprintf("\t\t<issuenum>%s</issuenum>\n",$entry['number']);
            }
            // editor
            if(array_key_exists('editor',$entry)){
                $export .= sprintf("\t\t<editor>%s</editor>\n",$entry['editor']);
            }
            // abstract
            if(array_key_exists('abstract',$entry)){
                $export .= "\t\t<abstract>\n";
                $export .= "\t\t\t<para>".$entry['abstract']."\n\t\t\t</para>\n";
                $export .= "\t\t</abstract>\n";
            }
            $export .= "\t</biblioentry>\n";
        }
        $export .= "</bibliography>\n";
        return $export;
    }

    /**
     * Import a docbook string.
     * @param $docbook The DocBook string.
     * @return An array of entries present in the DocBook string.
     */
    function DocBook_to_array($docbook)
    {
        // clean up before starting
        unset($this->entries);
        unset($this->currentEntry);
        // create an xml parser
        $this->xp = xml_parser_create() or trigger_error("Unable to create an XML Parser!.",ERROR);
        xml_set_object($this->xp,$this);
        xml_set_element_handler($this->xp,"docbook_start_tag","docbook_end_tag");
        xml_set_character_data_handler($this->xp,"docbook_cdata");
        if( !xml_parse($this->xp,$docbook,true)){
            trigger_error("XML Parsing error:\n".xml_error_string(xml_get_error_code($this->xp))."\nError at line: ".xml_get_current_line_number($this->xp),ERROR);
        }


        for($i=0;$i<count($this->entries);$i++){
            // set title and journal fields
            if(array_key_exists('citetitle',$this->entries[$i])){
                if(array_key_exists('article',$this->entries[$i]['citetitle'])){
                    $this->entries[$i]['title'] = $this->entries[$i]['citetitle']['article'];
                }
                if(array_key_exists('journal',$this->entries[$i]['citetitle'])){
                    $this->entries[$i]['journal'] = $this->entries[$i]['citetitle']['journal'];
                }
                unset($this->entries[$i]['citetitle']);
            }
            // remove spaces
            foreach($this->entries[$i] as $key=>$value){
                $val = trim($value);
                if($val != ""){
                    $this->entries[$i][$key] = $val;
                }
                else{
                    unset($this->entries[$i][$key]);
                }
            }
        }
        xml_parser_free($this->xp);

        return $this->entries;
    }

    /**
     * Called when a docbook start tag is parsed.
     */
    function docbook_start_tag($parser, $name, $att)
    {
        $name = strtolower($name);
        switch($name){
            case 'bibliography':
                // start a new bibliography
                $this->entries = array();
                break;

            case 'biblioentry':
                // start a new entry
                $this->currentEntry = array();
                if(array_key_exists('XREFLABEL',$att)){
                    $this->currentEntry['id'] = $att['XREFLABEL'];
                }
                break;

            case 'authorgroup':
                // waiting for an author
                $this->currentAuthor = array();
                break;

            case 'author':
                // an author
                // if it isn't the first author, add an 'and' to the string
                if(isset($this->currentEntry['author'])){
                    $this->currentEntry['author'] .= " and ";
                }
                else{
                    $this->currentEntry['author'] = "";
                }
                $this->currentAuthor = array();
                break;

            case 'citetitle':
                //title
                $this->currentTag = $name;
                if(array_key_exists('PUBWORK',$att)){
                    $this->currentTitle = $att['PUBWORK'];
                    $this->currentEntry['citetitle'][$att['PUBWORK']] = "";
                }
                break;
        }
        if($name == 'othername' && isset($att['role']) && $att['role'] == 'mi'){
            $this->currentTag = $name;
        }
        if($name != 'para'){
            $this->currentTag = $name;
        }
    }
    /**
     * Called when DocBook and tag is parsed.
     */
    function docbook_end_tag($parser,$name)
    {
        $name = strtolower($name);
        switch($name){
            case 'biblioentry':
                // add the entry
                $this->entries[] = $this->currentEntry;
                break;
            case 'author':
                // add the author
                if(isset($this->currentAuthor[0]))
                    $this->currentEntry['author'] .= " ".$this->currentAuthor[0];
                if(isset($this->currentAuthor[1]))
                    $this->currentEntry['author'] .= " ".$this->currentAuthor[1];
                if(isset($this->currentAuthor[2]))
                    $this->currentEntry['author'] .= " ".$this->currentAuthor[2];

                $this->currentEntry['author'] = preg_replace('/\s+/',' ',$this->currentEntry['author']);
                unset($this->currentAuthor);
                break;
        }
    }

    /**
     * CDATA values for DocBook parsing.
     */
    function docbook_cdata($parser,$data)
    {
        switch($this->currentTag){
            case 'firstname':
                if(isset($this->currentAuthor[0])){
                    $this->currentAuthor[0] .= $data;
                }
                else{
                    $this->currentAuthor[0] = $data;
                }
                break;
            case 'surname':
                if(isset($this->currentAuthor[2])){
                    $this->currentAuthor[2] .= $data;
                }
                else{
                    $this->currentAuthor[2] = $data;
                }
                break;
            case 'othername':
                if(isset($this->currentAuthor[1])){
                    $this->currentAuthor[1] .= $data;
                }
                else{
                    $this->currentAuthor[1] = $data;
                }
                break;
            case 'citetitle':
                $this->currentEntry['citetitle'][$this->currentTitle] .= $data;
                break;
            case 'volumenum':
                if(isset($this->currentEntry['volume'])){
                    $this->currentEntry['volume'] .= $data;
                }
                else{
                    $this->currentEntry['volume'] = $data;
                }
                break;
            case 'pubdate':
                if(isset($this->currentEntry['year'])){
                    $this->currentEntry['year'] .= $data;
                }
                else{
                    $this->currentEntry['year'] = $data;
                }
                break;
            case 'artpagenums':
                if(isset($this->currentEntry['pages'])){
                    $this->currentEntry['pages'] .= $data;
                }
                else{
                    $this->currentEntry['pages'] = $data;
                }
                break;
            case 'issuenum':
                if(isset($this->currentEntry['number'])){
                    $this->currentEntry['number'] .= $data;
                }
                else{
                    $this->currentEntry['number'] = $data;
                }
                break;
            case 'editor':
                if(isset($this->currentEntry['editor'])){
                    $this->currentEntry['editor'] .= $data;
                }
                else{
                    $this->currentEntry['editor'] = $data;
                }
                break;
            case 'publishername':
                if(isset($this->currentEntry['publishername'])){
                    $this->currentEntry['publishername'] .= $data;
                }
                else{
                    $this->currentEntry['publishername'] = $data;
                }
                break;
            case 'abstract':
                if(isset($this->currentEntry['abstract'])){
                    $this->currentEntry['abstract'] .= $data;
                }
                else{
                    $this->currentEntry['abstract'] = $data;
                }
                break;
        }
    }


    /**
     * Some transformations to perform after importing BibTeX entries.
     * @param &$entries A reference to an array of imported entries.
     */
    function bibtex_import_post_traitment(&$entries){
        for($i=0;$i<count($entries);$i++){
            if(isset($entries[$i]['pdf'])){
                if(strpos($entries[$i]['pdf'],"http://") !== FALSE ||
                   strpos($entries[$i]['pdf'],"https://") !== FALSE ||
                   strpos($entries[$i]['pdf'],"ftp://") !== FALSE){
                    $entries[$i]['ad_pdf'] = $entries[$i]['pdf'];
                    unset($entries[$i]['pdf']);
                }
            }
            if(isset($entries[$i]['url'])){
                if(strpos($entries[$i]['url'],"http://") !== FALSE ||
                   strpos($entries[$i]['url'],"https://") !== FALSE ||
                   strpos($entries[$i]['url'],"ftp://") !== FALSE){
                    $entries[$i]['ad_url'] = $entries[$i]['url'];
                    unset($entries[$i]['url']);
                }
            }
            if(isset($entries[$i]['urlzip'])){
                if(strpos($entries[$i]['urlzip'],"http://") !== FALSE ||
                   strpos($entries[$i]['urlzip'],"https://") !== FALSE ||
                   strpos($entries[$i]['urlzip'],"ftp://") !== FALSE){
                    $entries[$i]['ad_urlzip'] = $entries[$i]['urlzip'];
                    unset($entries[$i]['urlzip']);
                }
            }
        }
    }

    /**
     * Extract some information from a reference.
     * Remove from $tab all elements whose key is not in $extract.
     * @param $tab The array from which to extract values.
     * @param $extract The fields to extract.
     */
    function extract_bibtex_data($tab,$extract){
        $result = array();
        foreach($tab as $key => $value){
            $val = trim($value);
            if(in_array($key,$extract) && $val != ''){
                $result[$key] = $val;
            }
        }
        return $result;
    }
}

/**
 * Convert LaTeX code to HTML
 * @param $latex A string with LaTeX macros
 */
function latex_macro_to_html($latex)
{
    $latex_conversion_table =
        array("\'a" => "á", "\`a" => "à", "\^a" => "â", "\~a" => "ã", "\\\"a" => "ä", "\aa" => "å", "\ae" => "æ",
              "\c{c}" => "ç",
              "\'e" => "é", "\^e" => "ê", "\`e" => "è", "\\\"e" => "ë",
              "\'i" => "í", "\`i" => "ì", "\^i" => "î", "\\\"i" => "ï",
              "\~n" => "ñ",
              "\'o" => "ó", "\^o" => "ô", "\`o" => "ò", "\\\"o" => "ö", "\~o" => "õ",
              "\'u" => "ú", "\`u" => "ù", "\^u" => "û", "\\\"u" => "ü",
              "\'y" => "ý", "\\\"y" => "ÿ");
    return str_replace(array_keys($latex_conversion_table),
                       array_values($latex_conversion_table),
                       $latex);
}


?>
