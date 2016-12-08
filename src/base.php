<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/4 0004
 * Time: 下午 3:42
 */
namespace mmxs\wxcorp;

class base
{
    //成功
    const RETURN_CODE_SUCCESS = 'SUCCESS';
    //失败
    const RETURN_CODE_FAIL = 'FAIL';

    protected $config = [];//微信配置
    protected $corpname;//企业号名称
    protected $corpid; //企业号id
    protected $corpsecret;//企业号secret
    protected $agent_list = [];//应用列表
    protected $is_weixin_browser = false;//是否在微信浏览器内

    /**
     * @var callback
     */
    protected $getCacheCallback;
    /**
     * @var callback
     */
    protected $setCacheCallBack;

    /**
     * @var callback
     */
    protected $logCallback;

    /**
     * WxBase constructor. 构造函数
     *
     * @param string|array $config 公众号名称
     *
     * @throws wxcorpException
     */
    public function __construct($config = '')
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            $this->is_weixin_browser = true;
        }
        $this->config = $config;

        if (empty($this->config)) {
            throw new wxcorpException("微信配置缺失~", 121000);
        }

        if (empty($this->config['corpid'])) {
            throw new wxcorpException("corpid 不能为空~", 'CORPID_EMPTY');
        }

        if (empty($this->config['corpsecret'])) {
            throw new wxcorpException("corpsecret 不能为空~", 121003);
        }

        $this->corpid     = $this->config['corpid'];
        $this->corpsecret = $this->config['corpsecret'];
        $this->corpname   = $this->config['name'] ? $this->config['name'] : '';
        $this->agent_list = $this->config['agent_list'];

        isset($this->config['callback']['getcache']) && $this->getCacheCallback = $this->config['callback']['getcache'];
        isset($this->config['callback']['setcache']) && $this->getCacheCallback = $this->config['callback']['setcache'];
        isset($this->config['callback']['log']) && $this->getCacheCallback = $this->config['callback']['log'];
    }

    /**
     * isWeixinBrowser 是否在微信浏览器中
     *
     * @author chenmingming
     * @return bool
     */
    public function isWeixinBrowser()
    {
        return $this->is_weixin_browser;
    }

    /**
     * sslCurl curl 访问
     *
     * @author chenchao
     *
     * @param string $url     请求地址
     * @param string $vars    post数据
     * @param string $cert    cert
     * @param string $key     key
     * @param string $rootca  rootca
     * @param int    $timeout 超时时间
     * @param null   $chinfo  curl访问句柄
     *
     * @return mixed
     */
    protected function sslCurl($url, $vars, $cert, $key, $rootca, $timeout = 30, &$chinfo = null)
    {
        $ch = curl_init($url);
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //cert 与 key 分别属于两个.pem文件
        //请确保您的libcurl版本是否支持双向认证，版本高于7.20.1
        curl_setopt($ch, CURLOPT_SSLCERT, $cert);
        curl_setopt($ch, CURLOPT_SSLKEY, $key);
        curl_setopt($ch, CURLOPT_CAINFO, $rootca);
        $chinfo = curl_getinfo($ch);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        $data = curl_exec($ch);

        curl_close($ch);

        return $data;
    }

    /**
     * curlPostJson POST json字符串到指定地址
     *
     * @author chenmingming
     *
     * @param string $url  请求地址
     * @param array  $data 请求数据
     *
     * @return string
     */
    protected function curlPostJson($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, 1);
        //一定需要编码,否则接收方会错误

        //不编码中文
        if (PHP_VERSION > '5.4') {
            $datastring = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $datastring = json_encode($data);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datastring);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($datastring),
            ]
        );
        $curl_data = curl_exec($ch);
        curl_close($ch);

        return $curl_data;
    }

    /**
     * curl curl远程连接
     *
     * @author chenmingming
     *
     * @param string $url       curl远程连接
     * @param array  $post_data 远程连接post变量数组
     * @param array  $config    其他配置
     *
     * @return string
     */
    protected function curl($url, $post_data = null, $config = [])
    {
        $this->log($url, 'URL');

        settype($config, 'array');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_ENCODING, true);
        if ($post_data) {
            //一定需要编码,否则接收方会错误
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        }
        foreach ($config as $k => $v) {
            if (is_numeric($k)) {
                curl_setopt($ch, $k, $v);
            }

        }
        $curl_data            = curl_exec($ch);
        $curl_error           = curl_error($ch);
        $chinfo               = curl_getinfo($ch);
        $chinfo['curl_error'] = $curl_error;
        curl_close($ch);

        return $curl_data;
    }

    /**
     * log 日志记录
     *
     * @author chenmingming
     *
     * @param string $message 日志内容
     * @param string $label   label
     *
     * @return void
     */
    protected function log($message, $label = '')
    {
        if (!$this->logCallback || !is_callable($this->logCallback)) {
            return;
        }

        call_user_func($this->logCallback, $message, $label);
    }

    /**
     * @desc   获取缓存
     * @author 陈明明 mailto:838965806@qq.com
     * @since  2015-04-01 11:58:37
     *
     * @param string $key 缓存key
     *
     * @return mixed
     */
    protected function getCache($key)
    {
        if (!$this->getCacheCallback || !is_callable($this->getCacheCallback)) {
            return false;
        }

        return call_user_func($this->getCacheCallback, $key);
    }

    /**
     * @desc   设置缓存
     * @author 陈明明 mailto:838965806@qq.com
     * @since  2015-04-01 11:57:59
     *
     * @param  string $key    缓存key
     * @param  mixed  $data   要缓存的数据
     * @param int     $option 缓存时间
     *
     * @return bool
     */
    protected function setCache($key, $data, $option = 86400)
    {
        if (!$this->setCacheCallBack || !is_callable($this->setCacheCallBack)) {
            return false;
        }

        return call_user_func($this->setCacheCallBack, $key, $data, $option);
    }

    /**
     * getAccessToken 获取全局统一票据
     *
     * @author chenchao
     * @return string
     * @throws wxcorpException
     */
    public function getAccessToken()
    {
        $key   = 'wxcrop_access_token_' . $this->corpname;
        $token = $this->getCache($key);
        if ($token) {
            return $token;
        }
        $request_url   = sprintf("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=%s&corpsecret=%s", $this->corpid, $this->corpsecret);
        $json_response = $this->curl($request_url);
        if (!$json_response) {
            throw new wxcorpException("请求 AccessToken 失败~", 'REQUEST_ACCESSTOKEN_EMPTY');
        }
        $content = json_decode($json_response, true);

        if (isset($content['access_token'])) {
            $this->setCache($key, $content['access_token'], $content['expires_in'] - 600);

            return $content['access_token'];
        }

        throw new wxcorpException("请求 AccessToken json字符串 异常~" . $json_response, 'REQUEST_ACCESSTOKEN_FAILED');
    }

    /**
     * @desc   parseResponse
     * @author chenmingming
     *
     * @param string $json_response json返回数据
     *
     * @return array
     * @throws wxcorpException
     */
    protected function parseResponse($json_response)
    {
        $result = json_decode($json_response, true);
        if ($result) {
            if ($result['errcode'] == 0) {
                return $result;
            } else {
                throw new wxcorpException($result['errmsg'], $result['errmsg'], $json_response);
            }
        } else {
            throw new wxcorpException('请求发送数据结果为空', 'REQUEST_EMPTY');
        }
    }

    /**
     * @param callable $getCacheCallback
     */
    public function setGetCacheCallback($getCacheCallback)
    {
        $this->getCacheCallback = $getCacheCallback;
    }

    /**
     * @param callable $setCacheCallBack
     */
    public function setSetCacheCallBack($setCacheCallBack)
    {
        $this->setCacheCallBack = $setCacheCallBack;
    }

    /**
     * @param callable $logCallback
     */
    public function setLogCallback($logCallback)
    {
        $this->logCallback = $logCallback;
    }

}