<?xml version="1.0" encoding="iso-8859-1" ?>
<!--
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
-->
<!--
 * File: extract_field_values.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *  
 *
 *
-->

<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    version="1.0">
  
    <xsl:output method="text" encoding="iso-8859-1"/>
	
	<!-- include generic parameters -->
	<xsl:include href="xsl/parameters.xsl"/>

	<xsl:template match="/">
        <xsl:choose>
            <!-- Extract year -->
            <xsl:when test="$field = 'year'">
                <xsl:for-each select=".//bibtex:year">
                    <xsl:sort select="node()" data-type="number"/>
                    <xsl:value-of select='node()[not(.=following::bibtex:year)]'/><xsl:if test='position()!=last()'>|</xsl:if>
                </xsl:for-each>
            </xsl:when>
            <!-- extract series -->
            <xsl:when test="$field='series'">
                <xsl:for-each select=".//bibtex:series">
                    <xsl:sort select="node()" data-type="text"/>
                    <xsl:value-of select='node()[not(.=following::bibtex:series)]'/><xsl:if test='position()!=last()'>|</xsl:if>
                </xsl:for-each>
            </xsl:when>
            <!-- extract journal -->
            <xsl:when test="$field='journal'">
                <xsl:for-each select=".//bibtex:journal">
                    <xsl:sort select="node()" data-type="text"/>
                    <xsl:value-of select='node()[not(.=following::bibtex:journal)]'/><xsl:if test='position()!=last()'>|</xsl:if>
                </xsl:for-each>
            </xsl:when>
            <!-- extract groups -->
            <xsl:when test="$field='group'">
                <xsl:for-each select=".//bibtex:group">
                    <xsl:sort select="node()" data-type="text"/>
                    <xsl:value-of select='node()[not(.=following::bibtex:group)]'/><xsl:if test='position()!=last()'>|</xsl:if>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <xsl:for-each select=".//*[local-name()=$field]">
                    <xsl:value-of select='node()'/><xsl:if test='position()!=last()'>|</xsl:if>
                </xsl:for-each>
            </xsl:otherwise>
        </xsl:choose>
	</xsl:template>

	
</xsl:stylesheet>