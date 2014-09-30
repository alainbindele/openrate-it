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
use Application\Model\Entity\ContainerNode;
use Application\Model\Neo4jHelper;
use HireVoice\Neo4j\Exception;

/**
 * This class interacts with the model reading and writing on the
 * database all the info regarding the containers
 * @author Alain Bindele
 */
class ContainersManager{

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
     * adds a container to the database
     */

    public function addContainer($params) {

        $container 	= 	new ContainerNode();

        $container 	->  setName           ($params['name']);
        $container 	->  setDescription    ($params['description']);
        $container 	->  setCreator        ($params['creator']);
        $container 	->  setFlags          ($params['flags']);
        $container 	->  setDelegationLevel($params['delegationLevel']);
        $container 	->  setNestingLevel   ($params['nestingLevel']);
        $container 	->  setCircles        ($params['circles']);
        $this->em->persist($container);
        $this->em->flush();
        return $container->getId();
    }

    /*
     * Returns the default container id for the requested User
     * identified by $userId
     *
     */

    public function getDefaultContainer($userId){
        $startNode 	     = 	$this->client->getNode($userId);
        $relationships = $startNode->getRelationships('createContainer',Relationship::DirectionOut);
        foreach ($relationships as $tmpRel){
            if($tmpRel->getProperty('default')==true)
                return $tmpRel->getEndNode()->getId();
        }
    }

    /*
     * Connect a container to the user specified
     * if another container is specified will be
     * used as destination container.
     */

    public function addContainersRels($params,$newContainerId){
        // ADD A RELATIONSHIP BETWEEN NODE CREATOR AND THE SURVEY NODE
        try{
            // GET start and END nodes
            $startNode 	     = 	$this->client->getNode($params['creator']);
            $containerNode 	 = 	$this->client->getNode($newContainerId);
            $relId = $this->n4jh->areNodesLinked($params['creator'], $newContainerId, 'createContainer');

            if($relId!='0'){
                /*
                 * User is trying to recreate the same Survey...or something nasty is coming on!!
                 */
                return 0;
            }else{
                /*
                 * Here we create the container -> user relationship
                 * If another target container is passed as argument
                 * => the container is linked to is with a 'in' relation
                 */
                $rel 	=  $this->client->makeRelationship();


                /*
                 * If was specified a destination container
                 * put the new container in it
                 * (useful for gerarchical containers eg: [Univ]->[dep1,dep2,..]->[students,professors...])
                 * otherwise..
                 */
                if((int)$params['container']!=0){
                    $relInContainer	=  $this->client->makeRelationship();
                    $endNode        =  $this->client->getNode($params['container']);
                    $relInContainer -> setStartNode($containerNode)
                        -> setEndNode($endNode)
                        -> setType("in")
                        -> setProperty('timestamp',time())
                        -> save();
                }
                /*
                 * CONNECT the new container node to creator node
                 *
                 */
                $rel	-> setStartNode($startNode)
                    -> setEndNode($containerNode)
                    -> setType("createContainer")
                    -> setProperty('timestamp', time());
                /*
                 * UPDATE number of containers property in User Node
                 */
                $noc = $startNode->getProperty('numberOfContainers');
                $noc+=1;
                $startNode->setProperty('numberOfContainers',$noc);
                $startNode->save();

                //IF is default container
                if($params['default']==true){
                    $relationships = $startNode->getRelationships('createContainer',Relationship::DirectionOut);
                    //PUT THE OLD "DEFAULT CONTAINER" TO DEFAULT==FALSE
                    foreach ($relationships as $tmpRel){
                        if($tmpRel->getProperty('default')==true)
                            $rel->setProperty('default',false);
                    }
                    //PUT the new container to DEFAULT
                    $rel->setProperty('default',true);
                }else{
                    $rel -> setProperty('default',false);
                }

                // If circles are present in the request process them also
                sizeof($params['circles']) > 0 ?
                    $rel ->setProperty('circles', $params['circles'] )->save()
                    :
                    $rel->save();
            }

        }catch(Exception $e){
            echo "SurveysManager:err";
            return $e;
        }
        //echo "RelId".$rel->getId();
        return $rel->getId();

    }


    /*
     * returns all containers of the user
     */

    public function getAllContainers(){
        $repository = $this -> em -> getRepository('Application\Model\Entity\ContainerNode');
        $collection = $repository -> findall();
        return $collection;
    }

    /*
     * returns the container specified by the
     * input identifier
     */

    public function getContainer($id){
        $container=$this->n4jh->getNodeViaRest($id);
        $container=json_decode($container,true);
        if($container['class']=='Application\Model\Entity\ContainerNode'){
            return $container;
        }else{
            return 0;
        }
    }

    /**
     * edits the container name with the provided one
     */
    public function editContainerName($id,$containerName){
        try{
            $surveyNode = $this->client->getNode($id);
            $surveyNode->setProperty('name',$containerName);
            $surveyNode->save();
        }catch(\Exception $e){
            throw new Exception("Cannot edit container name");
        }
        return 0;
    }

    /**
     * edits the container description with the provided one
     */
    public function editContainerDescription($id,$containerDescription){
        try{
            $surveyNode = $this->client->getNode($id);
            $surveyNode->setProperty('description',$containerDescription);
            $surveyNode->save();
        }catch(\Exception $e){
            throw new Exception("Cannot edit container description");
        }
        return 0;

    }


    /*
     * delete the container specified by the $containerId
     * of the user specified by the $uid variable
     *
     */

    public function deleteContainer($uid,$containerId){
        if(!$this->n4jh->areNodesLinked($uid,$containerId,'createSurvey')){
            $container = $this->em->findAny($containerId);
            $this->em->remove($container);
            $this->em->persist($container);
            $this->em->flush();
        }
    }


    /*
     * READ
     */


    /*	input: a user ID
     * 	output: unit created by the input user
     */
    public function getSurveyCollection($params){
        $container = $this->em->findAny($params['cid']);
        return  $container->getSurveys();
    }

    /*
     * Returns the array of surveys created by the input user id
     */
    public function getUserListOfContainers($id){
        $list = array();
        $relationships = $this->client->getNode($id)->getRelationships(array('createContainer'),Relationship::DirectionOut);
        foreach ($relationships as $rel) {
            array_push($list,$rel->getEndNode()->getId());
        }
        return $list;
    }

    /*
     * UPDATE
     */


    public function is_associative(array $a) {
        return is_string(key($a));
    }


}
