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

use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Relationship;
use Application\Model\ConnectionManager;
use Application\Model\Entity\UserNode;
use Application\Model\Neo4jHelper;
use HireVoice\Neo4j\Exception;

class UsersManager
{

    protected $cm;

    protected $em;

    protected $client;

    protected $n4jh;

    public function __construct($serviceLocator, $client)
    {
        $this->cm = new ConnectionManager($serviceLocator);
        $this->em = $this->cm->getEntityManager();
        $this->client = $client;
        $this->n4jh = new Neo4jHelper($this->em, $this->client);
        return $this;
    }

    /*
     * CRUD USERS
     */
    public function addUser($params)
    {
        $user = new UserNode();
        $user->setName($params['name']);
        $user->setSurname($params['surname']);
        $user->setMail($params['mail']);
        $user->setCircles(array(
            'social'
        ));
        $user->setNumberOfContainers(0);
        $user->setNumberOfFriends(0);
        $user->setNumberOfInVotes(0);
        $user->setNumberOfOutVotes(0);
        $user->setNumberOfSurveys(0);
        $user->setAccountType($params['accountType']);
        $this->em->persist($user);
        $this->em->flush();
        $uid = $user->getId();

        return $uid;
    }


    public function updateUserInfoNode($params){
        //TODO:BETTER CHECK DATA CONSISTENCY
        try{
            //take the node from db
            $user = $this->client->getNode($params['uid']);
            //if params are not empty set the property that it contains
            if($params['name']!='')
                $user->setProperty('name',$params['name']);
            if($params['surname']!='')
                $user->setProperty('surname',$params['surname']);
            if($params['mail']!='')
                $user->setProperty('mail',$params['mail']);
            //save the node
            $user->save();
        }catch(\Exception $e){
            throw new Exception("Can't Update node!");
        }
        return;
    }

    public function updateUserCircle($uid,$oldCircleName,$newCircleName){
        //TODO:BETTER CHECK DATA CONSISTENCY
        try{
            $user    = $this->client->getNode($uid);
            $circles = $user->getProperty('circles');

            $val = array_search($oldCircleName,$circles);
            if($val==0 && isset($circles[$val])){
                $circles[$val] = $newCircleName;
                $user->setProperty('circles',$circles);
                $user->save();
            }else{throw new Exception("Can't find the circle to update");}
        }catch(\Exception $e){
            throw new Exception("Can't Update node!");
        }

        return;
    }


    public function deleteUserCircle($uid,$circleName){
        //TODO:BETTER CHECK DATA CONSISTENCY
        try{
            $user = $this->client->getNode($uid);
            $circles = $user->getProperty('circles');
            if (isset($circles[$circleName])){
                $user->save();
            }
        }catch(\Exception $e){
            throw new Exception("Can't Update node!");
        }
        return;
    }



    public function getUserByEmail($email){
        $repo = $this->em->getRepository('Application\\Model\\Entity\\UserNode');
        $user = $repo->findOneByMail($email);

        if(!$user){
            error_log("user with mail ".$email." Not found!");
            return 0;
        }
        else return $user;
    }

    /*
     * Delete a user given its identifier
     */
    public function deleteUser($uid)
    {
        $user = $this->em->findAny($uid);
        $this->em->remove($user);
        $this->em->persist($user);
        $this->em->flush();
    }

    /*
     * CIRCLES FUNCTIONS
     */


    /**
     * Create a circle
     * @param $params = array($uid,array("circle1","circle2",...))
     */

    public function createCircle($params)
    {
        $user = $this->em->findAny($params['uid']);

        $oldCircles = $user->getCircles();

        foreach ($params['circlesNames'] as $key => $c) {
            // TODO: ADD > IF (CIRCLES IS NOT ALREADY PRESENT)
            array_push($oldCircles, $c);
        }
        array_unique($oldCircles);

        // writing changes to database
        $user->setCircles($oldCircles);
        $this->em->persist($user);
        $this->em->flush();
    }

    /*
     * READ
     */


    /*
     *  input: user
     * 	output:
     */

    public function getUserCirclesCollection($uid)
    {
        $user = $this->em->findAny($uid);
        return $user->getCircles();
    }



    public function getUserCircleContent($uid, $circleName)
    {
        try {
            // GET start and END nodes
            $startNode = $this->client->getNode($uid);
            $friendsInsideCircle = array();
            $relationships = $startNode->getRelationships(array(
                'contact'
            ), Relationship::DirectionAll);
            foreach ($relationships as $rel) {
                //Push in $friendsInsideCircle the correct node
                //(since relationships are mapped as undirected arcs on neo4jDBMS)
                if( $rel->getEndNode()->getId()!= $uid ){
                    if ( in_array($circleName,$rel->getProperty('circles>')) ){
                        array_push($friendsInsideCircle,$rel->getEndNode()->getId());
                    }
                }else{
                    if ( in_array($circleName,$rel->getProperty('circles<')) ){
                        array_push($friendsInsideCircle,$rel->getStartNode()->getId());
                    }
                }
            }
            return $friendsInsideCircle;
        }catch (Exception $e) {
            return $e;
        }
    }


