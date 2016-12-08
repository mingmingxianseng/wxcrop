<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/4 0004
 * Time: 下午 3:42
 */
namespace wxcorp;

class base
{
    //成功
    const RETURN_CODE_SUCCESS = 'SUCCESS';
    //失败
    const RETURN_CODE_FAIL = 'FAIL';

    protected $config = [];//微信配置
    protected $name;//企业号名称
    protected $corpid; //企业号id
    protected $corpsecret;//企业号secret
    protected $agent_list = [];//应用列表
    protected $is_weixin_browser = false;//是否在微信浏览器内
    static private $memcacheObj;

    /**
     * WxBase constructor. 构造函数
     *
     * @param string|array $config 公众号名称
     *
     * @throws WxcorpException
     */
    public function __construct($config = '')
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            $this->is_weixin_browser = true;
        }

        if ($config == '') {
            $this->config = C('WEIXIN_CORP');
        } elseif (is_string($config)) {
            $this->config = C('WEIXIN_CORP_' . $config);
        } elseif (is_array($config)) {
            $this->config = $config;
        }

        if (empty($this->config)) {
            throw new WxcorpException("微信配置缺失~", 121000);
        }

        if (empty($this->config['corpid'])) {
            throw new WxcorpException("corpid 不能为空~", 'CORPID_EMPTY');
        }

        if (empty($this->config['corpsecret'])) {
            throw new WxcorpException("corpsecret 不能为空~", 121003);
        }

        $this->corpid     = $this->config['corpid'];
        $this->corpsecret = $this->config['corpsecret'];
        $this->name       = $this->config['name'] ? $this->config['name'] : '';
        $this->agent_list = $this->config['agent_list'];
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
        return curlPostJson($url, $data);
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

        return curl($url, $post_data, $config);
    }

    /**
     * log 日志记录
     *
     * @author chenmingming
     *
     * @param string $message 日志内容
     * @param string $label   label
     */
    protected function log($message, $label = '')
    {
        if ($label) {
            Log::write($message, 'WECHAT-' . $label);
        } else {
            Log::write($message, 'WECHAT');
        }
    }

    /**
     * @desc   获取缓存
     * @author 陈明明 mailto:838965806@qq.com
     * @since  2015-04-01 11:58:37
     *
     * @param string $key 缓存key
     *
     * @return array|bool|string
     */
    protected function getCache($key)
    {
        if (is_null(self::$memcacheObj)) {
            self::$memcacheObj = McacheFactory::provide();
        }

        return self::$memcacheObj->get($key);
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
        if (is_null(self::$memcacheObj)) {
            self::$memcacheObj = McacheFactory::provide();
        }

        return self::$memcacheObj->set($key, $data, $option);
    }
}