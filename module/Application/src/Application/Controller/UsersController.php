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
 * This class manages users interactions
 * Uses the class UsersManager to read and write
 * to database the users informations
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Config\Reader\Json;
use Application\Model\UsersManager;
use Application\Model\SurveysManager;
use Application\Model\ContainersManager;
use Application\Model\Neo4jHelper;



/**
 * UsersController
 * @package OpenRate-it!
 * @author Alain Bindele
 * @version PreAlpha 0.1
 * @filesource UsersController.php
 */
class UsersController extends AbstractActionController {
    /**
     * The default action - show the home page
     */
    private $logPartialPath = "";
    private $logPath = "";

    /**
     * (non-PHPdoc)
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction() {


    }




    /**
     * Add a user to database N4J.
     * HTTP Request type: POST<br>
     * Example:<br>
     * {<br>
     * &nbsp; "name":"Name",<br>
     * &nbsp; "surname":"Surname",<br>
     * &nbsp; "mail":"name@surname.com",<br>
     * &nbsp; "howMany":"1",<br>
     * &nbsp; "accountType":"normal",<br>
     * &nbsp; "phpsessid":"46f18da253fe9fd09028a97eac8f9a2e"<br>
     * }<br>
     * Response example:<br>
     * {<br>
     * &nbsp; "response":true,<br>
     * &nbsp; "newUserId":[320],<br>
     * &nbsp; "newRelsAndContainers":[{"relId":3292,"containerId":321}]<br>
     * }<br>
     * @api
     */
    public function addUserAction()
    {
        //TODO: check integrity with MYSQL side
        //TODO: separate code for auth check

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $user = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();

        if ($accountType == 'admin') {
            // This shows the :controller and :action parameters in default route
            // are working when you browse to /test1/test1-controller/foo
            $request  = $this->getRequest();
            $response = $this->getResponse();



            //MANAGE REQUEST!
            $sl		  = $this->getServiceLocator();
            $client   = $sl->get('Neo4jClientFactory');
            $um       = new UsersManager($sl,$client);
            $sm       = new SurveysManager($sl,$client);
            $cm       = new ContainersManager($sl,$client);
            $params=[];

            if (!$um){
                //SERVER PROBLEM: CANNOT INSTANTIATING USER MANAGER
                $response -> setStatusCode(500);
                return $response;
            }else{
                $par=$this->params();
                if(!$par->fromRoute('from_file')){
                    //PARSING THE REQUEST PARAMETERS FROM REQUEST
                    $body=$request->getContent();
                    if (empty($body)) {
                        $response -> setStatusCode(400);
                        return $response;
                    }
                    $reader = new Json();
                    $json   = $reader->fromString($body);

                    if($json['name']==''||$json['surname']==''||$json['mail']==''||$json['howMany']==''||$json['accountType']==''){
                        $response -> setStatusCode(400);
                        return $response;
                    }
                    $params = array (
                        'name'			=>	$json['name'],
                        'surname'		=>	$json['surname'],
                        'mail'			=>	$json['mail'],
                        'howMany'		=>	$json['howMany'],
                        'accountType'	=>	$json['accountType']
                    );
                    $people = array();
                    array_push($people, $params);
                    $fromFile=false;
                }else{ //PARSING THE REQUEST PARAMETERS FROM FILE
                    $people = array();
                    $people= $this->params()->fromRoute('param');
                    $fromFile=true;
                }
                $newUserId          =   array();
                $relAndContainerId  =   array();
                foreach ($people as $user){
                    if($fromFile==true){
                        $params=array(
                            'name'        =>   $user['name'],
                            'surname'     =>   $user['surname'],
                            'mail'        =>   $user['email'],
                            'howMany'     =>   1,
                            'accountType' =>   'normal'
                        );
                    }
                    for($i = 0 ; $i<(int)$params['howMany'] ; $i++){
                        $log                =   "\n PARAMS:\n".json_encode($params)."\n";
                        //STORE INFO ON DB
                        $newUserId[$i]      =   $um->addUser($params);

                        //CONTROL INFO RETURNED
                        if($newUserId[$i]){ //IF User was SUCCESSFULLY created

                            // create DEFAULT Container and Relation to it too
                            $relAndContainerId[$i] = $sm->addDefaultContainerRel($newUserId[$i],$cm);

                            // if relation and container are SUCCESSFULLY created
                            // var_dump($relAndContainerId);
                            if ($relAndContainerId[$i]) {
                                //add ids to array and continue!!

                            } else {
                                $response->setContent(json_encode(array(
                                    'response' => false,
                                    'message' => 'could not create user'
                                )));
                                $response->setStatusCode(500);
                            }


                        }else{ //user wasn't created
                            $response->setContent(json_encode(array('response' => false, 'newUserId' =>"-1",'json'=>$json)));
                            $response ->setStatusCode(500); // GENERIC CODE TODO: improve error management
                            return $response;

                        }
                    }  //<<< end for howmany
                }

                if($fromFile==false){
                    $response->setContent(json_encode(array('response' => true, 'newUserId' =>$newUserId,'newRelsAndContainers'=>$relAndContainerId)));
                    $response ->setStatusCode(201); //HTTP OK!
                    return $response;
                }else{
                    return $this->redirect()->toRoute('admin', array(
                        'action' => 'index'
                    ));
                }
            } // <<< END ELSE if(UM)
        }else{
            $this->getResponse()->setStatusCode(401);
            $notAuthView =  new viewModel();
            $notAuthView -> setTemplate('error/401');
            return $notAuthView;
        }
    }

