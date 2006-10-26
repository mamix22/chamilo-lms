<?php // $Id: LinkCategory.class.php 3305 2005-02-03 12:44:01Z bmol $
/*
============================================================================== 
	Dokeos - elearning and course management software
	
	Copyright (c) 2004 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Bart Mollet (bart.mollet@hogent.be)
	
	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	See the GNU General Public License for more details.
	
	Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
============================================================================== 
*/
require_once('Resource.class.php');
/**
 * A LinkCategory
 * @author Bart Mollet <bart.mollet@hogent.be>
 */
class LinkCategory extends Resource
{
	/**
	 * The title
	 */
	var $title;
	/**
	 * The description
	 */
	var $description;
	/**
	 * Create a new LinkCategory
	 * @param int $id
	 * @param string $title
	 * @param string $description
	 */
	function LinkCategory($id,$title,$description)
	{
		parent::Resource($id,RESOURCE_LINKCATEGORY);
		$this->title = $title;
		$this->description = $description;	
	}	
	/**
	 * Show this LinkCategory
	 */
	function show()
	{
		parent::show();
		echo $this->title.' '.$this->description.'<br/>';
	}
}
?>