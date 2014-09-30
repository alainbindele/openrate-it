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


namespace Application\Model;

use Everyman\Neo4j\Relationship;
use Application\Model\Entity\SurveyNode;
use Application\Model\Neo4jHelper;
use HireVoice\Neo4j\Exception;
use Monolog\Handler\error_log;
//include_once getcwd().'/module/Application/src/Application/Utils.php';

/**
 * This class interacts with the model reading and writing on the
 * database all the info regarding the survey
 * @author Alain Bindele
 */
class SurveysManager{

    protected $cm;
    protected $em;
    protected $client;
    protected $n4jh;
    private $units;

    /**
     * Initialize Manager Object
     *  And create a neo4jHelper to make the relationship...(yes HumusNeo4J wrapper
     *  does not support relationship creation yet... :(  )
     */
    public function __construct($serviceLocator,$client) {
        $this->cm       =   new ConnectionManager($serviceLocator);
        $this->em       =   $this->cm->getEntityManager();
        $this->client   =   $client;
        $this->n4jh     =   new Neo4jHelper($this->em,$this->client);
        return $this;
    }




    /*
     * CRUD UNIT
     */

    /**
     * Adds a new survey on database<br>
     * array(<br>
    'title' => $json['title'],<br>
    'description' => $json['description'],<br>
    'tags' => $json['tags'],<br>
    'flags' => $json['flags'], // array of active flags<br>
    'creator' => $json['creator'],<br>
    'container' => $json['container'],<br>
    'circles' => $json['circles'],<br>
    'units' => $json['units']<br>
    );<br>
     * @param array $params
     * @return Ambigous <\Application\Model\Entity\the, field_type>
     */
    public function addSurvey($params) {
        $survey 	= 	new SurveyNode();
        $survey 	->  setTitle($params['title']);
        $survey 	->  setDescription($params['description']);
        $survey 	->  setCreator($params['creator']);
        $survey 	->  setFlags($params['flags']);
        $survey 	->  setCircles($params['circles']);
        $survey 	->  setUnits(json_encode($params['units'],true));
        $survey 	->  setHits('0');
        $survey 	->  setTotVotes('0');
        $this->em->persist($survey);
        $this->em->flush();
        return $survey->getId();
    }


    /**
     * Create the relation between creator user and survey id<br>
     * (usually returned by addSurvey() )
     * @param array $params
     * @param string $newSurveyId
     * @return number|Exception
     */
    public function addSurveyRel($params,$newSurveyId){
        // ADD A RELATIONSHIP BETWEEN NODE CREATOR AND THE SURVEY NODE
        try{
            // GET start and END nodes
            $startNode 	= 	$this->client->getNode($params['creator']);
            $endNode 	= 	$this->client->getNode($newSurveyId);
            $relId = $this->n4jh->areNodesLinked($params['creator'], $newSurveyId, 'publicSurvey');
            if($relId!='0'){
                /*
                 * User is trying to recreate the same Survey...or something nasty is coming on!!
                 */
                return 0;
            }else{
                $rel 	= $this->client->makeRelationship();
                $rel	->setStartNode($startNode)
                    ->setEndNode($endNode)
                    ->setType("createSurvey");
                $rel ->setProperty('timestamp', time());
                // If circles are present in the request process them also
                sizeof($params['circles']) > 0 ?
                    $rel ->setProperty('circles', $params['circles'] )->save()
                    :
                    $rel->save();
                /*
                 * UPDATE number of surveys property in User Node
                 */
                $nos = $startNode->getProperty('numberOfSurveys');
                $nos+=1;
                $startNode->setProperty('numberOfSurveys',$nos);
                $startNode->save();
            }
        }catch(Exception $e){
            echo "SurveysManager:err";
            return $e;
        }
        //echo "RelId".$rel->getId();
        return $rel->getId();
    }