    /**
     * Returns the JSON representation of the user record on the n4j DB.
     * HTTP request type: GET<br>
     * @api
     * @return \Zend\View\Model\ViewModel|\Zend\Stdlib\ResponseInterface
     */
    public function getUserAction() {
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $sl		  = $this->getServiceLocator();
        $response = $this->getResponse();

        //MANAGE REQUEST!

        $client = $sl->get('Neo4jClientFactory');
        $um     = new UsersManager($sl,$client);
        $n4jh   = new Neo4jHelper($um,$client);

        if (!$um){
            //SERVER PROBLEM: CANNOT INSTANTIATING USERS MANAGER
            $response -> setStatusCode(500);
            return $response;
        }
        else {
            // get the user iD
            $id = (int)$this->params('id');
            // take the user
            $content = $um->getUser($id);

            $myArray=json_decode($content);
            $myArray=get_object_vars($myArray);
            /*
             * Check if node requested is a user node
             */
            if($myArray['class']!="Application\\Model\\Entity\\UserNode"){
                $response -> setMetadata('Content-type', 'application/json; charset=utf-8');
                $response -> setContent(json_encode(array("error"=>'user not found')));
                $response -> setStatusCode(404); // TODO: separate client code between mobile API and browsers
                return $response;

            }
            //Workaround to memorize nested json formats in neo4j property fields!!
            $myArray['id'] = $id;

            $response -> setMetadata('Content-type', 'application/json; charset=utf-8');
            $response -> setContent(json_encode($myArray));
            $response -> setStatusCode(200);
            return $response;
        }

    }


    /**
     * Action that returns to the client a JSON list with all users ID
     * on n4j DB.
     * HTTP request type: GET<br>
     * @api
     * @return \Zend\View\Model\ViewModel|\Zend\Stdlib\ResponseInterface
     */
    public function getAllUsersIdsJsonAction(){
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin ->checkAuthentication($this);
        if($status)return $status;
        $user = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();

        if ($accountType == 'admin') {
            // initialize needed data structures
            $sl		  = $this->getServiceLocator();
            $response = $this->getResponse();

            //MANAGE REQUEST!

            $client = $sl->get('Neo4jClientFactory');
            $um = new UsersManager($sl,$client);
            $users=array();
            foreach ($um->getAllUsers() as $user){
                array_push($users, $user->getId());
            }
            $response->setContent(json_encode($users));
            return $response;
        }else{
            $this->getResponse()->setStatusCode(401);
            $notAuthView =  new viewModel();
            $notAuthView -> setTemplate('error/401');
            return $notAuthView;
        }

    }

