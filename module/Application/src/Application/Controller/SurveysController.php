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
 * This class manages surveys in a REST way
 * Contains all methods used to create,access, modify and destroy
 * surveys.
 *
 * @author Alain Bindele
 *
 */
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\SurveysManager;
use Application\Model\TagsManager;
use Zend\Config\Reader\Json;
use Application\Model\ContainersManager;
use Monolog\Handler\error_log;
use Application\Model\UsersManager;
include_once getcwd() . '/module/Application/src/Application/Utils.php';

/**
 * SurveyController
 *
 * @package OpenRate-it!
 * @author Alain Bindele
 * @version Pre-Alpha 0.1
 */
class SurveysController extends AbstractActionController
{

    private $logPartialPath = "";

    private $logPath = "";

    /**
     * Add a survey to database N4J <br>
     * HTTP request type: POST<br>
     * TODO: check integrity with MYSQL side
     * @api
     */
    public function addSurveyAction()
    {
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $sl = $this->getServiceLocator();
        $request = $this->getRequest();
        $response = $this->getResponse();

        // Create a neo4jHelper to make the relationship...(yes HumusNeo4J wrapper
        // does not support relationship creation yet... :( )
        // MANAGE REQUEST!

        if ($request->isPost()) {

            $client = $this->getServiceLocator()->get('Neo4jClientFactory');
            $sm = new SurveysManager($sl, $client);
            $tm = new TagsManager($sl, $client);

            if (! $sm || ! $tm) {
                // SERVER PROBLEM: CANNOT INSTANTIATING FORM MANAGER
                $response->setStatusCode(500);
                return $response;
            } else {
                // PARSING THE REQUEST PARAMETERS
                $body = $request->getContent();
                if (empty($body)) {
                    $response->setContent(json_encode(array(
                        'status'=> 'error',
                        'ERR' => 'Malformed Request'
                    )));
                    $response->setStatusCode(400);
                    return $response;
                }

                $reader = new Json();
                $json = $reader->fromString($body);
                // TODO: parse json and check formatting correctness
                foreach ($json['units'] as $unitName => &$unit) {
                    $unit['nVotes'] = 0;
                }
                if (! isset($json['circles']) || $json['circles'] == '') {
                    if ($json['flags']['private'] == 'false' || ! isset($json['flags']['private'])) {
                        $json['circles'] = array(
                            'public'
                        );
                    }
                }
                if (! isset($json['flags']))
                    $json['flags'] = array();
                if( ! isset($json['container'])||$json['container']==''){
                    $json['container']="default";
                }

                $params = array(
                    'title' => $json['title'],
                    'description' => $json['description'],
                    'tags' => $json['tags'],
                    'flags' => $json['flags'], // array of active flags
                    'creator' => $json['creator'],
                    'container' => $json['container'],
                    'circles' => $json['circles'],
                    'delegationLevel' => $json['delegationLevel'],
                    'units' => $json['units']
                );

                // SINCE NEO4J DOES NOT SUPPORT DIRECTLY JSON FORMAT IN FIELDS....
                // we need to convert it in a PHP array
                // I also add some internal field:
                // labels:
                // number of votes
                foreach ($params['units'] as $key => $value) {
                    // If the word "unit" is at the beginning of the key
                    if (strpos($key, 'unit') == 0) {
                        if ($params['units'][$key]['type'] == 'thumb') {
                            $newValue = array();
                            for ($i = 0; $i < 3; $i ++) {
                                if ($i == 0)
                                    $newValue['item' + $i]['label'] = 'yes';
                                if ($i == 1)
                                    $newValue['item' + $i]['label'] = 'neutral';
                                if ($i == 2)
                                    $newValue['item' + $i]['label'] = 'no';
                                $newValue['item' + $i]['USID'] = rand(9999999999, 1000000000);
                                $newValue['item' + $i]['USID'] = rand(9999999999, 1000000000);
                                $newValue['item' + $i]['USID'] = rand(9999999999, 1000000000);
                                $newValue['item' + $i]['votes'] = '0';
                            }
                            $params['units'][$key]['items'] = $newValue;
                        } else {
                            // add mean and variance parameters if the unit is of type shultze
                            if ($params['units'][$key]['type'] == 'shultze') {
                                $value['mean'] = array();
                                $value['variance'] = array();
                            }
                            // for each item in the unit
                            foreach ($value['items'] as $key2 => $value2) {
                                // if item is at the beginning of the inner key
                                if (strpos($key2, 'item') === 0) {
                                    // fill the tmp_value with: label and # of votes then
                                    // replace the actual param
                                    $newValue = array();
                                    $newValue['label'] = $value2;
                                    $newValue['USID'] = rand(9999999999, 1000000000);
                                    $newValue['votes'] = '0';
                                    $params['units'][$key]['items'][$key2] = $newValue;
                                }
                            }
                        }
                    }
                }

                // STORE NODE INFO ON DB
                $newSurveyId = $sm->addSurvey($params);
                // instantiate log variable for error and info logging!
                $log = "\n PARAMS:\n" . json_encode($params) . "\n";
                // echo "newSurveyId=".$newSurveyId;

                if ($newSurveyId) {
                    // WRITING TO LOG THAT'S ALL RIGHT
                    $relSurveyId = $sm->addSurveyRel($params, $newSurveyId);
                    /*
                     * Manage Tags
                     */
                    // 1 - check if tags exists and add them to db
                    // 2 - link tags to survey
                    $tagsArray = array_unique($params['tags']);
                    $tagsIdArray = array();
                    foreach ($tagsArray as $tag) {
                        $tagId = $tm->addTag($tag);
                        array_push($tagsIdArray, $tagId);
                        $tm->connectSurveyAndTag($newSurveyId, $tagId);
                    }

                    $skip = 0;
                    $i = 0;
                    $connected = array();
                    $connArray = array();
                    foreach ($tagsIdArray as $tagId1) {
                        foreach ($tagsIdArray as $tagId2) {
                            if ($skip > $i) {
                                array_push($connArray, $tm->connectTwoTagsById($tagId1, $tagId2));
                                $connected[$tagId1] = strval($tagId2);
                            }
                            $skip += 1;
                        }
                        $i += 1;
                        $skip = 0;
                    }

                    // **********************************

                    // If was specified in wich container put the new survey
                    if ($params['container'] != 'default') {
                        // add the survey to that container
                        $relContainerId = $sm->addContainerRel($params, $newSurveyId);
                    } else {
                        // otherwise put the survey in the default container
                        $relContainerId = $sm->addToDefaultContainerRel($params['creator'], $newSurveyId);
                    }

                    if ($relSurveyId && $relContainerId) {
                        $response->setContent(json_encode(array(
                            'response' => true,
                            'newSurveyId'=> $newSurveyId,
                            'newRelId'   => $relSurveyId,
                            'tagsId'     => $tagsIdArray,
                            'units'      => $params['units']
                        )));
                        $response->setStatusCode(200);

                    } else {
                        $response->setContent(json_encode(array(
                            'response' => true,
                            'newSurveyId' => $newSurveyId
                        )));
                        $response->setStatusCode(500);
                    }
                    return $response;
                } else {
                    // WRITING TO LOG THAT THERE WAS A PROBLEM
                    $response->setContent(json_encode(array(
                        'status' => 'error',
                        'json' => $json
                    )));
                    $response->setStatusCode(504); // GENERIC INTERNAL SERVER ERROR CODE TODO: improve error management
                    return $response;
                }
            }
        }
        // IF NONE OF ABOVE CASES IS SATISFIED THEN -> REDIRECT_TO_INDEX!
        return $this->redirect()->toRoute('admin', array(
            'action' => 'index'
        ));
    }


