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

//TODO: SISTEMARE DOCUMENTAZIONE

namespace Application\Model\Entity;

use HireVoice\Neo4j\Annotation as OGM;


/**
 * All entity classes must be declared as such.
 *
 * @OGM\Entity
 */
class SurveyNode
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
	protected  $title;

	/**
	 * @OGM\Property
	 */
	protected  $description;

	/**
	 * @OGM\Property
	 */
	protected  $circles;
	

	/**
	 * @OGM\Property
	 */
	protected  $hits;

	/**
	 * @OGM\Property
	 */
	protected  $units;

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
	protected  $allowComments;
	
	/**
	 * @OGM\Property
	 */
	protected  $allowAnonymous;

	/**
	 * @OGM\Property
	 */
	protected  $delegationLevel;

	/**
	 * @OGM\Property
	 */
	protected  $creator;


	/**
	 * @OGM\Property
	 */
	protected  $expirationTS;

    /**
     * @OGM\Property
     */
    protected  $allowMultipleVotes;

    /**
     * @OGM\Property
     */
    protected  $multiVotesTimeInterval;


	/**
	 * @OGM\Property
	 */
	protected  $totVotes;
	
	
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
	 * @return the $units
	 */
	public function getUnits() {
		return $this->units;
	}

	/**
	 * @param field_type $units
	 */
	public function setUnits($units) {
        $this->units="";
        $this->units=$units;
		//$this->setJsonLevel($units);
	}

	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param field_type $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return the $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return the $description
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param mixed $hits
	 */
	public function setHits($hits)
	{
		$this->hits = $hits;
	}

    /**
     * @param mixed $hits
     */
    public function incrementHits()
    {
        $this->hits = $this->getHits()+1;
    }

	/**
	 * @return mixed
	 */
	public function getHits()
	{
		return $this->hits;
	}


	/**
	 * @return the $delegationLevel
	 */
	public function getDelegationLevel() {
		return $this->delegationLevel;
	}

	/**
	 * @return the $creator
	 */
	public function getCreator() {
		return $this->creator;
	}

	/**
	 * @return the $type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return the Unit flags
	 */
	public function getFlags(){
	
		$flags=array();
	
		$flags["private"]            = $this->private;
		$flags["moderated"]          = $this->moderated;
		$flags["allowComments"]      = $this->allowComments;
		$flags["allowAnonymous"]     = $this->allowAnonymous;
        $flags["allowMultipleVotes"] = $this->allowMultipleVotes;
	
		return $flags;
	
	}

	/**
	 * @param flags $flags
	 */
	public function setFlags($params) {

		if(isset($params['private']))
			$params['private']              =='1'  ? $this->private=true       : $this->private=false;
		else 
		  $this->private = false;
		if(isset($params['moderated']))
			$params['moderated']            =='1'  ? $this->moderated=true     : $this->moderated=false;
		else 
		   $this->moderated = false;
		if(isset($params['allowComments']))
			$params['allowComments']        =='1'  ? $this->allowComments=true : $this->allowComments=false;
		else
		   $this->allowComments = false;
		if(isset($params['allowAnonymous']))
			$params['allowAnonymous']       =='1'  ? $this->allowAnonymous=true: $this->allowAnonymous=false;
		else
		   $this->allowAnonymous = false;
        if(isset($params['allowMultipleVotes']))
            $params['allowMultipleVotes']   =='1'  ? $this->allowMultipleVotes=true     : $this->allowMultipleVotes=false;
        else
            $this->allowMultipleVotes = false;
	}

	/**
	 * @param field_type $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @param field_type $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @param field_type $delegationLevel
	 */
	public function setDelegationLevel($delegationLevel) {
		$this->delegationLevel = $delegationLevel;
	}

	/**
	 * @param field_type $creator
	 */
	public function setCreator($creator) {
		$this->creator = $creator;
	}

	/**
	 * @param field_type $type
	 */
	public function setType($type) {
		$this->type = $type;
	}


	public function setPrivate($value){
		return $value != true || $value != false ? false : $this->private=$value;
	}
	
	public function setModerated($value){
		return $value != true || $value != false ? false : $this->moderated=$value;
	}
	
	public function setAllowComments($value){
		return $value != true || $value != false ? false : $this->allowComments=$value;
	}
	
	/**
	 * @return the $allowComments
	 */
	public function getAllowComments() {
		return $this->allowComments;
	}

	public function setAllowAnonymous($value){
		return $value != true || $value != false ? false : $this->allowAnonymous=$value;
	}

	/**
	 * @param mixed $expirationTS
	 */
	public function setExpirationTS($expirationTS)
	{
		$this->expirationTS = $expirationTS;
	}

	/**
	 * @return mixed
	 */
	public function getExpirationTS()
	{
		return $this->expirationTS;
	}
	/**
	 * @return the $totVotes
	 */
	public function getTotVotes() {
		return $this->totVotes;
	}

	/**
	 * @param field_type $totVotes
	 */
	public function setTotVotes($totVotes) {
		$this->totVotes = $totVotes;
	}
	/**
	 * @return the $circles
	 */
	public function getCircles() {
		return $this->circles;
	}

	/**
	 * @param field_type $circles
	 */
	public function setCircles($circles) {
	    if($circles)
		  $this->circles = $circles;
	}

    /**
     * @param mixed $allowMultipleVotes
     */
    public function setAllowMultipleVotes($allowMultipleVotes)
    {
        $this->allowMultipleVotes = $allowMultipleVotes;
    }

    /**
     * @return mixed
     */
    public function getAllowMultipleVotes()
    {
        return $this->allowMultipleVotes;
    }

    /**
     * @param mixed $multiVotesTimeInterval
     */
    public function setMultiVotesTimeInterval($multiVotesTimeInterval)
    {
        $this->multiVotesTimeInterval = $multiVotesTimeInterval;
    }

    /**
     * @return mixed
     */
    public function getMultiVotesTimeInterval()
    {
        return $this->multiVotesTimeInterval;
    }

}