    /**
     * Returns a list of all the users present in n4j DB
     * in a view that presents them in an html select object
     * HTTP request type: GET
     * @api
     * @return void|\Zend\View\Model\ViewModel|\Zend\Stdlib\ResponseInterface
     */
    public function getAllUsersAction(){

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $user = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();

        if ($accountType == 'admin') {
            // This shows the :controller and :action parameters in default route
            // are working when you browse to /test1/test1-controller/foo
            $sl = $this->getServiceLocator();
            $request = $this->getRequest();
            $response = $this->getResponse();
            //MANAGE REQUEST!
            if ($request->isGet()) {
                $client = $sl->get('Neo4jClientFactory');
                $um = new UsersManager($sl,$client);
                if (!$um){
                    $response->setContent(json_encode(array('response' => false)));
                    return $response;
                }
                else {
                    $usersCollection=$um->getAllUsers();
                    //$response->setContent(json_encode(array('response' => true, 'list' => $responseArray)));
                    $view= new ViewModel(array('response' => true, 'list' => $usersCollection));
                    $view->setTemplate('usersAndRelationships/getAllUsers');
                    return $view;
                }
            }
        }else{
            $this->getResponse()->setStatusCode(401);
            $notAuthView =  new viewModel();
            $notAuthView -> setTemplate('error/401');
            return $notAuthView;
        }
        return;
    }



    /**
     * Updates the user specified in the POST request on N4j DB
     * in a view that presents them in an html select object
     * HTTP request type: GET
     * @api
     * @return void|\Zend\View\Model\ViewModel|\Zend\Stdlib\ResponseInterface
     */
    public function updateUserAction(){

        //TODO: check integrity with MYSQL side
        //TODO: separate code for auth check

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $user = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();
        $email = $authPlugin->getMyEmail($this);
        $sl		  = $this->getServiceLocator();
        $client   = $sl->get('Neo4jClientFactory');
        $um = new UsersManager($sl, $client);
        $user = $um ->getUserByEmail($email);
        $uid = $user->getId();


        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $request  = $this->getRequest();
        $response = $this->getResponse();



        //MANAGE REQUEST!
        $sl		  = $this->getServiceLocator();
        $client   = $sl->get('Neo4jClientFactory');
        $um       = new UsersManager($sl,$client);

        if (!$um){
            //SERVER PROBLEM: CANNOT INSTANTIATING USER MANAGER
            $response -> setStatusCode(500);
            return $response;
        }else{
            //PARSING THE REQUEST PARAMETERS FROM REQUEST
            $body=$request->getContent();
            if (empty($body)) {
                $response -> setStatusCode(400);
                return $response;
            }
            if($request->isPut()){
                //grabbing request
                $reader = new Json();
                $json   = $reader->fromString($body);
                //parsing request
                if ($accountType == 'admin') {
                    !isset($json['uid'])   ||$json['uid']     ==''? $params['uid']=$uid:$params['uid']=$json['uid'];
                    !isset($json['accountType'])   ||$json['accountType']     ==''? $params['accountType']=$uid:$params['accountType']=$json['accountType'];
                }else if($accountType = 'normal'){
                    if(!isset($json['uid'])   ||$json['uid'] =='')
                        $params['uid'] = $uid;
                    else if($json['uid']!= $uid){
                        //normal users are not authorized to update other user infos
                        $this->getResponse()->setStatusCode(401);
                        $notAuthView =  new viewModel();
                        $notAuthView -> setTemplate('error/401');
                        return $notAuthView;
                    }
                }
                //TODO: add parameters checking
                !isset($json['name'])   ||$json['name']     ==''? $params['name']='':$params['name']=$json['name'];
                !isset($json['surname'])||$json['surname']  ==''? $params['surname']='':$params['surname']=$json['surname'];
                !isset($json['mail'])   ||$json['mail']     ==''? $params['mail']='':$params['mail']=$json['mail'];
                // CORE FUNCTION
                try{
                    $um->updateUserInfoNode($params);
                }catch(\Exception $e){
                    //TODO: check client type: Browsers and mobile apps should have different returntypes/
                    $this->getResponse()->setStatusCode(404);
                    $internalErrorView =  new viewModel();
                    $internalErrorView -> setTemplate('error/404');
                    return $internalErrorView;
                }

                $response->setContent(json_encode(array('status' => 'success',
                    'data'=>array('updatedUserId' =>$uid))));
                $response ->setStatusCode(201); //HTTP OK!
                return $response;
            }
        } // <<< END ELSE if(UM)
    }