    /**
     * Adds the relation between the survey creator and the default container
     * @param unknown $newUserId
     * @param unknown $cm
     * @return Exception|unknown
     */
    public function addDefaultContainerRel($newUserId,$cm){

        // ADD A RELATIONSHIP BETWEEN NODE CREATOR AND THE DEFAULT CONTAINER NODE
        try{
            // GET start and END nodes

            $startNode 	= 	$this->client->getNode((int)$newUserId);
            if(!$startNode){
                //TODO:Insert control here
            }
            /*
             * DEFAULT CONTAINERS PARAMS (TODO: export them in a configuration file)
             */
            $params['name']            =   'default';
            $params['description']     =   'This is the default container';
            $params['creator']         =   $newUserId;
            $params['flags']           =   array('private');
            $params['delegationLevel'] =   0;
            $params['circles']         =   'Social';
            $params['nestingLevel']    =   '1';
            $endNode    =   $cm->addContainer($params);
            $endNode    =   $this->client->getNode($endNode);
            $rel 	    =   $this->client->makeRelationship();
            $rel	    ->setStartNode($startNode)
                ->setEndNode($endNode)
                ->setType("createContainer")
                ->setProperty('timestamp', time())
                ->setProperty('default', true)
                ->save();
            /*
             * UPDATE number of containers property in User Node
             */
            $noc = $startNode->getProperty('numberOfContainers');
            $noc+=1;
            $startNode->setProperty('numberOfContainers',$noc);
            $startNode->save();
        }catch(Exception $e){
            echo "SurveysManager:err";
            return $e;
        }
        //Pushing return values in array
        $relAndContainerId['relId']=$rel->getId();
        $relAndContainerId['containerId']=$endNode->getId();
        return $relAndContainerId;
    }

    /**
     *
     * Adds a relationship of type "contains" between a survey and the user's default container
     * @param unknown $userId
     * @param unknown $surveyId
     * @return number|Exception
     */
    public function addToDefaultContainerRel($userId,$surveyId){
        try{
            // GET start and END nodes
            $allContainersRels  =   $this->client->getNode($userId)->getRelationships(array('createContainer'),Relationship::DirectionOut);
            $containerNode = null;
            foreach($allContainersRels as $containerRel){
                if($containerRel->getProperty("default")==true){
                    $containerNode = $containerRel->getEndNode();
                }
            }if(!$containerNode)return 0;
            $surveyNode 	= 	$this->client->getNode($surveyId);
            try{
                $relId = $this->n4jh->areNodesLinked($containerNode->getId(), $surveyId, 'contains');
                error_log("worgwornfg".$relId);
            }catch (\Exception $e){
                var_dump($e);
            }
            if($relId!=0){
                /*
                 * User is trying to recreate the same Survey...or something nasty is coming on!!
                */
                return 0;
            }else{
                $rel 	= $this->client->makeRelationship();
                $rel	->setStartNode($containerNode)
                    ->setEndNode($surveyNode)
                    ->setType("contains")
                    ->setProperty('timestamp', time())
                    ->save();
            }

        }catch(Exception $e){
            echo "SurveysManager:err";
            return $e;
        }
        return $rel->getId();
    }

    /**
     * Add a relation between a survey and a container (not necessarily the
     * default one)
     * @param array $params
     * @param string $newSurveyId
     * @return number|Exception
     */
    public function addContainerRel($params,$newSurveyId){
        // ADD A RELATIONSHIP BETWEEN A CONTAINER AND A SURVEY NODE
        try{
            // GET start and END nodes
            $startNode 	= 	$this->client->getNode($params['container']);
            $endNode 	= 	$this->client->getNode($newSurveyId);
            try{
                $relId = $this->n4jh->areNodesLinked($params['container'], $newSurveyId, 'contains');
            }catch (\Exception $e){
                var_dump($e);
            }
            if($relId!='0'){
                /*
                 * User is trying to recreate the same Survey...or something nasty is coming on!!
                 */
                return 0;
            }else{
                $rel 	= $this->client->makeRelationship();
                $rel	->setStartNode($startNode)
                    ->setEndNode($endNode)
                    ->setType("contains")
                    ->setProperty('timestamp', time())
                    ->save();
            }

        }catch(Exception $e){
            echo "SurveysManager:err";
            return $e;
        }
        return $rel->getId();
    }


    /**
     * Returns all surveys on n4j DB
     * @return unknown
     */
    public function getAllSurveys(){
        $repository = $this -> em -> getRepository('Application\Model\Entity\SurveyNode');
        $collection = $repository -> findall();
        return $collection;
    }

    /**
     * Returns all user survey nodes on n4j DB
     * @return Nodes collection
     */
    public function getAllMySurveys($uid){
        $userNode = $this->client->getNode($uid);
        $relationships = $userNode->getRelationships(array('createSurvey'),Relationship::DirectionOut);
        foreach ($relationships as $rel) {
            array_push($collection,$rel->getEndNode());
        }
        return $collection;
    }