    /**
     * Edit a survey title to database N4J <br>
     * HTTP request type: POST<br>
     * TODO: check integrity with MYSQL side
     * @api
     */
    public function updateSurveyTitleAction(){
        //check if user is logged
        $authPlugin = $this   -> CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $sl = $this->getServiceLocator();
        $request = $this->getRequest();
        $response = $this->getResponse();

        // Create a neo4jHelper to make the relationship...(yes HumusNeo4J wrapper
        // does not support relationship creation yet... :( )

        // MANAGE REQUEST!

        if ($request->isPut()) {

            $client = $this->getServiceLocator()->get('Neo4jClientFactory');
            $sm = new SurveysManager($sl, $client);
            $tm = new TagsManager($sl, $client);

            if (! $sm || ! $tm) {
                // SERVER PROBLEM: CANNOT INSTANTIATING FORM MANAGER
                $response->setStatusCode(500);
                return $response;
            } else {
                // PARSING THE REQUEST PARAMETERS
                $body = $request->getContent();
                if (empty($body)) {
                    $response->setContent(json_encode(array(
                        'status'=> 'error',
                        'ERR' => 'Malformed Request'
                    )));
                    $response->setStatusCode(400);
                    return $response;
                }
                $reader = new Json();
                $json = $reader->fromString($body);

                // If the field is set
                if ( isset($json['title']) && $json['title'] != '') {
                    try{
                        $sm -> editSurveyTitle($json['id'],$json['title']);
                    }catch(\Exception $e){
                        $response->setContent(json_encode(array(
                            'status'=> 'error',//set this better
                            'data' => array('info'=> 'whatever')
                        )));
                        $response->setStatusCode(500);
                        return $response;
                    }
                    $response->setContent(json_encode(array(
                        'status'=> 'success'

                    )));
                    $response->setStatusCode(200);
                    return $response;
                }
            }
        }
        $response->setContent(json_encode(array(
            'status'=> 'fail',
            'data' => array("bad request")
        )));
        $response->setStatusCode(400);
        return $response;
    }


