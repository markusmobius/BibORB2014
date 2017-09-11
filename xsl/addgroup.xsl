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
 * File: addgroup.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *     Get given IDs entries from the database and add a group.
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    version="1.0">
  
    <xsl:output method="xml" encoding="iso-8859-1"/>
    
    <!-- include generic parameters -->
	<xsl:include href="xsl/parameters.xsl"/>
    
    <xsl:template match="/">
        <!-- load the xml file --> 
        <xsl:variable name="bibfile" select="document($bibname)"/>
        <xsl:variable name="list_id" select="//id"/>
        <!-- look for all id in the xml file and output the corresponding bibtex entry -->
        <xsl:element name="bibtex:file">
            <xsl:attribute name="name"><xsl:value-of select="$bibfile/bibtex:file/@name"/></xsl:attribute>
            <xsl:attribute name="version"><xsl:value-of select='$biborb_xml_version'/></xsl:attribute>
            <xsl:copy>
                <xsl:apply-templates select="$bibfile//bibtex:entry[@id=$list_id]" />
            </xsl:copy>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="bibtex:groups">
        <xsl:element name="bibtex:{local-name()}">
            <xsl:for-each select=".//bibtex:group">
                <xsl:if test="node() != $group">
                    <xsl:element name="bibtex:group">
                        <xsl:value-of select="node()"/>
                    </xsl:element>
                </xsl:if>
            </xsl:for-each>
            <xsl:element name ="bibtex:group">
                <xsl:value-of select="$group"/>
            </xsl:element>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="bibtex:article | bibtex:book | bibtex:booklet | bibtex:conference | bibtex:inbook | bibtex:incollection | bibtex:inproceedings | bibtex:manual | bibtex:masterthesis | bibtex:misc | bibtex:phdthesis | bibtex:proceedings | bibtex:techreport | bibtex:unpublished">
        <xsl:element name="bibtex:{local-name()}">
            <xsl:choose>
                <xsl:when test="count(.//bibtex:groups)!=0">
                    <xsl:apply-templates select="./*"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:apply-templates select="./*"/>
                    <xsl:element name="bibtex:groups">
                        <xsl:element name="bibtex:group">
                            <xsl:value-of select="$group"/>
                        </xsl:element>
                    </xsl:element>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>
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