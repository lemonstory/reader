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
    
     1)获取用户发布的故事
     
        api:    /story/userStoryList
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
                    
     2)创建故事[支持批量]
     
        api:    /story/create
        method: POST
        params:
                uid:用户uid
                story[]['local_story_id']:本地故事id
                story[]['story_id']:故事id
                story[]['name']:标题
                story[]['description']:简介
                story[]['cover']:封面
                story[]['is_published']:是否发布
                story[]['actor']:故事角色信息
                story[]['tag']:故事标签信息
                story[]['chapter_count']:章节总数量
                story[]['message_count']:消息总数量
                story[]['taps']:点击数
                story[]['create_time']:创建时间
                story[]['last_modify_time']:最后修改时间
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
                    
     (选择封面,标题，描述; 选择标签; 点击发布; 设置角色不可见) 
     3)修改,删除故事[支持批量]
      
         api:    /story/update
         method: POST
         params:
                 uid:用户uid
                 story[]['local_story_id']:本地故事id
                 story[]['story_id']:故事id
                 story[]['name']:标题
                 story[]['description']:简介
                 story[]['cover']:封面
                 story[]['is_published']:是否发布
                 story[]['actor']:故事角色信息
                 story[]['tag']:故事标签信息
                 story[]['chapter_count']:章节总数量
                 story[]['message_count']:消息总数量
                 story[]['taps']:点击数
                 story[]['create_time']:创建时间
                 story[]['last_modify_time']:最后修改时间
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
                     
     4)新建,修改,删除章节消息内容
     
          api:    /chapter/upload
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
                      
     5)查看故事基本信息
         api:    /story/view/{故事Id}
         method: GET
         params:
                uid:用户uid
                story_id:故事Id
         ret:    Json
            {
                "code": 200,
                "data": {
                    "chapter_count": 0,
                    "cover": null,
                    "create_time": "2017-05-23 09:50:38",
                    "description": null,
                    "last_modify_time": null,
                    "message_count": 0,
                    "name": "aaa",
                    "status": 2,
                    "story_id": 1,
                    "actor": [
                        {
                            "actor_id": "1",
                            "name": "张三",
                            "avatar": "角色",
                            "number": "序号",
                            "is_visible": "是否可见"
                        },
                        {
                            "actor_id": "2",
                            "name": "赵四",
                            "avatar": "角色",
                            "number": "序号",
                            "is_visible": "是否可见"
                        }
                    ],
                    "tag": [
                        {
                            "name": "悬疑",
                            "tag_id": "1"
                        },
                        {
                            "name": "恐怖",
                            "tag_id": "2"
                        }
                    ],
                    "uid": 1,
                    "taps": "0"
                },
                "msg": "OK"
            }
         6)查看故事章节
               api:    /chapter/view/{故事Id}
               method: GET
               params:
                      uid:用户uid
                      (story_id)故事Id
               ret:    Json数组
                       {
                           "code": 200,
                           "data": {
                               "chapter_info":[
                                   {
                                       "chapter_id": "章节id",
                                       "name": "章节名称(保留)",
                                       "story_id": "故事id",
                                       "background": "背景图",
                                       "message_count": "消息数量",
                                       "number": "序号",
                                       "create_time": "创建时间",
                                       "last_modify_time": "最后修改时间"
                                   },
                                   {
                                       "chapter_id": "章节id",
                                       "name": "章节名称(保留)",
                                       "story_id": "故事id",
                                       "background": "背景图",
                                       "message_count": "消息数量",
                                       "number": "序号",
                                       "create_time": "创建时间",
                                       "last_modify_time": "最后修改时间"
                                   }
                               ],
                               "chapter_read_record": {
                                       "story_id": "故事id",
                                       "last_chapter_id": "最后阅读章节id",
                                       "last_chapter_number": "最后阅读章节序号",
                                       "last_message_id": "最后阅读的消息id",
                                       "last_message_number": "最后阅读的消息序号",
                                       "last_modify_time": "最后修改时间"
                               }
                           },
                           "msg": "OK"
                       }
                 
         7)查看故事章节消息内容
              api:    /chapter/view/{故事Id}
              method: GET
              params:
                     uid:用户uid
                     story_id:故事Id
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
         9)阅读记录列表
              api:    /readStoryRecord/index
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
                                "read_story_record_list": [
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
                            "msg": "OK"
                        }
         10)提交阅读记录更改(新增,修改,删除)
               api:    /readStoryRecord/index
               method: POST
               params:
                       uid:用户uid
                       read_story_record[]['story_id']:故事id
                       read_story_record[]['last_chapter_id']:最后阅读章节id
                       read_story_record[]['last_chapter_number']:最后阅读章节序号
                       read_story_record[]['last_message_id']:最后阅读的消息id
                       read_story_record[]['last_message_number']:最后阅读的消息序号
                       read_story_record[]['status']:状态
                       read_story_record[]['create_time']:创建时间
                       read_story_record[]['last_modify_time']:修改时间
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
 
 
 
     
