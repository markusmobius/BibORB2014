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
 * File: update_xml.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *   Update entries.
 *   the bibname.xml file is updated according to the xml file provided.
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
        <!-- load the xml file into a variable --> 
        <xsl:variable name="bibfile" select="document($bibname)" />
        <xsl:variable name="original_id" select ="$bibfile//@id"/>
        <xsl:variable name="updated_id" select="//@id"/>
        <xsl:element name="bibtex:file">
            <xsl:attribute name="name"><xsl:value-of select="$bibfile/bibtex:file/@name"/></xsl:attribute>
            <xsl:attribute name="version"><xsl:value-of select='$biborb_xml_version'/></xsl:attribute>
            <xsl:copy>
                <!-- copy all unchanged elements -->
                <xsl:apply-templates select="$bibfile//bibtex:entry[not(@id = $updated_id)]"/>
                <!-- copy updated elements -->
                <xsl:apply-templates select="//bibtex:entry"/>
            </xsl:copy>
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