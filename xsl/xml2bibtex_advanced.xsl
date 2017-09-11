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
 * File: xml2bibtex.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *   Transform the XML bibentry in a true bib entry.
 *
-->
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"> 

    <xsl:output method="text" encoding="iso-8859-1"/>
  
    <!-- include generic parameters -->
	<xsl:include href="xsl/parameters.xsl"/>

    <xsl:template match="/bibtex:file">
        <xsl:text> </xsl:text>
        <xsl:choose>
            <xsl:when test="$id != ''">
                <xsl:apply-templates select="//bibtex:entry[@id=$id]/bibtex:*"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select=".//bibtex:entry/bibtex:*"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="bibtex:*">
        @<xsl:value-of select="local-name()"/>{<xsl:value-of select="../@id"/>,
        <xsl:for-each select="*">
            <xsl:variable name="currentfield" select="concat('.',local-name(),'.')"/>
            <xsl:if test="contains($fields_to_export,$currentfield)">
                <xsl:choose>
                    <xsl:when test="local-name() = 'groups'">
                        groups = {<xsl:for-each select="bibtex:group">
                            <xsl:value-of select="."/>
                            <xsl:if test="position() != last()">,</xsl:if>
                        </xsl:for-each>}
                    </xsl:when><xsl:otherwise><xsl:value-of select="local-name()"/> = {<xsl:value-of select="node()"/>}</xsl:otherwise>
                </xsl:choose>
                <xsl:if test="position() != last()">,
                </xsl:if>
            </xsl:if>
        </xsl:for-each>
}
    </xsl:template>
</xsl:stylesheet>