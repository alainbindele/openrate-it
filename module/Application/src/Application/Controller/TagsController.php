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
 * This class manages tags associated with Surveys
 * when some tags are passed as input in survey creation
 * they forms a clique on the graph database.
 * @author h4p0
 *
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\TagsManager;


/**
 * TagsController
 *
 * @package OpenRate-it!
 * @author Alain Bindele
 * @version Pre-Alpha 0.1
 */
class TagsController extends AbstractActionController
{
    /**
     * Add a tag to DB
     * @return \Zend\Stdlib\ResponseInterface|Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function addTagAction(){


        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        /*
         * Instantiating base data structures
        */
        $sl		  = $this->getServiceLocator();
        $client   = $sl->get('Neo4jClientFactory');
        $response = $this->getResponse();
        $request  = $this->getRequest();
        $tm       = new TagsManager($sl,$client);

        if (!$tm){
            //SERVER PROBLEM: CANNOT INSTANTIATING USERS MANAGER
            $response -> setStatusCode(500);
            return $response;
        }


        if ($request->isGet()) {
            $query=$_REQUEST['q'];
            $tm->addTag($query);
        }
        return $this->redirect()->toRoute('admin', array(
            'action' => 'index'
        ));

    }


    /**
     * Return a tag node in JSON format
     * @return \Zend\Stdlib\ResponseInterface|\Zend\Stdlib\mixed
     */
    public function getTagAction(){
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;
        /*
         * Instantiating base data structures
        */
        $sl		  = $this->getServiceLocator();
        $client   = $sl->get('Neo4jClientFactory');
        $response = $this->getResponse();
        $request  = $this->getRequest();
        $tm = new TagsManager($sl,$client);
        if (!$tm){
            //SERVER PROBLEM: CANNOT INSTANTIATING USERS MANAGER
            $response -> setStatusCode(500);
            return $response;
        }
        $id  = (int)$this->params('id');
        $tag = $tm -> getTagNodeByIdRest($id);
        $myArray=json_decode($tag);
        $myArray=get_object_vars($myArray);
        //error_log($myArray['class']);
        if($myArray['class']!="Application\\Model\\Entity\\TagNode"){
            $response -> setMetadata('Content-type', 'application/json; charset=utf-8');
            $response -> setContent(json_encode(array("error"=>'tag not found')));
            $response -> setStatusCode(404); // TODO: separate client code between mobile API and browsers
            return $response;
        }
        return $response->setContent(json_encode($myArray,JSON_PRETTY_PRINT));
    }

    /*
     * returns the tag specified by the request
     * starting with the characters passed
     * i.e. if the requested tag is 'info'
     * and in the database are contained the tags
     * 'info' and 'informative' both will be returned
     */
    public function getTagsAction(){

        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        /*
         * Instantiating base data structures
         */
        $sl		  = $this->getServiceLocator();
        $client   = $sl->get('Neo4jClientFactory');
        $response = $this->getResponse();
        $request  = $this->getRequest();
        if ($request->isGet()) {
            $query=$_REQUEST['q'];
        }


        $tm = new TagsManager($sl,$client);
        if (!$tm){
            //SERVER PROBLEM: CANNOT INSTANTIATING USERS MANAGER
            $response -> setStatusCode(500);
            return $response;
        }

        $tags=$tm->getTagsByInitialName($query);

        $returnArray=Array();
        $exist=0;
        foreach ($tags as $index=>$node){
            $element=Array();
            $element['id']   = strVal($index+1);
            $element['name'] = $node->getName();
            array_push($returnArray,$element);
            if($query==$node->getName())$exist=1;
        }
        if(!$exist){
            $element=Array();
            $element['id']   = strVal(sizeof($tags)+1);
            $element['name'] = strVal($query);
            array_push($returnArray,$element);
        }
        return $response->setContent(json_encode($returnArray,JSON_PRETTY_PRINT));
    }



    /**
     * add a tag to the survey
     * the action expects a POST request
     */
    public function addTagToSurveyAction($id, $tagName){
        //check if user is logged
        $authPlugin = $this->CredentialsPlugin();
        $status = $authPlugin -> checkAuthentication($this);
        if($status)return $status;

        /*
         * Instantiating base data structures
        */
        $sl		  = $this->getServiceLocator();
        $client   = $sl->get('Neo4jClientFactory');
        $response = $this->getResponse();
        $request  = $this->getRequest();
        $tm       = new TagsManager($sl,$client);

        if (!$tm){
            //SERVER PROBLEM: CANNOT INSTANTIATING USERS MANAGER
            $response -> setStatusCode(500);
            return $response;
        }


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
                if (isset($json['id']) && $json['id'] != '' && isset($json['tagName']) && $json['tagName'] != '') {
                    $tm->addTagToSurvey($id,$tagName);

                    $response->setContent(json_encode(array("status"=>"success")));
                    $response->setStatusCode(200);
                    return $response;
                }
            }
        }

        $response->setContent(json_encode(array("status"=>"fail",array("data"=>"uncorrect method (use PUT)"))));
        $response->setStatusCode(400);
        return $response;

    }

}