    /**
     * Updates the user specified in the POST request on N4j DB
     * in a view that presents them in an html select object
     * HTTP request type: GET
     * @api
     * @return void|\Zend\View\Model\ViewModel|\Zend\Stdlib\ResponseInterface
     */
    public function updateUserCircleAction(){

        //TODO: check integrity with MYSQL side
        //TODO: separate code for auth check

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $user = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();
        $email = $authPlugin->getMyEmail($this);
        $sl		  = $this->getServiceLocator();
        $client   = $sl->get('Neo4jClientFactory');
        $um = new UsersManager($sl, $client);
        $user = $um ->getUserByEmail($email);
        $uid = $user->getId();


        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $request  = $this->getRequest();
        $response = $this->getResponse();



        //MANAGE REQUEST!
        $sl		  = $this->getServiceLocator();
        $client   = $sl->get('Neo4jClientFactory');
        $um       = new UsersManager($sl,$client);

        if (!$um){
            //SERVER PROBLEM: CANNOT INSTANTIATING USER MANAGER
            $response -> setStatusCode(500);
            return $response;
        }else{
            //PARSING THE REQUEST PARAMETERS FROM REQUEST
            $body=$request->getContent();
            if (empty($body)) {
                $response -> setStatusCode(400);
                return $response;
            }
            if($request->isPut()){
                //grabbing request
                $reader = new Json();
                $json   = $reader->fromString($body);
                //parsing request
                if ($accountType == 'admin') {

                }else if($accountType = 'normal'){
                    if(!isset($json['uid'])   || $json['uid'] =='')
                        $params['uid'] = $uid;
                    else if($json['uid']!= $uid){
                        //normal users are not authorized to update other user infos
                        $this->getResponse()->setStatusCode(401);
                        $notAuthView =  new viewModel();
                        $notAuthView -> setTemplate('error/401');
                        return $notAuthView;
                    }
                }
                //TODO: add parameters checking
                $oldCircleName = $json['oldName'];
                $newCircleName = $json['newName'];

                try{
                    $um -> updateUserCircle($uid,$oldCircleName,$newCircleName);
                }catch(\Exception $e){
                    //TODO: check client type: Browsers and mobile apps should have different return types
                    $this->getResponse()->setStatusCode(404);
                    $internalErrorView =  new viewModel();
                    $internalErrorView -> setTemplate('error/404');
                    return $internalErrorView;
                }

                $response->setContent(json_encode(array('status' => 'success',
                    'data'=>array('updatedUserId' => $uid))));
                $response ->setStatusCode(200); //HTTP OK!
                return $response;
            }
        } // <<< END ELSE if(UM)
    }

    /**
     * Updates the user specified in the POST request on N4j DB
     * in a view that presents them in an html select object
     * HTTP request type: GET
     * @api
     * @return void|\Zend\View\Model\ViewModel|\Zend\Stdlib\ResponseInterface
     */
    public function deleteCircleAction(){

        //TODO: check integrity with MYSQL side
        //TODO: separate code for auth check

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status     = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $user = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();
        $email       = $authPlugin->getMyEmail($this);
        $sl		  = $this->getServiceLocator();
        $client   = $sl->get('Neo4jClientFactory');
        $um = new UsersManager($sl, $client);
        $user = $um ->getUserByEmail($email);
        $uid = $user->getId();


        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $request  = $this->getRequest();
        $response = $this->getResponse();



        //MANAGE REQUEST!
        $sl		  = $this->getServiceLocator();
        $client   = $sl->get('Neo4jClientFactory');
        $um       = new UsersManager($sl,$client);

        if (!$um){
            //SERVER PROBLEM: CANNOT INSTANTIATING USER MANAGER
            $response -> setStatusCode(500);
            return $response;
        }else{
            //PARSING THE REQUEST PARAMETERS FROM REQUEST
            $body=$request->getContent();
            if (empty($body)) {
                $response -> setStatusCode(400);
                return $response;
            }
            if($request->isDelete()){
                //grabbing request
                $reader = new Json();
                $json   = $reader->fromString($body);
                //parsing request
                if ($accountType == 'admin') {

                }else if($accountType = 'normal'){
                    if(!isset($json['uid']) || $json['uid'] =='')
                        $params['uid'] = $uid;
                    else if($json['uid']!= $uid) {
                        //normal users are not authorized to update other user infos
                        $this->getResponse()->setStatusCode(401);
                        $notAuthView =  new viewModel();
                        $notAuthView -> setTemplate('error/401');
                        return $notAuthView;
                    }
                }
                //TODO: add parameters checking
                $circleName = $json['name'];
                try{
                    $um -> deleteUserCircle($uid,$circleName);
                }catch(\Exception $e){
                    //TODO: check client type: Browsers and mobile apps should have different return types
                    $this->getResponse()->setStatusCode(404);
                    $internalErrorView =  new viewModel();
                    $internalErrorView -> setTemplate('error/404');
                    return $internalErrorView;
                }

                $response->setContent(json_encode(array('status' => 'success',
                    'data'=>array('updatedUserId' =>$uid))));
                $response ->setStatusCode(201); //HTTP OK!
                return $response;
            }
        } // <<< END ELSE if(UM)
    }

