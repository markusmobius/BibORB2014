<?xml version="1.0" encoding="iso-8859-1" ?>
<!--
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
-->
<!--
 * File: parameters.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *    Contains XSL generic parameters.
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    exclude-result-prefixes="bibtex"
    version="1.0">
  

    <!-- The name of the bibliography                 -->
    <xsl:param name="bibname"/>
	
	<!-- The url of the bibliography				  -->
	<xsl:param name="bibnameurl"/>
	
	<!-- The list of bibtex ids present in the basket -->
	<xsl:param name="basketids"/>
	
	<!-- Use images for action links TRUE/FALSE		  -->
	<xsl:param name="display_images"/>

	<!-- Use text for action links TRUE/FALSE		  -->
    <xsl:param name="display_text"/>
	
	<!-- Abstract always visible NO					  -->
	<xsl:param name="abstract"/>
	
	<!-- Display the basket actions					  -->
	<xsl:param name="display_basket_actions"/>
	
	<!-- A group value								  -->
	<xsl:param name="group"/>
	
	<!-- A search value								  -->
	<xsl:param name="search"/>
	
	<!-- An author value							  -->
	<xsl:param name="author"/>
	
	<!-- A title value								  -->
    <xsl:param name="title"/>
	
	<!-- A Keywords value							  -->
    <xsl:param name="keywords"/>
	
	<!-- journal									  -->
	<xsl:param name="journal"/>
	
	<!-- editor										  -->
	<xsl:param name="editor"/>
	
	<!-- year										  -->
	<xsl:param name="year"/>
	
	<!-- A bibtex id								  -->
	<xsl:param name="id"/>
	
	<!-- Some bibtex ids (XML formated)				  -->
	<xsl:param name="ids"/>
	
	<!-- Extra get parameters to add in a link		  -->
	<xsl:param name="extra_get_param"/>
	
    <!-- admin mode -->
    <xsl:param name="can_delete"/>
    <xsl:param name="can_modify"/>

	<!-- Which page to generate (welcome, groups...)  -->
	<xsl:param name="bibindex_mode"/>
    
    <!-- Sort -->
    <xsl:param name="sort"/>
    <xsl:param name="sort_order"/>
    
    <xsl:param name="field"/>
    
    <!-- Basket action images -->
    <xsl:variable name="add-basket-image">cvs-add-16.png</xsl:variable>
    <xsl:variable name="remove-basket-image">cvs-remove-16.png</xsl:variable>
    <!-- Edit/Delete/url/pdf... images -->
    <xsl:variable name="pdf-image">pdf-document.png</xsl:variable>
    <xsl:variable name="ps-image">stock_book-16.png</xsl:variable>
    <xsl:variable name="ps.gz-image">tar.png</xsl:variable>
    <xsl:variable name="pdf-image-link">pdf-document-link.png</xsl:variable>
    <xsl:variable name="ps-image-link">stock_book-16-link.png</xsl:variable>
    <xsl:variable name="ps.gz-image-link">tar-link.png</xsl:variable>
    <xsl:variable name="url-image">link-url-16.png</xsl:variable>
    <xsl:variable name="bibtex-image">stock_convert-16.png</xsl:variable>
    <xsl:variable name="abstract-image">stock_about-16.png</xsl:variable>
    <xsl:variable name="edit-image">stock_edit-16.png</xsl:variable>
    <xsl:variable name="delete-image">stock_delete-16.png</xsl:variable>
    <xsl:variable name="info-image">dialog_info.png</xsl:variable>
    
    <!-- Shelf mode -->
    <xsl:param name="shelf-mode"/>
    <xsl:variable name="own-image">stock_own.png</xsl:variable>
    <xsl:variable name="notown-image">stock_notown.png</xsl:variable>
    <xsl:variable name="buy-image">stock_buy.png</xsl:variable>
    <xsl:variable name="borrow-image">stock_borrow.png</xsl:variable>
    <xsl:variable name="read-image">stock_read.png</xsl:variable>
    <xsl:variable name="readnext-image">stock_readnext.png</xsl:variable>
    <xsl:variable name="notread-image">stock_notread.png</xsl:variable>
	
    <!-- A list of fields -->
    <xsl:param name="fields_to_export"/>

    <!-- An XPath query -->
    <xsl:param name="xpath_query"/>
    
    <!-- XML version -->
    <xsl:param name="biborb_xml_version"/>


    <xsl:variable name="lcletters">abcdefghijklmnopqrstuvwxyzYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy</xsl:variable>
    <xsl:variable name="ucletters">ABCDEFGHIJKLMNOPQRSTUVWXYZ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ</xsl:variable>    
</xsl:stylesheet>