    /**
     * Edit a survey description to database N4J <br>
     * HTTP request type: POST<br>
     * TODO: check integrity with MYSQL side
     * @api
     */
    public function updateSurveyDescriptionAction(){

        //check if user is logged
        $authPlugin = $this   -> CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $sl = $this->getServiceLocator();
        $request = $this->getRequest();
        $response = $this->getResponse();

        // Create a neo4jHelper to make the relationship...(yes HumusNeo4J wrapper
        // does not support relationship creation yet... :( )

        // MANAGE REQUEST!

        if ($request->isPut()){
            $client = $this->getServiceLocator()->get('Neo4jClientFactory');
            $sm = new SurveysManager($sl, $client);
            $tm = new TagsManager($sl, $client);

            if (! $sm || ! $tm) {
                // SERVER PROBLEM: CANNOT INSTANTIATING FORM MANAGER
                $response->setStatusCode(500);
                return $response;
            } else {
                // PARSING THE REQUEST PARAMETERS
                $body = $request->getContent();
                if (empty($body)) {
                    $response->setContent(json_encode(array(
                        'status'=> 'error',
                        'ERR' => 'Malformed Request'
                    )));
                    $response->setStatusCode(400);
                    return $response;
                }

                $reader = new Json();
                $json = $reader->fromString($body);

                if ( isset($json['description']) && $json['description'] != '') {
                    try{
                        $sm -> editSurveyDescription($json['id'],$json['description']);
                    }catch(\Exception $e){
                        $response->setContent(json_encode(array(
                            'status'=> 'error',//set this better
                            'data' => array('info'=> 'whatever')
                        )));
                        $response->setStatusCode(500);
                        return $response;
                    }
                    $response->setContent(json_encode(array(
                        'status'=> 'success'

                    )));
                    $response->setStatusCode(200);
                    return $response;
                }

            }
        }
        $response->setContent(json_encode(array(
            'status'=> 'fail',
            'data' => array("bad request")
        )));
        $response->setStatusCode(400);
        return $response;

    }





    /**
     * Edit survey flags <br>
     * HTTP request type: POST<br>
     * TODO: check integrity with MYSQL side
     * @api
     */
    public function updateSurveyFlagsAction(){

        //check if user is logged
        $authPlugin = $this   -> CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $sl = $this->getServiceLocator();
        $request = $this->getRequest();
        $response = $this->getResponse();

        // Create a neo4jHelper to make the relationship...(yes HumusNeo4J wrapper
        // does not support relationship creation yet... :( )

        // MANAGE REQUEST!

        if ($request->isPut()) {

            $client = $this->getServiceLocator()->get('Neo4jClientFactory');
            $sm = new SurveysManager($sl, $client);
            $tm = new TagsManager($sl, $client);

            if (! $sm || ! $tm) {
                // SERVER PROBLEM: CANNOT INSTANTIATING FORM MANAGER
                $response->setStatusCode(500);
                return $response;
            } else {
                // PARSING THE REQUEST PARAMETERS
                $body = $request->getContent();
                if (empty($body)) {
                    $response->setContent(json_encode(array(
                        'status'=> 'error',
                        'ERR' => 'Malformed Request'
                    )));
                    $response->setStatusCode(400);
                    return $response;
                }

                $reader = new Json();
                $json = $reader->fromString($body);

                if ( isset($json['flags']) && $json['flags'] != '') {
                    try{
                        $sm -> editSurveyFlags($json['id'],$json['flags']);
                    }catch(\Exception $e){
                        $response->setContent(json_encode(array(
                            'status'=> 'error',//set this better
                            'data' => array('info'=> 'whatever')
                        )));
                        $response->setStatusCode(500);
                        return $response;
                    }
                    $response->setContent(json_encode(array(
                        'status'=> 'success'
                    )));
                    $response->setStatusCode(200);
                    return $response;
                }
            }
        }
        $response->setContent(json_encode(array(
            'status'=> 'fail',
            'data' => array("bad request")
        )));
        $response->setStatusCode(400);
        return $response;

    }




    /**
     * Edit the container name<br>
     * HTTP request type: POST<br>
     * TODO: check integrity with MYSQL side
     * @api
     */
    public function updateContainerNameAction(){
        //check if user is logged
        $authPlugin = $this   -> CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $sl = $this->getServiceLocator();
        $request = $this->getRequest();
        $response = $this->getResponse();

        // MANAGE REQUEST!
        if($request->isPut()){
            $client = $this->getServiceLocator()->get('Neo4jClientFactory');
            $sm = new SurveysManager($sl, $client);
            $tm = new TagsManager($sl, $client);
            $cm = new ContainersManager($sl, $client);

            if (! $sm || ! $tm) {
                // SERVER PROBLEM: CAN'T INSTANTIATE FORM MANAGER
                $response->setStatusCode(500);
                return $response;
            } else {
                // PARSING THE REQUEST PARAMETERS
                $body = $request->getContent();
                if (empty($body)) {
                    $response->setContent(json_encode(array(
                        'status'=> 'error',
                        'ERR' => 'Malformed Request'
                    )));
                    $response->setStatusCode(400);
                    return $response;
                }

                $reader = new Json();
                $json = $reader->fromString($body);

                if ( isset($json['name']) && $json['name'] != '') {
                    try{
                        $cm -> editContainerName($json['id'],$json['name']);
                    }catch(\Exception $e){
                        $response->setContent(json_encode(array(
                            'status'=> 'error',//set this better
                            'data' => array('info'=> 'whatever')
                        )));
                        $response->setStatusCode(500);
                        return $response;
                    }
                    $response->setContent(json_encode(array(
                        'status'=> 'success'

                    )));
                    $response->setStatusCode(200);
                    return $response;
                }
            }
        }
        $response->setContent(json_encode(array(
            'status'=> 'fail',
            'data' => array("bad request")
        )));
        $response->setStatusCode(400);
        return $response;

    }


