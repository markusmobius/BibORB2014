<?php
/**
 *
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
 */

/**
 * File: basket.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *      This file defines a Basket class. The basket stores distinct items. 
 */


/**
 * A basket to store distinct items.
 * 
 * @author G. Gardey
 */
class Basket {
    // an array of distinct items
	var $items;
	
    /**
     * Constructor.
     */
	function Basket() {
		$this->items = array();
	}

    /**
     * Number of items in the basket.
     */
	function count_items(){
		return count($this->items);
	}

    /**
     * Add an item.
     * If the item is already present, it is not added.
     */
	function add_item($item) {
		if(!in_array($item,$this->items) && $item != ''){
			array_push($this->items,$item);
		}
	}

    /**
     * Add a set of items.
     */
	function add_items($array){
		foreach($array as $item){
			$this->add_item($item);
		}
	}

    /**
     * Remove an item.
     */
	function remove_item($item) {
		$key = array_search($item,$this->items);
        if($key !== FALSE){
            unset($this->items[$key]);
            $this->items = array_values($this->items);
        }
	}
	
    /**
     * Remove all items.
     */
	function reset(){
        $this->item = array();
	}
	
    /**
     * Retun a string representing the list of items separated by a dot.
     */
	function items_to_string(){
		$res = ".";
		foreach($this->items as $item){
			$res .= $item.".";
		}
		return $res;
	}
}

?>