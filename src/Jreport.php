<?php

/*
 * User: keke
 * Date: 2018/4/9
 * Time: 11:40
 *——————————————————佛祖保佑 ——————————————————
 *                   _ooOoo_
 *                  o8888888o
 *                  88" . "88
 *                  (| -_- |)
 *                  O\  =  /O
 *               ____/`---'\____
 *             .'  \|     |//  `.
 *            /  \|||  :  |||//  \
 *           /  _||||| -:- |||||-  \
 *           |   | \\  -  /// |   |
 *           | \_|  ''\---/''  |   |
 *           \  .-\__  `-`  ___/-. /
 *         ___`. .'  /--.--\  `. . __
 *      ."" '<  `.___\_<|>_/___.'  >'"".
 *     | | :  ` - `.;`\ _ /`;.`/ - ` : | |
 *     \  \ `-.   \_ __\ /__ _/   .-` /  /
 *======`-.____`-.___\_____/___.-`____.-'======
 *                   `=---='
 *——————————————————代码永无BUG —————————————————
 */

namespace mobile\push;

class Jreport
{
    private $data = [];

    // 若实例化的时候传入相应的值，则按新的相应值进行配置推送
    public function __construct($app_key = null, $master_secret = null, $url = null)
    {
        if ($app_key) $this->app_key = $app_key;
        if ($master_secret) $this->master_secret = $master_secret;
        if ($url) $this->url = $url;
    }

    //需要report的网址
    public function receivedUrl()
    {
        $this->data['url'] = 'https://report.jpush.cn/v3/received?msg_ids=';

        return $this;
    }

    //送达状态查询的url
    public function statusMsgUrl()
    {
        $this->data['url'] = 'https://report.jpush.cn/v3/status/message';

        return $this;
    }
    //送达统计的msg_id
    /*
     * android_received Android 送达。如果无此项数据则为 null。
ios_apns_sent iOS 通知推送到 APNs 成功。如果无此项数据则为 null。
ios_apns_received iOS 通知送达到设备。如果无项数据则为 null。统计该项请参考 集成指南高级功能 。
ios_msg_received iOS 自定义消息送达数。如果无此项数据则为null。
wp_mpns_sent winphone通知送达。如果无此项数据则为 null。
     */
    public function received($msg_ids)
    {
        $this->data['msg_ids'] = implode(',', $msg_ids);
        return $this;
    }

    //发送消息
    public function send()
    {
        $base64 = base64_encode("$this->app_key:$this->master_secret");

        $header = array("Authorization:Basic $base64", "Content-Type:application/json");

        $res = $this->report_curl($this->data, $header);
        $message = [
            'msg' => '发送成功',
            'code' => '200',
            'data' => json_decode($res, 1),
        ];
        return $message;
    }

    public function report_curl($param, $header)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $param['url'] . $param['msg_ids'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($curl);
        curl_error($curl);

        curl_close($curl);

        return $response;
    }
}