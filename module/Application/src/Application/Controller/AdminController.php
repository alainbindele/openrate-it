<?php
/*
 * Application name: OpenRate-it! A general-purpose polling platform Copyright (C) 
 * 2014 Alain Bindele (alain.bindele@gmail.com) 
 * 
 * This file is part of OpenRate-it! OpenRate-it! is free software;
 * you can redistribute it and/or modify it under the terms of the 
 * GNU General Public License as published by the Free Software Foundation; 
 * either version 2 of the License, or (at your option) any later version. 
 * OpenRate-it! is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * See the GNU General Public License for more details. 
 * You should have received a copy of the GNU General Public License 
 * along with this program;
 *  if not, write to the Free Software Foundation, Inc., 51 Franklin Street, 
 *  Fifth Floor, Boston, MA 02110-1301, USA.
 */
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Everyman\Neo4j\Client as N4JClient;
use Everyman\Neo4j\Cypher\Query as InternalCypherQuery;
use Application\Model\UsersManager;
use Application\Model\ContainersManager;
use Application\Model\SurveysManager;
use Application\AdminPanelForm\UsersForm;
use Application\AdminPanelForm\SurveyForm;
use Application\AdminPanelForm\ContainersForm;
use Application\AdminPanelForm\SocialForm;
use Application\AdminPanelForm\RatesForm;
use Application\AdminPanelForm\CommentsForm;
use Zend\Authentication\AuthenticationService;


/**
 * AdminController
 *
 * @author Alain Bindele
 * @package OpenOpenRate-it!
 * @version Pre-Alpha 0.1
 */
class AdminController extends AbstractActionController
{

    /**
     *
     * @param array $usersCollection            
     * @return \Zend\View\Model\ViewModel
     */
    private function craftUsersAndRelationshipsView($usersCollection)
    {
        
        /*
         * USERS-AND-RELATIONS SECTION
         */
        
        // CRAFTING USERS AND CIRCLES FORM
        $usersForm = new UsersForm($usersCollection);
        $socialForm = new SocialForm($usersCollection);
        
        $addUsersForm = $usersForm->getAddUsersForm();
        $addCirclesForm = $usersForm->getAddCirclesForm();
        $deleteUsersForm = $usersForm->getDeleteUsersForm();
        $deleteAllUsersForm = $usersForm->getDeleteAllUsersForm();
        $addSocialRelationForm = $socialForm->getAddSocialRelationForm();
        $deleteSocialRelationForm = $socialForm->getDeleteSocialRelationForm();
        
        // CRAFTING USERS AND CIRCLES VIEWS
        $addUsersView = new ViewModel(array(
            'addUsersForm' => $addUsersForm
        ));
        
        $addCirclesView = new ViewModel(array(
            'addCirclesForm' => $addCirclesForm
        ));
        
        $deleteUsersView = new ViewModel(array(
            'deleteUsersForm' => $deleteUsersForm
        // 'deleteAllUsersForm' => $deleteAllUsersForm
                ));
        
        $deleteAllUsersView = new ViewModel(array(
            'deleteAllUsersForm' => $deleteAllUsersForm
        ));
        $addSocialRelationView = new ViewModel(array(
            'addSocialRelationForm' => $addSocialRelationForm
        ));
        $deleteSocialRelationView = new ViewModel(array(
            'deleteSocialRelationForm' => $deleteSocialRelationForm
        ));
        
        // SETTING TEMPLATES FORM VIEWS
        $addUsersView->setTemplate('usersAndRelationships/addUsers');
        $addCirclesView->setTemplate('usersAndRelationships/addCircles');
        $deleteUsersView->setTemplate('usersAndRelationships/deleteUsers');
        $deleteAllUsersView->setTemplate('usersAndRelationships/deleteUsers');
        $addSocialRelationView->setTemplate('usersAndRelationships/addSocialRelation');
        $deleteSocialRelationView->setTemplate('usersAndRelationships/deleteSocialRelation');
        
        // NESTING VIEWS IN A SUPER-VIEW
        $usersAndRelationsView = new ViewModel();
        $usersAndRelationsView->addChild($addUsersView, 'addUsersView')
            ->addChild($addCirclesView, 'addCirclesView')
            ->addChild($deleteUsersView, 'deleteUsersView')
            ->addChild($addSocialRelationView, 'addSocialRelationView')
            ->addChild($deleteSocialRelationView, 'deleteSocialRelationView');
        
        // SETTING TEMPLATE FOR THE SUPER-VIEW
        $usersAndRelationsView->setTemplate('usersAndRelationships/usersAndRelationships');
        
        return $usersAndRelationsView;
    }

