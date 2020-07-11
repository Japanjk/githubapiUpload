# github Api
演示图：https://common-fd.zol-img.com.cn/g5/M00/01/03/ChMkJ18Jd66ITy50AAIKJ0l2h5EAAw1GgOxJSEAAgo_098.jpg
## 使用案例



```php
<?php
require './githubApi.php';
ignore_user_abort(true);
error_reporting(0);
set_time_limit(0);

$git = [
    'user'=>'', #用户名
    'repo'=>'', #仓库名
    'token'=>'', #token
    'mail'=>'', #邮箱
    'repos'=>'master', #分支名
    'timezones'=>'Asia/Shanghai', #时区
    'gitapi'=>"api.github.com", #github api  网上有很多镜像api 例如 api.git.sdut.me
];

echo (GithubUp::getInstance($git)->validate());

```

**提交方式：Post**

**请求参数 file:[file]**





