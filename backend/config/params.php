<?php
return [
    'adminEmail' => 'admin@example.com',

    //http://www.yiichina.com/doc/guide/2.0/security-authorization
    //容许访问后台的uid列表
    'adminUidWhiteList' => [10001],

    //容许访问后台的ip列表
    'adminIps' => [],//'127.0.*'

    //后台标签Id对应标签名称背景颜色
    //http://c.youwei.xiaoningmeng.net/assets/d3535800/css/AdminLTE.min.css
    //tagID => class name(color)
    'tagNameBg' => [
        '1' => 'bg-yellow',
        '2' => 'bg-green',
        '3' => 'bg-aqua',
        '4' => 'bg-blue',
        '5' => 'bg-navy',
        '6' => 'bg-teal',
        '7' => 'bg-olive',
        '8' => 'bg-lime',
        '9' => 'bg-orange',
        '10' => 'bg-fuchsia',
        '11' => 'bg-purple',
        '12' => 'bg-maroon',
    ],

    'defaultTagNameBg' => 'bg-red',
];