    /**
     * Delete a single user from database N4J<br>
     * HTTP request type: DEL
     * TODO: check integrity with MYSQL side
     */
    public function deleteUserAction(){

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $sl		  = $this->getServiceLocator();
        $request  = $this->getRequest();
        $response = $this->getResponse();

        //MANAGE REQUEST!
        if ($request->isDelete()){
            $client = $this->getServiceLocator()->get('Neo4jClientFactory');
            $um = new usersManager($sl,$client);
            if (!$um){
                // CANNOT CREATE A NEW INSTANCE OF USERSMANAGER
                $response->setStatusCode(500);
                $response->setContent(json_encode(array('response' => false)));
                return $response;
            }
            else {
                // get the user iD
                $id = (int)$this->params('id');
                //TODO: TRY CATCH
                $um->deleteUser($id);

                $logMessage="Node". $this->params('id'). "deleted!";
                //file_put_contents($logPartialPath,$logMessage);
                $response->setStatusCode(200);
                return $response;

            }
        }
        return $this->redirect()->toRoute('admin', array(
            'action' => 'index',
        ));
    }


    /**
     * Delete all users from n4j DB
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAllUsersAction(){

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)   return $status;
        $sl		    = $this->getServiceLocator();
        $client     = $this->getServiceLocator()->get('Neo4jClientFactory');
        $um         = new usersManager($sl,$client);
        $usersResult= $um->deleteAll();
        $view       = new ViewModel(array('controllerInfo'=>"Some info",'usersResult'=>$usersResult));

        $this->layout('layout/adminPanelLayout');
        $view->setTemplate('application/admin/index.phtml');
        return $view;
    }


    /**
     * Add the circles passed in input to the user node<br>
     * HTTP request type: POST<br>
     * Request example:<br>
     * {"circles":["Friends","Family"],"user":"164","PHPSESSID":"46f18da253fe9fd09028a97eac8f9a2e"}
     * Response example:<br>
     * {"response":true}
     * @api
     * @return \Zend\View\Model\ViewModel|\Zend\Stdlib\ResponseInterface|Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function addCircleAction()
    {
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $sl		    = $this->getServiceLocator();
        $client     = $this->getServiceLocator()->get('Neo4jClientFactory');
        $um         = new usersManager($sl,$client);
        $request    = $this->getRequest();
        $response   = $this->getResponse();



        //MANAGE REQUEST!

        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            if (!$um){
                // CANNOT CREATE A NEW INSTANCE OF USERSMANAGER
                $response->setContent(json_encode(array('status' => 'success')));
                return $response;
            }
            else {
                //PARSING THE REQUEST PARAMETERS
                $body=$request->getContent();
                if (!empty($body)) {
                    $json = json_decode($body, true);
                }else{
                    $response -> setStatusCode(400);
                    return $response;
                }
                $reader = new Json();
                $json   = $reader->fromString($body);

                foreach($json['circles'] as $key=>$value){
                    if($value==''){
                        $response -> setStatusCode(400);
                        return $response;
                    }
                }

                if($json['user']==''||$json['circles']==''){
                    $response -> setStatusCode(400);
                    return $response;
                }

                //TODO: REMEMBER TO SANITIZE INPUT AND TRY/CATCH
                $um->createCircle(array (
                    'uid'			=> $json['user'],
                    'circlesNames' 	=> $json['circles'],
                ));
                $response -> setContent(json_encode(array('response' => true)));
                $response -> setStatusCode(200);
                return $response;
            }
        }
        return $this->redirect()->toRoute('admin', array(
            'action' => 'index',
        ));
    }



    /**
     * Request: a userId
     * Response: a collection of circles of the specified user in JSON format
     * HTTP request type: POST
     * @api
     */
    public function getCirclesAction(){

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $sl		  = $this->getServiceLocator();
        $request  = $this->getRequest();
        $response = $this->getResponse();

        $client = $this->getServiceLocator()->get('Neo4jClientFactory');
        $um = new usersManager($sl,$client);
        if (!$um){
            $response->setContent(json_encode(array('response' => false)));
            return $response;
        }
        else {
            //PARSING THE REQUEST PARAMETERS
            $uid = (int) $this->params('id');
            $response->setContent(json_encode( $um->getUserCirclesCollection($uid) ));
            return $response;
        }
    }