    /**
     * Edit the container name<br>
     * HTTP request type: POST<br>
     * TODO: check integrity with MYSQL side
     * @api
     */
    public function updateContainerDescriptionAction(){
        //check if user is logged
        $authPlugin = $this   -> CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $sl = $this->getServiceLocator();
        $request = $this->getRequest();
        $response = $this->getResponse();

        // MANAGE REQUEST!
        if($request->isPut()){
            $client = $this->getServiceLocator()->get('Neo4jClientFactory');
            $sm = new SurveysManager($sl, $client);
            $tm = new TagsManager($sl, $client);
            $cm = new ContainersManager($sl, $client);

            if (! $sm || ! $tm) {
                // SERVER PROBLEM: CANNOT INSTANTIATING FORM MANAGER
                $response->setStatusCode(500);
                return $response;
            } else {
                // PARSING THE REQUEST PARAMETERS
                $body = $request->getContent();
                if (empty($body)) {
                    $response->setContent(json_encode(array(
                        'status'=> 'error',
                        'ERR' => 'Malformed Request'
                    )));
                    $response->setStatusCode(400);
                    return $response;
                }

                $reader = new Json();
                $json = $reader->fromString($body);

                if ( isset($json['description']) && $json['description'] != '') {
                    try{
                        $cm -> editContainerDescription($json['id'],$json['description']);
                    }catch(\Exception $e){
                        $response->setContent(json_encode(array(
                            'status'=> 'error',//set this better
                            'data' => array('info'=> 'whatever')
                        )));
                        $response->setStatusCode(500);
                        return $response;
                    }
                    $response->setContent(json_encode(array(
                        'status'=> 'success'

                    )));
                    $response->setStatusCode(200);
                    return $response;
                }
            }
        }
        $response->setContent(json_encode(array(
            'status'=> 'fail',
            'data' => array("bad request")
        )));
        $response->setStatusCode(400);
        return $response;

    }


    /**
     * Add a survey from the specified container <br>
     * HTTP request type: POST<br>
     * TODO: check integrity with MYSQL side
     * @api
     */
    public function addSurveyToContainerAction(){
        //check if user is logged
        $authPlugin = $this   -> CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $sl = $this->getServiceLocator();
        $request = $this->getRequest();
        $response = $this->getResponse();

        // MANAGE REQUEST!
        if($request->isPut()){
            $client = $this->getServiceLocator()->get('Neo4jClientFactory');
            $sm = new SurveysManager($sl, $client);
            $tm = new TagsManager($sl, $client);
            $cm = new ContainersManager($sl, $client);

            if (! $sm || ! $tm) {
                // SERVER PROBLEM: CANNOT INSTANTIATING FORM MANAGER
                $response->setStatusCode(500);
                return $response;
            } else {
                // PARSING THE REQUEST PARAMETERS
                $body = $request->getContent();
                if (empty($body)) {
                    $response->setContent(json_encode(array(
                        'status'=> 'error',
                        'ERR' => 'Malformed Request'
                    )));
                    $response->setStatusCode(400);
                    return $response;
                }

                $reader = new Json();
                $json = $reader->fromString($body);

                if ( isset($json['name']) && $json['name'] != '') {
                    try{
                        $cm -> editContainerName($json['id'],$json['name']);
                    }catch(\Exception $e){
                        $response->setContent(json_encode(array(
                            'status'=> 'error',//set this better
                            'data' => array('info'=> 'whatever')
                        )));
                        $response->setStatusCode(500);
                        return $response;
                    }
                    $response->setContent(json_encode(array(
                        'status'=> 'success'

                    )));
                    $response->setStatusCode(200);
                    return $response;
                }
            }
        }
        $response->setContent(json_encode(array(
            'status'=> 'fail',
            'data' => array("bad request")
        )));
        $response->setStatusCode(400);
        return $response;

    }


    /**
     * Deletes a survey from the specified container <br>
     * HTTP request type: POST<br>
     * TODO: check integrity with MYSQL side
     * @api
     */
    public function deleteSurveyFromContainerAction(){


    }


    /**
     *
     */
    public function moveSurveyToContainerAction(){

    }


    /**
     * Takes an id as request and returns the content of the survey with that id on database
     * HTTP request type: GET<br>
     * @api
     */
    public function getSurveyAction(/*$_REQUEST $surveyId*/) {
        //check if user is logged and account type
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $user = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $sl = $this->getServiceLocator();
        $response = $this->getResponse();


        // MANAGE REQUEST!
        $client = $this->getServiceLocator()->get('Neo4jClientFactory');
        $sm = new SurveysManager($sl, $client);
        $um = new UsersManager($sl,$client);
        if (! $sm || ! $um) {
            // SERVER PROBLEM: CANNOT INSTANTIATING SURVEY MANAGER
            error_log("[ERR] Cannot instantiate survey manager or user manager...");
            $response->setStatusCode(500);
            return $response;
        } else {
            // get the survey iD
            $id = (int) $this->params('id');
            $uid = $um->getUserByEmail($authPlugin->getMyEmail($this))->getId();
            // take the survey
            if($accountType!='admin'){
                if($sm->getSurveyCreatorId($id)!=$uid){
                    //TODO: check if user is authorized to read survey
                    error_log("User is not the survey creator and is not admin!");
                }
            }
            $content = $sm->getSurveyInArrayFormat($id);

            if (! $content) {
                $response->setContent(json_encode(array(
                    'response' => false,
                    'error' => 'cannot find the requested survey'
                )));
                $response->setStatusCode(500); // GENERIC INTERNAL SERVER ERROR CODE TODO: improve error management
                return $response;
            }
            // decode it

            //$myArray = json_decode($content,true);
            if($sm->getSurveyCreatorId($id)!=$uid)$sm->hitSurvey($id);
            $content['hits']++;
            // send it to the view
            $response->setContent(json_encode($content,JSON_PRETTY_PRINT));
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            $response->setStatusCode(200); // GENERIC INTERNAL SERVER ERROR CODE TODO: improve error management
            return $response;
        }
    }

