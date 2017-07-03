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
    'LIKE_TYPE_COMMENT' => 1,
    'LIKE_TYPE_STORY' => 2,

    //热门评论最大数量
    'COMMENT_HOT_MAX_COUNT' => 3,
    //评论对象类型：故事
    'COMMENT_TARGET_TYPE_STORY' => 1,
    //评论对象类型：章节
    'COMMENT_TARGET_TYPE_CHAPTER' => 2,
    //评论对象类型：消息
    'COMMENT_TARGET_TYPE_MESSAGE' => 3,

    //消息评论[投票]内容项
    'COMMENT_MESSAGE_VOTE_CONTENT' => array(
        1, //
        2,
        3,
        4
    )
];