    /**
     * Request: a couple userId
     * Response: the id of the relation created
     * HTTP request type: POST
     * @api
     */
    public function createSocialRelationshipAction(){

        //check if user is logged
        $authPlugin  = $this->CredentialsPlugin();
        $status      = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $user        = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();

        if ($accountType == 'admin') {
            // This shows the :controller and :action parameters in default route
            // are working when you browse to /test1/test1-controller/foo
            $sl		  = $this->getServiceLocator();
            $request  = $this->getRequest();
            $response = $this->getResponse();

            //MANAGE REQUEST!


            if ($request->isPost()) {
                $client = $this->getServiceLocator()->get('Neo4jClientFactory');
                $um = new usersManager($sl,$client);
                if (!$um){
                    // CANNOT CREATE A NEW INSTANCE OF USERSMANAGER
                    $response->setContent(json_encode(array('response' => false)));
                    $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                    return $response;
                }
                else {
                    //PARSING THE REQUEST PARAMETERS
                    $body=$request->getContent();
                    if (!empty($body)) {
                        $json = json_decode($body, true);
                    }else{
                        $response -> setStatusCode(400);
                        return $response;
                    }
                    $reader = new Json();
                    $json   = $reader->fromString($body);


                    //TODO: REMEMBER TO SANITIZE INPUT AND TRY/CATCH
                    try{
                        $ret = $um->createSocialRelationship(array (
                            'sUid'		 => $json['startUser'],
                            'eUid'		 => $json['endUser'],
                            'circles' 	 => $json['circles']
                        ));
                    }catch(Zend_Exception $e){
                        $response ->setStatusCode(500);
                        echo "UsersController:err";
                        return $response;
                    }
                    //redirect to admin control panel
                    $response->setContent(json_encode(array('response' => true,"RelId"=>$ret)));
                    $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                    $response ->setStatusCode(201);
                    return $response;

                }

            }
        }else{
            $this->getResponse()->setStatusCode(401);
            $notAuthView =  new viewModel();
            $notAuthView -> setTemplate('error/401');
            return $notAuthView;
        }
    }


    /**
     * Request: a couple userId
     * Response: the id of the relation created
     * HTTP request type: POST
     * @api
     */
    public function requestSocialRelationshipAction(){

        //check if user is logged
        $authPlugin  = $this->CredentialsPlugin();
        $status      = $authPlugin -> checkAuthentication($this);
        if ($status) return $status;
        $user        = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();

        if ($accountType == 'admin') {
            // This shows the :controller and :action parameters in default route
            // are working when you browse to /test1/test1-controller/foo
            $sl		  = $this->getServiceLocator();
            $request  = $this->getRequest();
            $response = $this->getResponse();

            //MANAGE REQUEST!


            if ($request->isPost()) {
                $client = $this->getServiceLocator()->get('Neo4jClientFactory');
                $um = new usersManager($sl,$client);
                if (!$um){
                    // CANNOT CREATE A NEW INSTANCE OF USERSMANAGER
                    $response->setContent(json_encode(array('response' => false)));
                    $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                    return $response;
                }
                else {
                    //PARSING THE REQUEST PARAMETERS
                    $body=$request->getContent();
                    if (!empty($body)) {
                        $json = json_decode($body, true);
                    }else{
                        $response -> setStatusCode(400);
                        return $response;
                    }
                    $reader = new Json();
                    $json   = $reader->fromString($body);


                    //TODO: REMEMBER TO SANITIZE INPUT AND TRY/CATCH
                    try{
                        $ret = $um->createSocialRelationship(array (
                            'sUid'		 => $json['startUser'],
                            'eUid'		 => $json['endUser'],
                            'circles' 	 => $json['circles']
                        ));
                    }catch(Zend_Exception $e){
                        $response ->setStatusCode(500);
                        echo "UsersController:err";
                        return $response;
                    }
                    //redirect to admin control panel
                    $response->setContent(json_encode(array('response' => true,"RelId"=>$ret)));
                    $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                    $response ->setStatusCode(201);
                    return $response;

                }

            }
        }else{
            $this->getResponse()->setStatusCode(401);
            $notAuthView =  new viewModel();
            $notAuthView -> setTemplate('error/401');
            return $notAuthView;
        }
    }



