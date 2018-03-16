<?php
/*
 * User: keke
 * Date: 2018/3/16
 * Time: 10:43
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

namespace HuaweiPush\src;

use Illuminate\Support\Facades\Redis;

/**
 * Http请求类
 * Class Http
 */
class Http
{
    /**
     * http请求推送token参数
     *
     * @param        $url
     * @param        $option
     * @param int $header
     * @param string $type
     * @param int $setopt
     * @param bool $is_json [返回数据是否是json 下载文件的时候用到]
     *
     * @return mixed
     * @throws EasemobError
     */

    //申请应用时获得的应用密钥
    public $client_secret;

    //申请应用时获得的应用ID
    public $client_id;

    //请求地址
    private $get_curl = 'https://login.cloud.huawei.com/oauth2/v2/token';

    public function __construct($client_secret, $client_id, $get_curl)
    {
//        if ($client_secret) $this->client_secret = $client_secret;
//        if ($client_id) $this->client_id = $client_id;

        $this->client_secret = Config::get('huawei_push.client_secret');
        $this->client_id = Config::get('huawei_push.client_id');
        $this->get_curl = $get_curl;
    }

    public function GetToken()
    {
        $result = $this->PushCurl();
        if ($result) {
            $res_arr = json_decode($result, true);
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
                //token写入redis中
                Redis::set('huawei_push_token', $res_arr['access_token']);
                Redis::expire('huawei_push_token', $res_arr['expires_in']);
                $message = $res_arr['access_token'];
//                $message = "发送成功！";
            }
        } else {      //接口调用失败或无响应
            $message = '接口调用失败或无响应';
        }

        return $message;
    }

    //curl请求token
    public function PushCurl()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->get_curl,
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
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
}