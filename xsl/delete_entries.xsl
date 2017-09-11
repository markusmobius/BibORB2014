<?xml version="1.0" encoding="iso-8859-1"?>
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
 * File: delete_entries.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *    Delete entries in the bibliography
 *
-->
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    version="1.0">

    <xsl:param name="id"/>
    <xsl:param name="biborb_xml_version"/>

    <xsl:output method="xml" indent="yes" encoding="iso-8859-1"/>

    <!-- something i do not understand, copy-of do not copy namespace-->
    <!-- so i do it manually :( -->
    <xsl:template match="/">
        <xsl:element name="bibtex:file">
            <xsl:attribute name="name"><xsl:value-of select="bibtex:file/@name"/></xsl:attribute>
            <xsl:attribute name="version"><xsl:value-of select='$biborb_xml_version'/></xsl:attribute>
            <xsl:for-each select="//bibtex:entry[@id!=$id]">
                <xsl:call-template name="entry"/>
            </xsl:for-each>
        </xsl:element>
    </xsl:template>

    <xsl:template name="entry">
        <xsl:element name="bibtex:entry">
            <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
            <xsl:element name="bibtex:{local-name(./*[position() = 1])}">
                <xsl:for-each select="./*[position() = 1]/*">
                    <xsl:choose>
                        <xsl:when test="local-name()!='groups'">
                            <xsl:element name="bibtex:{local-name(.)}"><xsl:value-of select="current()"/></xsl:element>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:element name="bibtex:groups">
                                <xsl:for-each select="*">
                                    <xsl:element name="bibtex:group"><xsl:value-of select="current()"/></xsl:element>
                                </xsl:for-each>
                            </xsl:element>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:for-each>
            </xsl:element>
        </xsl:element>
    </xsl:template>

</xsl:stylesheet>
