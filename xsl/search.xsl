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
 * File: search.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *    search and display result
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    version="1.0"> 
   
    <xsl:output method="xml" encoding="iso-8859-1"/>
    
	<!-- include generic parameters -->
	<xsl:include href="xsl/parameters.xsl"/>
    
    <xsl:param name="thesearch">
        <xsl:value-of select="translate($search,$ucletters,$lcletters)"/>
    </xsl:param>
  
    <xsl:template match="/bibtex:file">
		<xsl:element name="bibtex:file">
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="bibtex:entry">
        <!-- search entries matching the query -->
		<xsl:variable name="author_val">
			<xsl:value-of select="translate(.//bibtex:author,$ucletters,$lcletters)"/>
		</xsl:variable>
		<xsl:variable name="title_val">
			<xsl:value-of select="translate(.//bibtex:title,$ucletters,$lcletters)"/>
		</xsl:variable>
		<xsl:variable name="keywords_val">
			<xsl:value-of select="translate(.//bibtex:keywords,$ucletters,$lcletters)"/>
		</xsl:variable>
		<xsl:variable name="journal_val">
			<xsl:value-of select="translate(.//bibtex:journal,$ucletters,$lcletters)"/>
		</xsl:variable>
		<xsl:variable name="editor_val">
			<xsl:value-of select="translate(.//bibtex:editor,$ucletters,$lcletters)"/>
		</xsl:variable>
		<xsl:variable name="year_val">
			<xsl:value-of select="translate(.//bibtex:year,$ucletters,$lcletters)"/>
		</xsl:variable>
		<xsl:choose>
			<xsl:when test="$author and contains($author_val,$thesearch)">
				<xsl:element name="bibtex:entry">
					<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
					<xsl:copy>
						<xsl:apply-templates/>
					</xsl:copy>
				</xsl:element>
			</xsl:when>
			<xsl:when test="$title and contains($title_val,$thesearch)">
				<xsl:element name="bibtex:entry">
					<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
					<xsl:copy>
						<xsl:apply-templates/>
					</xsl:copy>
				</xsl:element>
			</xsl:when>
			<xsl:when test="$keywords and contains($keywords_val,$thesearch)">
				<xsl:element name="bibtex:entry">
					<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
					<xsl:copy>
						<xsl:apply-templates/>
					</xsl:copy>
				</xsl:element>
			</xsl:when>
			<xsl:when test="$journal and contains($journal_val,$thesearch)">
				<xsl:element name="bibtex:entry">
					<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
					<xsl:copy>
						<xsl:apply-templates/>
					</xsl:copy>
				</xsl:element>
			</xsl:when>
			<xsl:when test="$editor and contains($editor_val,$thesearch)">
				<xsl:element name="bibtex:entry">
					<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
					<xsl:copy>
						<xsl:apply-templates/>
					</xsl:copy>
				</xsl:element>
			</xsl:when>
			<xsl:when test="$year and contains($year_val,$thesearch)">
				<xsl:element name="bibtex:entry">
					<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
				    <xsl:apply-templates/>
				</xsl:element>
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