    /**
     *
     * @param array $usersCollection            
     * @return \Zend\View\Model\ViewModel
     */
    private function craftSurveysAndContainersView($usersCollection)
    {
        
        /*
         * surveys-AND-CONTAINERS SECTION
         */
        
        // CRAFTING surveys AND CONTAINERS FORM
        $surveyForm = new SurveyForm($usersCollection);
        $containersForm = new ContainersForm($usersCollection);
        $addSurveyForm = $surveyForm->getAddSurveyForm();
        $addContainersForm = $containersForm->getAddContainerForm();
        
        // CRAFTING surveys AND CONTAINERS VIEWS
        $addSurveysView = new ViewModel(array(
            'addSurveyForm' => $addSurveyForm
        ));
        $addContainersView = new ViewModel(array(
            'addContainersForm' => $addContainersForm
        ));
        
        // SETTING TEMPLATES FORM VIEWS
        $addSurveysView->setTemplate('input-panel/addSurveys');
        $addContainersView->setTemplate('input-panel/addContainers');
        
        // NESTING VIEWS IN A SUPER-VIEW
        
        $surveysAndContainersView = new ViewModel();
        $surveysAndContainersView->addChild($addSurveysView, 'addSurveysView')->addChild($addContainersView, 'addContainersView');
        /* aggiungere altre form della tab surveysAndContainersView */
        
        // SETTING TEMPLATE FOR THE SUPER-VIEW
        $surveysAndContainersView->setTemplate('input-panel/surveysAndContainers');
        
        return $surveysAndContainersView;
    }

    /**
     *
     * @param array $usersCollection            
     * @param array $surveysCollection            
     * @return multitype:\Zend\View\Model\ViewModel
     */
    private function craftRatesView($usersCollection, $surveysCollection)
    {
        
        /*
         * RATES SECTION
         */
        // CRAFTING RATES FORM
        $ratesForm = new RatesForm($usersCollection);
        $addRatesForm = $ratesForm->getRatesForm();
        
        // CRAFTING RATES VIEWS
        $ratesView = new ViewModel(array(
            'addRatesForm' => $addRatesForm
        ));
        
        // SETTING TEMPLATES FORM VIEWS
        $ratesView->setTemplate('input-panel/rates');
        
        // //////////////////
        

        /*
         * COMMENTS SECTION
         */
        
        // CRAFTING COMMENTS FORM
        $commentsForm = new CommentsForm($usersCollection, $surveysCollection);
        $addCommentsForm = $commentsForm->getAddCommentsForm();
        
        $addCommentView = new ViewModel(array(
            'addCommentsForm' => $addCommentsForm
        ));
        
        // SETTING TEMPLATES FORM VIEWS
        $addCommentView->setTemplate('input-panel/addComments');
        
        // PUTTIN' ALL TOGETHER
        $ratesAndCommentsView = new ViewModel();
        $ratesAndCommentsView->setTemplate('input-panel/rates&comments');
        $ratesAndCommentsView->addChild($ratesView, 'RatesView')
                             ->addChild($addCommentView, 'CommentsView');
        return array(
            "ratesView" => $ratesAndCommentsView
        );
    }



    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
        //check if user is autenticated and show him the right view
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $adminUserPanelView = $this->createAdminPanelInterface();
        return $adminUserPanelView;
    }

    /**
     * Craft the Admin Panel View
     * @return \Zend\View\Model\ViewModel
     */
    public function createAdminPanelInterface()
    {
        
        $this->layout('layout/adminPanelLayout');
        $adminPanelView = new ViewModel();
        
        /*
         * GET USERS COLLECTION
         */
        $client = $this->getServiceLocator()->get('Neo4jClientFactory');
        $sl = $this->getServiceLocator();
        $um = new UsersManager($sl, $client);
        $sm = new SurveysManager($sl, $client);
        $usersCollection = $um->getAllUsers();
        $surveysCollection = $sm->getAllSurveys();

        /*
         * CRAFTING OUTPUT CONSOLE VIEW
         */
        $config = $this->getServiceLocator()->get('Config');

        /*
         * CRAFTING USERS AND RELATIONS VIEW
         */
        $usersAndRelationsView = $this->craftUsersAndRelationshipsView($usersCollection);
        
        /*
         * CRAFTING surveys AND CONTAINERS VIEW
         */
        $surveysAndContainersView = $this->craftSurveysAndContainersView($usersCollection);
        
        /*
         * CRAFTING RATES VIEW
         */
        $ratesView = $this->craftRatesView($usersCollection, $surveysCollection);

        // CRAFTING THE INPUT VIEW
        $inputView = new ViewModel();
        $inputView->setTemplate('adminPanel/inputPanel');
        $inputView  ->addChild($usersAndRelationsView, 'usersAndRelationsView')
                    ->addChild($surveysAndContainersView, 'surveysAndContainersView')
                    ->addChild($ratesView['ratesView'], 'ratesView');

        // Compose the final view of the adminPanel
        $adminPanelView->addChild($inputView, 'inputPanel'); 
        
        return $adminPanelView;
    }


    
    /**
     * Clean the neo4j database
     *
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function wipeDbAction()
    {
        // Check if user is logged and if it's an Admin
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        
        $user = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();
        
        if ($accountType == 'admin') {
            // execute the operations on the N4j DB
            $client = new N4JClient('localhost', 7474);
            $client->getTransport();
            $queryTest = "START r=rel(*) DELETE r";
            $query = new InternalCypherQuery($client, $queryTest);
            $result = $query->getResultSet();
            $queryTest = "START n=node(*) DELETE n";
            $query = new InternalCypherQuery($client, $queryTest);
            $result = $query->getResultSet();
        }else{
            $this->getResponse()->setStatusCode(401);
            $notAuthView =  new viewModel();
            $notAuthView -> setTemplate('error/401');
            return $notAuthView;
        }
        return $this->redirect()->toRoute('admin', array(
            'action' => 'index'
        ));
    }
}

 

