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
    
     1)获取用户发布的故事[已完成]
     
        api:    /user/storys
        method: GET
        params:
                uid:用户uid
                page:页码
                pre_page:每页显示内容数
        ret:    Json数组
        example:http://api.youwei.xiaoningmeng.net/user/storys?uid=1&page=1&pre_page=1
        
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
     
        api:    /story/batch-create
        method: POST
        params:
                uid:用户uid
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
                    
     
     (已同步的故事:选择封面,标题，描述; 选择标签; 点击发布; 设置角色不可见..等等)    
     3)修改,删除故事[支持批量][已完成]
      
         api:    /story/batch-update
         method: POST
         params:
                 uid:用户uid
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
                    "code": 200,
                    "data": [
                        {
                            "local_story_id": "本地故事id",
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
                            "local_story_id": "本地故事id",
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
                        }
                    ],
                    "msg": "OK"
                }
            备注:    
                1.  code=200:全部修改成功;code=206:部分修改成功
                2.  actor:故事角色的Json串(见上面示例代码)
                3.  tag:故事标签的Json串(见上面示例代码)
                     
     4)新建,修改,删除章节消息内容[已完成]
     
          api:    /chapter/upload-message-content
          method: POST(multipart/form-data)
          params:
                  uid:用户uid
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
                        "last_modify_time": "最后修改时间",
                        "message_content": "消息内容(markdown文本)"
                    },
                    "msg": "OK"
                }
                      
     5)查看故事基本信息[已完成]
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
                    "uid": 1,
                    "chapter_count": 1,
                    "message_count": 21,
                    "taps": "33",
                    "is_published": 0,
                    "status": 1,
                    "create_time": "2017-06-13 09:37:28",
                    "last_modify_time": "2017-06-13 10:09:09",
                    "actor": [
                        {
                            "actor_id": "1",
                            "name": "帅帅",
                            "avator": "http://p5.gexing.com/GSF/touxiang/20170612/17/1onxfg31j60996l23ku7jdlyv.jpg@!200x200_3?recache=20131108",
                            "number": "1"
                        },
                        {
                            "actor_id": "2",
                            "name": "昭昭",
                            "avator": "http://p5.gexing.com/GSF/touxiang/20170612/17/17y0gn7l1s62rca89urd0o3bx.jpg@!200x200_3?recache=20131108",
                            "number": "2"
                        },
                        {
                            "actor_id": "3",
                            "name": "乐乐",
                            "avator": "http://p5.gexing.com/GSF/touxiang/20170610/02/gmykod8dfr9xm2f99wdcefdg.jpg@!200x200_3?recache=20131108",
                            "number": "3"
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
                    ]
                },
                "code": 200,
                "message": "OK"
            }
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
              example:http://api.youwei.xiaoningmeng.net/chapter-message-content/view?story_id=1&chapter_id=19
              ret:    Json
                      {
                          "code": 200,
                          "data": {
                                      "story_id":"故事id"
                                      "chapter_id": "章节id",
                                      "message_content": "章节内容",
                                  },
                          },
                          "msg": "OK"
                      }
 首页                     
         8)首页故事精选
         
               api:    /home/index
               method: GET
               params:
                       uid:用户uid
                       page:页码
                       page_size:每页显示内容数
               ret:    Json数组
                      {
                           "code": 200,
                           "data": {
                               "page": "页码",
                               "page_size": "每页显示内容数",
                               "story_list": [
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
                                       "tag": "故事标签信息",
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
 阅读记录                           
         9)阅读记录列表[已完成]
              api:    /user-read-story-record/index
              method: GET
              params:
                      uid:用户uid
                      page:页码
                      page_size:每页显示内容数
              example:http://api.youwei.xiaoningmeng.net/user-read-story-record/index?uid=1&page=20&per_page=1
              ret:    Json数组
                        {
                            "data": [
                                {
                                    "story_id": "1",
                                    "name": "超级怪兽工厂",
                                    "description": "叶不非是不幸的，不幸的是他被老板随手抡起的一本破书砸晕了头。",
                                    "cover": "http://qidian.qpic.cn/qdbimg/349573/1002959239/180",
                                    "chapter_count": "1",
                                    "message_count": "21",
                                    "taps": "33",
                                    "is_published": "0",
                                    "story_create_time": "2017-06-13 09:37:28",
                                    "story_last_modify_time": "2017-06-13 10:09:09",
                                    "last_chapter_id": "1",
                                    "last_message_id": "1",
                                    "create_time": "2017-06-16 11:24:34",
                                    "last_modify_time": "2017-06-16 11:24:34",
                                    "user": {
                                        "uid": "1",
                                        "name": "小逗",
                                        "avatar": "http://p5.gexing.com/GSF/touxiang/20170615/17/4jcoh44l7zlt5e0vszuj1aawv.jpg@!200x200_3?recache=20131108",
                                        "signature": "这是签名"
                                    }
                                }
                            ],
                            "code": 200,
                            "message": "OK"
                        }
                        
         10)提交阅读记录更改(新增,修改,删除)[已完成]
               api:    /user-read-story-record/batch-process
               method: POST
               params:
                      uid:用户uid
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
 搜索
         11)关键字搜索
               api:    /search/index
               method: GET
               params:
                       uid:用户uid
                       keyword:关键字
                       type:1(故事),2(用户)
                       page:页码
                       page_size:每页显示内容数
               ret:    Json数组
                       搜索故事:
                       {
                            "code": 200,
                            "data": {
                                "page": "页码",
                                "page_size": "每页显示内容数",
                                "story_list": [
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
                                        "tag": "故事标签信息",
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
                             
                       搜索用户:
                       {
                            "code": 200,
                            "data": {
                                "page": "页码",
                                "page_size": "每页显示内容数",
                                "user_list": [
                                    {
                                        "uid": "用户uid",
                                        "name": "用户姓名",
                                        "avatar": "头像",
                                        "signature": "个性签名",
                                        "followed_count": "用户被关注(粉丝)总数量",
                                        "story_count": "故事总数量"
                                    },
                                    {
                                        "uid": "用户uid",
                                        "name": "用户姓名",
                                        "avatar": "头像",
                                        "signature": "个性签名",
                                        "followed_count": "用户被关注(粉丝)总数量",
                                        "story_count": "故事总数量"
                                    }
                                ]
                            },
                            "msg": "OK"
                       }
 标签
         12)标签列表
               api:    /tag/index
               method: GET
               params:
                       uid:用户uid
               ret:    Json数组
                       {
                           "code": 200,
                           "data": [
                               {
                                   "name": "悬疑",
                                   "tag_id": "1"
                               },
                               {
                                   "name": "恐怖",
                                   "tag_id": "2"
                               }
                           ],
                           "msg": "OK"
                       }
               
         13)标签下的故事列表
               api:    /tag/storyList
               method: GET
               params:
                       uid:用户uid
                       tag_id:关键字
                       page:页码
                       page_size:每页显示内容数
               ret:    Json数组
                       搜索故事:
                        {
                            "code": 200,
                            "data": {
                                "page": "页码",
                                "page_size": "每页显示内容数",
                                "story_list": [
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
                                        "tag": "故事标签信息",
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

 ```
 
 
 
     
