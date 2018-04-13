<?php
/*
 * User: keke
 * Date: 2018/3/19
 * Time: 23:50
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

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

/**
 * Http请求类
 * Class Http
 */
class Http
{
    /*
     * http请求推送token参数
     */

    //请求地址
    private $get_curl = 'https://login.cloud.huawei.com/oauth2/v2/token';

    public function __construct($client_secret = null, $client_id = null, $get_curl = null)
    {
        if ($client_secret) $this->client_secret = $client_secret;
        if ($client_id) $this->client_id = $client_id;
        $this->get_curl = $get_curl;
    }

    public function GetToken()
    {
        return $this->PushCurl();
    }

    //curl请求token
    public function PushCurl()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://login.cloud.huawei.com/oauth2/v2/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials&client_secret=' . $this->client_secret . '&client_id=' . $this->client_id,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
                "host: Login.cloud.huawei.com"
            ),
        ));

        $response = curl_exec($curl);
        //获取http状态码
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_error($curl);
        curl_close($curl);

        if ($status !== 200) {
            $return_array = json_decode($response, true);
            if ($return_array) {
                $error_message = '请求错误:';
                if (isset($return_array['error']))
                    $error_message .= $return_array['error'];
                if (isset($return_array['error_description']))
                    $error_message .= ' ' . $return_array['error_description'];
            } else {
                $error_message = '请求错误!';
            }
            //接口调用失败或无响应
            $message = [
                'msg' => $error_message,
                'code' => '412'
            ];
            return $message;
//            throw new NeteaseError($error_message, $status);

        }

        return self::ErrorCode(json_decode($response, true));
    }

    public function ErrorCode($res_arr)
    {
        if (isset($res_arr['error'])) {
            // 如果返回了error则证明失败
            $error_code = $res_arr['error'];      // 错误码
            switch ($error_code) {
                case 1101:
                    $message = '请求非法';
                    break;
                case 1102:
                    $message = '缺少必须的参数';
                    break;
                case 1104:
                    $message = '不支持的Response Type';
                    break;
                case 1105:
                    $message = '不支持的Grant Type';
                    break;
                case 1107:
                    $message = '用户或授权服务器拒绝授予数据访问权限';
                    break;
                case 1201:
                    $message = '非法的ticket';
                    break;
                case 1202:
                    $message = '非法的sso_st';
                    break;
                default:
                    $message = '失败(返回其他状态，目前不清楚额，请联系开发人员！)';
                    break;
            }
        } else {
//            echo '<pre />';
//            var_dump($res_arr);
//            echo '<pre />';
            //去除转义的字符
            $token = str_replace("\\", "", $res_arr['access_token']);
            //token写入缓存中
            Cache::put(self::CACHE_NAME, $token, (int)($res_arr['expires_in'] / 60));

            $message = [
                'msg' => $token,
                'code' => '200'
            ];
        }
        return $message;
    }
}