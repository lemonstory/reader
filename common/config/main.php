<?php
use yii\helpers\ArrayHelper;

$config = [
    'timeZone' => 'Asia/Shanghai',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];

$config = ArrayHelper::merge(
    require(__DIR__ . '/db.php'),
    $config);

return $config;



