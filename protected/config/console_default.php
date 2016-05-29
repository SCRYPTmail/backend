<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.

return array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name' => 'My Console Application',

	// preloading 'log' component
	'preload' => array('log'),
	'import' => array(
		'application.models.*',
		'application.extensions.*',
		'application.vendor.*',
		'application.components.*',
		'application.commands.*',
		'application.extensions.mongoDB.*',
		'application.vendor.dns.*',
		'application.vendor.dns.DNS2.*',
		'application.vendor.dns.DNS2.Packet.*',
		'application.vendor.dns.DNS2.Socket.*',
		'application.vendor.dns.DNS2.Cache.*',
		'application.vendor.dns.DNS2.RR.*',
		'application.vendor.softLayer.*',
		'application.vendor.softLayer.ObjectStorage.*',
		'application.vendor.softLayer.ObjectStorage.Http.*',
		'application.vendor.softLayer.ObjectStorage.Http.Adapter.*',
		'application.vendor.softLayer.ObjectStorage.TokenStore.*',
		'application.vendor.softLayer.ObjectStorage.Exception.*',
		'application.vendor.softLayer.ObjectStorage.Exception.Http.*',


	),


		'modules' => array(// uncomment the following to enable the Gii tool
			'user' => array('debug' => false),
		),

	// application components
	'components' => array(
		'EmailParser' => array(
			'class' => 'EmailParser'
		),
		'SavingUserDataV2' => array(
			'class' => 'SavingUserDataV2'
		),
		'db' => array(
			'connectionString' => 'mysql:host=localhost;dbname=encrypted',
			//'connectionString' => 'mysql:host=173.193.178.243;dbname=encrypted',

			'emulatePrepare' => true,
			'username' => '',
			'password' => '',

			'charset' => 'utf8',
			'class' => 'CDbConnection',
			'enableProfiling' => true,
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
					))),

				'db'=>'scryptmail',
				'writeConcerns'=>1,
				'wTimeoutMS'=>20000
			),

		),
		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				array(
					'class' => 'CFileLogRoute',
					'levels' => 'error, warning',
					'categories'=>'system.*',
				),
				array(
					'class'=>'CWebLogRoute',
					'categories'=>'system.db.*',
				),
				array(
					'class'=>'CEmailLogRoute',
					'levels'=>'error, warning,notice',
					'filter'=>'CLogFilter',
					'emails'=>'sergyk17@yahoo.com',
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

		'host'=>'<Object Storage host>',//public
		'folder'=>'',

		'username'=>'<Object Storage Username>',
		'password'=>'<Object Storage Password>'
	),
);

