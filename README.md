# reader


消息结构：

​	消息正文[img]配图地址[/img]



故事点赞：

​	使用Redis SortedSet 和 String实现

​	1.	所有对$story_id点赞的uid集合:

​		like_story_user_set_{$story_id} : {uid....}[ZREVRANGE like_story_user_set_{$story_id} 0 -1 WITHSCORES]

​	2.	uid点赞的所有$story_id集合:

​		story_user_like_set_{$uid}:{story_id…} [ZREVRANGE story_user_like_set_{$uid} 0 -1 WITHSCORES]

​	3.	$story_id的总赞数

​		count_story_{$story_id} [GET count_story_{$story_id}]

​	4.	$uid对$story_id点赞		

​		1） like_story_user_set_{$story_id} 添加 $uid [ ZADD like_story_user_set_{$story_id}  time $uid]

​		2）story_user_like_set_{$uid} 添加 $story_id [ ZADD story_user_like_set_{$uid} time $story_id]

​		3）count_story_{$story_id} + 1[INCR count_story_{$story_id} ]


​	5.	$uid对$story_id取消点赞

​		1）like_story_user_set_{$story_id} [ZREM like_story_user_set_{$story_id} $uid]

​		2）story_user_like_set_{$uid} [ZREM story_user_like_set_{$uid} $story_id]

​		3）count_story_{$story_id} - 1 [DECR count_story_{$story_id} ]



评论点赞：

​	实现思路和故事点赞思路相同

​	1.	所有对$comment_id点赞的uid集合:

​		like_comment_user_set_{$comment_id} : {uid....}[ZREVRANGE like_comment_user_set_{$comment_id} 0 -1 WITHSCORES]

​	2.	uid点赞的所有$comment_id集合:

​		comment_user_like_set_{$uid}:{$comment_id…} [ZREVRANGE comment_user_like_set_{$uid} 0 -1 WITHSCORES]

​	3.	$comment_id的总赞数

​		count_comment_{$comment_id} [GET count_comment_{$comment_id}]

​	4.	$uid对$comment_id点赞		

​		1） like_comment_user_set_{$comment_id} 添加 $uid [ ZADD like_comment_user_set_{$comment_id}  time $uid]

​		2）comment_user_like_set_{$uid} 添加 $comment_id [ ZADD comment_user_like_set_{$uid} time $comment_id]

​		3）count_comment_{$comment_id} + 1[INCR count_comment_{$comment_id} ]

​	5.	$uid对$comment_id取消点赞

​		1）like_comment_user_set_{$comment_id} [ZREM like_comment_user_set_{$comment_id} $uid]

​		2）comment_user_like_set_{$uid} [ZREM comment_user_like_set_{$uid} $comment_id]

​		3）count_comment_{$comment_id} - 1 [DECR count_comment_{$comment_id} ]	



客户端阅读记录实现：

​	添加阅读记录：

​		进入故事阅读页时：向[client_]user_story_record:添加一条记录.

​		离开故事阅读页是：更新上面记录的last_last_scenes_id,last_message_id,last_modify_time字段

​		**问题：数据同步**



​	清空阅读记录：

​		删除[client_]user_story_record中的所以记录

​		**问题：数据同步**

​	

​	点击阅读记录中的某故事进入阅读页且阅读了消息：

​		更新该故事的last_last_scenes_id,last_message_id,last_modify_time

​		同时刷新{用户阅读记录列表}

​	

​	点击阅读记录中的某故事进入阅读页且未阅读消息[未单击]：

​		什么都不做.



​	用户阅读记录列表:

​		根据last_modify_time倒序排序



客户端订阅故事：

	1.	故事有更新会推送至客户端(可以在设置内关闭)



​	**问题: 订阅故事后,故事发生更新,客户端如何处理**

​	当scenes发布时：检查story_update_subscript，然后向用户push

​	Push: 向所以订阅同一故事的用户打一个tag. 向这个tag做push业务

​	Redis：某个故事下面的所有订阅用户.


### 业务###

#### 状态码####

status:

​	0：删除

​	1：正常