    /**
     * @param $uid the user id whose is associated the default container
     * @return \Node|Exception|int
     */
    public function getDefaultContainer($uid)
    {
        try {
            // GET start node (user node)
            $startNode = $this->client->getNode($uid);

            $relationships = $startNode->getRelationships(array(
                'createContainer'
            ), Relationship::DirectionOut);
            foreach ($relationships as $rel) {
                if ($rel->getProperty('default') == 'true')
                    return $rel->getEndNode();
            }
            return 0;
        } catch (Exception $e) {

            return $e;
        }
    }


    public function getAllContainers($uid)
    {

        try {
            // GET start
            $startNode = $this->client->getNode($uid);
            $containers = array();
            $relationships = $startNode->getRelationships(array(
                'createContainer'
            ), Relationship::DirectionOut);
            foreach ($relationships as $rel) {
                array_push($containers,$rel->getEndNode());
            }
            return $containers;
        } catch (Exception $e) {
            return $e;
        }
    }

    /*
     * UPDATE
     */
    public function addToCircles($user, $circle)
    {}

    public function moveToCircle($user, $circle)
    {}

    public function renameCircle()
    {}

    /*
     * DELETE
     */
    public function deleteCircles()
    {}

    public function deleteAll()
    {}

    /**
     * @return collection
     */
    public function getAllUsers()
    {
        $repository = $this->em->getRepository('Application\Model\Entity\UserNode');
        $collection = $repository->findall();
        return $collection;
    }

    /**
     * @return collection
     */
    public function getAllMyFriendsUsers()
    {
        $repository = $this->em->getRepository('Application\Model\Entity\UserNode');
        $collection = $repository->findall();
        return $collection;
    }

    /*
     * Administrators may need to create relationships in one step
     * $params is an associative array with two fields:
     * sUid : start user id
     * eUid : end user id
     * @return
     */
    public function createSocialRelationship($params)
    {

        // ADD CONTACT PROPERTY (Circles)
        try {
            // GET start and END nodes
            $startNode = $this->client->getNode($params['sUid']);
            $endNode = $this->client->getNode($params['eUid']);
            $relId = $this->n4jh->areNodesUndirectionallyLinked($params['sUid'], $params['eUid'], 'contact');
            $rel = null;
            if ($relId != 0) {
                /*
                 * Input nodes are alredy linked... make the union of old circles and new circles
                 */
                $oldCircles = $startNode->getProperty("circles");
                $newCircles = $params['circles'];
                $resultCircle = array_unique(array_merge($oldCircles, $newCircles));
                $rel = $this->client->getRelationship($relId);
                $rel->setProperty('circles>',$resultCircle);
            } else {
                //relationship doesn't exist yet!
                $defaultCircle = array('social');
                $newCircles = $params['circles'];
                $resultCircle = array_unique(array_merge($defaultCircle, $newCircles));
                $rel = $this->client->makeRelationship();
                $rel->setStartNode($startNode)
                    ->setEndNode($endNode)
                    ->setProperty('circles>',$resultCircle)
                    ->setProperty('circles<',$defaultCircle)
                    ->setType("contact");
            }
            $rel->setProperty('req_timestamp', time());
            $rel->setProperty('status', 'CONFIRMED');
            $rel->setProperty('conf_timestamp', time())->save();

            // Update the number of friends in the user's nodes
            $friendsNum = (int) $startNode->getProperty('numberOfFriends');
            $friendsNum += 1;
            $startNode->setProperty('numberOfFriends', $friendsNum)->save();
            $friendsNum = (int) $endNode->getProperty('numberOfFriends');
            $friendsNum += 1;
            $endNode->setProperty('numberOfFriends', $friendsNum)->save();

        } catch (Exception $e) {
            echo "UsersManager:err";
            return $e;
        }
        return $rel->getId();
    }


    /*
     * SOCIAL RELATIONSHIPS Manage the Social Relationships at Model Level
     * This function is called when a social relation is requested
     */
    public function requestSocialRelationship($params)
    {
        $rel=null;
        try {
            // GET start and END nodes
            $startNode = $this->client->getNode($params['sUid']);
            $endNode = $this->client->getNode($params['eUid']);
            $relId = $this->n4jh->areNodesUndirectionallyLinked($params['sUid'], $params['eUid'], 'contact');
            if ($relId != '0') {
                throw new Exception('Users are already linked');
            } else {
                //relationship doesn't exist yet!
                $defaultCircle = array('social');
                $newCircles = $params['circles'];
                $resultCircle = array_unique(array_merge($defaultCircle, $newCircles));
                $rel = $this->client->makeRelationship();
                $rel ->setStartNode($startNode)
                    ->setEndNode($endNode)
                    ->setType("contact")
                    ->setProperty('circles>',$resultCircle)
                    ->setProperty('req_timestamp', time())
                    ->setProperty('status','PENDING')->save();
            }
        } catch (Exception $e) {
            echo "UsersManager:err";
            return $e;
        }
        return $rel->getId();
    }

