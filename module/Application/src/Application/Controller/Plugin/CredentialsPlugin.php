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


namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ViewModel;


/**
 * This class provides the authentication layer useful
 * to manage users and their credentials
 * Class CredentialsPlugin
 * @package Application\Controller\Plugin
 */
class CredentialsPlugin extends AbstractPlugin{

    /**
     * check if the user is authenticated by the specified controller
     * @param $controller
     * @return int|ViewModel
     *
     */
    public function checkAuthentication($controller){
	    if (! $controller->zfcUserAuthentication()->hasIdentity()) {
	    	$notAuthView = new ViewModel(array(
	    			'reason' => 'not-logged-in'
	    	));
	    	$controller->getResponse()->setStatusCode(401);
	    	$notAuthView->setTemplate('error/401');
	    	return $notAuthView;
	    }return 0;
	}

    /*
     * Returns the account type
     * i.e. the db tuple corresponding to the account type
     * (normal, admin, developer)
     */
	public function getMyAccontType($controller){
	    $user = $controller->zfcUserAuthentication()->getIdentity();
	    $accountType = $user->getAccountType();
	    return $accountType;
	}

    /*
     * Returns the email address of the user
     */
	public function getMyEmail($controller){
		$user = $controller->zfcUserAuthentication()->getIdentity();
		$email = $user->getEmail();
		//error_log($email);
		return $email;
	}

    /*
     * returns the username
     */
	public function getMyUsername($controller){
		$user = $controller->zfcUserAuthentication()->getIdentity();
		$userName = $user->getUsername();
		return $userName;
	}


    /*
     * check if the user is logged as admin
     */
	public function checkAuthenticationIsAdmin($controller){
	    $user = $controller->zfcUserAuthentication()->getIdentity();
	    
	    if(!$user)return false;
	    
	    $accountType = $user->getAccountType();
	    
	    if ($accountType == 'admin') {
	    	return true;
	    }else{
	        return false;
	    }
	}


    /*
     * check if the user logged as tester
     */
	public function checkAuthenticationIsTester($controller){
	    $user = $controller->zfcUserAuthentication()->getIdentity();
	    $accountType = $user->getAccountType();
	     
	    if ($accountType == 'tester') {
	    	return true;
	    }
		 
	}


    /*
     * check if the user is logged with a normal account
     */
	public function checkAuthenticationIsNormal($controller){
		$user = $controller->zfcUserAuthentication()->getIdentity();
		$accountType = $user->getAccountType();
	
		if ($accountType == 'normal') {
			return true;
		}
			
	}


    /**
     * get the user id on the N4J Db
     */
    public function getNodeDbUid($controller, $um){
        return $um->getUserByEmail($this->getMyEmail($controller))->getId();
    }

	
	
}
