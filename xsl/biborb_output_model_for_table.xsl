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
 * File: biborb_output_model_for_table.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *  This file describe the transformation of a bibtex entry into HTML.
 *  Several parameters are taken into account to create an output.
 *	
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    exclude-result-prefixes="bibtex"
    version="1.0">
        
	<!-- This is the template to apply to a bibtex entry -->
    <xsl:template match="bibtex:entry">
	
		<!--
			Look if the id is present in the basket.
			If true, $inbasket = inbasket. For latter use.
		-->
		<xsl:variable name="theidp" select="concat('.',@id,'.')"/>
		<xsl:variable name="inbasket">
			<xsl:choose>
			 <xsl:when test="contains($basketids,$theidp)">inbasket</xsl:when>
			 <xsl:otherwise>notinbasket </xsl:otherwise>
            </xsl:choose>
		</xsl:variable>
	
		<!-- 
			The first row contains the bibtex ID and buttons/links to manage
			the entry.
			A different style is applied if the entry is in the basket
		-->
        <tr class="{$inbasket}" id="{@id}">
            <td class="bibtex_start">
				<!-- The bibtex entry -->
                <div class="bibtex_key">
                    <xsl:value-of select="@id"/>
                </div>
				
				<!-- Various links (abstract,url,urlzip,pdf,website,linl,bibtex -->
				<div class="bibtex_misc">
<!--                    <xsl:value-of select=".//bibtex:year"/>-->
					<!-- 
						If an abstract is present and we do not want to see 
						the abstract by default we display a small button
					-->
					<xsl:if test=".//bibtex:abstract and $abstract != 'true'">
						<xsl:call-template name="abstract">
							<xsl:with-param name="id" select="@id"/>
						</xsl:call-template>
					</xsl:if>
					<!-- url -->
					<xsl:apply-templates select=".//bibtex:url"/>
                    <!-- ad_url -->
					<xsl:apply-templates select=".//bibtex:ad_url"/>
					<!-- urlzip -->
					<xsl:apply-templates select=".//bibtex:urlzip"/>
                    <!-- ad_urlzip -->
					<xsl:apply-templates select=".//bibtex:ad_urlzip"/>
					<!-- pdf -->
					<xsl:apply-templates select=".//bibtex:pdf"/>
                    <!-- ad_pdf -->
					<xsl:apply-templates select=".//bibtex:ad_pdf"/>
					<!-- website -->
					<xsl:apply-templates select=".//bibtex:website"/>
					<!-- link -->
					<xsl:apply-templates select=".//bibtex:link"/>
					<!-- bibtex -->
					<xsl:call-template name="link2bibtex">
						<xsl:with-param name="id" select="@id"/>
					</xsl:call-template>
                    <!-- longnotes. Same behavior as abstract. -->
                    <xsl:if test=".//bibtex:longnotes">
						<xsl:call-template name="longnotes">
							<xsl:with-param name="id" select="@id"/>
						</xsl:call-template>
					</xsl:if>
				</div>
				<!-- end of the div 'bibtex_misc' -->
				
				<!-- Various command : edit, delete, add/remove from basket -->
				<div class="command">
					
                    <!-- Shelf mode on -->
                    <xsl:if test="$shelf-mode">
                        <!-- Own = yes -->
                        <xsl:if test=".//bibtex:own='own'">
                            <xsl:if test="$display_images">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_ownership&amp;ownership=&amp;id={@id}&amp;{$extra_get_param}#{@id}">
                                    <img src="data/images/{$own-image}" alt='BIBORB_OUTPUT_OWN_ALT' title='BIBORB_OUTPUT_OWN_TITLE' />
                                </a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <img src="data/images/{$own-image}" alt='BIBORB_OUTPUT_OWN_ALT' title='BIBORB_OUTPUT_OWN_TITLE' />
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                            <xsl:if test="$display_text">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a class="bibtex_action" href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_ownership&amp;ownership=&amp;id={@id}&amp;{$extra_get_param}#{@id}" title='BIBORB_OUTPUT_OWN_TITLE'>BIBORB_OUTPUT_OWN_ALT</a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <span class='shelf_text' title='BIBORB_OUTPUT_OWN_TITLE'>BIBORB_OUTPUT_OWN_ALT</span>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                        </xsl:if>
                    
                        <!-- Own = borrow -->
                        <xsl:if test=".//bibtex:own='borrowed'">
                            <xsl:if test="$display_images">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_ownership&amp;ownership=buy&amp;id={@id}&amp;{$extra_get_param}#{@id}">
                                    <img src="data/images/{$borrow-image}" alt='BIBORB_OUTPUT_BORROW_ALT' title='BIBORB_OUTPUT_BORROW_TITLE'/>
                                </a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <img src="data/images/{$borrow-image}" alt='BIBORB_OUTPUT_BORROW_ALT' title='BIBORB_OUTPUT_BORROW_TITLE'/>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                            <xsl:if test="$display_text">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a class="bibtex_action" href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_ownership&amp;ownership=buy&amp;id={@id}&amp;{$extra_get_param}#{@id}" title='BIBORB_OUTPUT_BORROW_TITLE'>BIBORB_OUTPUT_BORROW_ALT</a>
                                </xsl:when>
                                <xsl:otherwise>
                                <span class='shelf_text' title='BIBORB_OUTPUT_BORROW_TITLE'>BIBORB_OUTPUT_BORROW_ALT</span>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                        </xsl:if>
                        
                        <!-- Own = buy -->
                        <xsl:if test=".//bibtex:own='buy'">
                            <xsl:if test="$display_images">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_ownership&amp;ownership=own&amp;id={@id}&amp;{$extra_get_param}#{@id}">
                                    <img src="data/images/{$buy-image}" alt='BIBORB_OUTPUT_BUY_ALT' title='BIBORB_OUTPUT_BUY_TITLE'/></a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <img src="data/images/{$buy-image}" alt='BIBORB_OUTPUT_BUY_ALT' title='BIBORB_OUTPUT_BUY_TITLE'/>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                            <xsl:if test="$display_text">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a class="bibtex_action" href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_ownership&amp;ownership=own&amp;id={@id}&amp;{$extra_get_param}#{@id}" title='BIBORB_OUTPUT_BUY_TITLE'>BIBORB_OUTPUT_BUY_ALT</a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <span class="shelf_text" title='BIBORB_OUTPUT_BUY_TITLE'>BIBORB_OUTPUT_BUY_ALT</span>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                        </xsl:if>
                        
                        <!-- Not own -->
                        <xsl:if test="not(normalize-space(.//bibtex:own)) or .//bibtex:own='notown'">
                            <xsl:if test="$display_images">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_ownership&amp;ownership=borrowed&amp;id={@id}&amp;{$extra_get_param}#{@id}">
                                    <img src="data/images/{$notown-image}" alt='BIBORB_OUTPUT_NOTOWN_ALT' title='BIBORB_OUTPUT_NOTOWN_TITLE'/></a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <img src="data/images/{$notown-image}" alt='BIBORB_OUTPUT_NOTOWN_ALT' title='BIBORB_OUTPUT_NOTOWN_TITLE'/>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                            <xsl:if test="$display_text">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a class="bibtex_action" href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_ownership&amp;ownership=borrowed&amp;id={@id}&amp;{$extra_get_param}#{@id}" title='BIBORB_OUTPUT_NOTOWN_TITLE'>BIBORB_OUTPUT_NOTOWN_ALT</a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <span class="shelf_mode" title='BIBORB_OUTPUT_NOTOWN_TITLE'>BIBORB_OUTPUT_NOTOWN_ALT</span>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                        </xsl:if>
                        
                        
                        <!-- Read = yes -->
                        <xsl:if test=".//bibtex:read='read'">
                            <xsl:if test="$display_images">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_readstatus&amp;readstatus=notread&amp;id={@id}&amp;{$extra_get_param}#{@id}">
                                    <img src="data/images/{$read-image}" alt='BIBORB_OUTPUT_READ_ALT' title='BIBORB_OUTPUT_READ_TITLE'/>
                                </a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <img src="data/images/{$read-image}" alt='BIBORB_OUTPUT_READ_ALT' title='BIBORB_OUTPUT_READ_TITLE'/>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                            <xsl:if test="$display_text">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a class="bibtex_action" href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_readstatus&amp;readstatus=notread&amp;id={@id}&amp;{$extra_get_param}#{@id}" title='BIBORB_OUTPUT_READ_TITLE'>BIBORB_OUTPUT_READ_ALT</a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <span class="shelf_mode" title='BIBORB_OUTPUT_READ_TITLE'>BIBORB_OUTPUT_READ_ALT</span>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                        </xsl:if>
                        
                        <!-- Read = next -->
                        <xsl:if test=".//bibtex:read='readnext'">
                            <xsl:if test="$display_images">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_readstatus&amp;readstatus=read&amp;id={@id}&amp;{$extra_get_param}#{@id}">
                                    <img src="data/images/{$readnext-image}" alt='BIBORB_OUTPUT_READNEXT_ALT' title='BIBORB_OUTPUT_READNEXT_TITLE'/>
                                </a>
                                </xsl:when>
                                <xsl:otherwise>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                            <xsl:if test="$display_text">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a class="bibtex_action" href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_readstatus&amp;readstatus=read&amp;id={@id}&amp;{$extra_get_param}#{@id}" title='BIBORB_OUTPUT_READNEXT_TITLE'>BIBORB_OUTPUT_READNEXT_ALT</a>
                                </xsl:when>
                                <xsl:otherwise>
                                <span class="shelf_mode" title='BIBORB_OUTPUT_READNEXT_TITLE'>BIBORB_OUTPUT_READNEXT_ALT</span>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                        </xsl:if>
                        
                        <!-- Read = no -->
                        <xsl:if test="not(normalize-space(.//bibtex:read)) or .//bibtex:read='notread'">
                            <xsl:if test="$display_images">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_readstatus&amp;readstatus=readnext&amp;id={@id}&amp;{$extra_get_param}#{@id}">
                                    <img src="data/images/{$notread-image}" alt='BIBORB_OUTPUT_NOTREAD_ALT' title='BIBORB_OUTPUT_NOTREAD_TITLE'/>
                                </a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <img src="data/images/{$notread-image}" alt='BIBORB_OUTPUT_NOTREAD_ALT' title='BIBORB_OUTPUT_NOTREAD_TITLE'/>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                            <xsl:if test="$display_text">
                            <xsl:choose>
                                <xsl:when test="$can_modify">
                                <a class="bibtex_action" href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_readstatus&amp;readstatus=readnext&amp;id={@id}&amp;{$extra_get_param}#{@id}" title='BIBORB_OUTPUT_READ_TITLE'>BIBORB_OUTPUT_NOTREAD_ALT</a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <span class="shelf_mode" href="./bibindex.php?mode={$bibindex_mode}&amp;action=update_readstatus&amp;readstatus=next&amp;id={@id}&amp;{$extra_get_param}#{@id}" title='BIBORB_OUTPUT_READ_TITLE'>BIBORB_OUTPUT_NOTREAD_ALT</span>
                                </xsl:otherwise>
                            </xsl:choose>
                            </xsl:if>
                        </xsl:if>
                    </xsl:if>
                        
                    <xsl:if test="$can_modify">
						<!-- Edit action -->
						<!-- display images if necessary: $display_images!=null -->
						<xsl:if test="$display_images">
							<a href="./bibindex.php?mode=update&amp;id={@id}">
								<img src="data/images/{$edit-image}" alt='BIBORB_OUTPUT_EDIT_ALT' title='BIBORB_OUTPUT_EDIT_TITLE'/>
							</a>
						</xsl:if>
						<!-- display text if necessary: $display_text != null -->
						<xsl:if test="$display_text">
							<a class="bibtex_action" href="./bibindex.php?mode=update&amp;id={@id}" title='BIBORB_OUTPUT_EDIT_TITLE'>
								BIBORB_OUTPUT_EDIT_ALT
							</a>
						</xsl:if>
                    </xsl:if>
						
                    <xsl:if test="$can_delete">
						<!-- Delete action -->
						<xsl:if test="$display_images">
							<a href="./bibindex.php?mode={$bibindex_mode}&amp;id={@id}&amp;action=delete&amp;{$extra_get_param}">
								<img src="data/images/{$delete-image}" alt='BIBORB_OUTPUT_DELETE_ALT' title='BIBORB_OUTPUT_DELETE_TITLE' />
							</a>
						</xsl:if>
						<xsl:if test="$display_text">                        
							<a class="bibtex_action" href="./bibindex.php?mode={$bibindex_mode}&amp;id={@id}&amp;action=delete&amp;{$extra_get_param}" title='BIBORB_OUTPUT_DELETE_TITLE'>
								BIBORB_OUTPUT_DELETE_ALT
							</a>
						</xsl:if>
					</xsl:if>

				
					<!-- Dispay basket actions if needed ($basket!='no') -->
					<xsl:if test="$display_basket_actions != 'no'">
				
						<!-- if not present in basket, display the add action -->
						<xsl:if test="$display_basket_actions = '' and contains($inbasket,'notinbasket')">
							<xsl:if test="$display_images">
								<a href="./bibindex.php?mode={$bibindex_mode}&amp;action=add_to_basket&amp;id={@id}&amp;{$extra_get_param}#{@id}">
									<img src="data/images/{$add-basket-image}" alt='BIBORB_OUTPUT_ADD_BASKET_ALT' title='BIBORB_OUTPUT_ADD_BASKET_TITLE' />
								</a>
							</xsl:if>
							<xsl:if test="$display_text">
								<a class="basket_action" href="./bibindex.php?mode={$bibindex_mode}&amp;action=add_to_basket&amp;id={@id}&amp;{$extra_get_param}#{@id}" title='BIBORB_OUTPUT_ADD_BASKET_TITLE'>
									BIBORB_OUTPUT_ADD_BASKET_ALT
								</a>
							</xsl:if>
						</xsl:if>

						<!-- if present in basket display the remove action -->
						<xsl:if test="$display_basket_actions != '' or not( contains($inbasket,'notinbasket'))">
							<xsl:if test="$display_images">
								<a href="./bibindex.php?mode={$bibindex_mode}&amp;action=delete_from_basket&amp;id={@id}&amp;{$extra_get_param}#{@id}">
									<img src="data/images/{$remove-basket-image}" alt='BIBORB_OUTPUT_REMOVE_BASKET_ALT' title='BIBORB_OUTPUT_REMOVE_BASKET_TITLE' />
								</a>
							</xsl:if>
							<xsl:if test="$display_text">
								<a class="basket_action" href="./bibindex.php?mode={$bibindex_mode}&amp;action=delete_from_basket&amp;id={@id}&amp;{$extra_get_param}#{@id}" title='BIBORB_OUTPUT_REMOVE_BASKET_TITLE'>
									BIBORB_OUTPUT_REMOVE_BASKET_ALT
								</a>
							</xsl:if>
						</xsl:if>
					</xsl:if>
				</div>
				<!-- end of the div "command" -->
            </td>
        </tr>
		<!-- end of the first row -->
		
		
        <!-- 
			The second row contains the title of the article
		-->
        <tr>
            <td class="bibtex_title">
                <xsl:apply-templates select=".//bibtex:title"/>
            </td>
        </tr>
		
        <!-- 
			The third row contains the authors
		-->
        <tr>
            <td class="bibtex_author">
                <xsl:apply-templates select=".//bibtex:author"/>
            </td>
        </tr>
		
        <!-- 
			The fourth row contains the abstract
		-->
        <tr>
            <td class="bibtex_abstract">
                <xsl:call-template name="bibtex:abstract">
                    <xsl:with-param name="id" select="@id"/>
                </xsl:call-template>
            </td>
        </tr>

        <!-- 
			The fifth row contains the keywords
		-->
        <tr>
            <td class="bibtex_keywords">
                <xsl:apply-templates select=".//bibtex:keywords"/>
            </td>
        </tr>
        
        <!--
            7th row: longnotes
        -->
        <tr>
            <td class="bibtex_longnotes">
                <xsl:call-template name="bibtex:longnotes">
                    <xsl:with-param name="id" select="@id"/>
                </xsl:call-template>
            </td>
        </tr>
        
		<!-- a little trick to add a space between records -->
		<!-- waiting for the corresponding CSS trick :) -->
        <tr class="last"><td><p/></td></tr>
    </xsl:template>
    <!-- end of the template bibtex:entry -->
	
	<!--
		Template for the pdf field.
		Display a link(text/image) to the recorded pdf.
	-->
    <xsl:template match="bibtex:pdf">
        <xsl:variable name="link">
            ./bibs/<xsl:value-of select="$bibname"/>/papers/<xsl:value-of select="."/>
        </xsl:variable>
        <xsl:if test="$display_images">
            <a href="{$link}">
                <img src="data/images/{$pdf-image}" alt='BIBORB_OUTPUT_PDF_ALT' title='BIBORB_OUTPUT_PDF_ALT' />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="{$link}" title='BIBORB_OUTPUT_PDF_TITLE'>
                BIBORB_OUTPUT_PDF_ALT
            </a>
        </xsl:if>
    </xsl:template>
    
    <!--
		Template for the ad_pdf field.
		Display a link(text/image) to the recorded pdf link.
	-->
    <xsl:template match="bibtex:ad_pdf">
        <xsl:variable name="link"><xsl:value-of select="."/></xsl:variable>
        <xsl:if test="$display_images">
            <a href="{$link}">
                <img src="data/images/{$pdf-image-link}" alt='BIBORB_OUTPUT_PDF_ALT' title='BIBORB_OUTPUT_PDF_ALT' />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="{$link}" title='BIBORB_OUTPUT_PDF_TITLE'>
                BIBORB_OUTPUT_PDF_ALT
            </a>
        </xsl:if>
    </xsl:template>
    
	<!--
		Template for the url field.
		Display a link(text/image) to the recorded url (ps file).
	-->
    <xsl:template match="bibtex:url">
        <xsl:variable name="link">
            ./bibs/<xsl:value-of select="$bibname"/>/papers/<xsl:value-of select="."/>
        </xsl:variable>
        <xsl:if test="$display_images">
            <a href="{$link}">
                <img src="data/images/{$ps-image}" alt='BIBORB_OUTPUT_PS_ALT' title='BIBORB_OUTPUT_PS_TITLE'/>
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="{$link}" title='BIBORB_OUTPUT_PS_TITLE'>
                BIBORB_OUTPUT_PS_ALT
            </a>
        </xsl:if>
    </xsl:template>
    
    <!--
		Template for the ad_url field.
		Display a link(text/image) to the recorded url (ps file).
	-->
    <xsl:template match="bibtex:ad_url">
        <xsl:variable name="link">
            <xsl:value-of select="."/>
        </xsl:variable>
        <xsl:if test="$display_images">
            <a href="{$link}">
                <img src="data/images/{$ps-image-link}" alt='BIBORB_OUTPUT_PS_ALT' title='BIBORB_OUTPUT_PS_TITLE'/>
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="{$link}" title='BIBORB_OUTPUT_PS_TITLE'>
                BIBORB_OUTPUT_PS_ALT
            </a>
        </xsl:if>
    </xsl:template>
    
	<!--
		Template for the urlzip field.
		Display a link(text/image) to the recorded urlzip (ps.gz file).
	-->
    <xsl:template match="bibtex:ad_urlzip">
        <xsl:variable name="link">
            <xsl:value-of select="."/>
        </xsl:variable>
        <xsl:if test="$display_images">
            <a href="{$link}">
                <img src="data/images/{$ps.gz-image-link}" alt='BIBORB_OUTPUT_PSGZ_ALT' title='BIBORB_OUTPUT_PSGZ_TITLE'/>
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="{$link}" title='BIBORB_OUTPUT_PSGZ_TITLE'>
                BIBORB_OUTPUT_PSGZ_ALT
            </a>
        </xsl:if>
    </xsl:template>
    
    <!--
		Template for the urlzip field.
		Display a link(text/image) to the recorded urlzip (ps.gz file).
	-->
    <xsl:template match="bibtex:urlzip">
        <xsl:variable name="link">
            ./bibs/<xsl:value-of select="$bibname"/>/papers/<xsl:value-of select="."/>
        </xsl:variable>
        <xsl:if test="$display_images">
            <a href="{$link}">
                <img src="data/images/{$ps.gz-image}" alt='BIBORB_OUTPUT_PSGZ_ALT' title='BIBORB_OUTPUT_PSGZ_TITLE'/>
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="{$link}" title='BIBORB_OUTPUT_PSGZ_TITLE'>
                BIBORB_OUTPUT_PSGZ_ALT
            </a>
        </xsl:if>
    </xsl:template>
    
    
	<!--
		Template for the website field.
		Display a link(text/image) to the recorded website (internet).
	-->
    <xsl:template match="bibtex:website">
        <xsl:if test="$display_images">
            <a href="http://{node()}">
                <img src="data/images/{$url-image}" alt='BIBORB_OUTPUT_WEBSITE_ALT' title='BIBORB_OUTPUT_WEBSITE_TITLE' />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="http://{node()}" title='BIBORB_OUTPUT_WEBSITE_TITLE'>
                BIBORB_OUTPUT_WEBSITE_ALT
            </a>
        </xsl:if>
    </xsl:template>
    
	<!--
		Template for the link field.
		Display a link(text/image) to the recorded link (intranet/on the biborb server).
	-->
    <xsl:template match="bibtex:link">
        <xsl:if test="$display_images">
            <a href="{node()}">
                <img src="data/images/stock_jump-to-16.png" alt='BIBORB_OUTPUT_LINK_ALT' title='BIBORB_OUTPUT_LINK_TITLE' />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="{node()}" title='BIBORB_OUTPUT_LINK_TITLE'>
                BIBORB_OUTPUT_LINK_ALT
            </a>
        </xsl:if>
    </xsl:template>
    
    <!--
        Some javascript here to display the abstract. 
		If javascript is not supported, another page is generated to display 
		the abstract of the given entry.
		If supported, clicking on the abstract link will (un)hide the abstract.
    -->
    <xsl:template name="abstract">
		<!-- pass the id to know to which entry apply the javascript -->
        <xsl:param name="id"/>
		
		<!-- the icone version -->
        <xsl:if test="$display_images">
			<!-- if javascript is supported -->
            <script type="text/javascript">
                <xsl:comment><![CDATA[
                    document.write("<a href=\"javascript:toggle_element(\'abs_]]><xsl:value-of select="$id"/><![CDATA[\')\"><img src=\"data/images/]]><xsl:value-of select='$abstract-image'/><![CDATA[\" alt=\'BIBORB_OUTPUT_ABSTRACT_ALT\' title=\'BIBORB_OUTPUT_ABSTRACT_TITLE\' /></a>");]]>
                </xsl:comment>
				<!-- easy to insert javasacript in XSL, isn'it? :-D -->
            </script>
			<!-- if javascript not supported -->
            <noscript>
                <div style="display:inline;">
                    <a href="./bibindex.php?mode=details&amp;abstract=1&amp;menu=0&amp;bibname={$bibname}&amp;id={$id}">
                        <img src="data/images/{$abstract-image}" alt='BIBORB_OUTPUT_ABSTRACT_ALT' title='BIBORB_OUTPUT_ABSTRACT_TITLE'/>
                    </a>
                </div>
            </noscript>
        </xsl:if>
        
		<!-- the text version -->
		<xsl:if test="$display_text">
            <script type="text/javascript">
                <xsl:comment><![CDATA[
                    document.write("<a href=\"javascript:toggle_element(\'abs_]]><xsl:value-of select="$id"/><![CDATA[\')\" title=\'BIBORB_OUTPUT_ABSTRACT_TITLE\'>BIBORB_OUTPUT_ABSTRACT_ALT</a>");]]>
                </xsl:comment>
            </script>
            <noscript>
                <div>
                    <a href="./bibindex.php?mode=details&amp;abstract=1&amp;menu=0&amp;bibname={$bibname}&amp;id={$id}" title='BIBORB_OUTPUT_ABSTRACT_TITLE'>
                        BIBORB_OUTPUT_ABSTRACT_ALT
                    </a>
                </div>
            </noscript>
        </xsl:if>
    </xsl:template>
    
    
    <!--
        Some javascript here to display the longnotes. 
		If javascript is not supported, another page is generated to display 
		the longnotes of the given entry.
		If supported, clicking on the longnotes link will (un)hide the longnotes.
    -->
    <xsl:template name="longnotes">
		<!-- pass the id to know to which entry apply the javascript -->
        <xsl:param name="id"/>
		
		<!-- the icone version -->
        <xsl:if test="$display_images">
			<!-- if javascript is supported -->
            <script type="text/javascript">
                <xsl:comment><![CDATA[
                    document.write("<a href=\"javascript:toggle_element(\'longnotes_]]><xsl:value-of select="$id"/><![CDATA[\')\"><img src=\"data/images/]]><xsl:value-of select='$info-image'/><![CDATA[\" alt=\'BIBORB_OUTPUT_LONGNOTES_ALT\' title=\'BIBORB_OUTPUT_LONGNOTES_TITLE\' /></a>");]]>
                </xsl:comment>
				<!-- easy to insert javasacript in XSL, isn'it? :-D -->
            </script>
			<!-- if javascript not supported -->
            <noscript>
                <div style="display:inline;">
                    <a href="./bibindex.php?mode=details&amp;abstract=1&amp;menu=0&amp;bibname={$bibname}&amp;id={$id}">
                        <img src="data/images/{$info-image}" alt='BIBORB_OUTPUT_LONGNOTES_ALT' title='BIBORB_OUTPUT_LONGNOTES_TITLE'/>
                    </a>
                </div>
            </noscript>
        </xsl:if>
        
		<!-- the text version -->
		<xsl:if test="$display_text">
            <script type="text/javascript">
                <xsl:comment><![CDATA[
                    document.write("<a href=\"javascript:toggle_element(\'longnotes_]]><xsl:value-of select="$id"/><![CDATA[\')\" title=\'BIBORB_OUTPUT_LONGNOTES_TITLE\'>BIBORB_OUTPUT_LONGNOTES_ALT</a>");]]>
                </xsl:comment>
            </script>
            <noscript>
                <div>
                    <a href="./bibindex.php?mode=details&amp;abstract=1&amp;menu=0&amp;bibname={$bibname}&amp;id={$id}" title='BIBORB_OUTPUT_LONGNOTES_TITLE'>
                        BIBORB_OUTPUT_LONGNOTES_ALT
                    </a>
                </div>
            </noscript>
        </xsl:if>
    </xsl:template>
    
    
	<!--
		Template to generate a link to the bibtex version of the entry.
	-->
    <xsl:template name="link2bibtex">
		<!-- get the bibtex id -->
        <xsl:param name="id"/>
		<!-- image version -->
        <xsl:if test="$display_images">
            <a href ="./bibindex.php?mode=bibtex&amp;bibname={$bibname}&amp;id={$id}" onclick="window.open(this.href,'bibtex','toolbar=no,menubar=no,status=no,height=400,width=600,resizable=yes'); return false;">
                <img src="data/images/{$bibtex-image}" alt='BIBORB_OUTPUT_BIBTEX_ALT' title='BIBORB_OUTPUT_BIBTEX_TITLE' />
            </a>
        </xsl:if>
		<!-- text version -->
        <xsl:if test="$display_text">
            <a href ="./bibindex.php?mode=bibtex&amp;bibname={$bibname}&amp;id={$id}" title='BIBORB_OUTPUT_BIBTEX_TITLE' onclick="window.open(this.href,'bibtex','toolbar=no,menubar=no,status=no,height=400,width=400,resizable=yes'); return false;">
                BIBORB_OUTPUT_BIBTEX_ALT
            </a>
        </xsl:if>
    </xsl:template>
    
	<!--
		Template for the abstract field.
		Display the abstract, preserving empty lines.
	-->
    <xsl:template name="bibtex:abstract">
		<!-- the bibtex id -->
        <xsl:param name="id"/>
        <xsl:choose>
			<!-- display the abstract if abstract should always be present -->
            <xsl:when test="$abstract != ''">
                <span id="abs_{$id}">
					<!-- replacing text empty lines with HTML empty lines -->
                    <xsl:call-template name="string-replace">
                        <xsl:with-param name="string" select="translate(string(.//bibtex:abstract),'&#xD;','@#xA;')"/>
                        <xsl:with-param name="from" select="'&#xA;'" />
                        <xsl:with-param name="to" select="'&lt;BR/>'" />
                    </xsl:call-template>
                </span>
            </xsl:when>
			<!-- create the abstract but hide it  -->
            <xsl:otherwise>
                <span id="abs_{$id}" style="display:none;">
					<!-- replacing text empty lines with HTML empty lines -->
                    <xsl:call-template name="string-replace">
                        <xsl:with-param name="string" select="translate(string(.//bibtex:abstract),'&#xD;','@#xA;')"/>
                        <xsl:with-param name="from" select="'&#xA;'" />
                        <xsl:with-param name="to" select="'&lt;BR/>'" />
                    </xsl:call-template>
                </span>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!--
		Template for the longnotes field.
		Display the longnotes, preserving empty lines.
	-->
    <xsl:template name="bibtex:longnotes">
		<!-- the bibtex id -->
        <xsl:param name="id"/>
        <!-- create the longnotes but hide it  -->
        <span id="longnotes_{$id}" style="display:none;">
            <!-- replacing text empty lines with HTML empty lines -->
            <xsl:call-template name="string-replace">
                <xsl:with-param name="string" select="translate(string(.//bibtex:longnotes),'&#xD;','@#xA;')"/>
                <xsl:with-param name="from" select="'&#xA;'" />
                <xsl:with-param name="to" select="'&lt;BR/>'" />
            </xsl:call-template>
        </span>
    </xsl:template>

	
	<!--
		A string replacement function
	-->
    <xsl:template name="string-replace">
        <xsl:param name="string"/>
        <xsl:param name="from"/>
        <xsl:param name="to"/>
        <xsl:choose>
            <xsl:when test="contains($string,$from)">
                <xsl:value-of select="substring-before($string,$from)"/>
                <xsl:value-of select="$to" disable-output-escaping="yes"/>
                <xsl:call-template name="string-replace">
                    <xsl:with-param name="string" select="substring-after($string,$from)"/>
                    <xsl:with-param name="from" select="$from"/>
                    <xsl:with-param name="to" select="$to"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$string"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    
</xsl:stylesheet>
