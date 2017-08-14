####接口文档
 
 ```
 Version:0.4
 LastModifyTime:2017/6/12
 
 名称解释：
     故事(story)       故事，书籍的概念.
     章节(chapter)     故事的章节
     消息(message)     故事内容的最小单位,各个章节内里面的内容.
     角色(actor)       故事的角色(类似影视剧里面的角色)
     分类(tag)         故事的标签
     评论(commint)     故事内容的评论,可评价一个故事(或)故事的一个章节(或)故事的一个章节的一条消息
     状态(status)      0:删除,1:正常,2:修改(数据同步时会使用到)
 
 ```
 
 ```
 域名：
     PATH             http://api.youwei.xiaoningmeng.net
 ```
 
 ```
 测试工具：
     命令行：httpie https://github.com/jakubroztocil/httpie
     桌面：  postman https://chrome.google.com/webstore/detail/postman/fhbjgbiflinjbdggehcddcbncdddomop?hl=zh-CN
 
 ```
 
 ```
 我的故事
    
     1)获取用户自己发布的故事[已完成]
     
        api:    /user/storys
        method: GET
        params:
                uid:用户uid
                access-token:登录用户授权访问凭证
                page:页码
                pre_page:每页显示内容数
        ret:    Json数组
        example:http://api.youwei.xiaoningmeng.net/user/storys?uid=1&page=1&pre_page=1&access-token=x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX
        
              {
                   "code": 200,
                   "data": {
                       "totalCount": "内容总数量",
                       "pageCount": "总页数",
                       "currentPage": "当前页码",
                       "perPage": "每页显示内容数",
                       "storyList": [
                           {
                               "story_id": "故事id",
                               "name": "故事标题",
                               "description": "故事简介",
                               "cover": "封面",
                               "is_published": "是否发布",
                               "actor": "故事角色信息",
                               "tag": "故事标签信息",
                               "chapter_count": "章节总数量",
                               "message_count": "消息总数量",
                               "taps": "点击数",
                               "create_time": "创建时间",
                               "last_modify_time": "最后修改时间"
                           },
                           {
                               "story_id": "故事id",
                               "name": "故事标题",
                               "description": "故事简介",
                               "cover": "封面",
                               "is_published": "是否发布",
                               "actor": "故事角色信息",
                               "tag": "故事标签信息JSON数组",
                               "chapter_count": "章节总数量",
                               "message_count": "消息总数量",
                               "taps": "点击数",
                               "create_time": "创建时间",
                               "last_modify_time": "最后修改时间"
                           }
                       ]
                   },
                   "msg": "OK"
              }
                    
     2)创建故事[支持批量][已完成]
     
        api:    /story/batch-create?uid=x&access-token=y
        method: POST
        params:
                storys[0][local_story_id]:本地故事id
                storys[0][name]:标题
                storys[0][description]:简介
                storys[0][cover]:封面
                storys[0][is_published]:是否发布
                storys[0][actor]:故事角色信息 //[{"number":"角色序号-1","name":"角色姓名-1","avatar":"角色头像-2","is_visible":1},{"number":"角色序号-2","name":"角色姓名-2","avatar":"角色头像-2","is_visible":1}];
                storys[0][tag]:故事标签信息//[{"tag_id":1, "status":"1"},{"tag_id":2, "status":"1"}]
                storys[0][chapter_count]:章节总数量
                storys[0][message_count]:消息总数量
                storys[0][taps]:点击数
                storys[0][create_time]:创建时间
                storys[0][last_modify_time]:最后修改时间
                storys[1][local_story_id]:本地故事id
                storys[1][name]:标题
                storys[1][description]:简介
                storys[1][cover]:封面
                storys[1][is_published]:是否发布
                storys[1][actor]:故事角色信息
                storys[1][tag]:故事标签信息
                storys[1][chapter_count]:章节总数量
                storys[1][message_count]:消息总数量
                storys[1][taps]:点击数
                storys[1][create_time]:创建时间
                storys[1][last_modify_time]:最后修改时间
        ret:    Json数组
                {
                    "data": [
                        {
                            "local_story_id": "本地故事id-1",
                            "story_id": 44
                        },
                        {
                            "local_story_id": "本地故事id-2",
                            "story_id": 45
                        }
                    ],
                    "code": 200,//200:全部新建成功;206:部分新建成功
                    "msg": "OK"
                }
        备注:    
            1.  code=200:全部新建成功;code=206:部分新建成功
            2.  actor:故事角色的Json串(见上面示例代码)
            3.  tag:故事标签的Json串(见上面示例代码)
                    
     
     (已同步的故事:选择封面,标题，描述; 选择标签; 点击发布; 设置角色不可见; 故事点击量同步..等等)    
     3)修改,删除故事[支持批量][已完成]
      
         api:    /story/batch-update?uid=x&access-token=y
         method: POST
         params:
                 storys[0][story_id]:故事id
                 storys[0][name]:标题
                 storys[0][description]:简介
                 storys[0][cover]:封面
                 storys[0][is_published]:是否发布
                 storys[0][actor]:故事角色信息 //[{"actor_id":1, "number":"1","name":"姓名-1","avatar":"","is_visible":1},{"actor_id":2,"number":"2","name":"姓名-2","avatar":"","is_visible":1}];
                 storys[0][tag]:故事标签信息//[{"tag_id":1, "status":"1"},{"tag_id":2, "status":"1"}]
                 storys[0][chapter_count]:章节总数量
                 storys[0][message_count]:消息总数量
                 storys[0][taps]:点击数
                 storys[0][create_time]:创建时间
                 storys[0][last_modify_time]:最后修改时间
         ret:    Json数组
                {
                    "data": [
                        {
                            "local_story_id": "本地故事id-1",
                            "story_id": 44
                        },
                        {
                            "local_story_id": "本地故事id-2",
                            "story_id": 45
                        }
                    ],
                    "code": 200,//200:全部新建成功;206:部分新建成功
                    "msg": "OK"
                }
            备注:    
                1.  code=200:全部修改成功;code=206:部分修改成功
                2.  actor:故事角色的Json串(见上面示例代码), 如果是新增的角色则没有actor_id字段。
                3.  tag:故事标签的Json串(见上面示例代码)
                4.  故事点击量同步也使用该接口.taps在这里是递增量(即：原来故事taps=1,传入taps=3, 故事的taps最终会是4)
                     
     4)新建,修改,删除章节消息内容[已完成]
     
          api:    /chapter/commit-message-content?uid=x&access-token=y
          method: POST(multipart/form-data)
          params:
                  local_story_id:本地故事id
                  story_id:故事id
                  local_chapter_id:本地章节id
                  chapter_id:章节id
                  status:新建,修改,删除
                  create_time:创建时间
                  last_modify_time:最后修改时间
                  chapter_message_content:章节消息内容文件[type=file]
          ret:  Json
                {
                    "code": 200,
                    "data": {
                        "local_story_id": "本地故事id",
                        "story_id": "故事id",
                        "local_chapter_id": "本地章节id",
                        "chapter_id": "章节id",
                        "status": "正常，新建，修改，删除",
                        "create_time": "创建时间",
                        "last_modify_time": "最后修改时间"
                        "message_count": "章节消息数量"
                    },
                    "msg": "OK"
                }
                
     5)查看故事详情[已完成]
         api:    /story/{故事Id}
         method: GET
         params:
                uid:用户uid
                story_id:故事Id
         example:http://api.youwei.xiaoningmeng.net/story/1
         ret:    Json
                {
                    "data": {
                        "story_id": 1,
                        "name": "超级怪兽工厂",
                        "description": "叶不非是不幸的，不幸的是他被老板随手抡起的一本破书砸晕了头。",
                        "cover": "http://qidian.qpic.cn/qdbimg/349573/1002959239/180",
                        "chapter_count": 1,
                        "message_count": 21,
                        "taps": "33",
                        "is_published": 0,
                        "status": 1,
                        "create_time": "2017-06-13 09:37:28",
                        "last_modify_time": "2017-06-13 10:09:09",
                        "actor": [
                            {
                                "actor_id": "2",
                                "name": "昭昭",
                                "avatar": "http://p5.gexing.com/GSF/touxiang/20170612/17/17y0gn7l1s62rca89urd0o3bx.jpg@!200x200_3?recache=20131108",
                                "number": "2",
                                "location": "1",
                            },
                            {
                                "actor_id": "3",
                                "name": "乐乐",
                                "avatar": "http://p5.gexing.com/GSF/touxiang/20170610/02/gmykod8dfr9xm2f99wdcefdg.jpg@!200x200_3?recache=20131108",
                                "number": "3",
                                "location": "0",
                            }
                        ],
                        "tag": [
                            {
                                "tag_id": "1",
                                "name": "言情",
                                "number": "1"
                            },
                            {
                                "tag_id": "2",
                                "name": "悬疑",
                                "number": "2"
                            },
                            {
                                "tag_id": "3",
                                "name": "搞笑",
                                "number": "3"
                            }
                        ],
                        "user": {
                            "uid": 1,
                            "username": "小逗",
                            "avatar": "http://p5.gexing.com/GSF/touxiang/20170615/17/4jcoh44l7zlt5e0vszuj1aawv.jpg@!200x200_3?recache=20131108",
                            "signature": "这是签名"
                        }
                    },
                    "code": 200,
                    "message": "OK"
                }
                备注：location表示角色位置：0：左，1：右
            
         6)查看故事章节[已完成]
               api:    /story/chapters/{故事Id}
               method: GET
               params:
                      uid:用户uid
                      (story_id)故事Id
               example:http://api.youwei.xiaoningmeng.net/story/chapters/1
               ret:    Json数组
                {
                    "data": [
                        {
                            "chapter_id": "1",
                            "name": "",
                            "background": "https://ss0.bdstatic.com/94oJfD_bAAcT8t7mm9GUKT-xh_/timg?image&quality=100&size=b4000_4000&sec=1497318203&di=c12c42414d58b29942ee50fc0c72a610&src=http://img3.duitang.com/uploads/item/201605/08/20160508154716_QdWne.jpeg",
                            "message_count": "3",
                            "number": "1",
                            "is_published": "0",
                            "create_time": "2017-06-13 09:43:54",
                            "last_modify_time": "2017-06-13 09:44:26"
                        },
                        {
                            "chapter_id": "2",
                            "name": "",
                            "background": "https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1497328288837&di=0210ff50c50444c32c2bb13922585ea8&imgtype=0&src=http%3A%2F%2Fimg16.3lian.com%2Fgif2016%2Fq6%2F7%2F101.jpg",
                            "message_count": "4",
                            "number": "2",
                            "is_published": "1",
                            "create_time": "2017-06-13 09:45:29",
                            "last_modify_time": "2017-06-13 09:45:29"
                        }
                    ],
                    "code": 200,
                    "message": "OK"
                }
                 
         7)查看故事章节消息内容[已完成]
              api:    /chapter-message-content/view?story_id={故事Id}&chapter_id={章节id}
              method: GET
              params:
                     uid:用户uid
                     story_id:故事Id
                     chapter_id:章节id
              example:http://api.youwei.xiaoningmeng.net/chapter-message-content/view?story_id=1&chapter_id=503
              ret:    xml
                      <?xml version="1.0" encoding="utf-8"?>
                      <data>
                          <code>200</code>
                          <message>OK</message>
                          <chapter>
                            <story_id>故事Id</story_id>
                            <!-- 新建上传时: 为空-->
                            <chapter_id>章节Id</chapter_id>
                            <chapter_message_content>
                                <message>
                                  <!-- 新建上传时: 为空-->
                                  <message_id>消息id</message_id>
                                  <!-- 新建上传时：为空 -->
                                  <number>消息序号</number>
                                  <voice_over>旁白</voice_over>
                                  <actor>
                                      <actor_id>角色Id</actor_id>
                                  </actor>
                                  <text>消息文字</text>
                                  <img>图片网址</img>
                                  <status>消息状态</status>
                                  <is_loading>是否有加载条</is_loading>
                                </message>
                                <message>
                                  <message_id>消息id</message_id>
                                  <number>消息序号</number>
                                  <voice_over>旁白</voice_over>
                                  <actor>
                                      <actor_id>角色Id</actor_id>
                                  </actor>
                                  <text>消息文字</text>
                                  <img>图片网址</img>
                                  <is_loading>是否有加载条</is_loading>
                                  <status>消息状态</status>
                                </message>
                            </chapter_message_content>
                          </chapter>
                      </data>
 首页                     
         8)首页故事精选[已完成]
         
               api:    /home/index
               method: GET
               params:
                       uid:用户uid
                       page:页码
                       pre_page:每页显示内容数
               example:http://api.youwei.xiaoningmeng.net/home/index?page=1&pre_page=1
               ret:    Json数组
                {
                    "data": {
                        "storyList": [
                            {
                                "story_id": 45,
                                "name": "标题2",
                                "description": "简介1",
                                "cover": "https://a-ssl.duitang.com/uploads/item/201611/15/20161115141534_ePJAM.thumb.700_0.jpeg",
                                "uid": 2,
                                "chapter_count": 0,
                                "message_count": 0,
                                "taps": "0",****
                                "is_published": 1,
                                "create_time": "2017-06-14 12:32:29",
                                "last_modify_time": "2017-06-16 17:53:06",
                                "actor": [
                                    {
                                        "actor_id": 56,
                                        "name": "角色姓名-1",
                                        "avatar": "",
                                        "number": 1,
                                        "is_visible": 1
                                    },
                                    {
                                        "actor_id": 57,
                                        "name": "角色姓名-2",
                                        "avatar": "",
                                        "number": 2,
                                        "is_visible": 1
                                    }
                                ],
                                "tag": [
                                    {
                                        "tag_id": 1,
                                        "name": "言情",
                                        "number": 1
                                    },
                                    {
                                        "tag_id": 2,
                                        "name": "悬疑",
                                        "number": 2
                                    }
                                ]
                            }
                        ],
                        "totalCount": 25,
                        "pageCount": 25,
                        "currentPage": 1,
                        "perPage": 1
                    },
                    "code": 200,
                    "msg": "OK"
                }
 阅读记录                           
         9)获取阅读记录[已完成]
              api:    /user-read-story-record/index
              method: GET
              params:
                      uid:用户uid
                      access-token:登录用户授权凭证
                      page:页码
                      time:阅读记录最后更新时间
                      per_page:每页显示内容数
              example:http://api.youwei.xiaoningmeng.net/user-read-story-record/index?uid=1&access-token=x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX&time=0&page=1&per_page=20
              ret:    Json数组
                        {
                            "data": {
                                "totalCount": 5,
                                "pageCount": 5,
                                "currentPage": 1,
                                "perPage": 1,
                                "storyList": [
                                    {
                                        "story_id": 11,
                                        "name": "狂探",
                                        "description": "一个打架不要命,无节操无底线的小痞子，意外穿越到平行空间，摇身变成了一名重案组探员。",
                                        "cover": "http://qidian.qpic.cn/qdbimg/349573/1005392714/180",
                                        "chapter_count": 2,
                                        "message_count": 36,
                                        "taps": "22",
                                        "is_published": 1,
                                        "story_create_time": "2017-06-13 10:32:45",
                                        "story_last_modify_time": "2017-06-13 10:32:45",
                                        "last_chapter_id": 1,
                                        "last_message_line_number": 1,
                                        "create_time": "2017-06-16 16:16:35",
                                        "last_modify_time": "2017-06-16 16:16:35",
                                        "user": {
                                            "uid": "1",
                                            "username": "小逗",
                                            "avatar": "http://p5.gexing.com/GSF/touxiang/20170615/17/4jcoh44l7zlt5e0vszuj1aawv.jpg@!200x200_3?recache=20131108",
                                            "signature": "这是签名"
                                        }
                                    }
                                ]
                            },
                            "code": 200,
                            "message": "OK"
                        }
                        
         10)提交阅读记录更改(新增,修改,删除)[已完成]
               api:    /user-read-story-record/batch-process?uid=x&access-token=y
               method: POST
               params:
                      read_story_records[0][story_id]:故事id
                      read_story_records[0][last_chapter_id]:最后阅读章节id
                      read_story_records[0][last_message_line_number]:最后阅读的消息行号
                      read_story_records[0][status]:状态
                      read_story_records[0][create_time]:创建时间
                      read_story_records[0][last_modify_time]:修改时间
                      read_story_records[1][story_id]:故事id
                      read_story_records[1][last_chapter_id]:最后阅读章节id
                      read_story_records[1][last_message_line_number]:最后阅读的消息序号
                      read_story_records[1][status]:状态
                      read_story_records[1][create_time]:创建时间
                      read_story_records[1][last_modify_time]:修改时间
               ret:    Json数组
                             {
                                 "code": 200,
                                 "data": {
                                            [
                                                {
                                                    "story_id": "故事id",
                                                    "last_chapter_id": "最后阅读章节id",
                                                    "last_chapter_number": "最后阅读章节序号",
                                                    "last_message_id": "最后阅读的消息id",
                                                    "last_message_number": "最后阅读的消息序号",
                                                    "last_modify_time": "最后修改时间"
                                                },
                                                {
                                                    "story_id": "故事id",
                                                    "last_chapter_id": "最后阅读章节id",
                                                    "last_chapter_number": "最后阅读章节序号",
                                                    "last_message_id": "最后阅读的消息id",
                                                    "last_message_number": "最后阅读的消息序号",
                                                    "last_modify_time": "最后修改时间"
                                                }
                                            ]
                                         },
                                 },
                                 "msg": "OK"
                             }
                             
         11)获取阅读记录中的图书更新信息[已完成]   
               api:    user-read-story-record/stories-update
               method: GET
               params:
                       uid:用户uid
                       story_ids:以逗号(半角)分隔的故事id列表
                       access-token:登录用户授权凭证
               example:http://api.youwei.xiaoningmeng.net/user-read-story-record/stories-update?uid=1&access-token=x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX&story_ids=1,2,3,4,5,6,13
               ret:    Json数组
                         {
                             "code": 200,
                             "message": "OK",
                             "data": {
                                 "storyList": [
                                     {
                                         "story_id": "13",
                                         "name": "标题1",
                                         "description": "简介1",
                                         "cover": "https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1498623858198&di=e426cc2579548eb715f4ec73f7eb47b2&imgtype=0&src=http%3A%2F%2Fimg.newyx.net%2Fphoto%2F201603%2F10%2F88e994f6a8.jpg",
                                         "chapter_count": "0",
                                         "message_count": "0",
                                         "taps": "0",
                                         "is_published": "1",
                                         "story_update_time": "2017-07-26 11:42:15",
                                         "last_chapter_id": "1",
                                         "last_message_id": "1",
                                         "last_message_number": "1",
                                         "create_time": "2017-06-16 08:28:33",
                                         "last_modify_time": "2017-06-16 08:26:30",
                                         "user": []
                                     },
                                     {
                                         "story_id": "2",
                                         "name": "大盗贼",
                                         "description": "没炒过股，没买过彩票，官道商场一窍不通，陆离发现自己唯一能做的就是玩游戏。",
                                         "cover": "http://youwei-pic.oss-cn-shanghai.aliyuncs.com/cover/2017/07/02/0_1498987493.jpg",
                                         "chapter_count": "10",
                                         "message_count": "222",
                                         "taps": "14",
                                         "is_published": "1",
                                         "story_update_time": "2017-07-11 19:11:24",
                                         "last_chapter_id": "1",
                                         "last_message_id": "1",
                                         "last_message_number": "1",
                                         "create_time": "2017-06-16 14:05:16",
                                         "last_modify_time": "2017-06-16 14:05:16",
                                         "user": []
                                     }
                                 ]
                             }
                         }
 搜索
         12)搜索故事[已完成]
               api:    search/stories
               method: GET
               params:
                       uid:用户uid
                       keyword:关键字
                       page:页码
                       per_page:每页显示内容数
               example:http://api.youwei.xiaoningmeng.net/search/stories?keyword=%E5%A4%A7&page=1&per_page=20
               ret:    Json数组
                       {
                           "data": {
                               "totalCount": 5,
                               "pageCount": 1,
                               "currentPage": "1",
                               "perPage": "20",
                               "storyList": [
                                   {
                                       "story_id": "9",
                                       "name": "我是<em>大</em>科学家",
                                       "description": "有人说：周兴是现代科学之父.,未来科学之祖. 周兴笑着摇了摇头:“不,叫我大科学家就好,因为我只是一位大科学家.”",
                                       "cover": "http://qidian.qpic.cn/qdbimg/349573/1007090965/180",
                                       "uid": "1",
                                       "chapter_count": "2",
                                       "message_count": "36",
                                       "taps": "22",
                                       "is_published": "1",
                                       "status": "1",
                                       "create_time": "1497320453000",
                                       "last_modify_time": "1497320453000"
                                   },
                                   {
                                       "story_id": "8",
                                       "name": "红楼大官人",
                                       "description": "没什么可以把薛蟠打倒的，除了……红楼世界里的美女们！",
                                       "cover": "http://qidian.qpic.cn/qdbimg/349573/1006466556/180",
                                       "uid": "1",
                                       "chapter_count": "2",
                                       "message_count": "36",
                                       "taps": "22",
                                       "is_published": "1",
                                       "status": "1",
                                       "create_time": "1497320372000",
                                       "last_modify_time": "1497321439000"
                                   },
                                   {
                                       "story_id": "2",
                                       "name": "大盗贼",
                                       "description": "没炒过股，没买过彩票，官道商场一窍不通，陆离发现自己唯一能做的就是玩游戏。",
                                       "cover": "http://qidian.qpic.cn/qdbimg/349573/3434900/180",
                                       "uid": "2",
                                       "chapter_count": "11",
                                       "message_count": "222",
                                       "taps": "14",
                                       "is_published": "1",
                                       "status": "1",
                                       "create_time": "1497318101000",
                                       "last_modify_time": "1497319778000"
                                   },
                                   {
                                       "story_id": "10",
                                       "name": "圣墟",
                                       "description": "沧海成尘，雷电枯竭，那一缕幽雾又一次临近大地，世间的枷锁被打开了，一个全新的世界就此揭开神秘的一角",
                                       "cover": "http://qidian.qpic.cn/qdbimg/349573/1004608738/180",
                                       "uid": "1",
                                       "chapter_count": "2",
                                       "message_count": "36",
                                       "taps": "22",
                                       "is_published": "1",
                                       "status": "1",
                                       "create_time": "1497321093000",
                                       "last_modify_time": "1497321411000"
                                   },
                                   {
                                       "story_id": "5",
                                       "name": "悟空看私聊",
                                       "description": "说起来你可能不信，郭大路的山寨手机被一道闪电劈中之后，微信好友里莫名其妙地多了一个名叫“孙悟空”的人。",
                                       "cover": "http://qidian.qpic.cn/qdbimg/349573/1004600274/180",
                                       "uid": "2",
                                       "chapter_count": "2",
                                       "message_count": "56",
                                       "taps": "12",
                                       "is_published": "1",
                                       "status": "1",
                                       "create_time": "1497320119000",
                                       "last_modify_time": "1497320119000"
                                   }
                               ]
                           },
                           "code": 200,
                           "msg": "OK"
                       }
                       
               备注：<em>关键字</em>:em是飘红的字的标签
                             
         13)搜索用户[已完成]
               api:    search/users
               method: GET
               params:
                       uid:用户uid
                       keyword:关键字
                       page:页码
                       per_page:每页显示内容数
               example:http://api.youwei.xiaoningmeng.net/search/users?keyword=小&page=1&per_page=5
               ret:    Json数组
                        {
                            "data": {
                                "totalCount": 1,
                                "pageCount": 1,
                                "currentPage": "1",
                                "perPage": "5",
                                "userList": [
                                    {
                                        "uid": "2",
                                        "username": "小爱",
                                        "avatar": "http://p5.gexing.com/GSF/touxiang/20170615/17/4jcoh44l7zlt5e0vszuj1aawv.jpg@!200x200_3?recache=20131108",
                                        "signature": "也是签名",
                                        "status": "1",
                                        "create_time": "1497658790000",
                                        "last_modify_time": "1497593052000"
                                    }
                                ]
                            },
                            "code": 200,
                            "msg": "OK"
                        }
                        
 标签
         14)标签列表[已完成]
               api:    /tag/index
               method: GET
               params:
                       uid:用户uid
               example:http://api.youwei.xiaoningmeng.net/tag/index     
               ret:    Json数组
                        {
                            "data": [
                                {
                                    "tag_id": "1",
                                    "number": "1",
                                    "name": "言情"
                                },
                                {
                                    "tag_id": "2",
                                    "number": "2",
                                    "name": "悬疑"
                                },
                                {
                                    "tag_id": "3",
                                    "number": "3",
                                    "name": "搞笑"
                                }
                            ],
                            "code": 200,
                            "msg": "OK"
                        }
               
         15)标签下的故事列表[已完成]
               api:    /tag/storys
               method: GET
               params:
                       uid:用户uid
                       tag_id:关键字
                       page:页码
                       pre_page:每页显示内容数
               example:http://api.youwei.xiaoningmeng.net/tag/storys?tag_id=1&page=1&pre_page=1
               ret:    Json数组
                        {
                            "data": {
                                "storyList": [
                                    {
                                        "story_id": 45,
                                        "name": "标题2",
                                        "description": "简介1",
                                        "cover": "https://a-ssl.duitang.com/uploads/item/201611/15/20161115141534_ePJAM.thumb.700_0.jpeg",
                                        "uid": 2,
                                        "chapter_count": 0,
                                        "message_count": 0,
                                        "taps": "0",
                                        "is_published": 1,
                                        "create_time": "2017-06-14 12:32:29",
                                        "last_modify_time": "2017-06-16 17:53:06",
                                        "tag": [
                                            {
                                                "tag_id": 1,
                                                "name": "言情",
                                                "number": 1
                                            }
                                        ],
                                        "actor": [
                                            {
                                                "actor_id": "56",
                                                "name": "角色姓名-1",
                                                "avatar": "",
                                                "number": "1"
                                            },
                                            {
                                                "actor_id": "57",
                                                "name": "角色姓名-2",
                                                "avatar": "",
                                                "number": "2"
                                            }
                                        ]
                                    }
                                ],
                                "totalCount": 15,
                                "pageCount": 15,
                                "currentPage": 1,
                                "perPage": 1
                            },
                            "code": 200,
                            "msg": "OK"
                        }
                        
         16)获取oss Token[已完成]
                api:    /sts/token
                method: GET
                params:
                example:http://api.youwei.xiaoningmeng.net/sts/token
                ret:    Json
                {
                    "code": 200,
                    "msg": "OK",
                    "data": {
                        "AccessKeyId": "STS.GXBYqvZaDhDBRwrwK3TsUe8bA",
                        "AccessKeySecret": "GZZS5o2GZg7geQopb1e5qBnYsVWHNGKbc78VZwVwrse7",
                        "Expiration": "2017-06-20T09:20:20Z",
                        "SecurityToken": "CAISogN1q6Ft5B2yfSjIppv3EsvCt75l34apUFHDk0tmWPx5iv3Jozz2IH1MenZvBO0Ztvk+nW5X6voYlqJ4T55IQ1Dza8J148yHZd5vx8mT1fau5Jko1bcrcAr6Umxzta2/SuH9S8ynkJ7PD3nPii50x5bjaDymRCbLGJaViJlhHNZ1Ow6jdmhpCctxLAlvo9N4UHzKLqSVLwLNiGjdB1YKwg1nkjFT5KCy3sC74BjTh0GYr+gOvNbVI4O4V8B2IIwdI9Cux75ffK3bzAtN7wRL7K5skJFc/TDOsrP6BEJKsTGHKPbz+N9iJxNiHJJYfZRJt//hj/Z1l/XOnoDssXZ3MPpSTj7USfL+ornNE/j7Mc0iJ/SpeSaB+O2kFbTJhCUNRF9cdmE6ctE6eHhrEk5uGHOIZoWa03PnXjCFYo2o8tlviMAtlgW5oYDVfAXUGurJ60tCZM9gNXFPHgUNwGnsfpUBdwFxaF59D96XN94uNE4B9v614lWPB3Q+kS0L5eeNbvfXq70Zbp7kQpVF3IwSaZJLqWI2SE7tTLajmvXwhOPr8CA/GoABJfEMsR+RRiF9dWq0AweADBkUtsB805+kAFRKrBOR0cBRU9heojRytXaYSIaBAYvvgZfk6tylcdqVuvw1T8ZE4iDdF+zPp5V5HIyNGS9BkXu1ZKOG/WLfGoBy/GVPLJwMdJyyAKBxQ6ODParsSseMBzwM0AsjzENP2zPv1wHSX8A="
                    }
                }
                备注:各个字段说明如下：
                    status:表示获取Token的状态，获取成功时，返回值是200。
                    AccessKeyId: 表示Android/iOS应用初始化OSSClient获取的 AccessKeyId。
                    AccessKeySecret: 表示Android/iOS应用初始化OSSClient获取AccessKeySecret。
                    SecurityToken：表示Android/iOS应用初始化的Token。
                    Expiration: 表示该Token失效的时间。主要在Android SDK会自动判断是否失效，自动获取Token。注意上述这四个变量将构成了一个Token。
                参考文档：https://help.aliyun.com/document_detail/31920.html?spm=5176.product31815.6.623.KYJRp1
                
         17)获取消息的评论(投票)数据[已完成]
                api:    /comment/message-votes
                method: GET
                params:
                       uid:用户uid
                       message_id:消息id
                example:api.youwei.xiaoningmeng.net/comment/message-votes?message_id=1
                ret:    Json
                        {
                            "data": [
                                {
                                    "content": 1,
                                    "count": 1
                                },
                                {
                                    "content": 2,
                                    "count": 1
                                },
                                {
                                    "content": 3,
                                    "count": 0
                                },
                                {
                                    "content": 4,
                                    "count": 0
                                }
                            ],
                            "code": 200,
                            "msg": "OK"
                        }
         18)获取故事的评论(投票)汇总数据[已完成]
                api:    /comment/story-votes
                method: GET
                params:
                       uid:用户uid
                       story_id:故事id
                example:api.youwei.xiaoningmeng.net/comment/story-votes?story_id=1
                ret:Json
                    {
                        "data": [
                            {
                                "content": 1,
                                "count": 1
                            },
                            {
                                "content": 2,
                                "count": 1
                            },
                            {
                                "content": 3,
                                "count": 1
                            },
                            {
                                "content": 4,
                                "count": 0
                            }
                        ],
                        "code": 200,
                        "msg": "OK"
                    }

         19)提交消息的评论(投票)数据[已完成]
                api:    /comment/vote-commit?uid=x&access-token=y
                method: POST
                params:
                       message_id:消息id
                       content:内容(枚举值：1，2，3，4)
                example:api.youwei.xiaoningmeng.net/comment/vote-commit
                ret:    Json
                    {
                        "data": {
                            "comment_id": 6
                        },
                        "code": 200,
                        "msg": "OK"
                    }
         
         20)获取故事的评论内容[已完成]
                api:    /comment/index
                method: GET
                params:
                       uid:用户uid
                       story_id:故事id
                       page:页码
                       pre_page:每页显示内容数
                example:api.youwei.xiaoningmeng.net/comment/index?story_id=1&page=1&pre_page=10
                ret:    Json
                    {
                        "data": {
                            "commentList": {
                                "new": [
                                    {
                                        "comment_id": "1",
                                        "parent_comment_id": "0",
                                        "target_id": "1",
                                        "target_type": "1",
                                        "content": "范德萨发生",
                                        "target_uid": "0",
                                        "like_count": "6",
                                        "create_time": "2017-06-30 15:31:32",
                                        "last_modify_time": "2017-08-07 16:30:37",
                                        "owner_uid": "1",
                                        "owner_username": "小逗",
                                        "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.0.1242.1242.180/9cd38661ly8fgu30jmtwqj20yi0yin04.jpg",
                                        "owner_signature": "这是签名",
                                        "is_like": 1
                                    },
                                    {
                                        "comment_id": "3",
                                        "parent_comment_id": "1",
                                        "target_id": "1",
                                        "target_type": "1",
                                        "content": "回复111",
                                        "target_uid": "1",
                                        "like_count": "667",
                                        "create_time": "2017-06-30 15:32:23",
                                        "last_modify_time": "2017-08-05 18:41:20",
                                        "owner_uid": "3",
                                        "owner_username": "小的",
                                        "owner_avatar": "http://tvax4.sinaimg.cn/crop.0.0.511.511.50/c878e9c2ly8fgyhcinw0tj20e80e7myf.jpg",
                                        "owner_signature": "也是签名",
                                        "parent": {
                                            "comment_id": "1",
                                            "parent_comment_id": "0",
                                            "target_id": "1",
                                            "target_type": "1",
                                            "content": "范德萨发生",
                                            "target_uid": "0",
                                            "like_count": "6",
                                            "create_time": "2017-06-30 15:31:32",
                                            "last_modify_time": "2017-08-07 16:30:37",
                                            "status": "1",
                                            "owner_uid": "1",
                                            "owner_username": "小逗",
                                            "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.0.1242.1242.180/9cd38661ly8fgu30jmtwqj20yi0yin04.jpg",
                                            "owner_signature": "这是签名"
                                        },
                                        "is_like": 0
                                    },
                                    {
                                        "comment_id": "4",
                                        "parent_comment_id": "0",
                                        "target_id": "1",
                                        "target_type": "1",
                                        "content": "sadfasdf",
                                        "target_uid": "0",
                                        "like_count": "1",
                                        "create_time": "2017-06-30 16:34:36",
                                        "last_modify_time": "2017-08-05 18:50:44",
                                        "owner_uid": "4",
                                        "owner_username": "小都",
                                        "owner_avatar": "http://tva1.sinaimg.cn/crop.0.3.315.315.50/006ASU3qjw8f8o5gpwmqnj308r0943yl.jpg",
                                        "owner_signature": "也是签名",
                                        "is_like": 0
                                    },
                                    {
                                        "comment_id": "10",
                                        "parent_comment_id": "5",
                                        "target_id": "1",
                                        "target_type": "1",
                                        "content": "斤斤计较军军",
                                        "target_uid": "0",
                                        "like_count": "1",
                                        "create_time": "2017-07-01 10:44:29",
                                        "last_modify_time": "2017-08-05 18:50:46",
                                        "owner_uid": "2",
                                        "owner_username": "小爱",
                                        "owner_avatar": "http://tvax1.sinaimg.cn/crop.74.0.302.302.50/6a2dec21ly8ff6ujky4rrj20c808et9j.jpg",
                                        "owner_signature": "也是签名",
                                        "parent": {
                                            "status": "0"
                                        },
                                        "is_like": 0
                                    },
                                    {
                                        "comment_id": "13",
                                        "parent_comment_id": "1",
                                        "target_id": "1",
                                        "target_type": "1",
                                        "content": "就；拉空间登录；安居房；的",
                                        "target_uid": "0",
                                        "like_count": "1",
                                        "create_time": "2017-07-04 18:32:25",
                                        "last_modify_time": "2017-08-05 18:50:49",
                                        "owner_uid": "1",
                                        "owner_username": "小逗",
                                        "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.0.1242.1242.180/9cd38661ly8fgu30jmtwqj20yi0yin04.jpg",
                                        "owner_signature": "这是签名",
                                        "parent": {
                                            "comment_id": "1",
                                            "parent_comment_id": "0",
                                            "target_id": "1",
                                            "target_type": "1",
                                            "content": "范德萨发生",
                                            "target_uid": "0",
                                            "like_count": "6",
                                            "create_time": "2017-06-30 15:31:32",
                                            "last_modify_time": "2017-08-07 16:30:37",
                                            "status": "1",
                                            "owner_uid": "1",
                                            "owner_username": "小逗",
                                            "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.0.1242.1242.180/9cd38661ly8fgu30jmtwqj20yi0yin04.jpg",
                                            "owner_signature": "这是签名"
                                        },
                                        "is_like": 0
                                    },
                                    {
                                        "comment_id": null,
                                        "parent_comment_id": null,
                                        "target_id": null,
                                        "target_type": null,
                                        "content": null,
                                        "target_uid": null,
                                        "like_count": null,
                                        "create_time": null,
                                        "last_modify_time": null,
                                        "owner_uid": null,
                                        "owner_username": null,
                                        "owner_avatar": null,
                                        "owner_signature": null,
                                        "is_like": 0
                                    },
                                    {
                                        "comment_id": null,
                                        "parent_comment_id": null,
                                        "target_id": null,
                                        "target_type": null,
                                        "content": null,
                                        "target_uid": null,
                                        "like_count": null,
                                        "create_time": null,
                                        "last_modify_time": null,
                                        "owner_uid": null,
                                        "owner_username": null,
                                        "owner_avatar": null,
                                        "owner_signature": null,
                                        "is_like": 0
                                    },
                                    {
                                        "comment_id": "27",
                                        "parent_comment_id": "0",
                                        "target_id": "1",
                                        "target_type": "1",
                                        "content": "3699",
                                        "target_uid": "0",
                                        "like_count": "1",
                                        "create_time": "2017-08-05 17:58:59",
                                        "last_modify_time": "2017-08-05 18:51:01",
                                        "owner_uid": "1",
                                        "owner_username": "小逗",
                                        "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.0.1242.1242.180/9cd38661ly8fgu30jmtwqj20yi0yin04.jpg",
                                        "owner_signature": "这是签名",
                                        "is_like": 0
                                    },
                                    {
                                        "comment_id": "28",
                                        "parent_comment_id": "0",
                                        "target_id": "1",
                                        "target_type": "1",
                                        "content": "36999999",
                                        "target_uid": "0",
                                        "like_count": "1",
                                        "create_time": "2017-08-05 18:09:33",
                                        "last_modify_time": "2017-08-05 18:51:03",
                                        "owner_uid": "1",
                                        "owner_username": "小逗",
                                        "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.0.1242.1242.180/9cd38661ly8fgu30jmtwqj20yi0yin04.jpg",
                                        "owner_signature": "这是签名",
                                        "is_like": 0
                                    },
                                    {
                                        "comment_id": "29",
                                        "parent_comment_id": "28",
                                        "target_id": "1",
                                        "target_type": "1",
                                        "content": "952777777",
                                        "target_uid": "1",
                                        "like_count": "1",
                                        "create_time": "2017-08-05 18:12:19",
                                        "last_modify_time": "2017-08-05 18:51:05",
                                        "owner_uid": "1",
                                        "owner_username": "小逗",
                                        "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.0.1242.1242.180/9cd38661ly8fgu30jmtwqj20yi0yin04.jpg",
                                        "owner_signature": "这是签名",
                                        "parent": {
                                            "comment_id": "28",
                                            "parent_comment_id": "0",
                                            "target_id": "1",
                                            "target_type": "1",
                                            "content": "36999999",
                                            "target_uid": "0",
                                            "like_count": "1",
                                            "create_time": "2017-08-05 18:09:33",
                                            "last_modify_time": "2017-08-05 18:51:03",
                                            "status": "1",
                                            "owner_uid": "1",
                                            "owner_username": "小逗",
                                            "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.0.1242.1242.180/9cd38661ly8fgu30jmtwqj20yi0yin04.jpg",
                                            "owner_signature": "这是签名"
                                        },
                                        "is_like": 0
                                    }
                                ],
                                "hot": [
                                    {
                                        "comment_id": "41",
                                        "parent_comment_id": "0",
                                        "target_id": "1",
                                        "target_type": "1",
                                        "content": "36999999",
                                        "target_uid": "0",
                                        "like_count": "1",
                                        "create_time": "2017-08-05 18:52:33",
                                        "last_modify_time": "2017-08-05 18:52:40",
                                        "owner_uid": "1",
                                        "owner_username": "小逗",
                                        "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.0.1242.1242.180/9cd38661ly8fgu30jmtwqj20yi0yin04.jpg",
                                        "owner_signature": "这是签名",
                                        "is_like": 0
                                    },
                                    {
                                        "comment_id": "13",
                                        "parent_comment_id": "1",
                                        "target_id": "1",
                                        "target_type": "1",
                                        "content": "就；拉空间登录；安居房；的",
                                        "target_uid": "0",
                                        "like_count": "1",
                                        "create_time": "2017-07-04 18:32:25",
                                        "last_modify_time": "2017-08-05 18:50:49",
                                        "owner_uid": "1",
                                        "owner_username": "小逗",
                                        "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.0.1242.1242.180/9cd38661ly8fgu30jmtwqj20yi0yin04.jpg",
                                        "owner_signature": "这是签名",
                                        "parent": {
                                            "comment_id": "1",
                                            "parent_comment_id": "0",
                                            "target_id": "1",
                                            "target_type": "1",
                                            "content": "范德萨发生",
                                            "target_uid": "0",
                                            "like_count": "6",
                                            "create_time": "2017-06-30 15:31:32",
                                            "last_modify_time": "2017-08-07 16:30:37",
                                            "status": "1",
                                            "owner_uid": "1",
                                            "owner_username": "小逗",
                                            "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.0.1242.1242.180/9cd38661ly8fgu30jmtwqj20yi0yin04.jpg",
                                            "owner_signature": "这是签名"
                                        },
                                        "is_like": 0
                                    },
                                    {
                                        "comment_id": "4",
                                        "parent_comment_id": "0",
                                        "target_id": "1",
                                        "target_type": "1",
                                        "content": "sadfasdf",
                                        "target_uid": "0",
                                        "like_count": "1",
                                        "create_time": "2017-06-30 16:34:36",
                                        "last_modify_time": "2017-08-05 18:50:44",
                                        "owner_uid": "4",
                                        "owner_username": "小都",
                                        "owner_avatar": "http://tva1.sinaimg.cn/crop.0.3.315.315.50/006ASU3qjw8f8o5gpwmqnj308r0943yl.jpg",
                                        "owner_signature": "也是签名",
                                        "is_like": 0
                                    }
                                ]
                            },
                            "totalCount": "27",
                            "currentPage": "1",
                            "perPage": "10",
                            "pageCount": 1
                        },
                        "code": 200,
                        "msg": "OK"
                    }
                备注：hot:热门评论,new:最新评论,parent:父级评论,status:parent节点下面的status=0,表示父级评论被删除，is_like:表示用户是否赞过改评论,
                
         21)提交故事的评论数据[已完成]
                api:    /comment/commit?uid=x&access-token=y
                method: POST
                params:
                       story_id:故事id
                       content:评论文字内容
                       parent_comment_id:父级评论id
                example:http://api.youwei.xiaoningmeng.net/comment/commit
                ret:    Json
                        {
                            "data": [
                                {
                                    "comment_id": "21",
                                    "parent_comment_id": "2",
                                    "target_id": "2",
                                    "target_type": "1",
                                    "content": "回复评论内容blabla",
                                    "target_uid": "2",
                                    "like_count": "0",
                                    "create_time": "2017-08-05 14:43:25",
                                    "last_modify_time": "2017-08-05 14:43:25",
                                    "owner_uid": "5",
                                    "owner_username": "小就",
                                    "owner_avatar": "http://tvax1.sinaimg.cn/crop.0.25.1242.1242.50/7024b5f6gy8fh4atn4dpej20yi0zxdib.jpg",
                                    "owner_signature": "也是签名",
                                    "parent": {
                                        "comment_id": "2",
                                        "parent_comment_id": "0",
                                        "target_id": "2",
                                        "target_type": "1",
                                        "content": "哦哦范德萨发",
                                        "target_uid": "0",
                                        "like_count": "3",
                                        "create_time": "2017-06-30 15:31:59",
                                        "last_modify_time": "2017-08-02 15:15:17",
                                        "status": "1",
                                        "owner_uid": "2",
                                        "owner_username": "小爱",
                                        "owner_avatar": "http://tvax1.sinaimg.cn/crop.74.0.302.302.50/6a2dec21ly8ff6ujky4rrj20c808et9j.jpg",
                                        "owner_signature": "也是签名"
                                    }
                                }
                            ],
                            "code": 200,
                            "msg": "OK"
                        }
         
         22)评论点赞[已完成]
                 api:    /like/comment-like
                 method: get
                 params:
                        comment_id:评论id
                        uid:用户uid
                        access-token:登录用户授权凭证
                 example:http://api.youwei.xiaoningmeng.net/like/comment-like?comment_id=1&uid=1&access-token=x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX
                 ret:    Json
                     {
                         "data": [],
                         "code": 200,
                         "msg": "OK"
                     }      
                
         23)取消评论点赞[已完成]
             api:    /like/comment-dislike
             method: get
             params:
                    comment_id:评论id
                    uid:用户uid
                    access-token:登录用户授权凭证
             example:http://api.youwei.xiaoningmeng.net/like/comment-dislike?comment_id=1&uid=1&access-token=x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX
             ret:    Json
                 {
                     "data": [],
                     "code": 200,
                     "msg": "OK"
                 }
         
         24）获取用户通知列表[已完成]
             api:    /notify/index
             method: get
             params:
                    uid:用户uid
                    access-token:登录用户授权凭证
                    page:页码
                    pre_page:每页显示内容数
             example:
                    http://api.youwei.xiaoningmeng.net/notify/index?uid=4&page=1&pre_page=20&access-token=x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX
                    http://api.youwei.xiaoningmeng.net/notify/index?uid=2&page=1&pre_page=20&access-token=x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX
             ret:   Json
                    {
                        "code": 200,
                        "msg": "OK",
                        "data": {
                            "notifyList": [
                                [
                                    {
                                        "id": "130",
                                        "category": "like_reply",
                                        "senders": [
                                            {
                                                "uid": "10",
                                                "username": "巴巴",
                                                "avatar": "http://tvax4.sinaimg.cn/crop.0.0.996.996.50/e9b22e9ely8fgkkj3oiojj20ro0rptbl.jpg"
                                            },
                                            {
                                                "uid": "11",
                                                "username": "亮亮",
                                                "avatar": "http://tvax4.sinaimg.cn/crop.0.0.996.996.50/e9b22e9ely8fgkkj3oiojj20ro0rptbl.jpg"
                                            },
                                            {
                                                "uid": "8",
                                                "username": "三少",
                                                "avatar": "http://tvax4.sinaimg.cn/crop.0.0.996.996.50/e9b22e9ely8fgkkj3oiojj20ro0rptbl.jpg"
                                            },
                                            {
                                                "uid": "9",
                                                "username": "大大兵",
                                                "avatar": "http://youwei-avatar.oss-cn-shanghai.aliyuncs.com/user/9"
                                            }
                                        ],
                                        "count": 5,
                                        "comment_id": "18",
                                        "comment_content": "回复评论内容blabla",
                                        "create_time": "2017-08-02 18:17:02"
                                    },
                                    {
                                        "id": "60",
                                        "category": "post_story",
                                        "senders": [
                                            {
                                                "uid": "2",
                                                "username": "小爱",
                                                "avatar": "http://tvax1.sinaimg.cn/crop.74.0.302.302.50/6a2dec21ly8ff6ujky4rrj20c808et9j.jpg"
                                            }
                                        ],
                                        "count": 1,
                                        "story_id": "2",
                                        "story_name": "大盗贼",
                                        "story_cover": "http://youwei-pic.oss-cn-shanghai.aliyuncs.com/cover/2017/07/02/0_1498987493.jpg",
                                        "create_time": "2017-08-02 15:22:19"
                                    },
                                    {
                                        "id": "4",
                                        "category": "post_chapter",
                                        "senders": [
                                            {
                                                "uid": "4",
                                                "username": "小爱",
                                                "avatar": "http://tvax1.sinaimg.cn/crop.74.0.302.302.50/6a2dec21ly8ff6ujky4rrj20c808et9j.jpg"
                                            }
                                        ],
                                        "count": 1,
                                        "story_id": "2",
                                        "story_name": "大盗贼",
                                        "story_cover": "http://youwei-pic.oss-cn-shanghai.aliyuncs.com/cover/2017/07/02/0_1498987493.jpg",
                                        "chapter_id": "1",
                                        "chapter_name": "第1章",
                                        "create_time": "2017-08-02 15:17:56"
                                    }
                                ]
                            ],
                            "totalCount": 3,
                            "pageCount": 1,
                            "currentPage": 1,
                            "perPage": 20
                        }
                    }
                    
         25）发送手机验证码[已完成]
             api:    sms/send-sms
             method: get
             params:
                    mobilePhone:手机号码
             example:
                    http://api.youwei.xiaoningmeng.net/sms/send-sms?mobilePhone=15339188014
             ret:   Json
                    
                    //失败
                    {
                        "data": {
                            "RequestId": "F4A54AD6-B593-482F-951E-0C214DCE4FFA",
                            "BizId": null
                        },
                        "code": "isv.BUSINESS_LIMIT_CONTROL",
                        "msg": "触发业务级流控限制"
                    }
                    
                    //成功
                    {
                        "data": {
                            "RequestId": "C862746D-5B72-43CB-ADBB-BB99B1F3952D",
                            "BizId": "593409302510526665^0"
                        },
                        "code": "OK",
                        "msg": "OK"
                    }
                    
                    
             备注：RequestId：请求ID，BizId：发送回执ID,可根据该ID查询具体的发送状态，code：短信发送状态运营商返回的错误码,
             触发业务级流控限制: 是指对同一个手机号码发送短信验证码，支持1条/分钟，5条/小时 ，累计10条/天。(发送短信太频繁了)
             短信接口调用错误码文档地址：https://help.aliyun.com/knowledge_detail/57717.html?spm=5176.doc55451.6.576.WT9y6G
          
         26）查询手机验证码发送状态[已完成]
             api:    sms/send-sms
             method: get
             params:
                    mobilePhone:手机号码
                    bizId:发送回执ID
             example:
                    http://api.youwei.xiaoningmeng.net/sms/query-send-details?mobilePhone=15339188014&bizId=539300602507735533^0
             ret:   Json
                    {
                        "code": "OK",
                        "msg": "OK",
                        "data": {
                            "SendDate": "2017-08-12 11:15:35",
                            "SendStatus": 3,
                            "ReceiveDate": "2017-08-12 11:15:41",
                            "ErrCode": "DELIVERED",
                            "TemplateCode": "SMS_82705009",
                            "Content": "【有味读书】您的验证码是1234。请在10分钟内按照页面中的提示提交验证码完成验证。",
                            "PhoneNum": "15339188014"
                        }
                    }
                    
           备注：code = OK 且 SendStatus=3, 且 ErrCode = DELIVERED 表示短信已发送成功
            
            
         26) 手机号注册[已完成]
             api:    user/signup
             method: get
             params:
                    mobilePhone:手机号码
                    password:密码
             example:
                    http://api.youwei.xiaoningmeng.net/user/signup?mobilePhone=18600024911&password=123456
             ret:   Json
                    //失败
                    {
                        "data": [],
                        "code": 400,
                        "msg": "手机号已经被使用"
                    }
                    //成功
                    {
                        "data": {
                            "uid": 10152,
                            "username": "用户_3832",
                            "mobile_phone": "18600024925",
                            "email": null,
                            "avatar": null,
                            "signature": null,
                            "access_token": "3j4I6i9p_bjOMnR13VoAIGxbMxEr6rtO",
                            "status": 1,
                            "register_ip": "127.0.0.1",
                            "register_time": "2017-08-12 19:39:04",
                            "last_login_ip": "127.0.0.1",
                            "last_login_time": "2017-08-12 19:39:04",
                            "last_modify_time": "2017-08-12 19:39:04"
                        },
                        "code": 200,
                        "msg": "OK"
                    }
                    
         27) 修改用户名[已完成]
             api:    user/update-avatar
             method: get
             params:
                    access-token:登录用户授权访问凭证
                    avatar:用户新头像地址
             example:
                    http://api.youwei.xiaoningmeng.net/user/update-username?uid=10148&username=vvvvvv&access-token=x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX
                    http://api.youwei.xiaoningmeng.net/user/update-username?uid=10148&username=12&access-token=x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX
             ret:   Json
                    
                    //失败
                    {
                        "data": [],
                        "code": 400,
                        "msg": "姓名长度限制 4-24个字符"
                    }
                
                    //成功
                    {
                        "data": {
                            "uid": 10148,
                            "username": "vvvvvv",
                            "mobile_phone": "18600024914",
                            "email": null,
                            "avatar": "http://wx1.sinaimg.cn/mw690/4deda15fly1fig4zy0kj0j22kw3vde87.jpg",
                            "signature": "444444444",
                            "access_token": "x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX",
                            "status": 1,
                            "register_ip": "",
                            "register_time": "2017-08-12 18:42:46",
                            "last_login_ip": "",
                            "last_login_time": null,
                            "last_modify_time": "2017-08-12 19:27:46"
                        },
                        "code": 200,
                        "msg": "OK"
                    }
                    备注：用户名长度限制：4-24个字符
         
         28）修改用户头像[已完成]
             api:    user/update-avatar
             method: get
             params:
                    access-token:登录用户授权访问凭证
                    avatar:用户新头像地址
             example:
                    http://api.youwei.xiaoningmeng.net/user/update-avatar?uid=10148&avatar=http://wx1.sinaimg.cn/mw690/4deda15fly1fig4zy0kj0j22kw3vde87.jpg&access-token=x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX
             ret:   Json
             
                   //失败
                   {
                       "data": [],
                       "code": 400,
                       "msg": "头像地址不是一个合法的url"
                   }
                 
                   //成功
                   {
                       "data": {
                           "uid": 10148,
                           "username": "用户_8321",
                           "mobile_phone": "18600024914",
                           "email": null,
                           "avatar": "http://wx1.sinaimg.cn/mw690/4deda15fly1fig4zy0kj0j22kw3vde87.jpg",
                           "signature": "444444444",
                           "access_token": "x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX",
                           "status": 1,
                           "register_ip": "",
                           "register_time": "2017-08-12 18:42:46",
                           "last_login_ip": "",
                           "last_login_time": null,
                           "last_modify_time": "2017-08-12 19:26:02"
                       },
                       "code": 200,
                       "msg": "OK"
                   }
                   
         29）修改个性签名[已完成]
             api:    user/update-signature
             method: get
             params:
                    access-token:登录用户授权访问凭证
                    avatar:用户新头像地址
             example:
                    http://api.youwei.xiaoningmeng.net/user/update-signature?uid=10148&signature=bbbbbbbbbbb&access-token=x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX
             ret:   Json
                  
                    //成功
                    {
                        "data": {
                            "uid": 10148,
                            "username": "用户_8321",
                            "mobile_phone": "18600024914",
                            "email": null,
                            "avatar": null,
                            "signature": "444444444",
                            "access_token": "x8YVH0VzifPGp5PkHCsZ2ufmVENFWbsX",
                            "status": 1,
                            "register_ip": "",
                            "register_time": "2017-08-12 18:42:46",
                            "last_login_ip": "",
                            "last_login_time": null,
                            "last_modify_time": "2017-08-12 19:13:03"
                        },
                        "code": 200,
                        "msg": "OK"
                    }
                    
                    备注：个性签名长度限制 最多100个字符
         
         30）获取他人已发布的故事信息[已完成]
          api:    user/others-storys
          method: GET
          params:
                 uid:用户uid
                 page:页码
                 pre_page:每页显示内容数
          ret:    Json数组
          example:http://api.youwei.xiaoningmeng.net/user/others-storys?uid=2&page=1&pre_page=1
          {
              "data": {
                  "storyList": [
                      {
                          "story_id": 2,
                          "name": "大盗贼",
                          "description": "没炒过股，没买过彩票，官道商场一窍不通，陆离发现自己唯一能做的就是玩游戏。",
                          "cover": "http://youwei-pic.oss-cn-shanghai.aliyuncs.com/cover/2017/07/02/0_1498987493.jpg",
                          "uid": 2,
                          "chapter_count": 10,
                          "message_count": 222,
                          "taps": "14",
                          "is_published": 1,
                          "create_time": "2017-06-13 09:41:41",
                          "last_modify_time": "2017-08-08 14:34:07",
                          "actor": [],
                          "tag": [
                              {
                                  "tag_id": 1,
                                  "name": "言情",
                                  "number": 1
                              },
                              {
                                  "tag_id": 2,
                                  "name": "悬疑",
                                  "number": 2
                              },
                              {
                                  "tag_id": 3,
                                  "name": "搞笑",
                                  "number": 3
                              }
                          ]
                      }
                  ],
                  "totalCount": 4,
                  "pageCount": 4,
                  "currentPage": 1,
                  "perPage": 1
              },
              "code": 200,
              "msg": "OK"
          }
          
        31) 获取他人用户信息[已完成]
         api:    user/others-info
         method: GET
         params:
               uid:用户uid
         ret:    Json数组
         example:
               http://api.youwei.xiaoningmeng.net/user/others-info?uid=2
               http://api.youwei.xiaoningmeng.net/user/others-info?uid=20000
         //正确
         {
             "data": {
                 "uid": 2,
                 "username": "小爱",
                 "avatar": "http://tvax1.sinaimg.cn/crop.74.0.302.302.50/6a2dec21ly8ff6ujky4rrj20c808et9j.jpg",
                 "signature": "也是签名",
                 "taps": "0",
                 "status": 1
             },
             "code": 200,
             "msg": "OK"
         }
         
         //错误
         {
             "data": [],
             "code": 400,
             "msg": "用户不存在"
         }
         

            
         28) 手机号密码登录
            

         
 ```
 
 
 
     
