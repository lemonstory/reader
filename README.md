use Yii 2 Advanced Project Template


DIRECTORY STRUCTURE
-------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
api
    assets/              contains application assets such as JavaScript and CSS
    config/              contains api configurations
    controllers/         contains Web controller classes
    models/              contains api-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for api application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```
参考资料：
    http://www.yii-china.com/post/detail/457.html
    http://www.yiiframework.com/wiki/760/yii-2-0-write-use-a-custom-component-in-yii2-0-advanced-template/
    https://github.com/PizzaLiu/PHP-FIG/blob/master/PSR-1-basic-coding-standard-cn.md
    http://docs-aliyun.cn-hangzhou.oss.aliyun-inc.com/pdf/opensearch-best-practice-cn-zh-2016-05-12.pdf
    http://cdn.s.aliyun.com/L1/272/7933/opensearch/css/search/guide.pdf
    https://adminlte.io/themes/AdminLTE/