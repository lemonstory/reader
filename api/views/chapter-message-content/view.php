<?php
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
?>
<data>
    <status>200</status>
    <message>OK</message>
    <chapter>
        <story_id><?= $storyId; ?></story_id>
        <chapter_id><?= $chapterId; ?></chapter_id>
        <chapter_message_content>
            <?php
            foreach ($chapterMessageContent as $messageItem) {
                echo <<<EOT
                    <message>
                        <message_id>{$messageItem['message_id']}</message_id>
                        <number>{$messageItem['number']}</number>
                        <voice_over><![CDATA[{$messageItem['voice_over']}]]></voice_over>
                        <actor>
                            <actor_id>{$messageItem['actor_id']}</actor_id>
                        </actor>
                        <text><![CDATA[{$messageItem['text']}]]></text>
                        <img><![CDATA[{$messageItem['img']}]]></img>
                        <is_loading>{$messageItem['is_loading']}</is_loading>
                        <status>{$messageItem['status']}</status>
                    </message>
EOT;
            }
            ?>
        </chapter_message_content>
    </chapter>
</data>