    /**
     * Returns a specific survey in JSON format
     * @param string $id,
     * @return unknown
     */
    public function getSurveyInJsonFormat($id){
        $survey=json_encode($this->getSurveyInArrayFormat($id),JSON_PRETTY_PRINT);
        return $survey;
    }

    /**
     * Returns a specific survey in JSON format
     * @param string $id,
     * @return unknown
     */
    public function getSurveyInArrayFormat($id){
        $surveyNode = $this->client->getNode($id);
        $tagsRels   = $surveyNode->getRelationships(array('tag'),Relationship::DirectionOut);
        $tags       = array();
        foreach($tagsRels as $rel){
            array_push($tags, $rel->getEndNode()->getProperty("name"));
        }
        $survey=array(
            "id"                => $surveyNode->getId(),
            "title"             => $surveyNode->getProperty("title"),
            "description"       => $surveyNode->getProperty("description"),
            "hits"              => $surveyNode->getProperty("hits"),
            "totVotes"          => $surveyNode->getProperty("totVotes"),
            "tags"              => $tags,
            "circles"           => $surveyNode->getProperty("circles"),
            "creationDate"      => $surveyNode->getProperty("creationDate"),
            "updateDate"        => $surveyNode->getProperty("updateDate"),
            "private"           => $surveyNode->getProperty("private"),
            "moderated"         => $surveyNode->getProperty("moderated"),
            "multiVotesTimeInterval"=> $surveyNode->getProperty("multiVotesTimeInterval"),
            "allowMultipleVotes"=> $surveyNode->getProperty("allowMultipleVotes"),
            "allowAnonymous"    => $surveyNode->getProperty("allowAnonymous"),
            "allowComments"     => $surveyNode->getProperty("allowComments"),
            "delegationLevel"   => $surveyNode->getProperty("delegationLevel"),
            "units"             => json_decode($surveyNode->getProperty("units"),true)
        );
        return $survey;
    }




    /**
     * UPDATES hits property of the survey
     * @param string $id
     */
    public function hitSurvey($id){
        $surveyNode 	= 	$this->client->getNode($id);
        $hits = $surveyNode->getProperty('hits');
        $hits+=1;
        $surveyNode->setProperty('hits',(string)$hits);
        $surveyNode->save();
    }

    /**
     * Return the node representation of the survey
     * @param string $id
     */
    public function getSurveyNode($id){
        return $this->client->getNode($id);
    }


    /**
     * Returns the creation relation
     * @param string $id
     */
    function getCreationRelation($id){
        return $this->client->getNode($id)->getRelationships(array('createSurvey'),Relationship::DirectionIn);
    }


    /**
     * Change the survey description field
     */
    public function editSurveyDescription($id,$description){
        try{
            // get the node
            $surveyNode = $this->client->getNode($id);
            // set the new property
            $surveyNode->setProperty('description',$description);
            // save the node
            $surveyNode->save();
        }catch(\Exception $e){
            throw new Exception("Can't assign new survey description");
        }
        return 0;
    }

    /**
     *
     */
    public function editSurveyTitle($id,$title){
        try{
            $surveyNode = $this->client->getNode($id);
            $surveyNode->setProperty('title',$title);
            $surveyNode->save();
        }catch(\Exception $e){
            throw new Exception("Can't assign new survey title");
        }
        return 0;
    }


    /**
     *
     */

    public function editSurveyFlags($id,$flags){
        try{
            $surveyNode = $this->client->getNode($id);
            if(isset( $flags['private']) && $flags['private']=='true' )
                $surveyNode->setProperty('private',true);
            if(isset( $flags['private']) && $flags['private']=='false' )
                $surveyNode->setProperty('private',false);
            if(isset( $flags['moderated']) && $flags['moderated']=='true' )
                $surveyNode->setProperty('moderated',true);
            if(isset( $flags['moderated']) && $flags['moderated']=='false' )
                $surveyNode->setProperty('moderated',false);
            if(isset( $flags['allowComments']) && $flags['allowComments']=='true' )
                $surveyNode->setProperty('allowComments',true);
            if(isset( $flags['allowComments']) && $flags['allowComments']=='false' )
                $surveyNode->setProperty('allowComments',false);
            if(isset( $flags['allowAnonymous']) && $flags['allowAnonymous']=='true' )
                $surveyNode->setProperty('allowAnonymous',true);
            if(isset( $flags['allowAnonymous']) && $flags['allowAnonymous']=='false' )
                $surveyNode->setProperty('allowAnonymous',false);
            if(isset( $flags['allowMultipleVotes']) && $flags['allowMultipleVotes']=='true' )
                $surveyNode->setProperty('allowMultipleVotes',true);
            if(isset( $flags['allowMultipleVotes']) && $flags['allowMultipleVotes']=='false' )
                $surveyNode->setProperty('allowMultipleVotes',false);
            $surveyNode->save();
        }catch(\Exception $e){
            throw new Exception("Can't assign new survey title");
        }
        return 0;

    }


