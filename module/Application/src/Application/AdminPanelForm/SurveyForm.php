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
     * A form to insert return a survey
     * Class SurveyForm
     * @package Application\AdminPanelForm
     */
    class SurveyForm extends Form{

		protected $usersItems 		= array('select user'		=>'Select user');
		protected $containerItems 	= array('select container'=>'Select container');


        /**
         * @param array $usersCollection
         * Takes in input a users collection and returns
         * a form pre-compiled with users creators inside selections
         */
        function __construct($usersCollection=array()){

			if(sizeof($usersCollection)>0){
				foreach ($usersCollection as $u){
					$this->usersItems[$u->getId()]=$u->getId()." - ".$u->getName();
				}
			}


			return $this;
		}


        /**
         * Returns a survey form
         * @return Form
         */
        function getAddSurveyForm(){


            /**
             * A text label to insert a title
             */
            $title  = new Element\Text('title');
			$title	-> setLabel('Title')
    				-> setAttributes(array(
    					'class'  => 'pure-input-1',
    					'placeholder' => 'Type the survey name here'
    				));
            /**
             * The tags label text field
             */
            $tagInput = new Element\Text('tags');
			$tagInput -> setLabel('Tags')
            		  -> setAttributes(array(
            			        'id'           => 'my-text-input',
            					 'placeholder' => 'Insert some tag'
			));
            /**
             * A text description field
             */
            $description = new Element\Text('description');
			$description -> setLabel('Description')
				-> setAttributes(array(
					'class'  => 'pure-input-1',
					'placeholder' => 'Type a description here'
				));

            /**
             * A delegation level dropdown menu
             */
            $delegationLevel = new Element\Select('delegationLevel');
			$delegationLevel -> setLabel('Delegation Level')
				-> setOptions(array('value_options' => array(
					'1'=>'1',
					'2'=>'2',
					'3'=>'3',
					'4'=>'4',
					'5'=>'5'
					//TODO:PARAMETRIZE ME!
				)
				));

            /**
             * Select the kind o polling unit type
             */
            $pollType = new Element\Select('pollType');
			$pollType	-> setLabel('Poll type')
				-> setOptions(array('value_options' => array(
					'Single'=>'Single',
					'Multi'=>'Multi',
					'Ranking'=>'Ranking',
					'Likert'=>'Likert',
					'Like'=>'Like'
					//TODO:PARAMETRIZE ME!
				)
				));

            /**
             * A field to select a creator of the survey
             */
            $creator = new Element\Select('creator');
			$creator -> setLabel('Creator')
				     -> setOptions(array(
						'value_options'	=> $this->usersItems));


            /**
             * A select field to select a container id
             */
            $container = new Element\Select('container');
			$container  -> setLabel('Container')
				        -> setOptions(array(
						'value_options'	=> $this->containerItems
					)
		    );

            /**
             * Adds images to click to add a unit of type single
             */
            $addItemButton = new Element\Image('addSingleItemsButton');
			$addItemButton	-> setLabel('Add Item')
				-> setAttributes(array(
					'src' 		=> 'img/ico/single.png',
					'width' 	=> '20',
					'height' 	=> '20',
					'onclick' 	=> "addUnitInputText('item')"
				));

            /**
             * Adds images to click to add a unit of type multi
             */
			$addItemButton = new Element\Image('addMultiItemsButton');
			$addItemButton	-> setLabel('Add Item')
				-> setAttributes(array(
					'src' 		=> 'img/ico/multi.png',
					'width' 	=> '20',
					'height' 	=> '20',
					'onclick' 	=> "addUnitInputText('item')"
				));
            /**
             * Adds images to click to add a unit of type text
             */
			$addTextButton = new Element\Image('addTextItemsButton');
			$addTextButton -> setLabel('Add Item')
				-> setAttributes(array(
					'src' 		=> 'img/ico/text.png',
					'width' 	=> '20',
					'height' 	=> '20',
					'onclick' 	=> "addUnitInputText('item')"
				));
            /**
             * Adds images to click to add a unit of type single
             */
			$addItemButton = new Element\Image('addLikertItemsButton');
			$addItemButton	-> setLabel('Add Item')
				-> setAttributes(array(
					'src' 		=> 'img/ico/likert.png',
					'width' 	=> '20',
					'height' 	=> '20',
					'onclick' 	=> "addUnitInputText('item')"
				));

            /**
             * Adds images to click to add a unit of type shultze
             */
			$addItemButton = new Element\Image('addShultzeItemsButton');
			$addItemButton	-> setLabel('Add Item')
				-> setAttributes(array(
					'src' 		=> 'img/ico/shultze.png',
					'width' 	=> '20',
					'height' 	=> '20',
					'onclick' 	=> "addUnitInputText('item')"
				));

            /**
             * Adds images to click to add a unit of type thumb
             */
			$addItemButton 	= new Element\Image('addThumbItemsButton');
			$addItemButton	-> setLabel('Add Item')
				-> setAttributes(array(
					'src' 		=> 'img/ico/thumb.png',
					'width' 	=> '20',
					'height' 	=> '20',
					'onclick' 	=> "addUnitInputText('item')"
				));

            /**
             * Adds images to click to add a unit of type private
             */
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
            /**
             * Adds a checkbox to moderate the survey
             */
			$moderated = new Element\Checkbox('moderated');
			$moderated  -> setLabel("moderated")
						-> setValue('0')
						-> setAttributes(array(
							'id' => 'moderated',
							'name' => 'moderated'
						));

            /**
             * Adds a checkbox allow comments to the survey
             */
			$allowComments = new Element\Checkbox('allowComments');
			$allowComments  -> setLabel("allow Comments")
							-> setValue('ERGEIRGJ')
							-> setAttributes(array(
								'id' => 'allowComments',
								'name' => 'allowComments'
							));
            /**
             * Adds a checkbox to allow anonymous comments to the survey
             */
			$allowAnonymous = new Element\Checkbox('allowAnonymous');
			$allowAnonymous -> setLabel("allow Anonymous")
							-> setValue('0')
							-> setAttributes(array(
								'id' => 'allowAnonymous',
								'name' => 'allowAnonymous',
							));

            /**
             * Adds a clickable image to send the survey
             */
			$sendButton = new Element\Image('send');
		    $sendButton	-> setLabel('+')
					    -> setAttributes(array(
											'src' 		=> 'img/ico/check.png',
											'width' 	=> '25',
											'height' 	=> '25'
									));

    		
			//  put all together!!!
			$form = new Form('addSurveysForm');
			$form 	-> add($title)
					-> add($description)
					-> add($delegationLevel)
					-> add($tagInput)
					-> add($creator)
					-> add($container)
					-> add($addItemButton)
					-> add($addTextButton)
					-> add($sendButton)
					-> add($private)
					-> add($moderated)
					-> add($allowComments)
					-> add($allowAnonymous)
					->add(array(
						'type'    => 'Zend\Form\Element\DateSelect',
						'name'    => 'Date',
						'options' => array(
							'label'               => 'Expiration Date',
							'create_empty_option' => true,
							'day_attributes'      => array(
								'data-placeholder' => 'Day',
								'style'            => 'width: 20%',
							),
							'month_attributes'    => array(
								'data-placeholder' => 'Month',
								'style'            => 'width: 20%',
							),
							'year_attributes'     => array(
								'data-placeholder' => 'Year',
								'style'            => 'width: 20%',
							)
						)));

			return $form;

		}
	};