    /**
     * This function returns a list of identifiers of the surveys Ids for ALL USERS (IS PRIVATE!!) who made the request
     * HTTP request type: GET<br>
     * @api
     */
    public function getSurveysIdListAction(/*$_REQUEST $surveyId*/) {

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $sl = $this->getServiceLocator();
        $response = $this->getResponse();
        $client = $this->getServiceLocator()->get('Neo4jClientFactory');
        // MANAGE REQUEST!

        $sm = new SurveysManager($sl, $client);

        if (! $sm) {
            // SERVER PROBLEM: CANNOT INSTANTIATING SURVEY MANAGER
            $response->setStatusCode(500);
            return $response;
        } else {
            $id = (int) $this->params('id');
            $surveysIdList = $sm->getUserListOfSurveys($id);
            $response->setContent(json_encode($surveysIdList));
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            return $response;
        }
    }

    /**
     * This function returns all the surveys of the specified user in JSON format
     * It supports the Range header parameter
     */
    public function getUserSurveysAction() {

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $sl = $this->getServiceLocator();
        $request = $this->getRequest();
        $response = $this->getResponse();
        $client = $this->getServiceLocator()->get('Neo4jClientFactory');
        // MANAGE REQUEST!

        $sm = new SurveysManager($sl, $client);
        $um = new UsersManager($sl,$client);
        if (! $sm) {
            // SERVER PROBLEM: CANNOT INSTANTIATING SURVEY MANAGER
            $response->setStatusCode(500);
            return $response;
        } else {
            $id = (int) $this->params('id');
            if(!$id)$id = $um->getUserByEmail($authPlugin->getMyEmail($this))->getId();
            $range = $request->getHeaders()->get('Range');
            if($range){
                if (!preg_match('^bytes=\d*-\d*(,\d*-\d*)*$', $range)) {
                    $response->setStatusCode(416);
                    $response->getHeaders()->addHeader('Content-Range', "x-y/z"); // Required in 416.
                    return $response;
                }
                $ranges = explode(',', substr($range, 6));
                foreach ($ranges as $r) {
                    $parts = explode('-', $r);
                    $start = $parts[0]; // If this is empty, this should be 0.
                    $end = $parts[1]; // If this is empty or greater than than filelength - 1, this should be filelength - 1.

                    if ($start > $end) {
                        $response->setStatusCode(416);
                        $response->getHeaders()->addHeader('Content-Range', "x-y/z"); // Required in 416.
                        return $response;
                    }

                    $surveysList = $sm->getUserSurveys($id,$start,$end);
                    $response->setContent(json_encode($surveysList));
                    $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                    return $response;
                }
            }else{
                //If range is not defined return the first 5 results
                $surveysList = $sm->getUserSurveys($id,0,5);
                $response->setContent(json_encode($surveysList));
                $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                return $response;
            }
        }
    }


