<?php
/*
 * Application name: Rate-it!
* A general-purpose polling platform
* Copyright (C) 2014  Alain Bindele (alain.bindele@gmail.com)
* This file is part of Rate-it!
* Rate-it! is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* Rate-it! is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/*
 * @package rateit
 * @author Alain Bindele
 * @version pre-alpha 0.1
 */


namespace Application\AdminPanelForm;


use Zend\Form\Element;
use Zend\Form\Form;



class ProxyRatesForm extends Form{
	
	protected $usersItems = array('select user'=>'Select user');
	protected $surveysItems  = array('select unit'=>'Select unit');
	
	function __construct($usersCollection=array(),$surveysCollection=array()){
		if(sizeof($usersCollection)>0){
			foreach ($usersCollection as $u){
				$this->usersItems[$u->getId()]=$u->getId()." - ".$u->getName();
			}
		}
		if(sizeof($surveysCollection)>0){
			foreach ($surveysCollection as $u){
				$this->surveysItems[$u->getId()]=$u->getId();
			}
		}
		
		
		return $this;
	}
	
	function getAddProxyRatesForm(){
		
		//TODO: get user list
		
		$startUser  =  new Element\Select('startUser');
		$startUser 	-> setLabel('Select Voter')
					-> setOptions(array(
							'value_options'	=> $this->usersItems
					));

		$endUser  =  new Element\Select('endUser');
		$endUser 	-> setLabel('Select Delegate')
            		-> setOptions(array(
            				'value_options' => $this->usersItems
            		));
					
		$unitSelect = new Element\Select('unitSelect');
		$unitSelect 	-> setLabel('Select Unit')
						-> setOptions(array(
						    'value_options'	=> $this->surveysItems
				        ));
					
		$flagsRadioButtons =  new Element\MultiCheckbox('options');
		$flagsRadioButtons -> setOptions(array('value_options' => array(
												'delegationReqest' => 'Delegation Reqest',
												'delegation' => 'Delegation'
											)
										)
									);
						
						
		$sendButton = new Element\Image('send');
		$sendButton	-> setLabel('+')
					-> setAttributes(array(
											'src' 		=> 'img/ico/check.png',
											'width' 	=> '25',
											'height' 	=> '25'
									));
		
			
		$form = new Form('proxyRateForm');
		$form 	-> add($startUser)
				-> add($endUser)
				-> add($unitSelect)
				-> add($flagsRadioButtons)
				-> add($sendButton);
					 
		return $form;
		
	}
	
function getDeleteProxyRatesForm(){
		
		//TODO: get user list
		
		$startUser  =  new Element\Select('startUser');
		$startUser 	-> setLabel('Select Rater')
					-> setOptions(array('value_options' => array(
								'1'=>'1',
								'2'=>'2',
								'3'=>'3',
								'4'=>'4',
								'5'=>'5'
								//TODO:PARAMETRIZE ME!
					)
		));

		$endUser  =  new Element\Select('endUser');
		$endUser 	-> setLabel('Select Delegate')
		-> setOptions(array('value_options' => array(
				'1'=>'1',
				'2'=>'2',
				'3'=>'3',
				'4'=>'4',
				'5'=>'5'
				//TODO:PARAMETRIZE ME!
		)
		));
					
		$unitSelect = new Element\Select('unitSelect');
		$unitSelect 	-> setLabel('Select Unit')
						-> setOptions(array('value_options' => array(
								'Unit1'=>'Unit1',
								'Unit2'=>'Unit2',
								'Unit3'=>'Unit3',
								'Unit4'=>'Unit4',
								'Unit5'=>'Unit5'
								//TODO:PARAMETRIZE ME!
						)
				));
					
		$flagsCheckBox =  new Element\MultiCheckbox('options');
		$flagsCheckBox -> setOptions(array('value_options' => array(
												'delegationReqest' => 'Delegation Reqest',
												'delegation' => 'Delegation'
											)
										)
									);
						
						
		$sendButton = new Element\Image('send');
		$sendButton	-> setLabel('+')
					-> setAttributes(array(
											'src' 		=> 'img/ico/check.png',
											'width' 	=> '25',
											'height' 	=> '25'
									));
		
			
		$form = new Form('proxyVoteForm');
		$form 	-> add($startUser)
				-> add($endUser)
				-> add($unitSelect)
				-> add($flagsCheckBox)
				-> add($sendButton);
					 
		return $form;
		
	}

};
