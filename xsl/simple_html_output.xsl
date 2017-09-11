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
 * File: simple_html_output.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *
-->

<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    version="1.0">

	<xsl:include href="./xsl/parameters.xsl"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="//bibtex:entry"/>
	</xsl:template>

    <xsl:template match="bibtex:entry">
        <dt><xsl:apply-templates select="@id"/></dt>
        <dd>
            <xsl:apply-templates />
            <xsl:apply-templates select=".//bibtex:url"/>
            <xsl:apply-templates select=".//bibtex:urlzip"/>
            <xsl:apply-templates select=".//bibtex:pdf"/>
            <xsl:apply-templates select=".//bibtex:website"/>
        </dd>
    </xsl:template>
    
    <!-- 
      ** 
      ** Select which data to display for which type of entry
      **
      -->
    <xsl:template match="bibtex:article">
        <xsl:apply-templates select="bibtex:author"/>
        <xsl:apply-templates select="bibtex:title"/>
        <xsl:apply-templates select="bibtex:journal"/>
        <xsl:apply-templates select="bibtex:year"/>
    </xsl:template>
    
    <xsl:template match="bibtex:book">
        <xsl:choose>
            <xsl:when test="bibtex:author != ''">
                <xsl:apply-templates select="bibtex:author"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select="bibtex:editor"/>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:apply-templates select="bibtex:title"/>
        <xsl:apply-templates select="bibtex:publisher"/>
        <xsl:apply-templates select="bibtex:year"/>
    </xsl:template>
    
    <xsl:template match="bibtex:booklet">
        <xsl:apply-templates select="bibtex:title"/>
    </xsl:template>
    
    <xsl:template match="bibtex:inbook">
        <xsl:choose>
            <xsl:when test="bibtex:author != ''">
                <xsl:apply-templates select="bibtex:author"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select="bibtex:editor"/>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:apply-templates select="bibtex:title"/>
        <xsl:apply-templates select="bibtex:chapter"/>
        <xsl:apply-templates select="bibtex:pages"/>
        <xsl:apply-templates select="bibtex:publisher"/>
        <xsl:apply-templates select="bibtex:year"/>
    </xsl:template>
    
    <xsl:template match="bibtex:incollection">
        <xsl:apply-templates select="bibtex:author"/>
        <xsl:apply-templates select="bibtex:title"/>
        <xsl:apply-templates select="bibtex:booktitle"/>
        <xsl:apply-templates select="bibtex:publisher"/>
        <xsl:apply-templates select="bibtex:year"/>
    </xsl:template>
    
    <xsl:template match="bibtex:inproceedings | bibtex:conference">
        <xsl:apply-templates select="bibtex:author"/>
        <xsl:apply-templates select="bibtex:title"/>
        <xsl:apply-templates select="bibtex:booktitle"/>
        <xsl:apply-templates select="bibtex:year"/>
    </xsl:template>
    
    <xsl:template match="bibtex:manual">
        <xsl:apply-templates select="bibtex:title"/>
    </xsl:template>
    
    <xsl:template match="bibtex:masterthesis">
        <xsl:apply-templates select="bibtex:author"/>
        <xsl:apply-templates select="bibtex:title"/>
        <xsl:apply-templates select="bibtex:school"/>
        <xsl:apply-templates select="bibtex:year"/>
    </xsl:template>
    
    <xsl:template match="bibtex:misc">
        <xsl:apply-templates select="bibtex:author"/>
        <xsl:apply-templates select="bibtex:title"/>
        <xsl:apply-templates select="bibtex:howpublished"/>
        <xsl:apply-templates select="bibtex:year"/>
    </xsl:template>
    
    <xsl:template match="bibtex:phdthesis">
        <xsl:apply-templates select="bibtex:author"/>
        <xsl:apply-templates select="bibtex:title"/>
        <xsl:apply-templates select="bibtex:school"/>
        <xsl:apply-templates select="bibtex:year"/>
    </xsl:template>
    
    <xsl:template match="bibtex:proceedings">
        <xsl:apply-templates select="bibtex:title"/>
        <xsl:apply-templates select="bibtex:year"/>
    </xsl:template>
    
    <xsl:template match="bibtex:techreport">
        <xsl:apply-templates select="bibtex:author"/>
        <xsl:apply-templates select="bibtex:title"/>
        <xsl:apply-templates select="bibtex:institution"/>
        <xsl:apply-templates select="bibtex:year"/>
    </xsl:template>
    
    <xsl:template match="bibtex:unpublished">
        <xsl:apply-templates select="bibtex:author"/>
        <xsl:apply-templates select="bibtex:title"/>
        <xsl:apply-templates select="bibtex:note"/>
    </xsl:template>
    
    <!-- 
      ** 
      ** Select style for each fields
      **
      -->
    <xsl:template match="bibtex:url | bibtex:urlzip | bibtex:pdf | bibtex:website">
        <a href="./bibs/{$bibname}/papers/{node()}">[<xsl:value-of select="local-name()"/>]</a>
    </xsl:template>
    
    <xsl:template match="bibtex:website">
        <a href="http://{node()}">[<xsl:value-of select="local-name()"/>]</a>
    </xsl:template>
    
    <xsl:template match="@id">
        <span class="field_id">[<xsl:value-of select="."/>]</span>
    </xsl:template>
    
    <xsl:template match="*">
        <span class="field_{local-name()}"><xsl:value-of select="."/>.</span>
    </xsl:template>
</xsl:stylesheet>