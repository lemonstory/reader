<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,

    //aliyun open search
    'accessKeyID' => 'QHzux6QVXjQgfBNM',
    'accessKeySecret' => 'diWfijmBbiGlwle1s9KyAL8BQhB3Qc',
    'openSearchEndPoint' => 'opensearch-cn-hangzhou.aliyuncs.com',
    'openSearchAppName' => 'youwei',
    'openSearchOptions' => array('debug' => true),
    'openSearchSuggestAllName' => 'all',

    //aliyun oss
    //TODO:线上endpoint可以走内网
    'ossEndPoint' => 'oss-cn-shanghai.aliyuncs.com',
    'ossPicObjectBucket' => 'youwei-pic',
    'ossPicObjectCoverPrefix' => 'cover/',
    'ossPicObjectBackgroundPrefix' => 'background/',
    'ossPicObjectCoverSuffix' => '.jpg',

    //点赞对象类型
    'LIKE_TARGET_TYPE' => [
        ['label' => '故事','alias' => 'story','value' => 1],
        ['label' => '评论','alias' => 'comment','value' => 2],
    ],

    //热门评论最大数量
    'COMMENT_HOT_MAX_COUNT' => 3,

    //评论对象类型：故事
    'COMMENT_TARGET_TYPE' => [
        ['label' => '故事','alias' => 'story','value' => 1],
        ['label' => '章节','alias' => 'chapter','value' => 2],
        ['label' => '消息内容','alias' => 'chapter-message-content','value' => 3],
    ],

    //故事角色位置方向
    'storyActorLocation' => [
        ['label' => '左','alias' => 'left','value' => 0],
        ['label' => '右','alias' => 'right','value' => 1],
    ],

    //消息评论[投票]内容项
    'COMMENT_MESSAGE_VOTE_CONTENT' => array(
        1, //
        2,
        3,
        4
    )
];
