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
 * @version 0.1
 */

namespace Application\AdminPanelForm;

use Zend\Form\Element;
use Zend\Form\Form;


/**
 * A form to manage users and circles in the system
 * /add /delete
 * Class SurveyForm
 * @package Application\AdminPanelForm
 */
class UsersForm extends Form{

	public $many = false;
	protected $howMany = 1;
	protected $usersItems = array('select user'=>'select user');


    /**
     * @param array $usersCollection
     * @param int $howMany
     * Construct the form to create/delete users circles
     */
    public function __construct($usersCollection=array(),$howMany=10){
		
		if ($howMany>1){
			$this->many=true;
			$this->howMany=$howMany;
		}
		
		if(sizeof($usersCollection)>0){
			foreach ($usersCollection as $u){
				$this->usersItems[$u->getId()]=$u->getId()." - ".$u->getName();
			}
		}
		return $this;
	}


    /**
     * Returns a form to add users
     * @return Form
     */
    public function getAddUsersForm(){
	
		$name = new Element\Text('name');
		$name	-> setLabel('Name')
				-> setAttributes(array(
						'size'  => '45',
						'id'	=> 'name',
						'placeholder'=>'Name',
						'class' => 'pure-input-1'
				));
			
		$surname = new Element\Text('surname');
		$surname	-> setLabel('Surname')
					-> setAttributes(array(
							'size'  => '45',
							'id'	=> 'surname',
							'placeholder'=>'Surname',
							'class' => 'pure-input-1'
					));
	
		$mail = new Element\Text('mail');
		$mail	-> setLabel('E-mail')
				-> setAttributes(array(
						'size'  => '45',
						'id'	=> 'email',
						'placeholder'=>'E-mail',
						'class' => 'pure-input-1'
				));
	

		if( $this->howMany > 1 && $this->many ){
		
			$howManyElement = new Element\Text('howMany');
			$howManyElement	-> setLabel('How many')
							-> setAttributes(array(
									'size'  => '5',
									'id'	=> 'howMany',
									'value'	=> $this->howMany
							));
		}
				
		$type = new Element\Select('accountType');
		$type	-> setLabel('AccountType')
				-> setOptions(array('value_options' => array(
									'normal'=>'normal',
									'admin'=>'admin',
									'tester'=>'tester',
									'system'=>'system'
						)
				));
			
		$button = new Element\Submit('send');
		$button		-> setLabel('')
					-> setAttributes(array(
							'type'		=> 'submit',
							'value' 	=> '',
							'src'		=> 'img/ico/check.png',
							'width'		=> '25',
							'height'	=> '25',
							'class'		=> 'checkbutton'
					));
		
					
		$form = new Form('addUserForm');
		$form	-> add($name)
				-> add($surname)
				-> add($mail)
				-> add($type)
				-> add($button);
		
		if($this->howMany > 1 && $this->many ){
			$form -> add($howManyElement);
		}
		return $form;
	}


    /**
     * returns a form to add circles
     * @return Form
     */
    public function getAddCirclesForm(){
		
		
		//var_dump($usersCollection);
		$user = new Element\Select('user');
		$user 	-> setLabel('User')
				//-> setAttribute('onchange', "www.google.it")
				-> setOptions(array(
									'value_options'	=> $this->usersItems
							));
		
		
		
		$addCirclesImageButton = new Element\Image('addCirclesImageButton');
		$addCirclesImageButton	->setLabel('+')
								-> setAttributes(array(
									'value' 	=> 'Add Circles',
									'onclick' 	=> "addCirclesInput('circles')",
									'src' 		=> 'img/ico/addCircle.png',
									'width' 	=> '25',
									'height' 	=> '25'
							));

		$submitButton = new Element\Image('send');
		$submitButton	-> setLabel('+')
						-> setAttributes(array(
								'value' => '+',
								'src' 		=> 'img/ico/check.png',
								'width' 	=> '25',
								'height' 	=> '25'
						));
		
			
		$form = new Form('addCirclesForm');
		$form 	-> add($user)
				-> add($submitButton)
				-> add($addCirclesImageButton);
		
		return $form;
	}


    /**
     * @return Form
     * returns a form to delete users
     */
    public function getDeleteUsersForm(){
		
		
		$user = new Element\Select('user');
		$user 	-> setLabel('User')
        		-> setAttributes(array(
        				'id' => 'delUserId'
        		    ))
				-> setOptions(array(
				        'id'=>'delUserId',
						'value_options'	=> $this->usersItems
						//TODO:QUERY HERE!
		));
		
		$deleteButton = new Element\Image('deleteUsersButton');
		$deleteButton	-> setLabel('-')
		-> setAttributes(array(
				'src' 		=> 'img/ico/trash.png',
				'width' 	=> '30',
				'height' 	=> '30'
		));
		
			
		$form = new Form('addUserForm');
		$form 	-> add($user)
				-> add($deleteButton);
		
		return $form;
	}


    /**
     * @return Form
     * returns a form to delete all users in the database
     */
    public function getDeleteAllUsersForm(){

        /**
         * a button to delete all users
         */
        $deleteAllUsersButton = new Element\Image('deleteAllUsersButton');
		$deleteAllUsersButton	 -> setAttributes(array(
								'src' 		=> 'img/ico/trash.png',
								'width' 	=> '25',
								'height' 	=> '25',
								'onclick'	=>  'var conf=confirm("Do you really want to DELETE all users in DB?");
												if(!conf)return;
												javascript:location.href="/application/admin/del-all-users";'
							));

        /**
         * put all together
         */
        $form =  new Form('addUserForm');
		$form -> add($deleteAllUsersButton);
		return $form;
	}

	
};


