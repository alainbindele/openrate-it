<?php

/*
 * Application name: OpenRate-it!
 * A general-purpose polling platform
 * Copyright (C) 2014  Alain Bindele (alain.bindele@gmail.com)
 * This file is part of OpenRate-it!
 * OpenRate-it! is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * OpenRate-it! is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace Application\Model\Entity;

use HireVoice\Neo4j\Annotation as OGM;

/**
 * All entity classes must be declared as such.
 *
 * @OGM\Entity
 */
class TagNode
{
	/**
	 * The internal node ID from Neo4j must be stored. Thus an Auto field is required
	 * @OGM\Auto
	 */
	protected $id;

	/**
	 * @OGM\Property
	 * @OGM\Index
	 */
	protected  $name;

	/**
	 * @OGM\Property
	 */
	protected  $weight;


	function __construct($tagName)
	{
		$this->name = $tagName;
		$this->weight = 1;
	
	}
	

	/*
	 * These magic method are called each
	 * time a variable is referenced from the object
	*/

	public function __get($name) {
		$method = 'get' . $name;
		echo $method;
		if (!method_exists($this, $method)) {
			echo 'Invalid Method';
		}
		return $this->$method();
	}

	public function __set($name,$value){
		$method = 'set' . $name;
		if (!method_exists($this, $method)) {
			echo 'Invalid Method';
		}
		$this->$method($value);
	}

	function getName()
	{
		return $this->name;
	}

	
	function setName($name)
	{
		$this->name=$name;
		return ;
	}


	function getWeight()
	{
		return $this->weight;
	}
	
	
	function setWeight($weight)
	{
		$this->weight=$weight;
		return ;
	}
	
	
	function decrementWeight()
	{
		$this->weight-=1;
	}
	
	function incrementWeight()
	{
		$this->weight+=1;
	}

	
	function getId(){
		return $this->id;
	}

}
