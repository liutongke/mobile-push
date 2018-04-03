#集成了华为推送和极光推送 
#极光推送使用规则
Installation
使用 Composer 安装
在项目中的 composer.json 文件中添加依赖：
  "require": {
    "huawei/push": "dev-master"
  },
执行 $ composer update 进行安装。

引入 use huawei\push\Jpush;
$app_key = '***';
$master_secret = '***';
$jpush = new Jpush($app_key, $master_secret);

//安卓别名推送
//设置推送平台
//'android'
//'ios'
//['android' , 'ios']
$haha = $jpush->setPlatform('android')
    //设置别名
    ->addAlias('325')
    //推送的消息体,安卓调用androidNotification，iOS调用iosNotification
    ->androidNotification(
        [
            //这里指定了，则会覆盖上级统一指定的 alert 信息；内容可以为空字符串，则表示不展示到通知栏。
            "alert" => '山有木兮木有枝',
            //这里自定义 JSON 格式的 Key/Value 信息，以供业务使用
            'extras' => [
                'content' => '心悦君兮君不知',
                "badge" => (int)1,
            ]
        ])
    ->send();
//var_dump($haha);
//die;
//广播推送
//设置推送的平台
$haha = $jpush->setPlatform('all')
    //代表是全局广播
    ->addAllAudience()
    //设置安卓和iOS的消息体
    ->allNotification(
        [
            //这里指定了，则会覆盖上级统一指定的 alert 信息；内容可以为空字符串，则表示不展示到通知栏。
            "alert" => '病起萧萧两鬓华',
            //这里自定义 JSON 格式的 Key/Value 信息，以供业务使用
            'android' => [
                'extras' => [
                    'content' => '卧看残月上窗纱',
                    "badge" => (int)1,
                ]
            ],
            'ios' => [
                "badge" => (int)2,
//                    如果无此字段，则此消息无声音提示；有此字段，如果找到了指定的声音就播放该声音，否则播放默认声音,如果此字段为空字符串，iOS 7 为默认声音，iOS 8及以上系统为无声音。(消息) 说明：JPush 官方 API Library (SDK) 会默认填充声音字段。提供另外的方法关闭声音。
                "sound" => "",
                'extras' => [
                    'content' => '豆蔻连梢煎熟水',
                ]
            ],
        ])
    ->send();
#华为推送使用规则

