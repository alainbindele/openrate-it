<?php

return array(
    'humusneo4jogm' => array(
        'configuration' => array(
        		'ogm_default' => array(
        				'transport' => 'default',
        				'host' => '******',
        				'port' => ****,
        				'proxy_dir' => 'data/x',
        				'debug' => true,
        				'cache' => 'array',
        		)
        ),
        'entitymanager' => array(
            'ogm_default' => array(
                'configuration' => 'ogm_default',
            )
        ),
    ),
);
