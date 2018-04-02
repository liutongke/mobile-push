<?php
//require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Jpush.php';
use huawei\push\Client;
use huawei\push\Http;

//$client = new Client();
//echo $client::world();

//应用id
//define("HUAWEI_PUSH_CLIENT_SECRET", "");
//appid
//define("HUAWEI_PUSH_CLIENT_ID", "");
//$http = new Http();
//echo $http->GetToken();
//echo $http->GetToken();
$to_arr = '科科很帅。';
//                发送的人地址 要发送的数据 发送的设备 要更改的表的id字段
//角标数量
$badge = '1';
$jpush = new \Jpush('aed8819fcc431a8909f8b0e8', '82d9b8d8c756b3737af2cb7c');
/*
 * $to_comment_id 推送的地址， 精准推送的话用别名 ，全局广播用“all”
 * $to_arr 推送的内容
 * $badge 角标数量，iOS必须传递否则报错
 *$to_user->os 区分系统 android ios
 */
$to_comment_id = '324';
$jpush->send_pub($to_comment_id, $to_arr, $to_arr, $badge, 'android');