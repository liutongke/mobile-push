 #极光推送使用规则
Installation<br />
使用 Composer 安装<br />
在项目中的 composer.json 文件中添加依赖：<br />
  "require": {<br />
    "mobile/push": "dev-master"<br />
  },<br />
执行 $ composer update 进行安装。<br />

引入 use huawei\push\Jpush;<br />
$app_key = '***';<br />
$master_secret = '***';<br />
$jpush = new Jpush($app_key, $master_secret);<br />
#通知别名推送(安卓)<br />
$jpush->setPlatform('android')<br />
    //设置别名<br />
    ->addAlias($alias)<br />
    //推送的消息体,安卓调用androidNotification，iOS调用iosNotification<br />
    ->androidNotification(<br />
        [<br />
            //这里指定了，则会覆盖上级统一指定的 alert 信息；内容可以为空字符串，则表示不展示到通知栏。<br />
            "alert" => '山有木兮木有枝',<br />
            //这里自定义 JSON 格式的 Key/Value 信息，以供业务使用<br />
            'extras' => [<br />
                'content' => '心悦君兮君不知',<br />
                "badge" => (int)1,<br />
            ]<br />
        ])<br />
    ->send();<br />
    
#通知别名推送(iOS)<br />
$jpush->setPlatform('ios')<br />
    //设置别名<br />
    ->addAlias($alias)<br />
    //推送的消息体,安卓调用androidNotification，iOS调用iosNotification<br />
    ->iosNotification(<br />
        [<br />
            //这里指定了，则会覆盖上级统一指定的 alert 信息；内容可以为空字符串，则表示不展示到通知栏。<br />
            "alert" => '山有木兮木有枝',<br />
            //这里自定义 JSON 格式的 Key/Value 信息，以供业务使用<br />
            'extras' => [<br />
                'content' => '心悦君兮君不知',<br />
                "badge" => (int)1,<br />
            ]<br />
        ])<br />
    ->send();<br />
    
#通知广播推送<br />
$jpush->setPlatform('all')<br />
            ->addAllAudience()<br />
            ->allNotification([<br />
                //这里指定了，则会覆盖上级统一指定的 alert 信息；内容可以为空字符串，则表示不展示到通知栏。<br />
                "alert" => '病起萧萧两鬓华',<br />
                //这里自定义 JSON 格式的 Key/Value 信息，以供业务使用<br />
                'android' => [<br />
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