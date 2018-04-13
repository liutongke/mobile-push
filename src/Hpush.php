<?php
/*
 * User: keke
 * Date: 2018/3/20
 * Time: 9:38
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

class Hpush
{
    /*
     * device_token_list判断是否是数组还是字符串更加傻瓜化
     * 对于华为服务器的返回值
     */

    // 缓存的名称
    const CACHE_NAME = 'huawei_push';

    private $data = [];

    // 若实例化的时候传入相应的值，则按新的相应值进行配置推送
    public function __construct($client_secret = null, $client_id = null)
    {
        if ($client_secret) $this->client_secret = $client_secret;
        if ($client_id) $this->client_id = $client_id;
    }

    //
    public function setMsg($type, $body)
    {
//        取值含义和说明：
//
//        1 透传异步消息
//
//        3 系统通知栏异步消息
        $this->data['hps']['msg'] = [
            'type' => (int)$type,
            'body' => $body
        ];

        return $this;
    }

    public function setAction($type, $param)
    {
//        1 自定义行为：行为由参数intent定义
//
//        2 打开URL：URL地址由参数url定义
//
//        3 打开APP：默认值，打开App的首页
//
//        注意：富媒体消息开放API不支持

        if ($type == '1') {
            $this->data['hps']['action'] = [
                'type' => (int)$type,
                'param' => [
                    'intent' => $param
                ]
            ];
        } elseif ($type == '2') {
            $this->data['hps']['action'] = [
                'type' => (int)$type,
                'param' => [
                    'url' => $param
                ]
            ];
        } else {
            $this->data['hps']['action'] = [
                'type' => (int)$type,
                'param' => [
                    'appPkgName' => $param
                ]
            ];
        }

        return $this;
    }

    //设置扩展信息
    public function setExt($Trump, $ext)
    {
        if (empty($ext)) {
            $this->data['hps']['ext'] = [
                'biTag' => $Trump
            ];
        } else {
            $this->data['hps']['ext'] = [
                'biTag' => $Trump,
                'customize' => [$ext]
            ];
        }

        return $this;
    }

    //进行推送请求
    public function send_huawei_push($device_token)
    {
        //检查是否是数组
        if (is_array($device_token)) {
            $device_token_list = json_encode($device_token);
        } else {
            $device_token_list = json_encode([
                $device_token
            ]);
        }

        //token值
//        $huawei_token = Redis::get('huawei_push_token');
//        \需要去除
        $huawei_token = 'CFrC7eMGeKzMLOaoTMbqz3AyieUG7N/tzQLdmhvqCzpDMe/xKbew88oRWNrQW0phMRlJFfWlYWRISMQ12zC9qQ==';
//        $payload = json_encode($this->data);

        //token需要urlencode编码
        if ($huawei_token) {
            return self::huawei_curl(urlencode($huawei_token), $device_token_list, json_encode($this->data));
        } else {
//            $huawei_token = $this->GetHuaweiToken();
            return self::huawei_curl(urlencode($this->GetHuaweiToken()), $device_token_list, json_encode($this->data));
        }
    }

    //curl请求
    public function huawei_curl($token, $device_token_list, $payload)
    {
        //其中nsp_ctx为url-encoding编码，解码后为： nsp_ctx={"ver":"1", "appId":"10923253325"}
//        ver：用来解决大版本升级的兼容问题;
//        appId：用户在联盟申请的APPID;
        $nsp_ctx = json_encode([
            'ver' => '1',
            'appId' => $this->client_id
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.push.hicloud.com/pushsend.do?nsp_ctx=' . urlencode($nsp_ctx),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => 'access_token=' . $token . '&nsp_svc=openpush.message.api.send&nsp_ts=' . time() . '&device_token_list=' . $device_token_list . '&payload=' . $payload,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
//            echo "cURL Error #:" . $err;
            return self::errorHandling($err);
        } else {
//            echo $response;
            return self::errorHandling($response);
        }
    }

    //错误的处理
    public function errorHandling($msg)
    {
        $msg = json_decode($msg, true);

        if (isset($msg['code'])) {
            switch ((int)$msg['code']) {
                case 80000000:
                    $message = [
                        'status_code' => '200',
                        'msg' => "发送成功！"
                    ];

                    break;
                case 80000003:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "终端不在线！"
                    ];

                    break;
                case 80000004:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "应用已卸载！"
                    ];

                    break;
                case 80000005:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "响应超时！"
                    ];

                    break;
                case 80000006:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "无路由，终端未连接过push！"
                    ];

                    break;
                case 80000007:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "终端在其他大区，不在中国大陆使用push！"
                    ];

                    break;
                case 80000008:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "路由不正确，可能终端切换push服务器！"
                    ];

                    break;
                case 80100000:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "参数检查，部分参数错误，正确token已下发！"
                    ];

                    break;
                case 80100002:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "不合法的token列表！"
                    ];

                    break;

                case 80100003:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "不合法的payload！"
                    ];

                    break;

                case 80100004:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "不合法的超时时间！"
                    ];

                    break;
                case 80300002:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "无权限下发消息给参数中的token列表！"
                    ];

                    break;
                case 81000001:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => "内部错误！"
                    ];

                    break;
                default:
                    $message = [
                        'status_code' => $msg['code'],
                        'msg' => $msg['msg']
                    ];

                    break;
            }
        } else {
            //接口调用失败或无响应
            $message = [
                'status_code' => $msg['code'],
                'msg' => '接口调用失败或无响应'
            ];
        }

        return $message;
    }

    /***********************   token操作   **********************************/

    /**
     * 返回token
     */
    public function getToken()
    {
        $ht = new Http($this->client_secret, $this->client_id);
        $return = $ht->GetToken();

        if (Cache::has(self::CACHE_NAME)) {
            return Cache::get(self::CACHE_NAME);
        } else {
            //引入文件
            $ht = new Http($this->client_secret, $this->client_id);
            $return = $ht->GetToken();
            Cache::put(self::CACHE_NAME, $return['access_token'], (int)($return['expires_in'] / 60));

            return $return['access_token'];

        }
    }
}