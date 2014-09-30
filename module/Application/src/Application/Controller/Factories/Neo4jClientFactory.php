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


/* MyModule/src/MyModule/ControllerFactory/MyControllerFact.php */
namespace Application\Controller\Factories;

use \Zend\ServiceManager\FactoryInterface;
use \Zend\ServiceManager\ServiceLocatorInterface;
use Everyman\Neo4j\Client as N4JClient;

//use Zend\ServiceManager\AbstractFactoryInterface;
class Neo4jClientFactory implements FactoryInterface
{


    public function createService(ServiceLocatorInterface $serviceLocator) {
        /* @var $serviceLocator \Zend\Mvc\Controller\ControllerManager */
        //commented lines are for cloud service graphenedb
        //$client = new N4JClient('rateit.sb01.stations.graphenedb.com', 24789);
        //$client = new N4JClient('localhost', 7474);
        //$client->getTransport();
        //->useHttps()
        //->setAuth('rateit', 'FUzOsyJaTG6WsHGpXuT0');
        $client = new N4JClient('localhost', 7474);
        $client->getTransport();
        //->useHttps()
        return $client;
    }
}
