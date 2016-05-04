<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Console Application',
    #Important 
    'commandPath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'commands'.DIRECTORY_SEPARATOR.'shell',

    'import' => array (
                'application.models.*',
                'application.components.*',
        ),
	// preloading 'log' component
	'preload'=>array('log'),

	// application components
	'components'=>array(
        /**
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
        **/
		// uncomment the following to use a MySQL database
		'db'=>array(
			'connectionString' => 'mysql:host=127.0.0.1;dbname=OA-TEST',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
        'session'=>array(
				'class'=>'CHttpSession',
			),
	),
    'params'=>array(
        // this is used in contact page
        'adminEmail'=>'382597373@qq.com-a',
        'smtp_host' =>'smtp.exmail.qq.com-a',
        'smtp_email'=>'hr@shanyougame.com-a',
        'stmp_password'=>'sara315983078-a',
	),
);