    /**
     * Request: a couple userId
     * Response: the id of the relation created
     * HTTP request type: POST
     * @api
     */
    public function confirmSocialRelationshipAction(){

        //check if user is logged
        $authPlugin  = $this->CredentialsPlugin();
        $status      = $authPlugin -> checkAuthentication($this);
        if ($status) return $status;
        $user        = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();

        if ($accountType == 'admin') {
            // This shows the :controller and :action parameters in default route
            // are working when you browse to /test1/test1-controller/foo
            $sl		  = $this->getServiceLocator();
            $request  = $this->getRequest();
            $response = $this->getResponse();

            //MANAGE REQUEST!
            if ($request->isPost()) {
                $client = $this->getServiceLocator()->get('Neo4jClientFactory');
                $um = new usersManager($sl,$client);
                if (!$um){
                    // CANNOT CREATE A NEW INSTANCE OF USERSMANAGER
                    $response->setContent(json_encode(array('response' => false)));
                    $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                    return $response;
                }
                else {
                    //PARSING THE REQUEST PARAMETERS
                    $body=$request->getContent();
                    if (!empty($body)) {
                        $json = json_decode($body, true);
                    }else{
                        $response -> setStatusCode(400);
                        return $response;
                    }
                    $reader = new Json();
                    $json   = $reader->fromString($body);


                    //TODO: REMEMBER TO SANITIZE INPUT AND TRY/CATCH
                    try{
                        $ret = $um->confirmSocialRelationship(array (
                            'sUid'		 => $json['startUser'],
                            'eUid'		 => $json['endUser'],
                            'circles' 	 => $json['circles']
                        ));
                    }catch(Zend_Exception $e){
                        $response ->setStatusCode(500);
                        echo "UsersController:err";
                        return $response;
                    }
                    //redirect to admin control panel
                    $response->setContent(json_encode(array('response' => true,"RelId"=>$ret)));
                    $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                    $response ->setStatusCode(201);
                    return $response;
                }
            }
        }else{
            $this->getResponse()->setStatusCode(401);
            $notAuthView =  new viewModel();
            $notAuthView -> setTemplate('error/401');
            return $notAuthView;
        }
    }

    /**
     * Deletes the specified social relation<br>
     * HTTP request type: DEL
     * @api
     * @return \Zend\View\Model\ViewModel|\Zend\Stdlib\ResponseInterface
     */
    public function deleteSocialRelationshipAction(){

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $sl		  = $this->getServiceLocator();
        $request  = $this->getRequest();
        $response = $this->getResponse();


        //MANAGE REQUEST!


        if ($request->isDelete()) {
            $client = $this->getServiceLocator()->get('Neo4jClientFactory');
            $um = new usersManager($sl,$client);
            if (!$um){
                // CANNOT CREATE A NEW INSTANCE OF USERSMANAGER
                $response->setContent(json_encode(array('response' => false)));
                return $response;
            }
            else {
                //PARSING THE REQUEST PARAMETERS
                $body=$request->getContent();
                if (!empty($body)) {
                    $json = json_decode($body, true);
                }else{
                    $response -> setStatusCode(400);
                    return $response;
                }
                $reader = new Json();
                $json   = $reader->fromString($body);


                //TODO: REMEMBER TO SANITIZE INPUT AND TRY/CATCH
                try{
                    $ret = $um->deleteSocialRelationship(array (
                        'sUid'		=> $json['startUser'],
                        'contacts' 	=> $json['contacts'],
                    ));
                }catch(Zend_Exception $e){
                    $response ->setStatusCode(500);
                    echo "UsersController:err";
                    return $response;
                }

                $response -> getHeaders() -> addHeaderLine('Content-Type', 'application/json');
                $response -> setStatusCode(200);
                $response ->setContent(json_encode(array("status"=>"success")));
                return $response;
            }
        }
    }


