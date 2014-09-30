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
 * @package rateit
 * @author Alain Bindele
 * @version pre-alpha 0.1
 */
namespace Application\Model;



/**
 * Class that works as a proxy between
 * the database entity manager and the
 * associated service locator
 * Interacts with the model reading and writing on the 
 * database all the info regarding the survey
 * @author Alain Bindele
 */
 class ConnectionManager{

	private $em;
	public function __construct($serviceLocator) {
		$this->em = $serviceLocator->get('humusneo4jogm.entitymanager.ogm_default');
		return $this;
	}

    /*
     * Returns the inner entity manager that
     * manages the model layer
     */

	public function getEntityManager(){
		return $this->em;
	}
}