    /**
     *
     */
    public function addSurveyToContainer(){

    }


    /**
     *
     */

    public function deleteSurveyFromContainer(){

    }


    /**
     *
     */
    public function moveSurveyToContainer(){


    }

    /**
     *
     * Deletes a survey from n4j DB
     * @param string $surveyId
     */
    public function deleteSurvey($uid,$surveyId,$accountType){

        if(!$this->n4jh->areNodesLinked($uid,$surveyId,'createSurvey')&& $accountType=='normal'){
            throw new Exception('Not Authorized');
        }
        $survey = $this->em->findAny($surveyId);
        $nv     = (int) $survey->getTotVotes();
        $this->em->remove($survey);
        $this->em->persist($survey);
        $user = $this->em->findAny($uid);
        $user->setNumberOfSurveys($user->getNumberOfSurveys() - 1);
        $user->setNumberOfInVotes($user->getNumberOfInVotes() - $nv);
        $this->em->persist($user);
        $this->em->flush();
        return 0;
    }

    /*
     * READ
     */


    /**
     * 	input: a user ID in $params['uid']
     * 	output: unit created by the input user
     * @param array $params
     */
    public function getUnitCollection($params){

        $survey = $this->em->findAny($params['uid']);
        return  $survey->getCircles();
    }

    /**
     * Returns the array of surveys created by the input user id
     * @param string $id
     * @return multitype:
     */
    public function getUserListOfSurveys($id){
        $list = array();
        $relationships = $this->client->getNode($id)->getRelationships(array('createSurvey'),Relationship::DirectionOut);
        foreach ($relationships as $rel) {
            array_push($list,$rel->getEndNode()->getId());
        }
        return $list;
    }

    /**
     * Returns the list of user surveys in the given range
     */

    public function getUserSurveys($id, $start,$end){
        $list = array();
        $i=0;
        /*
         * TODO: substitute the query with a cypher query to better filter results directly in dbms
         */
        $relationships = $this->client->getNode($id)->getRelationships(array('createSurvey'),Relationship::DirectionOut);
        foreach ($relationships as $rel) {
            //Push only nodes in the given range
            if($i >= $start && $i <= $end){
                $node=$rel->getEndNode();
                array_push($list,array(
                    "id"                => $node->getId(),
                    "title"             => $node->getProperty("title"),
                    "description"       => $node->getProperty("description"),
                    "hits"              => $node->getProperty("hits"),
                    "totVotes"          => $node->getProperty("totVotes"),
                    "circles"           => $node->getProperty("circles"),
                    "creationDate"      => $node->getProperty("creationDate"),
                    "updateDate"        => $node->getProperty("updateDate"),
                    "private"           => $node->getProperty("private"),
                    "moderated"         => $node->getProperty("moderated"),
                    "allowAnonymous"    => $node->getProperty("allowAnonymous"),
                    "allowComments"     => $node->getProperty("allowComments"),
                    "allowMultipleVotes"=> $node->getProperty("allowMultipleVotes"),
                    "multiVotesTimeInterval"=> $node->getProperty("multiVotesTimeInterval"),
                    "delegationLevel"   => $node->getProperty("delegationLevel"),
                    "units"             => json_decode($node->getProperty("units"),true)
                ));
            }
            $i++;
        }
        return $list;
    }

    /**
     * Returns a list of all votes for the specified survey
     * @param string $id
     * @return multitype:
     */
    public function getSurveyVotes($id){
        $list = array();
        $relationships = $this->client->getNode($id)->getRelationships(array('rate'),Relationship::DirectionIn);
        foreach($relationships as $rel){
            array_push($list,$rel->getProperty('rate'));
        }
        return $list;
    }
    /*
     * UPDATE
     */

    /**
     * Return true if the array given in input is associative
     * false otherwise
     * @param array $a
     * @return boolean
     */
    public function is_associative(array $a) {
        return is_string(key($a));
    }


