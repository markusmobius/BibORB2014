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
 * File: xslt_processor.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *      A simple class to encapsulate XSLT method for PHP < 5
 *      Call XSLT module functions if PHP >=5
 *
 */


if (PHP_VERSION >= 5) {
    // Emulate the old xslt library functions
    function xslt_create() {
        $xsl = new XSLTProcessor;
        $xsl->registerPHPFunctions();
        return $xsl;
    }

    function xslt_process($xsltproc,
                          $xml_arg,
                          $xsl_arg,
                          $xslcontainer = null,
                          $args = null,
                          $params = null) {
        // Start with preparing the arguments
        $xml_arg = str_replace('arg:', '', $xml_arg);
        $xsl_arg = str_replace('arg:', '', $xsl_arg);

        // Create instances of the DomDocument class
        $xml = new DomDocument;
        $xsl = new DomDocument;

        // Load the xml document and the xsl template
        $xml->loadXML($args[$xml_arg]);
        $xsl->loadXML($args[$xsl_arg]);

        // Load the xsl template
        $xsltproc->importStyleSheet($xsl);

        // Set parameters when defined
        if ($params) {
            foreach ($params as $param => $value) {
                $xsltproc->setParameter("", $param, $value);
            }
        }

        // Start the transformation
        $processed = $xsltproc->transformToXML($xml);

        //print_r($processed);
        // Put the result in a file when specified
        if ($xslcontainer) {
            return @file_put_contents($xslcontainer, $processed);
        } else {
            return $processed;
        }

    }

    function xslt_free($xsltproc) {
        unset($xsltproc);
    }
 }



class XSLT_Processor {
	
	var $output;
	var $xsltproc;
	var $xsl_parameters;
	var $xml_string;
	var $xsl_string;
	
	function XSLT_Processor($base,$encoding){
		$this->xsltproc = xslt_create();
        if(PHP_VERSION < 5){
            xslt_set_base($this->xsltproc,$base);
            xslt_set_encoding($this->xsltproc,$encoding);
        }
		$this->xsl_parameters = null;
	}
	
	function free(){
		xslt_free($this->xsltproc);
	}
	
	function set_xslt_encoding($encoding){
        if(PHP_VERSION < 5){
            xslt_set_encoding($this->xsltproc,$encoding);
        }
	}
	
	function set_xslt_base($base){
        if(PHP_VERSION < 5){
            xslt_set_base($this->xsltproc,$base);
        }
	}
	
	function set_xslt_parameters($param){
		if(is_array($param)){
			$this->parameters = $param;
		}
	}
	
	function transform($xmlstring,$xslstring,$xslparam=array()){
		$arguments = array('/_xml' => $xmlstring,
						   '/_xsl' => $xslstring);
		$result = xslt_process($this->xsltproc,'arg:/_xml','arg:/_xsl',NULL,$arguments,$xslparam);
        if(PHP_VERSION < 5){
            if(!$result && xslt_errno($this->xsltproc)>0){
                die(sprintf("Cannot process XSLT document [%d]: %s", xslt_errno($this->xsltproc), xslt_error($this->xsltproc)));
            }
        }
		return $result;
	}	
}


?> 