    /**
     * API call that returns the caller's user ID
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function getMyIdAction()
    {
        //check if user is logged
        $authPlugin = $this       -> CredentialsPlugin();
        $status     = $authPlugin -> checkAuthentication($this);
        $response   = $this->getResponse();
        if($status){
            $response -> setStatusCode(400);
            $response->setContent(json_encode(array("status" => "failed",
                "data"  => array("info"=>"authentication failed")
            )));
            return $response;
        }
        $client   = $this->getServiceLocator() -> get('Neo4jClientFactory');
        $sl		  = $this->getServiceLocator();
        $um       = new usersManager($sl,$client);

        if (!$um){
            $response -> setStatusCode(500);
            $response->setContent(json_encode(array('status' => "error",
                'data' => array('info'=>"internal server error")
            )));
            return $response;
        }
        // Getting user credentials
        $email = $authPlugin->getMyEmail($this);
        $user = $um ->getUserByEmail($email);
        if($user){
            $response -> setStatusCode(200);
            $response -> setContent(json_encode(array('id'=> $user-> getId())));
            return $response;
        }
        else{
            $response -> setStatusCode(500);
            $response->setContent(json_encode(array('status' => "error",
                'data' => array('info'=>"user not found")
            )));
            return $response;
        }
    }




    /**
     * Returns all user contacts in JSON format<br>
     * HTTP request type: GET<br>
     * @api
     * @return \Zend\View\Model\ViewModel|\Zend\Stdlib\ResponseInterface
     */
    public function getUserContactsAction(){

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        $response = $this->getResponse();
        if($status){
            $response -> setStatusCode(400);
            $response->setContent(json_encode(array("status" => "failed",
                "data"  => array("info"=>"authentication failed")
            )));
            return $response;
        }
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $sl		  = $this->getServiceLocator();


        $client = $this->getServiceLocator()->get('Neo4jClientFactory');
        $um = new usersManager($sl,$client);
        if (!$um){
            $response->setContent(json_encode(array('response' => false)));
            return $response;
        }
        else {
            //PARSING THE REQUEST PARAMETERS
            if(!isset($_REQUEST['option'])){
                $uid = (int) $this->params('id');
                if(!$uid)
                    $uid = $um->getUserByEmail($authPlugin->getMyEmail($this))->getId();
                $responseJson=json_encode($um->getUserContacts($uid));
                $response->setContent($responseJson);
                $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                return $response;
            }elseif ($_REQUEST['option']=='json'){
                $response->setContent(json_encode($um->getUserContacts($_REQUEST['startUid'])));
                $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                return $response;
            }
        }
    }



    /**
     * Returns all user contacts in JSON format<br>
     * HTTP request type: GET<br>
     * @api
     * @return \Zend\View\Model\ViewModel|\Zend\Stdlib\ResponseInterface
     */
    public function getDetailedUserContactsAction(){

        //check if user is logged
        $authPlugin = $this       -> CredentialsPlugin();
        $status     = $authPlugin -> checkAuthentication($this);
        $response = $this->getResponse();
        if($status){
            $response -> setStatusCode(400);
            $response->setContent(json_encode(array("status" => "failed",
                "data"  => array("info"=>"authentication failed")
            )));
            return $response;
        }

        $sl		  = $this -> getServiceLocator();
        $client = $this->getServiceLocator()->get('Neo4jClientFactory');
        $um = new usersManager($sl,$client);
        if (!$um){
            $response->setContent(json_encode(array('response' => false)));
            return $response;
        }
        else {
            //PARSING THE REQUEST PARAMETERS
            if(!isset($_REQUEST['option'])){
                $uid = (int) $this->params("id");
                // get the user id
                if(!$uid)
                    $uid = $authPlugin->getNodeDbUid($this, $um);
                // get the user details
                $responseJson=json_encode($um->getDetailedUserContacts($uid));
                // setting the response content
                $response->setContent($responseJson);
                $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                return $response;
            }elseif ($_REQUEST['option']=='json'){
                $response->setContent(json_encode($um->getDetailedUserContacts($_REQUEST['startUid'])));
                $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                return $response;
            }
        }
    }
}
