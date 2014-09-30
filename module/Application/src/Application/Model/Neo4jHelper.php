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
 * Helper function: stores the procedures needed for
 * manage the neo4j database at low level.
 *
 */
namespace Application\Model;
use Everyman\Neo4j\Relationship;
use Zend\Http\Client;

class Neo4jHelper{
	private $client;
	private $em; //Entity Manager
	
	function __construct($em,$client){
		$this->client=$client;
		$this->em=$em;
	}
	
	/**
	 * Returns the connectivity property about two nodes
	 * based on a particular kind of relation tag:
	 * Question: IS Alice (205) FRIEND OF Bob (151) friends?
	 * A -> [FRIEND?] -> B
	 * Answer: yes | no
	 * 
	 */
	function areNodesLinked( $startNodeId, $endNodeId, $relName) {
		if($startNodeId && $endNodeId){
    		$relationships = $this->client->getNode($startNodeId)->getRelationships(array($relName),Relationship::DirectionOut);
            foreach ($relationships as $rel) {
                if($rel->getEndNode()->getId() == $endNodeId) return $rel->getId();
            }
		}
        //nodes are not linked
        return 0;
	}
	
	/**
	 * Returns the connectivity property about two nodes
	* based on a particular kind of relation tag:
	* Question: Are Alice (205) and Bob (151) friends?
	* * A <- [FRIEND?] -> B
	* Answer: yes | no
	* @param string $startNodeId 
	*      string $endNodeId
	*      string $relName
	*/
    function areNodesUndirectionallyLinked( $startNodeId, $endNodeId, $relName) {
        if($startNodeId && $endNodeId){
            $relationships = $this->client->getNode($startNodeId)->getRelationships(array($relName),Relationship::DirectionAll);
            foreach ($relationships as $rel) {
                // check if relationship exist and return it
                if($rel->getEndNode()->getId() == $endNodeId && $rel->getStartNode()->getId() == $startNodeId ) return $rel->getId();
                if($rel->getStartNode()->getId() == $endNodeId && $rel->getEndNode()->getId() == $startNodeId ) return $rel->getId();
            }
        }
        return 0;
    }

	
	
    /**
     * Function that returns a node using the N4J rest interface
     * !!!Temporary solution!!!
     * TODO: update function with PHP API solution!
     */
	public function getNodeViaRest($id){

		// First, instantiate the client

		/*
		$dbAddress='http://rateit.sb01.stations.graphenedb.com:24789';
		$webClient = new Client($dbAddress, array('keepalive' => true));
		$webClient->setUri($dbAddress."/db/data/node/".$id."/properties");
		$webClient->setAuth("h4p0", "FUzOsyJaTG6WsHGpXuT0",'basic');
		*/
	    $dbAddress='http://localhost:7474';
	    $webClient = new Client($dbAddress, array('keepalive' => true));
	    $webClient->setUri($dbAddress."/db/data/node/".$id."/properties");

        //echo $dbAddress."/db/data/node/".$id."/properties";
        $response=$webClient->setMethod('GET')->send();
        $response= $response->getContent();
       
		return $response;
	}
	
	
	
	
	/**
	 * Deletes the relationship identified by $relId
	 * @param unknown $relId
	 */
	public function deleteRelationship($relId){
	    //GET relationship and delete it
		$relationship = $this->client->getRelationship((int)$relId);
	    $relationship->delete();
	}

}
