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
 * File: model.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *    Transform bibtex fields into a nice html form for edition
 *
-->
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  
  <xsl:output method="xml" encoding="iso-8859-1"/>
  
  <xsl:param name="typeentry"/>
  
  <xsl:template match="/entrylist">
    <fieldset class='clean'>
      <input name="add_type" type="hidden" value="{$typeentry}"/>
    </fieldset>
    
    <!-- Required BibTeX fields -->        
    <fieldset class="required" id="required_ref">
      <legend><xsl:processing-instruction name="php">echo msg("BIBORB_OUTPUT_REQUIRED_FIELDS");</xsl:processing-instruction></legend>
      <xsl:for-each select="entry[@type=$typeentry]/required/*">
        <xsl:choose>
          <!-- an alternative : or -->
          <xsl:when test="name() = 'alternative'">
            <xsl:variable name="cpt" select="count(*)"/>
            <xsl:for-each select="*">
              <label title='{name()}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="name()"/>");</xsl:processing-instruction>:</label>
              <input name="{name(.)}"/><br/>
              <xsl:if test="not(position() = $cpt)">
                <span style='color:black;font-weight:normal;font-size:x-small;'>or/and</span><br/>
              </xsl:if>
            </xsl:for-each>
          </xsl:when>
          <!-- an exalternative: xor -->
          <xsl:when test="name() = 'exalternative'">
            <xsl:variable name="cpt" select="count(*)"/>
            <xsl:for-each select="*">
              <label title='{name()}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="name()"/>");</xsl:processing-instruction>:</label>
              <input name="{name(.)}"/><br/>
              <xsl:if test="not(position() = $cpt)">
                <span style='color:black;font-weight:normal;font-size:x-small;'>or</span><br/>
              </xsl:if>
            </xsl:for-each>
          </xsl:when>
          <!-- all other fields -->
          <xsl:otherwise>
            <label title='{name()}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="name()"/>");</xsl:processing-instruction>:</label>
            <input name="{name(.)}" /><br/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:for-each>
    </fieldset>
    
    <fieldset class="optional" id="optional_ref">
      <legend><xsl:processing-instruction name="php">echo msg("BIBORB_OUTPUT_OPTIONAL_FIELDS");</xsl:processing-instruction></legend>
      <xsl:for-each select="entry[@type=$typeentry]/optional/*">
        <xsl:choose>
          <!-- an alternative: or -->
          <xsl:when test="name() = 'alternative'">
            <xsl:variable name="cpt" select="count(*)"/>
            <xsl:for-each select="*">
              <label title='{name()}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="name()"/>");</xsl:processing-instruction>:</label>
              <input name="{name(.)}"/><br/>
              <xsl:if test="not(position() = $cpt)">
                <span style='color:black;font-weight:normal;font-size:x-small;'>or/and</span><br/>
              </xsl:if>
            </xsl:for-each>
          </xsl:when>
          <!-- an exalternative : xor -->
          <xsl:when test="name() = 'exalternative'">
            <xsl:variable name="cpt" select="count(*)"/>
            <xsl:for-each select="*">
              <label title='{name()}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="name()"/>");</xsl:processing-instruction>:</label>
              <input name="{name(.)}"/><br/>
              <xsl:if test="not(position() = $cpt)">
                <span style='color:black;font-weight:normal;font-size:x-small;'>or</span><br/>
              </xsl:if>
            </xsl:for-each>
          </xsl:when>
          <!-- all other fields -->
          <xsl:otherwise>
            <label title='{name()}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="name()"/>");</xsl:processing-instruction>:</label>
            <input name="{name(.)}"/><br/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:for-each>
    </fieldset>
    
    <fieldset class="additional" id="additional_ref">
      <legend><xsl:processing-instruction name="php">echo msg("BIBORB_OUTPUT_ADDITIONAL_FIELDS");</xsl:processing-instruction></legend>
      <xsl:for-each select="entry[@type=$typeentry]/additional/*[local-name(.) != 'read' and local-name(.) != 'own']">
        <label title='{name()}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="name()"/>");</xsl:processing-instruction>:
          <xsl:if test="name() = 'website'">http://</xsl:if>
        </label>
        <xsl:choose>
          <xsl:when test="name() = 'abstract' or name()='longnotes'">
            <textarea name="{name(.)}" rows="5" cols="80"><xsl:text> </xsl:text></textarea><br/>
          </xsl:when>
          <xsl:when test="name() = 'url' or name() = 'urlzip' or name() = 'pdf'">
            <input name="up_{name()}" type='file' />
          </xsl:when>
          <xsl:when test="name() = 'groups'">
            <input name="{name(.)}"/><br/>
            <label title='{name()}'><xsl:text> </xsl:text></label>
            <span style='color:black;font-weight:normal;'><xsl:processing-instruction name="php">echo msg("BIBORB_OUTPUT_ADD_A_GROUP");</xsl:processing-instruction></span>#XHTMLGROUPSLIST
            <br/>
          </xsl:when>
          <xsl:otherwise>
            <input name="{name(.)}"/><br/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:for-each>
      <!-- ReadStatus and ownership -->
      <label title='read'>
        <xsl:processing-instruction name="php">echo msg("Read Status");</xsl:processing-instruction>:
      </label>
      #XHTMLREADSTATUS <br/>
      <label title='own'>
        <xsl:processing-instruction name="php">echo msg("Ownership");</xsl:processing-instruction>:
      </label>
      #XHTMLOWNERSHIP <br/>
    </fieldset>
  </xsl:template>
  
 
</xsl:stylesheet>
