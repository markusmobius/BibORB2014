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
 * File: xml2htmledit.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *  Create a form to edit an entry
 *
-->
<xsl:stylesheet
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:bibtex="http://bibtexml.sf.net/"
  exclude-result-prefixes="bibtex"
  version="1.0">
  
    <xsl:output method="xml" encoding="iso-8859-1"/>

    <!-- include generic parameters -->
    <xsl:include href="xsl/parameters.xsl"/>
    
    <xsl:param name="update"/>
    <xsl:param name="modelfile"/>
    
    <xsl:template match="/">
      
      <!-- bibtex models -->
      <xsl:variable name="model" select="document($modelfile)"/>
      <!-- store the entry in a variable -->
      <xsl:variable name="entry" select="//bibtex:entry"/>
      <!-- get the entry's type -->
      <xsl:variable name="type" select="local-name($entry/*[position()=1])"/>
      <!-- date Added -->
      <xsl:variable name="dateAdded" select="//bibtex:dateAdded"/>
      <!-- Display required fields -->
      <fieldset id="required_ref" class="required">
        
        <input type="hidden" name="id" value="{$id}"/>
        <input type="hidden" name="type_ref" value="{$type}"/>
        <input type="hidden" name="dateAdded" value="{$dateAdded}"/>
        <legend><xsl:processing-instruction name="php">echo msg("BIBORB_OUTPUT_REQUIRED_FIELDS");</xsl:processing-instruction></legend>
        <!-- Process all required entries -->
        <xsl:for-each select="$model//entry[@type=$type]/required/*">
          <xsl:choose>
            <!-- An alternative : or -->
            <xsl:when test="name() = 'alternative'">
              <xsl:variable name="cpt" select="count(*)"/>
              <xsl:for-each select='*'>
                <xsl:variable name="field" select="name()"/>
                <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
                <label title='{$field}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="$field"/>");</xsl:processing-instruction>:</label>
                <input name="{name()}" value="{$val}" /><br/>
                <xsl:if test="not(position() = $cpt)">
                  <span style='color:black;font-weight:normal;font-size:small;'>or/and</span><br/>
                </xsl:if>
              </xsl:for-each>
            </xsl:when>
            <!-- An exalternative : xor -->
            <xsl:when test="name() = 'exalternative'">
              <xsl:variable name="cpt" select="count(*)"/>
              <xsl:for-each select='*'>
                <xsl:variable name="field" select="name()"/>
                <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>                                            
                <label title='{$field}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="$field"/>");</xsl:processing-instruction>:</label>
                <input name="{name()}" value="{$val}" /><br/>
                <xsl:if test="not(position() = $cpt)">
                  <span style='color:black;font-weight:normal;font-size:small;'>or</span><br/>
                </xsl:if>
              </xsl:for-each>
            </xsl:when>
            <xsl:when test="name() != 'id'">
              <!-- any other case -->
              <xsl:variable name="field" select="name()"/>
              <label title='{$field}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="name()"/>");</xsl:processing-instruction>:</label>
              <xsl:variable name="val">
                <xsl:value-of select="$entry//*[local-name() = $field]"/>
              </xsl:variable>
              <input name="{name()}" value="{$val}" /><br/>
            </xsl:when>
          </xsl:choose>
        </xsl:for-each>
      </fieldset>
      
      
      <!-- Optional fields -->
      <fieldset id="optional_ref" class="optional">
        <legend><xsl:processing-instruction name="php">echo msg("BIBORB_OUTPUT_OPTIONAL_FIELDS");</xsl:processing-instruction></legend>
        <xsl:for-each select="$model//entry[@type=$type]/optional/*">
          <xsl:choose>
            <!-- an alternative : or -->
            <xsl:when test="local-name() = 'alternative'">
              <xsl:variable name="cpt" select="count(*)"/>
              <xsl:for-each select='*'>
                <xsl:variable name="field" select="name()"/>
                <label title='{$field}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="$field"/>");</xsl:processing-instruction>:</label>
                <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
                <input name="{name()}" value='{$val}' /><br/>
                <xsl:if test="not(position() = $cpt)">
                  <span style='color:black;font-weight:normal;font-size:small;'>or/and</span><br/>
                </xsl:if>
              </xsl:for-each>
            </xsl:when>
            <!-- an exalternative : xor -->
            <xsl:when test="local-name() = 'exalternative'">
              <xsl:variable name="cpt" select="count(*)"/>
              <xsl:for-each select='*'>
                <xsl:variable name="field" select="name()"/>
                <label title='{$field}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="$field"/>");</xsl:processing-instruction>:</label>
                <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
                <input name="{name()}" value='{$val}' /><br/>
                <xsl:if test="not(position() = $cpt)">
                  <span style='color:black;font-weight:normal;font-size:small;'>or/and</span><br/>
                </xsl:if>
              </xsl:for-each>
            </xsl:when>
            <!-- any other field -->
            <xsl:otherwise>
              <xsl:variable name="field" select="name()"/>
              <label title='{$field}'><xsl:processing-instruction name="php">echo msg("<xsl:value-of select="$field"/>");</xsl:processing-instruction>:</label>
              <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
              <input name="{name()}" value='{$val}' /><br/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </fieldset>
      
      <!-- Additional fields -->
      <fieldset id="additional_ref" class="additional">
        <legend><xsl:processing-instruction name="php">echo msg("BIBORB_OUTPUT_ADDITIONAL_FIELDS");</xsl:processing-instruction></legend>
        <xsl:for-each select="$model//entry[@type=$type]/additional/*[local-name(.) != 'read' and local-name(.) != 'own']">
          <xsl:variable name="field" select="name()"/>
          <label title='{$field}'>
            <xsl:processing-instruction name="php">echo msg("<xsl:value-of select="name()"/>");</xsl:processing-instruction>:
            <xsl:if test="name() = 'website'"><span style='font-size:9px;'>(http://)</span></xsl:if>
          </label>
          <xsl:choose>
            <!-- abstract or longnotes -->
            <xsl:when test="$field = 'abstract' or $field='longnotes'">
              <textarea name="{name()}">
                <xsl:value-of select="$entry//*[local-name() = $field]"/>
                <xsl:text> </xsl:text>
              </textarea>
            </xsl:when>
            <!-- url, urlzip pdf -->
            <xsl:when test="$field = 'url' or $field = 'urlzip' or $field = 'pdf'">
              <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
              <table style='font-size:x-small;font-weight:normal;color:black;width:80%'>
                <tr>
                  <td>
                    <xsl:if test="$field = 'urlzip' and $val ">
                      <a href="./bibs/{$bibname}/papers/{$val}">
                        <img src="./data/images/tar.png" alt="ps.gz"/>
                      </a>
                    </xsl:if>
                    <xsl:if test="$field = 'pdf' and $val">
                      <a href="./bibs/{$bibname}/papers/{$val}">
                        <img src="./data/images/pdf-document.png" aldt="pdf"/>
                      </a>
                    </xsl:if>
                    <xsl:if test="$field = 'url' and $val">
                      <a href="./bibs/{$bibname}/papers/{$val}">
                        <img src="./data/images/stock-book-16.png" aldt="url"/>
                      </a>
                    </xsl:if>
                    <xsl:text> </xsl:text>
                    <xsl:value-of select="$val"/>
                  </td>
                </tr>
                <tr>
                  <td><input name="up_{name()}" type='file' />
                  </td>
                </tr>
              </table>
              <input type="hidden" name="{name()}" value="{$val}"/>
            </xsl:when>
            <!-- groups -->
            <xsl:when test="$field = 'groups'">
              <xsl:variable name="val">
                <xsl:for-each select="$entry//*[local-name() = 'group']">
                  <xsl:value-of select="current()"/>
                  <xsl:if test="position() != last()">,</xsl:if>
                </xsl:for-each>
              </xsl:variable>
              <input name="groups" value="{$val}" /><br/>
              <label><xsl:text> </xsl:text></label>
              <span style='color:black;font-weight:normal;font-size:small;'><xsl:processing-instruction name="php">echo msg("BIBORB_OUTPUT_ADD_A_GROUP");</xsl:processing-instruction></span>#XHTMLGROUPSLIST
              <br/>
            </xsl:when>
            <!-- any other fields -->
            <xsl:otherwise>
              <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
              <input name="{name()}" value='{$val}' /><br/>
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
      <br/>
    </xsl:template>
    
</xsl:stylesheet>
