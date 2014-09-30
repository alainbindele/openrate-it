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
//use HireVoice\Neo4j\Extension\ArrayCollection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * All entity classes must be declared as such.
 *
 * @OGM\Entity
 */
class UserNode
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
	protected  $surname;
	
	/**
	 * @OGM\Property
	 */
	protected  $accountType;
	
	/**
	 * @OGM\Property
	 */
	protected  $numberOfVotes;
	
	/**
	 * @OGM\Property
	 */
	protected  $numberOfInVotes;
	
	/**
	 * @OGM\Property
	 */
	protected  $numberOfOutVotes;
	
	/**
	 * @OGM\Property
	 */
	protected  $numberOfSurveys;
	
	
	/**
	 * @OGM\Property
	 */
	protected  $numberOfContainers;

	
	/**
	 * @OGM\Property
	 */
	protected  $numberOfFriends;
	
	/**
	 * @OGM\Property
	 * @OGM\Index
	 */
	protected  $mail;
	
	/**
	 * @OGM\Property
	 * @OGM\Index
	 */
	protected  $uid;
	
	/**
	 * @OGM\Property
	 */
	protected  $circles;
	
	/**
	 * @OGM\ManyToMany
	 */
	protected $contacts;
	
	function __construct()
	{
		$this->contacts = new ArrayCollection;
		
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

	function getContacts()
	{
		return $this->contacts;
	}
	
	function addContacts($contacts)
	{
		$this->contacts->add($contacts);
	}
	
	function setContacts(ArrayCollection $contacts)
	{
		$this->contacts= $contacts;
	}
	
	function getName(){
		return $this->name;
	}
	function setName($name){
        $this->name = $name;
    }
    
    function getCircles(){
    	return $this->circles;
    }
    function setCircles($circles){
    	$this->circles = $circles;
    }
    
    function getSurname(){
    	return $this->surname;
    }
    function setSurname($surname){
    	$this->surname = $surname;
    }

    /**
     * @param field_type $numberOfSurveys
     */
    public function setNumberOfSurveys($numberOfSurveys) {
    	$this->numberOfSurveys = $numberOfSurveys;
    }
    
    /**
	 * @return the $numberOfSurveys
	 */
	public function getNumberOfSurveys() {
		return $this->numberOfSurveys;
	}

	
	/**
	 * @param field_type $numberOfFriends
	 */
	public function setNumberOfFriends($numberOfFriends) {
		$this->numberOfFriends = $numberOfFriends;
	}
	
	/**
	 * @return the $numberOfFriends
	 */
	public function getNumberOfFriends() {
		return $this->numberOfFriends;
	}
	
	

	function getMail(){
    	return $this->mail;
    }
    function setMail($mail){
    	$this->mail = $mail;
    }
    function getAccountType(){
    	return $this->accountType;
    }
    function setAccountType($accountType){
    	$this->accountType = $accountType;
    }
    
    /**
	 * @return the $numberOfVotes
	 */
	public function getNumberOfVotes() {
		return $this->numberOfVotes;
	}

	/**
	 * @param field_type $numberOfVotes
	 */
	public function setNumberOfVotes($numberOfVotes) {
		$this->numberOfVotes = $numberOfVotes;
	}

	/**
	 * @return the $numberOfInVotes
	 */
	public function getNumberOfInVotes() {
		return $this->numberOfInVotes;
	}

	/**
	 * @param field_type $numberOfInVotes
	 */
	public function setNumberOfInVotes($numberOfInVotes) {
		$this->numberOfInVotes = $numberOfInVotes;
	}

	/**
	 * @return the $numberOfOutVotes
	 */
	public function getNumberOfOutVotes() {
		return $this->numberOfOutVotes;
	}

	/**
	 * @param field_type $numberOfOutVotes
	 */
	public function setNumberOfOutVotes($numberOfOutVotes) {
		$this->numberOfOutVotes = $numberOfOutVotes;
	}

	/**
	 * @return the $numberOfContainers
	 */
	public function getNumberOfContainers() {
		return $this->numberOfContainers;
	}

	/**
	 * @param field_type $numberOfContainers
	 */
	public function setNumberOfContainers($numberOfContainers) {
		$this->numberOfContainers = $numberOfContainers;
	}

	function getUid(){
    	return $this->uid;
    }
    function setUid($uid){
    	$this->uid = $uid;
    }
    
    function getId(){
    	return $this->id;
    }

}
