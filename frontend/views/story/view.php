<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title>有味读书</title>
    <link rel="stylesheet" href="http://youwei.xiaoningmeng.net/css/collection.css">
    <link rel="stylesheet" href="http://youwei.xiaoningmeng.net/css/webbase.css">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <!--百度统计 start-->
    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?db5c42f87417ac33017d1393aeba9922";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>
    <!--百度统计 end-->
</head>
<body>

<div id="J_StoryMainBox" class="story-main-box qq-theme" data-sid="x4kCfiYqKTvBOBd119P-Uw">
    <div id="J_StoryContent" class="story-content">
        <ul id="J_StoryContentList" class="story-dialog-list" style="">

            <?php
            if (!empty($chapterMessageContent) && is_array($chapterMessageContent)) {

                foreach ($chapterMessageContent as $chapterMessageContentItem) {

                    $content = "";

                    //旁白
                    if (empty($chapterMessageContentItem['actor_id'])) {

                        $content = "<li class=\"story-dialog-misc-item\"><span class=\"text\">{$chapterMessageContentItem['voice_over']}</span></li>";

                    } else {
                        //消息
                        if (!empty($actor) && is_array($actor)) {

                            //角色
                            $actorId = $chapterMessageContentItem['actor_id'];
                            $actorInfo = $actor[$actorId];
                            $actorLocation = $actorInfo['location'] == 0 ? 'left' : 'right';
                            $actorName = $actorInfo['name'];
                            $actorAvatar = $actorInfo['avatar'];

                            $text = $chapterMessageContentItem['text'];
                            $image = $chapterMessageContentItem['img'];

                            //文字
                            if(!empty($text)) {
                                $content .= "<li class=\"story-dialog-item {$actorLocation} \">
                                    <div class=\"avatar\">
                                        <div class=\"avatar-content\" style=\"background-image:url({$actorAvatar})\"></div>
                                    </div>
                                    <div class=\"content-box\">
                                        <p class=\"name\">{$actorName}</p>
                                        <div class=\"message-content-box\">
                                            <div class=\"message-bubble-wrap\">
                                                <p class=\"message-bubble\">{$text}</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>";
                            }

                            //图片
                            if(!empty($image)) {
                                $content .= "<li class=\"story-dialog-item {$actorLocation} image-type\">
                                    <div class=\"avatar\">
                                        <div class=\"avatar-content\" style=\"background-image:url(https://tx.i.hecdn.com/crucio/DW1vL5MUvXSN82MTOoOJ-g@w-90.jpg)\"></div>
                                    </div>
                                    <div class=\"content-box\">
                                        <p class=\"name\">{$actorName}</p>
                                        <div class=\"message-content-box\">
                                            <div class=\"message-bubble-wrap\">
                                                <p class=\"message-bubble\">
                                                    <img width=\"100%\" src=\"{$image}\" alt=\"Image\">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </li>";
                            }

                        }
                    }
                    echo $content;
                }
            }

            ?>


            <!--            <li class="story-dialog-item right ">-->
            <!---->
            <!--                <div class="avatar">-->
            <!--                    <div class="avatar-content" style="background-image:url(https://tx.i.hecdn.com/crucio/WM0jMbYCiuhJNdiVyIMmgQ@w-90.jpg)"></div>-->
            <!--                </div>-->
            <!--                <div class="content-box">-->
            <!--                    <p class="name">季叶</p>-->
            <!--                    <div class="message-content-box">-->
            <!--                        <div class="message-bubble-wrap">-->
            <!--                            <p class="message-bubble">干嘛？</p>-->
            <!--                        </div>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--            </li>-->
            <!---->
            <!---->
            <!--            <li class="story-dialog-misc-item"><span class="text">叩叩，季叶敲了敲那个最里面的门。</span></li>-->


        </ul>
        <div id="J_StoryEndTips" class="story-end-cat"></div>
        <div class="story-end-actions">
<!--            <ul class="story-end-misc-action-list">-->
<!--                <li class="story-end-misc-action-item action-like-related J_Download">赞 / 踩</li>-->
<!--                <li class="story-end-misc-action-item action-donate J_Download">投喂作者</li>-->
<!--            </ul>-->
<!--            <ul class="story-end-action-list">-->
<!--                <li class="story-end-action-item action-comment J_Download">-->
<!--                    <div class="action-item-content">评论</div>-->
<!--                </li>-->
<!--                <li class="story-end-action-item action-subscribe J_Download">-->
<!--                    <div class="action-item-content"></div>-->
<!--                </li>-->
<!--                <li class="story-end-action-item action-share J_Download">-->
<!--                    <div class="action-item-content">安利</div>-->
<!--                    <i class="redenvelope-flag"></i></li>-->
<!--            </ul>-->
<!--            <div class="author-info-box">-->
<!--                <a class="J_Download">-->
<!---->
<!--                    <span class="author-avatar"-->
<!--                          style="background-image:url(https://tx.i.hecdn.com/crucio/AQdIKzQId1xH2wvAlMfcdg@w-150.jpg)"></span>-->
<!--                    <span class="author-name">七姐姐</span>-->
<!--                </a>-->
<!--                <i class="icon-author"></i>-->
<!--                <span class="btn-follow-author J_Download"></span>-->
<!--            </div>-->
        </div>
        <div class="recommend-story-box">
            <h6 class="recommend-story-hd">继续阅读，看接下来会发生什么…<a href="http://a.app.qq.com/o/simple.jsp?pkgname=net.xiaoningmeng.youwei" style="height: 60px;line-height: 60px;color: #222222;    background: #fff;border: 2px solid #222222;    border-radius: 5px;    font-size: 14px;    padding: 0 10px;    text-decoration: none;">立即下载</a></h6>

<!--            <div class="recommend-story-bd">-->
<!---->
<!--                <div class="story-cover"-->
<!--                     style="background-image:url(https://tx.i.hecdn.com/crucio/N-EKJjp77eX6HPtJEEo_uQ@w-375.jpg);"></div>-->
<!--                <div class="story-bg-mask">-->
<!--                    <section class="story-intro">-->
<!--                        <h1 class="title">恐怖的厕所</h1>-->
<!--                        <p class="chapter">第2话</p>-->
<!--                        <div class="btn-read-more"><a href="#" class="J_Download">继续阅读</a></div>-->
<!--                    </section>-->
<!--                </div>-->
<!--            </div>-->
        </div>
    </div>
    <div id="J_GuideStoryClick" class="guide-story-click"></div>
</div>

<div id="J_TopDownloadTips" class="top-download-tips">
    <a href="#" class="J_Download">
        <div class="footer-download-box">
            <div class="logo"></div>
            <div class="tips">
                <p>有味读书</p>
                <p>00后都在玩的对话小说App</p>
            </div>
            <span class="btn-download">立即下载</span>
        </div>
    </a>
</div>

<script>
    var Crucio = {
        appDownloadUrl: 'http://www.youweiapp.com?from=1'
    };
</script>
<script src="http://youwei-static.oss-cn-beijing.aliyuncs.com/js/jquery.js"></script>
<script src="http://youwei-static.oss-cn-beijing.aliyuncs.com/js/velocity.js"></script>
<script src="http://youwei-static.oss-cn-beijing.aliyuncs.com/js/collection.js"></script>
</body>
</html>