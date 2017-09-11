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
 * File: advanced_search.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *    advanced search
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    version="1.0"> 
   
    <xsl:output method="xml" encoding="iso-8859-1"/>
    
	<!-- include generic parameters -->
	<xsl:include href="xsl/parameters.xsl"/>
	
	<!-- search connector -->
	<xsl:param name="search_connector"/>
	
	<!-- search value -->
	<xsl:param name="search_author"/>
	<xsl:param name="search_booktitle"/>
	<xsl:param name="search_edition"/>
	<xsl:param name="search_editor"/>
	<xsl:param name="search_journal"/>
	<xsl:param name="search_publisher"/>
	<xsl:param name="search_series"/>
	<xsl:param name="search_title"/>
	<xsl:param name="search_year"/>
	<xsl:param name="search_abstract"/>
	<xsl:param name="search_keywords"/>
	<xsl:param name="search_groups"/>
	<xsl:param name="search_longnotes"/>
    
    <!-- every parameter values to lower case -->
  
    <xsl:template match="/bibtex:file">
        <xsl:element name="bibtex:file">
            <xsl:apply-templates/>
        </xsl:element>
	</xsl:template>
	
	<xsl:template match="bibtex:entry">
        <xsl:choose>
            <xsl:when test="$search_connector = 'and'">
                <xsl:if test="(not($search_author) or contains(translate(.//bibtex:author,$ucletters,$lcletters),translate($search_author,$ucletters,$lcletters))) and (not($search_booktitle) or contains(translate(.//bibtex:booktitle,$ucletters,$lcletters),translate($search_booktitle,$ucletters,$lcletters))) and (not($search_edition) or contains(translate(.//bibtex:edition,$ucletters,$lcletters),translate($search_edition,$ucletters,$lcletters))) and (not($search_journal) or contains(translate(.//bibtex:journal,$ucletters,$lcletters),translate($search_journal,$ucletters,$lcletters))) and (not($search_publisher) or contains(translate(.//bibtex:publisher,$ucletters,$lcletters),translate($search_publisher,$ucletters,$lcletters))) and (not($search_series) or contains(translate(.//bibtex:series,$ucletters,$lcletters),translate($search_series,$ucletters,$lcletters))) and (not($search_title) or contains(translate(.//bibtex:title,$ucletters,$lcletters),translate($search_title,$ucletters,$lcletters))) and (not($search_year) or contains(translate(.//bibtex:year,$ucletters,$lcletters),translate($search_year,$ucletters,$lcletters))) and (not($search_abstract) or contains(translate(.//bibtex:abstract,$ucletters,$lcletters),translate($search_abstract,$ucletters,$lcletters))) and (not($search_keywords) or contains(translate(.//bibtex:keywords,$ucletters,$lcletters),translate($search_keywords,$ucletters,$lcletters))) and (not($search_groups) or contains(translate(.//bibtex:groups,$ucletters,$lcletters),translate($search_groups,$ucletters,$lcletters))) and (not($search_longnotes) or contains(translate(.//bibtex:longnotes,$ucletters,$lcletters),translate($search_longnotes,$ucletters,$lcletters)))">
                    <xsl:element name="bibtex:entry">
                        <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
                            <xsl:apply-templates />
                    </xsl:element>
                </xsl:if>
            </xsl:when>
            <xsl:when test="$search_connector = 'or'">
                <xsl:if test="($search_author and contains(translate(.//bibtex:author,$ucletters,$lcletters),translate($search_author,$ucletters,$lcletters))) or ($search_booktitle and contains(translate(.//bibtex:booktitle,$ucletters,$lcletters),translate($search_booktitle,$ucletters,$lcletters))) or ($search_edition and contains(translate(.//bibtex:edition,$ucletters,$lcletters),translate($search_edition,$ucletters,$lcletters))) or ($search_journal and contains(translate(.//bibtex:journal,$ucletters,$lcletters),translate($search_journal,$ucletters,$lcletters))) or ($search_publisher and contains(translate(.//bibtex:publisher,$ucletters,$lcletters),translate($search_publisher,$ucletters,$lcletters))) or ($search_series and contains(translate(.//bibtex:series,$ucletters,$lcletters),translate($search_series,$ucletters,$lcletters))) or ($search_title and contains(translate(.//bibtex:title,$ucletters,$lcletters),translate($search_title,$ucletters,$lcletters))) or ($search_year and contains(translate(.//bibtex:year,$ucletters,$lcletters),translate($search_year,$ucletters,$lcletters))) or ($search_abstract and contains(translate(.//bibtex:abstract,$ucletters,$lcletters),translate($search_abstract,$ucletters,$lcletters))) or ($search_keywords and contains(translate(.//bibtex:keywords,$ucletters,$lcletters),translate($search_keywords,$ucletters,$lcletters))) or ($search_groups and contains(translate(.//bibtex:groups,$ucletters,$lcletters),translate($search_groups,$ucletters,$lcletters))) or ($search_longnotes and contains(translate(.//bibtex:longnotes,$ucletters,$lcletters),translate($search_longnotes,$ucletters,$lcletters)))">
                    <xsl:element name="bibtex:entry">
                        <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
                            <xsl:apply-templates />
                    </xsl:element>
                </xsl:if>
            </xsl:when>
        </xsl:choose>
            
        
    </xsl:template>
    
    <xsl:template match="@*">
        <xsl:attribute name="{name()}"><xsl:value-of select="."/></xsl:attribute>
    </xsl:template>
    
    <xsl:template match="*">
        <xsl:element name="bibtex:{local-name()}">
            <xsl:apply-templates select=" @* | node()"/>
        </xsl:element>
    </xsl:template>
    
</xsl:stylesheet>
