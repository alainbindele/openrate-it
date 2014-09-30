<?php
return array(
    'modules' => array(
     	'HumusNeo4jOGMModule',
		'DoctrineModule',
		'ReverseOAuth2',
		'ZfcBase',
		'ZfcUser',
		'ScnSocialAuth',
		'Application'
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            '../../../config/autoload/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            'module',
            'vendor',
        ),
    ),
);
