<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
  'log_path'=> array(
      'path'=>"/Applications/MAMP/logs/php_error.log"
    ),  
  'db' => array (
				'adapters' => array (
						'loginDb' => array (
								'driver'        => 'Pdo',
								'dsn'           => 'mysql:dbname=zend_users;host=localhost',
								'driver_options'=> array (
										PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'' 
								) 
						),
						'appsDb' => array(
								'driver' => 'Pdo',
								'dsn'            => 'mysql:dbname=rateit;hostname=localhost',
								'driver_options' => array(
									PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
								),
						),
    				    'authDb' => array(
    				    		'driver' => 'Pdo',
    				    		'dsn'            => 'mysql:dbname=rateit;hostname=localhost',
    				    		'driver_options' => array(
    				    				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
    				    		),
    				    )
				) 
		),
		'service_manager' => array (
				'factories' => array (
						'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory' 
				),
				'abstract_factories' => array (
						'Zend\Db\Adapter\AdapterAbstractServiceFactory' 
				) 
		),
		'session' => array(
				'config' => array(
						'class' => 'Zend\Session\Config\SessionConfig',
						'options' => array(
								'name' => 'rateit',
						),
				),
				'storage' => 'Zend\Session\Storage\SessionArrayStorage',
				'validators' => array(
						array(
								'Zend\Session\Validator\RemoteAddr',
								'Zend\Session\Validator\HttpUserAgent',
						),
				),
		),
);
