####接口文档

```
名称解释：
    故事(story)       类似小说,书籍的概念.
    章节(chapter)     故事的章节
    消息(message)     故事内容的最小单位,各个章节内里面的内容.
    角色(actor)       故事的角色(类似影视剧里面的角色)
    分类(category)    故事的类别
    评论(commint)     故事内容的评论,可评价一个故事(或)故事的一个章节(或)故事的一个章节的一条消息

```

```
域名：
    PATH             http://api.reader.xiaoningmeng.net
```

```
故事
    创建故事
        api:    story/create
        method: POST
        params:
                name:标题
                description:简介
                uid:用户(作者)uid
                cover:封面图
                
        ret:    Json
         
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
```

    
