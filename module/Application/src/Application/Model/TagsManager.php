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


use Application\Model\ConnectionManager;
use Application\Model\Entity\TagNode;
use Application\Model\Neo4jHelper;
use HireVoice\Neo4j\Exception as Exception;


/**
 *
 * Class TagsManager
 * @package Application\Model
 */
class TagsManager{
    
	protected $cm;
	protected $em;
	protected $client;
    protected $n4jh;
    	
    public function __construct($serviceLocator, $client) {
    	$this-> cm      = new ConnectionManager($serviceLocator);
    	$this-> em      = $this->cm->getEntityManager();
    	$this-> client  = $client;
        $this-> n4jh    =new Neo4jHelper($this->em,$this->client);
    	return $this;
    }
    /**
     * Returns all tags in n4j DB
     */
    public function getAllTags(){
        
        $repository = $this->em->getRepository('Application\\Model\\Entity\\TagNode');
        return $repository->findAll();
    }
    
    /**
     * Returns all tags that starts with the first letters specified
     * in $tagname
     * @param string $tagName
     */
    public function getTagsByInitialName($tagName){
    	$repository = $this->em->getRepository('Application\\Model\\Entity\\TagNode');
    	return $repository->findBy(array('name'=>$tagName.'*'));
    }
    
    
    /**
     * Returns all tags named $tagName 
     * @param string $tagName
     * @return number|unknown
     */
    public function getTagsByFullName($tagName){
    	$repository = $this->em->getRepository('Application\\Model\\Entity\\TagNode');
    	$collection = $repository->findBy(array('name'=>$tagName));
    	if (sizeof($collection)==0)return 0;
    	else return $collection;
    }
    
    /**
     * Adds a new tag named $tagName
     * @param string $tagName
     * @return unknown
     */
    public function addTag($tagName){
    	//add a tag to db
        $tag = $this->getTagsByFullName($tagName);
    	if(!$tag){
    		$newTag = new TagNode($tagName);
    		$this->em->persist($newTag);
    		$this->em->flush();
    		$uid = $newTag->getId();
          	return $uid;
    	}else{
    		$tag['0']->incrementWeight();
    		$this->em->persist($tag['0']);
    		return $tag['0']->getId();
    	}
    }

    /**
     * Adds a tag to a survey
     */
    public function addTagToSurvey($surveyId,$tagName){
        $surveyNode = $this->client->getNode($surveyId);
        $newTag = new TagNode($tagName);
        $rel 	= $this->client->makeRelationship();
        $rel	->setStartNode($surveyNode)
                ->setEndNode($newTag)
                ->setType("tag")
                ->save();

        //$surveyNode -> get; //FUNCTION THAT GETS ADJACENTS
    }

    /**
     * Deletes the tag $tagName
     * @param string $tagName
     */
    public function delTag($tagName){
        $tag = $this->em->findByName($tagName);
        $this->em->remove($tag);
        $this->em->persist($tag);
        $this->em->flush();
    }
    
    
    /**
     * Deletes the tag specified by $tagId
     * @param unknown $tagId
     */
    public function delTagById($tagId){
        $tag = $this->em->findAny($tagId);
        $this->em->remove($tag);
        $this->em->persist($tag);
        $this->em->flush();
    }
    
    
    /**
     * Creates a relationship between the survey and the tag
     * @param string $surveyID
     * @param string $tagID
     * @return Exception
     */
    public function connectSurveyAndTag($surveyID,$tagID){
    	// retrive from db survey and tag
    	// connect them

        // ADD CONTACT PROPERTY (Circles)
        
        try{
        	// GET start and END nodes
        	$surveyNode = 	$this->client->getNode($surveyID);
        	$tagNode 	= 	$this->client->getNode($tagID);
        	$relId      =   $this->n4jh->areNodesLinked($surveyID, $tagID, 'tag');
        	if($relId!='0'){
    		  /*
    		  * Input nodes are alredy linked...
    		  * I should not be here!
    		  */
        	    echo "I should not be Here!";
        		
        	}else{ // nodes wasn't already linked
        
        		$rel 	= $this->client->makeRelationship();
        		$rel	->setStartNode($surveyNode)
                		->setEndNode($tagNode)
                		->setType("tag")
                		->save();
        	}
        }catch(Exception $e){
        	echo "TagsManager:err";
        	return $e;
        }
        return $rel->getId();  
    }
    
    
    /**
     * Connects the tags specified by the ids given in input
     * @param string $tagsID1
     * @param string $tagID2
     * @return Exception
     */
    public function connectTwoTagsById($tagsID1,$tagID2){
    	// retrive from db tag1 and tag2
    	// connect them
        // retrive from db survey and tag
        // connect them

        // ADD CONTACT PROPERTY (Circles)
        
        try{
        	// GET start and END nodes
        	$tagNode1 	= 	$this->client->getNode($tagsID1);
        	$tagNode2 	= 	$this->client->getNode($tagID2);
        	$relId = $this->n4jh->areNodesUndirectionallyLinked($tagsID1, $tagID2, 'related');
        	if($relId!='0'){
        		/*
        		 * Input nodes are alredy linked...
        		 */
        	    $rel = $this->client->getRelationship($relId);
        	    $rel ->setProperty('closeness', strval($rel->getProperty('closeness')+1))
        	         ->save();
        	}else{ // nodes wasn't already linked
        		$rel 	= $this->client->makeRelationship();
        		$rel	->setStartNode($tagNode1)
                        ->setEndNode($tagNode2)
                        ->setProperty('closeness','1')
                        ->setType("related")
        		        ->save();
        	}
        }catch(\Exception $e){
        	echo "TagsManager:err";
        	return $e;
        }
        return $rel->getId();
    }
    
    
    
    public function addTagList($tagsIdList){
        foreach ($tagsIdList as $tag){
        	foreach ($tagsIdList as $tag2){
        	    try{
        	       $this->connectTwoTags($tag2);
        	    }catch (\Exception $e){
        	        echo "TagsManager:err";
        	        return $e;
        	    }
        	}
        }
    }
     
    public function addTagNamesList($tagsNameList){
    	//Check if tag exist (add it to DB)
    	// If so:
    	//         1 - connect it to the survey
    	//         2 - connect it with all other tags
    	//         3 - increment arc weight
    	//otherwise, create new tag and add it to DB
    	foreach ($tagsNameList as $tagName1){
    		foreach ($tagsNameList as $tagName2){
    			try{
    			    $tagId1= $this->addTag($tagName1);
    			    $tagId2= $this->addTag($tagName2);
    				$this->connectTwoTags($tagId1,$tagId2);
    			}catch (Exception $e){
    				echo "TagsManager:err";
    				return $e;
    			}
    		}
    	}
    }

    public function getTagNodeByIdRest($id){
         try{
        	// GET user node
        	$tagNode 	= 	$this->n4jh->getNodeViaRest($id);
        }catch (\Exception $e){
        	return $e;
        }
        return $tagNode;
    }
    
    
    public function getTagNeighborsRest($id){
    	 
    }
    
    /**
     * Returns the tag specified by $id
     * @param string $id
     * @return unknown
     */
    public function getTagNodeById($id){
    	// Check if tag exist and return it
    	// otherwise launch error management
        $tag=$this->n4jh->getNode($id);
        return $tag;
    }
    
    

}
