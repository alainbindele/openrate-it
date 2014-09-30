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
 * This class defines the container Form
 * It's useful to comment a survey
 * Containers parameters like flags (moderated, private etc.)
 * must affect all surveys contained in it.
 * Specific survey flag must override this setting
 * e.g. this kind of form is used in the
 * /view/content/admin-panel/input-panel/surveys-and-containers/add-containers.phtml
 * Class ContainersForm
 * @package Application\AdminPanelForm
 */
class ContainersForm extends Form{
	
	protected $usersItems = array('select user'=>'Select user');


    /**
     * The constructor of the class takes a user's collection
     * and sets local variables accordly to them
     * The user's collection is filled with user's uid
     * @param array $usersCollection
     */
	function __construct($usersCollection=array()){
		
		if(sizeof($usersCollection) > 0){
			foreach ($usersCollection as $u){
				$this->usersItems[$u->getId()]=$u->getId()." - ".$u->getName();
			}
		}
		return $this;
	}

    /**
     * Return a form useful to add a containers
     *
     * @return Form
     */
    function getAddContainerForm(){

        //create the text label for the name entry
		$name = new Element\Text('name');
		$name -> setLabel('Container Name')
			  -> setAttributes(array(
									'placeholder'  => 'insert container name here',
			  						'class'  => 'pure-input-1'
							));

        //create the text label for the description entry
		$description = new Element\Text('description');
		$description -> setLabel('Description')
					 -> setAttributes(array(
					 				'placeholder'  => 'insert container description here',
					 				'class'  => 'pure-input-1'
									));
        //create the checkbox for the private option
		$private = new Element\Checkbox('private');
		$private    -> setLabel("private")
		-> setValue('0')
		-> setAttributes(array(
		 		'id' => 'private',
		 		'name' => 'private',
		 		'options' => array(
		 				'checkedValue' => 'true'
		 		)
		 ));
        //create the checkbox for the moderated option
		 $moderated = new Element\Checkbox('moderated');
		 $moderated  -> setLabel("moderated")
		 -> setValue('0')
		 -> setAttributes(array(
		 		'id' => 'moderated',
		 		'name' => 'moderated'
		 ));

         //create the checkbox for the comments option
		 $allowComments = new Element\Checkbox('allowComments');
		 $allowComments  -> setLabel("allow Comments")
		 -> setValue('ERGEIRGJ')
		 -> setAttributes(array(
		 		'id' => 'allowComments',
		 		'name' => 'allowComments'
		 ));

        //create the checkbox for the anonymous vote option
		 $allowAnonymous = new Element\Checkbox('allowAnonymous');
		 $allowAnonymous -> setLabel("allow Anonymous")
		 -> setValue('0')
		 -> setAttributes(array(
		 		'id' => 'allowAnonymous',
		 		'name' => 'allowAnonymous',
		 ));

        //create the checkbox for the delegation level option
        //delegation level is useful to delegate someone other
        //to vote for a survey the recursion level of the delegation
        // is specified by this parameter
		 $delegationLevel = new Element\Select('delegationLevel');
		 $delegationLevel -> setLabel('Delegation Level')
		 					-> setOptions(array('value_options' => array(
												 		'1'=>'1',
												 		'2'=>'2',
												 		'3'=>'3',
												 		'4'=>'4',
												 		'5'=>'5'
												 		//TODO:PARAMETRIZE-ME!
                                                        //(this value should be taken from survey-node)
										 	)));
					 
        // create the select field for the nesting level option
        // containers could be nested inside other containers
        // the nesting recursion limit is specified by this parameter
		$nestingLevel = new Element\Select('nestingLevel');
		$nestingLevel -> setLabel('Nesting Level')
					  -> setOptions(array('value_options' => array(
									'1'=>'1',
									'2'=>'2',
									'3'=>'3',
									'4'=>'4',
									'5'=>'5'
					  		// TODO:PARAMETRIZE-ME!
                            // (this value should be taken from survey-node)
					)
		));

        // create the select field to specify the
        // survey creator this field is filled with the list
        // of users identificators
        $creator = new Element\Select('creator');
        $creator 	  -> setLabel('Creator')
        		        -> setOptions(array(
        		  		'value_options'	=> $this->usersItems
        		  )
		);

        // create the select field to specify the
        // target container
        $container  = new Element\Select('container');
        $container 	-> setLabel('Container');

        // create the image used to send the form
        // the image
        // TODO: try to decremet dependecy level with the
        // view level since this image calls the jQuery script
        // addUnitInputText() stored in
    	$button = new Element\Image('send');
    	$button	-> setLabel('+')
    			-> setAttributes(array(
    									'src' 		=>  'img/ico/check.png',
    									'width' 	=>  '25',
    									'height' 	=>  '25',
    									'value' 	=>  'Add Item',
    									'onclick' 	=>  "addUnitInputText('item')"
    							));
        // create the image element used to send the form
        //
		$default = new Element\Checkbox('default');
		$default    -> setLabel("default")
		-> setValue('0')
		-> setAttributes(array(
				'id' => 'default',
				'name' => 'default',
				'options' => array(
						'checkedValue' => 'true'
				)
		));


        // put all together
		$form = new Form('addContainerForm');
		$form 	-> add($name)
				-> add($description)
				-> add($nestingLevel)
				-> add($private)
				-> add($moderated)
				-> add($allowAnonymous)
				-> add($allowComments)
				-> add($delegationLevel)
				-> add($creator)
				-> add($container)
				-> add($button)
		        -> add($default);
		return $form;
	}
};


