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
 * Zend Framework (http://framework.zend.com/)
 * This is the application index controller (displays the home page)
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * IndexController
 *
 * @author Alain Bindele
 * @package OpenRate-it!
 * @version Pre-Alpha 0.1
 */
class IndexController extends AbstractActionController
{
    /*
     * returns the index page and passes to the view
     * the information about is status (logged/not logged)
     */
    public function indexAction()
    {
    	return $this->zfcUserAuthentication()->hasIdentity()?
    		new ViewModel(array('logged'=>'true')):
    		new ViewModel(array('logged'=>'false'));
    }
}
