<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name' => 'SCRYPTmail.com',
	//'defaultController' => 'site',
	'timeZone' => 'UTC',
	// preloading 'log' component
	'preload' => array('log'),

	// autoloading model and component classes
	'import' => array(
		'application.models.*',
		'application.components.*',
		'application.commands.*',
		'application.extensions.mongoDB.*',
		'application.vendor.totp.*',
		'application.vendor.yubikey.*',
		'application.vendor.softLayer.*',
		'application.vendor.softLayer.ObjectStorage.*',
		'application.vendor.softLayer.ObjectStorage.Http.*',
		'application.vendor.softLayer.ObjectStorage.Http.Adapter.*',
		'application.vendor.softLayer.ObjectStorage.TokenStore.*',
		'application.vendor.softLayer.ObjectStorage.Exception.*',
		'application.vendor.softLayer.ObjectStorage.Exception.Http.*',
	),

	'modules' => array(// uncomment the following to enable the Gii tool
	),

	// application components
	'components' => array(
		'EmailParser' => array(
			'class' => 'application.extensions.EmailParser'
		),
		'user' => array(
			// enable cookie-based authentication
			'allowAutoLogin' => false,
			'class' => 'WebUser',
			'loginUrl' => array('login'),

		),
		'cache' => array(
			'class' => 'system.caching.CDbCache',
			'connectionID' => 'db',
			'autoCreateCacheTable' => true,
			'cacheTableName' => 'cache_data',
		),
		'session' => array(
			'sessionName' => 'scryptmail',
			'class' => 'application.components.MyDbHttpSession',
			'autoStart' => 'false',
			'cookieMode' => 'only',
			'timeout' => 86400,

			'connectionID' => 'db',
			'sessionTableName' => 'cache_session',
			'autoCreateSessionTable' => true,
			'cookieParams' => array(
				'httponly' => true,
			),
		),
		'request'=>array(
			'enableCookieValidation'=>true,
		),
		'urlManager' => array(
			'urlFormat' => 'path',
			'showScriptName' => false,
			'rules' => array(
				'<controller:\w+>/<action:\w+>/<id:\w+>' => 'site/<action>',
				'downloadFile/<id>/<name:.*>'=>'site/downloadFile',
				//'dFV2/<id>/<name:.*>'=>'site/dFV2',
				'safeBox/<fileName:.*>' => 'site/safeBox', //safebox
				'<controller:\w+>/<action:\w+>/<id:\w+>/*' => 'site/<action>',
				'<action:\w+>' => 'site/<action>',
				'<controller:\w+>/<id:\d+>' => 'site/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>' => 'site/<action>',
				'<controller:\w+>/<action:\w+>' => 'site/<action>',
				'<fileName:.*>' => 'site/index', //bots
				'retrieveEmail/<id:.*>' => '#retrieveEmailV2/', //safebox

				//'' => 'site/login',
			),
		),
		/*
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		// uncomment the following to use a MySQL database
		*/
		'db' => array(
			'connectionString' => 'mysql:host=127.0.0.1;dbname=encrypted',

			'emulatePrepare' => true,
			'username' => '',
			'password' => '',

			'charset' => 'utf8',
			'class' => 'CDbConnection',
			//'enableProfiling'=>true,
			'enableParamLogging' => true,
			'schemaCachingDuration' => '100',
		),
		'mongo'=>array(
			'connectionString'=>"mongodb://localhost:27017",
			'class'=>'MongoDBConnection',
			'options'=>array(
				"ssl" => false,
				"sslOptions"=>array(
					"context" =>stream_context_create(array(
						"ssl" => array(
							"allow_self_signed" => true,
							"verify_peer"       => false,
							"verify_peer_name"  => false,
							"verify_expiry"     => false,
						),
					))
				),

				'db'=>'scryptmail',
				'writeConcerns'=>1,
				'wTimeoutMS'=>2000
			),

		),

		'errorHandler' => array(
			// use 'site/error' action to display errors
			'errorAction' => 'site/error',
		),
		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				array(

					'class' => 'CProfileLogRoute',
					'enabled' => true,
					'levels' => 'profile,error,warning,notice,trace',
					'filter' => 'CLogFilter',
					//'ignoreAjaxInFireBug'=>false,
				),
				// uncomment the following to show log messages on web pages

				array(
					'class' => 'CWebLogRoute',
					'levels' => 'profile,error,warning,notice,trace',
					//'categories' => 'system.db.*',
				),


			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params' => array(
		// this is used in contact page
		'params' => include(dirname(__FILE__) . '/params.php'), //<– our internal params
		'adminEmail' => 'test@mail.com',
		'production' => false,

		'trustedSenders'=>array(
			'test@email.com'
		),

		'coinKey'=> "",
		'coinSecret'=> "",
		'paypalSign'=>'',
		'pass'=>"",

		'host'=>'<Object Storage Host>',//public
		'folder'=>'',
		'username'=>'<Object Storage Username>',
		'password'=>'<Object Storage Password>',

		'YuserID'=>'<YubiKey API ID>',
		'Ypass'=>'<YubiKey API Password>'
	),
);