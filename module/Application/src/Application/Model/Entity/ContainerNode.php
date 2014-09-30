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
class ContainerNode
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
	 * @OGM\Index
	 */
	protected  $description;
	
	/**
	 * @OGM\Property
	 */
	protected  $circles;
	
	/**
	 * @OGM\Property
	 */
	protected  $private;
	
	/**
	 * @OGM\Property
	 */
	protected  $moderated;
	/**
	 * @OGM\Property
	 */
	protected  $allowAnonymous;
	/**
	 * @OGM\Property
	 */
	protected  $allowComments;
	
	/**
	 * @OGM\Property
	 */
	protected  $creator;
	
	/**
	 * @OGM\Property
	 */
	protected  $delegationLevel;
	
	
	/**
	 * @OGM\Property
	 */
	protected  $nestingLevel;
	

	
	function __construct()
	{
		
	}

	/*
	 * These magic method are called each time a variable is referenced from the object 
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

	
	
	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	function getName(){
		return $this->name;
	}
	function setName($name){
        $this->name = $name;
    }
    
    function getDescription(){
    	return $this->description;
    }
    function setDescription($description){
    	$this->description = $description;
    }
    
    function getDelegationLevel(){
    	return $this->delegationLevel;
    }
    function setDelegationLevel($delegationLevel){
    	$this->delegationLevel = $delegationLevel;
    }
    
    function getCircles(){
    	return $this->circles;
    }
    function setCircles($circles){
    	$this->circles = $circles;
    }

    function getCreator(){
    	return $this->creator;
    }
    function setCreator($creator){
    	$this->creator = $creator;
    }
    
    function getNestingLevel(){
    	return $this->nestingLevel;
    }
    function setNestingLevel($nestingLevel){
    	$this->nestingLevel = $nestingLevel;
    }
    
    function getFlags(){
    	$flags=array();
	
		$flags["private"]=$this->private;
		$flags["moderated"]=$this->moderated;
		$flags["allowComments"]=$this->allowComments;
		$flags["allowAnonymous"]=$this->allowAnonymous;
	
		return $flags;
    }
    
    
    /**
     * @param flags $flags
     */
    public function setFlags($params) {
    
    	if(isset($params['private']))
    		$params['private']       =='1'  ? $this->private=true       : $this->private=false;
    	if(isset($params['moderated']))
    		$params['moderated']     =='1'  ? $this->moderated=true     : $this->moderated=false;
    	if(isset($params['allowComments']))
    		$params['allowComments'] =='1'  ? $this->allowComments=true : $this->allowComments=false;
    	if(isset($params['allowAnonymous']))
    		$params['allowAnonymous']=='1'  ? $this->allowAnonymous=true: $this->allowAnonymous=false;
    }


}
