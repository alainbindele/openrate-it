<?php
/*
 * You can upload new scripts using the "Upload script" button and 
 * then EDIT the script directly in this editor window. 
 * Remember to save it with the save button!
 * I will soon implement the autosave function :) 
 * WARNING! This is a very unsecure feature that could 
 * potentially cause server damages or DoS conditions, please use it wisely!
 */

use Application\Model\ContainersManager;
use Application\Model\SurveyManager;


function helloworld(){
	echo "hello!!!<br>";
}


/*
 * Custom method that returns all votes for the survey specified
 */
function getVotes($admin)
{
   $client = $admin->getServiceLocator()->get('Neo4jClientFactory');
    $sl = $admin->getServiceLocator();
    $sm = new SurveyManager($sl, $client);
    //Get votes of survey(105559) NB: node must exist!
    $votes=$sm->getSurveyVotes(64114);
    echo "<table>";
    foreach($votes as $v){
	echo "<tr><td>";
	echo $v;
	echo "</td></tr>";
    }
    echo "</table>";
}


/*
 * Test function that gets users from DB and display them
 */
function test($admin)
{
    $client = $admin->getServiceLocator()->get('Neo4jClientFactory');
    $sl = $admin->getServiceLocator();
    $sm = new SurveyManager($sl, $client);
    //Get votes of survey(55586)
    $votes=$sm->getSurveyVotes(55586);
    echo "<table>";
    foreach($votes as $v){
	echo "<tr><td>";
	echo $v;
	echo "</td></tr>";
    }
    echo "</table>";
    $Xvalues=array(0,1,2,3,12,54,6,21,5,34);
    $Yvalues=array(2,3,5,10,5,23,53,12,6,12);
    //store the values on the server for the survey and unit specified
    $sm->setCustomMethodXaxis('55586','unit0',$Xvalues);
    $sm->setCustomMethodYaxis('55586','unit0',$Yvalues);
    
    
    
}