    /**
     * COMMENTS FUNCTIONS
     */

    public function addCommentToSurvey($params){

        try{
            // GET start and END nodes
            $startNode 	= 	$this->client->getNode((int)$params['userId']);
            $endNode 	= 	$this->client->getNode((int)$params['surveyId']);

            if(!$startNode||!$endNode){
                return 0;
            }
            if($endNode->getProperty('allowComments')=='false')
                throw new Exception('Comments not allowed for this survey');

            $rel 	= $this->client->makeRelationship();
            $rel	-> setStartNode($startNode)
                -> setEndNode($endNode)
                -> setType("comment")
                -> setProperty('timestamp', time())
                -> setProperty('content' , $params['comment'])
                ->save();
            return $rel->getId();
        }catch (\Exception $e){
            return $e;
        }
    }


    /**
     * RATE FUNCTIONS
     * The rate phase is a two step transaction:
     *      1- Create Arc between user and survey
     *      2- Update Unit property in the survey node
     *   If one step fails => must rollback to a previous state!
     *   (...but that's Controller duty!!)
     *  @param array $params
     *      $params=array('voter_id'=>string,'survey_id'=>string,'units'=>array());
     */
    public function rateSurveyRelCreation($params){
        //echo "[INFO] SurveysManager:rateSurvey()\n";
        /*
         * take the input parameters and store them in db!
         * params:
         *      voter_id
         *      survey_id
         *      vote details array
         */
        try{
            // GET start and END nodes
            //error_log($params['voterUid']."-".$params['surveyId']);
            $startNode 	= 	$this->client->getNode((int)$params['voterUid']);
            $endNode 	= 	$this->client->getNode((int)$params['surveyId']);
            // GET survey-creator Node
            $creatorNode = $this->client->getNode($endNode->getProperty('creator'));
            if(!$startNode||!$endNode||!$creatorNode){
                return -1;
            }


            $rel 	= $this->client->makeRelationship();
            $rel	-> setStartNode($startNode)
                -> setEndNode($endNode)
                -> setType("rate")
                -> setProperty('timestamp', time())
                -> setProperty('rate' , json_encode($params['units'],true))
                ->save();
            /*
             * UPDATE number of rates property in
             * Survey and Users
             *
            */
            if($startNode){
                $voterVotesNum  =   (int)$startNode->getProperty('numberOfOutVotes');
                $voterVotesNum  +=  1;
                $startNode      ->  setProperty('numberOfOutVotes',(string)$voterVotesNum)->save();
            }
            if($creatorNode){
                $votedVotesNum  =   (int)$creatorNode->getProperty('numberOfInVotes');
                $votedVotesNum  +=  1;
                $creatorNode    ->  setProperty('numberOfInVotes',(string)$votedVotesNum)->save();
            }

            if($endNode){
                $surveyVotesNum =   (int)$endNode->getProperty('totVotes');
                $surveyVotesNum +=  1;
                //error_log("Survey:".(int)$params['surveyId']."-#".(int)$endNode->getProperty('totVotes'));
                //time_nanosleep(0,500000);
                $endNode        ->  setProperty('totVotes',$surveyVotesNum)->save();

                //error_log("Survey:".(int)$params['surveyId']."-NEW#".(int)$endNode->getProperty('totVotes'));
            }
        }catch(Exception $e){
            echo "SurveysManager:rateSurvey() err";
            return 0;
        }
        return $rel->getId();
    }

    /**
     * Update the content of the survey units
     * with those passed in $units
     */
    public function rateSurveyUnitsUpdate($surveyId,$units){
        try{
            $survey	= 	$this->client->getNode($surveyId);
            //error_log("TOTVOTES (rateSurveyUnitsUpdate)".$survey->getProperty('totVotes'))."\n";
            $survey->setProperty('units',$units)->save();
        }catch (Exception $e){
            throw new Exception("Cannot update survey");
            return $e;
        }
        return;
    }


    /*
     * Check if the specified user could
     */
    function getSurveyCreatorId($id){
        return $this->client->getNode($id)->getRelationships('createSurvey',Relationship::DirectionIn)[0]->getStartNode()->getId();
    }

    /*
    * Check if the specified user could
    */
    function getSurveyCreatorNode($id){
        return $this->client->getNode($id)->getRelationships('createSurvey',Relationship::DirectionIn)[0]->getStartNode();

    }


}
