<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../../vendor/autoload.php');
//阿里云oss
//https://help.aliyun.com/document_detail/32099.html?spm=5176.doc31886.6.762.P4dhRb
require(__DIR__ . '/../../vendor/aliyuncs/oss-sdk-php/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php'),
    require(__DIR__ . '/../config/main.php'),
    require(__DIR__ . '/../config/main-local.php')
);

//(new yii\web\Application($config))->run();
$application = (new yii\web\Application($config));
$application->name = "有味读书";
$application->run();