    /*
     * SOCIAL RELATIONSHIPS Manage the Social Relationships at Model Level
     * This function is called to confirm a contact request
     */
    public function confirmSocialRelationship($params)
    {
        $rel=null;
        try {
            $relId = $this->n4jh->areNodesUndirectionallyLinked($params['sUid'], $params['eUid'], 'contact');
            if ($relId != '0') {
                //If the user contextually choosed some destination circle
                if($params['circles']){
                    $defaultCircle = array('social');
                    $newCircles = $params['circles'];
                    $resultCircle = array_unique(array_merge($defaultCircle, $newCircles));
                }
                //retrieve the relation and update it
                $rel = $this->client->getRelationship($relId);
                $rel->setProperty('conf_timestamp', time());
                $rel->setProperty('circles>',$resultCircle);
                $rel->setProperty('status','CONFIRMED')->save();
            } else {
                throw new Exception('Cannot confirm unrequested social relationships');
            }
        } catch (Exception $e) {
            echo "UsersManager:err";
            return $e;
        }
        return 0;
    }

    /**
     * @param $params
     * @return \Exception|Exception
     */
    public function deleteSocialRelationship($params)
    {
        $this->n4jh = new Neo4jHelper($this->em, $this->client);

        // ADD CONTACT PROPERTY (Circles)
        try {
            foreach ($params['contacts'] as $contact) {
                $queryDelContacts = "start n=node(" . $params['sUid'] . "), m=node(" . $contact . ")
						match (n)-[r]->(m)
						delete r;";
                $query = new Query($this->client, $queryDelContacts);
                $result = $query->getResultSet();
                $startNode = $this->client->getNode($params['sUid']);
                $friendsNum = (int) $startNode->getProperty('numberOfFriends');
                $friendsNum -= 1;
                $startNode->setProperty('numberOfFriends', (string) $friendsNum);
                $startNode->save();
            }
        } catch (Exception $e) {

            echo "UsersManager:err";
            return $e;
        }
        return;
    }

    public function getUser($uid)
    {
        $this->n4jh = new Neo4jHelper($this->em, $this->client);

        try {
            // GET user node
            $userNode = $this->n4jh->getNodeViaRest($uid);
        } catch (Exception $e) {
            return $e;
        }
        return $userNode;
    }

    public function getUserNode($uid)
    {
        $user = $this->client->getNode($uid);
        return $user;
    }

    public function getUserContacts($uid)
    {

        try {
            // GET start and END nodes
            $startNode = $this->client->getNode($uid);
            $contacts = array();
            $relationships = $startNode->getRelationships(array(
                'contact'
            ), Relationship::DirectionAll);
            foreach ($relationships as $rel) {
                if($rel->getProperty("status")=="CONFIRMED"){
                    if($rel->getStartNode()->getId()==$uid){
                        array_push($contacts,array(
                            "id"=>$rel->getEndNode()->getId(),
                            "name"=>$rel->getEndNode()->getProperty('name'),
                            "surname"=>$rel->getEndNode()->getProperty('surname')
                        ));
                    }else{
                        array_push($contacts,array(
                            "id"=>$rel->getStartNode()->getId(),
                            "name"=>$rel->getStartNode()->getProperty('name'),
                            "surname"=>$rel->getStartNode()->getProperty('surname')
                        ));
                    }
                }
            }

            // print_r($contacts);
            return $contacts;
        } catch (Exception $e) {

            return $e;
        }
    }

    public function getDetailedUserContacts($uid)
    {
        $this->n4jh = new Neo4jHelper($this->em, $this->client);
        try {
            // GET start and END nodes
            $startNode = $this->client->getNode($uid);
            $contacts = array();
            $relationships = $startNode->getRelationships(array(
                'contact'
            ), Relationship::DirectionAll);
            foreach ($relationships as $rel) {
                if($rel->getProperty("status")=='CONFIRMED'){
                    $rel->getEndNode()->getId()==$uid?
                        $contactNode=$rel->getStartNode():
                        $contactNode=$rel->getEndNode();
                    array_push($contacts, array(
                        "id" => $contactNode->getId(),
                        "name"=>$contactNode->getProperty('name'),
                        "surname"=>$contactNode->getProperty('surname'),
                        "numberOfSurveys"=>$contactNode->getProperty('numberOfSurveys'),
                        "numberOfFriends"=>$contactNode->getProperty('numberOfFriends'),
                        "numberOfOutVotes"=>$contactNode->getProperty('numberOfOutVotes'),
                        "numberOfInVotes"=>$contactNode->getProperty('numberOfInVotes')
                    ));
                }
            }
            // print_r($contacts);
            return $contacts;
        } catch (Exception $e) {
            return $e;
        }
    }
}