    /**
     * Adds a new container and links it to its creator<br>
     * HTTP request type: POST<br>
     * Request example:<br>
     * {<br>
     * &nbsp; "name":"Container Name",<br>
     * &nbsp; "description":"Container Description",<br>
     * &nbsp; "flags":<br>
     * &nbsp; &nbsp; {<br>
     * &nbsp; &nbsp; &nbsp; "private":"1",<br>
     * &nbsp; &nbsp; &nbsp; "moderated":"1",<br>
     * &nbsp; &nbsp; &nbsp; "allowComments":"1",<br>
     * &nbsp; &nbsp; &nbsp; "allowAnonymous":"1",<br>
     * &nbsp; &nbsp; &nbsp; "default":"1"<br>
     * &nbsp; &nbsp; },<br>
     * &nbsp; "creator":"168",<br>
     * &nbsp; "container":"169",<br>
     * &nbsp; "circles":["social"],<br>
     * &nbsp; "delegationLevel":"3",<br>
     * &nbsp; "nestingLevel":"1",<br>
     * &nbsp; "phpsessid":"46f18da253fe9fd09028a97eac8f9a2e"<br>
     * }
     * @api
     */
    public function addContainerAction()
    {
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $request = $this->getRequest();
        $response = $this->getResponse();

        // MANAGE REQUEST!

        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $client = $sl->get('Neo4jClientFactory');
            $contMan = new ContainersManager($sl, $client);

            if (! $contMan || ! $client) {
                echo "OOOPS!!";
                // SERVER PROBLEM: CANNOT CREATE CONTAINER MANAGER OR CLIENT
                $response->setStatusCode(500);
                return $response;
            } else {
                // PARSING THE REQUEST PARAMETERS

                $body = $request->getContent();
                if (empty($body)) {
                    echo "FAILED!";
                    $response->setContent(json_encode(array(
                        'ERR' => 'Malformed Request'
                    )));
                    $response->setStatusCode(400);
                    return $response;
                }

                $reader = new Json();
                $json = $reader->fromString($body);

                $params = array(
                    'name' => $json['name'], // the name of the container
                    'description' => $json['description'], // a brief description
                    'flags' => $json['flags'], // array of active flags ['private','moderated',...,]
                    'creator' => $json['creator'], // id of creator
                    'delegationLevel' => $json['delegationLevel'], // delegation level TODO: parametrize it
                    'nestingLevel' => $json['nestingLevel'], // nestingLevel level TODO: parametrize it
                    'circles' => $json['circles'] // array of circles who can view the container
                );

                if (isset($json['default']))
                    $params['default'] = $json['default'];
                else
                    $params['default'] = false;
                if (isset($json['container']) && gettype((int) $json['container'] == 'integer'))
                    $params['container'] = $json['container']; // container wich contains the survey

                // STORE NODE INFO ON DB

                $newContainerId = $contMan->addContainer($params);
                // echo "ID=".$newContainerId."\n";
                // instantiate log variable for error and info logging!
                $log = "\n PARAMS:\n" . json_encode($params) . "\n";

                if ($newContainerId) {

                    $relId = $contMan->addContainersRels($params, $newContainerId);

                    if ($relId) {
                        $response->setContent(json_encode(array(
                            'status' => 'success',
                            'newContainerId' => $newContainerId
                        )));
                        $response->setStatusCode(200);
                    } else {
                        $response->setContent(json_encode(array(
                            'status' => 'success',
                            'newContainerId' => $newContainerId
                        )));
                        $response->setStatusCode(500);
                    }
                    return $response;
                    // return $this->redirect()->toRoute('admin',array('action' => 'index'));
                } else {
                    // WRITING TO LOG THAT THERE WAS A PROBLEM
                    $response->setContent(json_encode(array(
                        'status' => 'failed',
                        'newContainerId' => "null",
                        'json' => $json
                    )));
                    $response->setStatusCode(504); // GENERIC INTERNAL SERVER ERROR CODE TODO: improve error management
                    return $response;
                }
            }
        }
        // IF NONE OF ABOVE CASES IS SATISFIED THEN -> REDIRECT_TO_INDEX!
        return $this->redirect()->toRoute('admin', array(
            'action' => 'index'
        ));
    }


    /**
     * This function returns a list of identifiers of the surveys Ids for ALL USERS (IS PRIVATE!!) who made the request
     * HTTP request type: GET<br>
     * @api
     */
    public function getContainerAction(/*$_REQUEST $surveyId*/) {
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $sl = $this->getServiceLocator();
        $response = $this->getResponse();

        // MANAGE REQUEST
        $client = $this->getServiceLocator()->get('Neo4jClientFactory');
        $cm = new ContainersManager($sl, $client);

        if (! $cm) {
            // SERVER PROBLEM: CANNOT INSTANTIATING SURVEY MANAGER
            $response->setStatusCode(500);
            return $response;
        } else {
            $id = (int) $this->params('id');
            $containersIdList = $cm->getContainer($id);
            $response->setContent(json_encode($containersIdList));
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            return $response;
        }
    }

    /**
     * This function returns a list of identifiers of the surveys Ids for ALL USERS (IS PRIVATE!!) who made the request
     * HTTP request type: GET<br>
     * @api
     */
    public function getContainersIdListAction(/*$_REQUEST $surveyId*/) {

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /test1/test1-controller/foo
        $sl = $this->getServiceLocator();
        $request = $this->getRequest();
        $response = $this->getResponse();

        // MANAGE REQUEST!
        $client = $this->getServiceLocator()->get('Neo4jClientFactory');
        $cm = new ContainersManager($sl, $client);

        if (! $cm) {
            // SERVER PROBLEM: CANNOT INSTANTIATING SURVEY MANAGER
            $response->setStatusCode(500);
            return $response;
        } else {
            $id = (int) $this->params('id');
            $containersIdList = $cm->getUserListOfContainers($id);
            $response->setContent(json_encode($containersIdList));
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            return $response;
        }
    }



    /**
     * Delete the survey specified in the url
     */
    public function deleteSurveyAction()
    {
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        $sl = $this->getServiceLocator();
        $client = $this->getServiceLocator()->get('Neo4jClientFactory');

        $request = $this->getRequest();
        $response = $this->getResponse();

        $sm = new SurveysManager($sl, $client);
        $um = new UsersManager($sl, $client);
        $uid = $um->getUserByEmail($authPlugin->getMyEmail($this))->getId();
        $user = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();
        if ($request->isDelete()) {
            if ($this->params('id')) {
                try{
                    $sm->deleteSurvey($uid,$this->params('id'),$accountType);
                }catch(\Exception $e){
                    //TODO: format the response based on the user agent and the content type accepted for response
                    $this->getResponse()->setStatusCode(401);
                    $notAuthView = new ViewModel();
                    $notAuthView -> setTemplate('error/401');
                    return $notAuthView;
                }
                $response->setStatusCode(204);
                return $response;
            }
        }
        $response->setStatusCode(500);
        return $response;
    }

    /**
     * Delete the survey specified in the url
     */
    public function deleteContainerAction()
    {
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        $sl = $this->getServiceLocator();
        $client = $this->getServiceLocator()->get('Neo4jClientFactory');
        $request = $this->getRequest();
        $response = $this->getResponse();
        $sm = new SurveysManager($sl, $client);
        $um = new UsersManager($sl, $client);

        $uid = $um->getUserByEmail($authPlugin->getMyEmail($this))->getId();
        $user = $this->zfcUserAuthentication()->getIdentity();
        $accountType = $user->getAccountType();
        if($request->isDelete()){
            if ($this->params('id')) {
                try{
                    $sm->deleteContainer($uid,$this->params('id'),$accountType);
                }catch(\Exception $e){
                    //TODO: format the response based on the user agent and the content type accepted for response
                    $this->getResponse()->setStatusCode(401);
                    $notAuthView = new ViewModel();
                    $notAuthView -> setTemplate('error/401');
                    return $notAuthView;
                }
                $response->setStatusCode(204);
                return $response;
            }
        }
        $response->setStatusCode(500);
        return $response;
    }

    /**
     * Add a comment to the survey <br>
     * HTTP request: POST <br>
     */
    public function addCommentToSurveyAction()
    {
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        $sl = $this->getServiceLocator();
        $client = $this->getServiceLocator()->get('Neo4jClientFactory');

        $request = $this->getRequest();
        $response = $this->getResponse();

        $sm = new SurveysManager($sl, $client);
        $um = new UsersManager($sl, $client);

        if ($request->isPost()) {
            $arguments = $request->getContent();
            $params = json_decode($arguments, true);

            if ($params['userId']) {
                // TODO: improve parameters checking
                $userId = (string) $params['userId'];
            }
            if ($params['surveyId']) {
                // TODO: improove parameters checking
                $surveyId = (string) $params['surveyId'];
            }
            if ($params['comment']) {
                // TODO: improove parameters checking
                $comment = (string) $params['comment'];
            }

            $p = array(
                'userId' => $userId,
                'surveyId' => $surveyId,
                'comment' => $comment
            );


            // add comment to survey
            try{
                $relId = $sm->addCommentToSurvey($p);
            }catch(\Exception $e){
                $response->setContent(json_encode(array(
                    'status' => 'fail',
                    'info' => 'comments are not allowed for this survey'
                ), true));
                $response->setStatusCode(400);
                return $response;
            }
            if ( $relId != 0 ) {
                $response->setContent(json_encode(array(
                    'status' => 'success',
                    'relId' => $relId
                ), true));
                $response->setStatusCode(200);
                return $response;
            }
        }
        // TODO: manage wrong request types
        // IF NONE OF ABOVE CASES IS SATISFIED THEN -> REDIRECT_TO_INDEX!
        $response->setStatusCode(500);
        return $response;
    }









        /**
     * Given the survey, returns the list of the user that could see it.
     *
     */
    public function getAuthorizedVotersListAction()
    {
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        $sl = $this->getServiceLocator();
        $client = $this->getServiceLocator()->get('Neo4jClientFactory');


        $response = $this->getResponse();
        $surveyId = $this->params('id');
        $sm = new SurveysManager($sl, $client);
        $um = new UsersManager($sl, $client);

        $survey = json_decode($sm->getSurveyInJsonFormat($surveyId), true);
        $relCreation = $sm->getCreationRelation($surveyId);
        $creator = $relCreation[0]->getStartNode();
        // TODO: check "json_decode() expects parameter 1 to be string"
        $circles = $relCreation[0]->getProperty('circles');
        if ($circles[0]=='public' || $circles == null) { // there are no circles selected to share the survey
            // the survey could be public or private
            // if it is public return the list of all users (TODO: limit list to a max number for production purposes )
            // if it is private then return the empty set (the survey is not shared yet)
            if ($survey['private'] == false) {
                $users = $um->getAllUsers();
                $usersArray = array();
                foreach ($users as $u) {
                    $usersArray[$u->getId()] = $u->getName();
                }
                $response->setContent(json_encode($usersArray, true));
                $response->setStatusCode(200);
                return $response;
            } else {
                $response->setStatusCode(200);
                return $response;
            }
        } else { // there is some circle selected
            // for each circle return the users and then
            // return the union set
            // the private/public flag will be ignored
            $r = array();
            foreach ($circles as $c) {
                $users = $um->getUserCircleContent($creator->getId(), $c);
                foreach ($users as $u) {
                    $r[$u]=  $um->getUserNode($u)->getProperty('name');
                }
                $r = array_unique($r);
            }
            $response->setStatusCode(200);
            $response->setContent(json_encode($r));
            return $response;
        }
    }

    private function objectToArray($d)
    {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }
        if (is_array($d)) {
            /*
             * Return array converted to object Using __FUNCTION__ (Magic constant) for recursive call
             */
            return array_map(__FUNCTION__, $d);
        } else {
            // Return array
            return $d;
        }
    }

    /**
     * RATE FUNCTIONS The rate phase is a two step transaction: <br>
     * HTTP request type: POST<br>
     * 1- Create Arc between user and survey <br>
     * 2- Update Unit property in the survey node
     * If one step fails => must rollback to a previous state!
     * @api
     */
    public function addRateAction()
    {

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        // preparing default action handlers
        $sl = $this->getServiceLocator();
        $request = $this->getRequest();
        $response = $this->getResponse();

        // MANAGE REQUEST!
        if ($request->isPost()) {
            /*
             * I instantiate all object needed to manage request: - client for N4J - MODEL\Survey_manager class";
             */
            $client = $sl->get('Neo4jClientFactory');
            $sm = new SurveysManager($sl, $client);
            if (! $sm) {
                // SERVER PROBLEM: CANNOT INSTANTIATING FORM MANAGER
                $response->setStatusCode(500);
                return $response;
            } else {
                // PARSING THE REQUEST PARAMETERS

                $body = $request->getContent();
                if ($this->params('from_file') == 'true') {
                    $body = $this->params('param');
                }
                // echo $body;
                if (empty($body)) {
                    echo "FAILED!";
                    $response->setContent(json_encode(array(
                        'status' => 'fail',
                        'ERR' => 'Malformed Request'
                    )));
                    $response->setStatusCode(400);
                    return $response;
                } else {
                    // Validate input json
                    $reader = new Json();
                    $json = $reader->fromString($body);
                    if (! $json) {
                        //echo "FAILED!";
                        $response->setContent(json_encode(array(
                            'status' => 'error',
                            'ERR' => 'Malformed Request'
                        )));
                        $response->setStatusCode(400);
                        return $response;
                    } else {
                        // error_log("UNIT VOTE:" . json_encode($json['units'], true));
                        $params = array(
                            'voterUid' => $json['voterUid'],
                            'surveyId' => $json['surveySelectId'],
                            'units' => $json['units'],
                            'anonymous' => $json['anonymousVote']
                        );

                        $survey = $sm->getSurveyNode($params['surveyId']);
                        if ($survey->getProperty('allowAnonymous') == false && $params['anonymous'] == 1) {
                            // method not allowed
                            $response->setStatusCode(405);
                            $response->setContent(json_encode(array(
                                'status' => 'fail',
                                'info' => 'anonymous votes are not allowed for this survey'
                            )));
                            return $response;
                        }
                        // PASS PARSED PARAMETERS TO DATABASE
                        // starting with RELATION "rates"
                        $relId = $sm->rateSurveyRelCreation($params);
                        if ($relId > 0) {
                            // RELATION IS OK!...NOW PASSING to SURVEY NODE PROCESSING
                            // if rate relation is successfully created => update node
                            // GET the whole SURVEY from DB
                            $surveyContent = $sm->getSurveyInArrayFormat($params['surveyId']);
                            if (! $surveyContent) {
                                $response->setContent(json_encode(array(
                                    'status' => 'error',
                                    'error' => 'error processing requested survey'
                                ), true));
                                $response->setStatusCode(500); // GENERIC INTERNAL SERVER ERROR CODE TODO: improve error management
                                return $response;
                            }
                            // DECODE it and take only the UNITS
                            //$dbVoteArray = json_decode($surveyContent,true);
                            // error_log("ARRAY:>>" . $dbVoteArray['totVotes']);
                            $units = $surveyContent['units']; // extract UNITS
                            // UPDATE $UNITS (from db) CONTENT
                            // with $params (from user)
                            foreach ($units as $unitName => &$unit) {
                                $unitIndex = 0;
                                $unit['nVotes'] += 1;
                                if ($unit['type'] != 'shultze') { // NON SHULTZE METHOD
                                    foreach ($unit['items'] as $itemName => $item) {
                                        foreach ($params['units'][$unitName] as $key => $USID) {
                                            if ($item['USID'] == $USID) {
                                                $units[$unitName]['items'][$itemName]['votes'] += 1;
                                            }
                                        }
                                    }
                                } else { // SHULTZE METHOD
                                    /*
                                     * TODO: separate the code relative to the statistical collection (mean/variance methods) and the part relative to the votes structures updates (surveyNodes, surveyCliques)
                                     */
                                    // update the items clique
                                    $sm->updateShultzeCliques($params);
                                    $items = array();
                                    foreach ($unit['items'] as $key => $value) {
                                        // push USID codes in $items
                                        array_push($items, $value['USID']);
                                    }
                                    $itemsN = sizeof($items);
                                    // Calculate the simple sum value of each vote and save it in $units[unitX][items][itemX]['votes']
                                    // This method can detect between ex-aequo
                                    $itemIndex = 0;
                                    foreach ($params['units'][$unitName] as $idx1 => $USIDVECTOR) {
                                        foreach ($unit['items'] as $itemName => $item) {
                                            foreach ($USIDVECTOR as $idx2 => $USID){
                                                if ($item['USID'] == $USID) {
                                                    $units[$unitName]['items'][$itemName]['votes'] += $itemsN - $itemIndex;
                                                }
                                            }
                                        }
                                        $itemIndex += 1;
                                    }
                                }
                            }

                            // STORE NEW CONTENT
                            $units = json_encode($units, true);
                            try{
                                $sm->rateSurveyUnitsUpdate($params['surveyId'], $units);
                            }catch(\Exception $e){
                                $response->setContent(json_encode((array(
                                    'status' => 'error',
                                    'info' => 'internal server error updating survey'
                                ))));
                                $response->setStatusCode(500);
                                return $response;
                            }
                            // send it to the view
                            $response->setContent(json_encode((array(
                                'response' => true,
                                'relId' => $relId,
                                'surveyUpdated' => $params['surveyId']
                            ))));
                            $response->setStatusCode(200);
                            return $response;
                        } else {
                            // WRITING TO LOG THAT THERE WAS A PROBLEM
                            $response->setContent(json_encode((array(
                                'response' => false,
                                'relId' => "-1",
                                'json' => $body
                            ))));
                            $response->setStatusCode(500); // GENERIC CODE TODO: improve error management
                            return $response;
                        }
                    }
                }
            }
        }
        // IF NONE OF ABOVE CASES IS SATISFIED THEN -> REDIRECT_TO_INDEX!
        $response->setStatusCode(500);
        return $response;
    }

}
