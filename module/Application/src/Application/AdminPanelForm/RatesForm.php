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

/**
 * @package rateit
 * @author Alain Bindele
 * @version pre-alpha 0.1
 */
namespace Application\AdminPanelForm;


use Zend\Form\Element;
use Zend\Form\Form;



class RatesForm extends Form{
	
	protected $usersItems = array('select user'=>'Select user');
	
	function __construct($usersCollection=array()){
		if(sizeof($usersCollection)>0){
			foreach ($usersCollection as $u){
				$this->usersItems[$u->getId()]=$u->getId()." - ".$u->getName();
			}
		}
		return $this;
	}
	
	function getRatesForm(){
		
		//TODO: get user list
		
	    
	    //CREATOR SELECT FIELD
		$creatorUserSelect   = new Element\Select('surveyCreatorId');
		$creatorUserSelect   -> setLabel('Survey Creator')
							 -> setOptions(array('value_options'	=> $this->usersItems))
							 -> setAttribute('id','surveyCreatorId');
        //VOTER SELECT FIELD
		$voterUserSelect = new Element\Select('voterUid');
		$voterUserSelect -> setLabel('Survey voter')
			             -> setOptions(array('value_options'	=> $this->usersItems))
			             -> setAttribute('id','voterUid');

        //SURVEY TO BE VOTED SELECT FIELD
		$surveySelect = new Element\Select('surveySelectId');
		$surveySelect 	-> setLabel('Select survey')
						-> setAttribute('id','surveySelectId');
		
		//ANONYMOUS CHECKBOX
		$anonymous = new Element\Checkbox('anonymousVote');
		$anonymous -> setLabel("Vote Anonymously")
		-> setValue('0')
		-> setAttributes(array(
				'id' => 'anonymousVote',
				'name' => 'anonymousVote',
		));
		
		//SEND BUTTON
		$sendButton = new Element\Image('send');
		$sendButton	-> setLabel('+')
					-> setAttributes(array(
											'src' 		=> 'img/ico/check.png',
											'width' 	=> '25',
											'height' 	=> '25'
									));
		
			
		
		//COMPOSE THE FORM
		$form   =  new Form('addCommentsForm');
		$form 	-> add( $creatorUserSelect )
				-> add( $surveySelect )
				-> add( $voterUserSelect )
				-> add( $anonymous )
				-> add( $sendButton );
		return $form;
	}
};


