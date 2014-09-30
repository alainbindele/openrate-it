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


/**
 * This class defines social interaction forms
 * It's useful to add/delete social relationships, circles
 * /view/content/admin-panel/input-panel/surveys-and-containers/add-containers.phtml
 * Class ContainersForm
 * @package Application\AdminPanelForm
 */
class SocialForm extends Form{

	public $many = false;
	protected $howMany = 1;
	protected $usersItems = array('select user'=>'Select user');
	
	public function __construct($usersCollection=array()){
		if(sizeof($usersCollection)>0){
			foreach ($usersCollection as $u){
				$this->usersItems[$u->getId()]=$u->getId()." - ".$u->getName();
			}
		}
 	}


    /**
     * Returns a form useful to add social relationship
     * @return Form
     */
    public function getAddSocialRelationForm(){
		
		/*
		 * Prepare the basic AddSocialRelation form
		 * wich contains only the start user and the 
		 * final user... the circles
		 */
		$startUser  = new Element\Select('circlesCreatorUid');
		$startUser	-> setLabel('Start User')
					-> setOptions(array('value_options'	=> $this->usersItems));


        /**
         * Select the start user for the ralationship
         * with a select html field
         */
        $targetUser = new Element\Select('targetUser');
		$targetUser	-> setLabel('End User')
					-> setOptions(array('value_options' => $this->usersItems));


        /**
         * Create the image to click to send the form
         */
		$button = new Element\Image('send');
		$button		-> setLabel('')
					-> setAttributes(array(
							'type'		=> 'submit',
							'value' 	=> '',
							'src'		=> 'img/ico/check.png',
							'width'		=> '25',
							'height'	=> '25',
							'class'		=> 'checkbutton'
					));

        /*
         * Put all together
         */
		$form = new Form('addSocialRelationForm');
		$form	-> add($startUser)
				-> add($targetUser)
				-> add($button);

		return $form;
	}

    /**
     *
     * Returns a form to delete social relationship
     * @return Form
     *
     */
    public function getDeleteSocialRelationForm(){
	
		/*
		 * Prepare the basic AddSocialRelation form
		* wich contains only the start user and the
		* final user... the circles
		*/
	
		$startUser = new Element\Select('contactCreatorUid');
		$startUser	-> setLabel('Start User')
					-> setOptions(array('value_options'	=> $this->usersItems))
					-> setAttribute("id", "startUid");

        /**
         * Create the image to click to send the form
         */
		$button = new Element\Image('send');
		$button		-> setLabel('')
		-> setAttributes(array(
				'type'		=> 'submit',
				'value' 	=> '',
				'src'		=> 'img/ico/check.png',
				'width'		=> '25',
				'height'	=> '25',
				'class'		=> 'checkbutton'
		));
			
		$form = new Form('deleteSocialRelationForm');
		$form	-> add($startUser)
				-> add($button);
	
		return $form;
    }
};


