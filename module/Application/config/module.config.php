<?php


/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication 
 * for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. 
 * (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package rateit
 * @author Alain Bindele
 * @version 0.1
 */


return array(
    'controller_plugins' => array(
    		'invokables' => array(
    				'CredentialsPlugin' => 'Application\Controller\Plugin\CredentialsPlugin',
    		)
    ),
    'module_config' => array(
    		'upload_location'   => __DIR__.'/../../../data/uploads'
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',

                    ),
                ),
            ),
            'admin' => array(
            	'type' => 'segment',
            	'options' => array(
            		'route'    => '/admin[/:action][/:filename]',
            		'constraints' => array(
            			'controller' => 'Application\Controller\Admin',
            			'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
            		),
            		'defaults' => array(
            			'controller' => 'Application\Controller\Admin',
            			'action'     => 'index',
            		),
            	),
            ),
            
            'auth' => array(
            		'type' => 'segment',
            		'options' => array(
            				'route'    => '/auth[/:action]',
            				'constraints' => array(
            						'controller' => 'Auth\Controller\Auth',
            						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
            				),
            				'defaults' => array(
            						'controller' => 'Auth\Controller\Auth',
            						'action'     => 'index',
            				),
            		),
            ),
            
            'users' => array(
            		'type' => 'segment',
            		'options' => array(
            				'route'    => '/users[/:action][/:id]',
            				'constraints' => array(
            						'controller' => 'Application\Controller\Users',
            						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
						            'id'         => '[0-9]+',
            				),
            				'defaults' => array(
            						'controller' => 'Application\Controller\Users',
            						'action'     => 'index',
            				),
            		),
            ),
            'surveys' => array(
            		'type' => 'segment',
            		'options' => array(
            				'route'    => '/surveys[/:action][/:id]',
            				'constraints' => array(
            						'controller' => 'Application\Controller\Survey',
            						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
						            'id'         => '[0-9]+',
            				),
            				'defaults' => array(
            						'controller' => 'Application\Controller\Survey',
            						'action'     => 'index',
            				),
            		),
            ),
            
            'db' => array(
            		'type' => 'Zend\Mvc\Router\Http\Literal',
            		'options' => array(
            				'route'    => '/db',
            				'defaults' => array(
            						'controller' => 'Application\Controller\Admin',
            						'action'     => 'index',
            				),
            		),
            ),
            'tags' => array(
            		'type' => 'segment',
            		'options' => array(
            				'route'    => '/tags[/:action][/:id]',
            				'constraints' => array(
            						'controller' => 'Application\Controller\Tags',
            						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
						            'id'         => '[0-9]+',
            				),
            				'defaults' => array(
            						'controller' => 'Application\Controller\Tags',
            						'action'     => 'getTags',
            				),
            		),
            ),
            'test' => array(
            		'type' => 'segment',
            		'options' => array(
            				'route'    => '/test[/:action][/:id]',
            				'constraints' => array(
            						'controller' => 'Application\Controller\Test',
            						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
            						'id'         => '[0-9]+',
            				),
            				'defaults' => array(
            						'controller' => 'Application\Controller\Test',
            						'action'     => 'index',
            				),
            		),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'service_manager' => array(
    	'factories' => array(
    	'Neo4jClientFactory'	=>	'Application\Controller\Factories\Neo4jClientFactory',
    	),
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index'  => 'Application\Controller\IndexController',
            'Application\Controller\Admin'  => 'Application\Controller\AdminController',
            'Application\Controller\Users'  => 'Application\Controller\UsersController',
            'Application\Controller\Survey' => 'Application\Controller\SurveysController',
            'Application\Controller\Tags'   => 'Application\Controller\TagsController',
            'Application\Controller\Test'   => 'Application\Controller\TestController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'not_auth_template'        => 'error/401',
        'exception_template'       => 'error/index',
        'template_map' => array(
        
        	//MAIN LAYOUTS
            'layout/layout'           						=> __DIR__ . '/../view/layout/layout.phtml',
            'layout/adminPanelLayout' 						=> __DIR__ . '/../view/layout/admin-panel-layout.phtml',


            ///////////////// A D M I N   INTERFACE SECTION \\\\\\\\\\\\\\
            //USER MENU
            'userMenu'			  							=> __DIR__ . '/../view/content/admin-panel/user-menu/user-menu.phtml',

            //ADMIN PANEL
            'adminPanel/output'		  						=> __DIR__ . '/../view/content/admin-panel/output-panel/output.phtml',
            'adminPanel/inputPanel'	  						=> __DIR__ . '/../view/content/admin-panel/input-panel/input-panel.phtml',

            //USERS AND RELATIONSHIPS
    		'usersAndRelationships/usersAndRelationships'	=> __DIR__ . '/../view/content/admin-panel/input-panel/users-and-relationships/users-and-relationships.phtml',
            	'usersAndRelationships/addCircles'			=> __DIR__ . '/../view/content/admin-panel/input-panel/users-and-relationships/add-circles.phtml',
            	'usersAndRelationships/addUsers'	  		=> __DIR__ . '/../view/content/admin-panel/input-panel/users-and-relationships/add-users.phtml',
            	'usersAndRelationships/deleteUsers'	  		=> __DIR__ . '/../view/content/admin-panel/input-panel/users-and-relationships/delete-users.phtml',
            	'usersAndRelationships/deleteAllUsers'	  	=> __DIR__ . '/../view/content/admin-panel/input-panel/users-and-relationships/delete-all-users.phtml',
            	'usersAndRelationships/addSocialRelation'	=> __DIR__ . '/../view/content/admin-panel/input-panel/users-and-relationships/add-social-relation.phtml',
            	'usersAndRelationships/deleteSocialRelation'=> __DIR__ . '/../view/content/admin-panel/input-panel/users-and-relationships/delete-social-relation.phtml',
    			'usersAndRelationships/getAllUsers'			=> __DIR__ . '/../view/content/admin-panel/input-panel/users-and-relationships/get-all-users.phtml',
    			
            //TODELETE
    		'usersAndRelationships/users'					=> __DIR__ . '/../view/content/admin-panel/input-panel/users-and-relationships/users.phtml',

            //UNITS AND CONTAINERS
            'input-panel/surveysAndContainers'	  			=> __DIR__ . '/../view/content/admin-panel/input-panel/surveys-and-containers/surveys-and-containers.phtml',
            'input-panel/addSurveys'				  		=> __DIR__ . '/../view/content/admin-panel/input-panel/surveys-and-containers/add-survey.phtml',
            'input-panel/addContainers'				  		=> __DIR__ . '/../view/content/admin-panel/input-panel/surveys-and-containers/add-containers.phtml',

            //RATES
            'input-panel/rates&comments'	  				=> __DIR__ . '/../view/content/admin-panel/input-panel/rates/rates-and-comments.phtml',
            'input-panel/rates'	  							=> __DIR__ . '/../view/content/admin-panel/input-panel/rates/rates.phtml',
            'input-panel/addRates'	  						=> __DIR__ . '/../view/content/admin-panel/input-panel/rates/add-rates.phtml',

            //COMMENTS
            'input-panel/addComments'				  		=> __DIR__ . '/../view/content/admin-panel/input-panel/rates/comments.phtml',

            ///////////////// END ADMIN INTERFACE SECTION \\\\\\\\\\\\\\\\

            //INDEXES
            'application/index/index' 						=> __DIR__ . '/../view/application/index/index.phtml',
            'application/admin/index' 						=> __DIR__ . '/../view/application/admin/index.phtml',
    
            //ERRORS
            'error/404'               						=> __DIR__ . '/../view/error/404.phtml',
            'error/401'               						=> __DIR__ . '/../view/error/401.phtml',
            'error/index'             						=> __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
    'dbnames' => array(
    ),
);
