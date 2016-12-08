<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/23 0023
 * Time: 下午 12:14
 */

namespace mmxs\wxcorp;

class config extends base
{
    //获取code
    const URL_CODE = "https://open.weixin.qq.com/connect/oauth2/authorize"
    . "?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=%s#wechat_redirect";
    //根据code获取成员信息
    const URL_USERID = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=%s&code=%s";
    //userid转openid
    const URL_USERID_TO_OPENID = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token=%s";
    //openid转userid
    const URL_OPENID_TO_USERID = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_userid?access_token=%s";
    //获取微信ip段
    const URL_IPLIST = 'https://qyapi.weixin.qq.com/cgi-bin/getcallbackip?access_token=%s';
    //发消息
    const URL_MESSAGE = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=%s';

    protected $token = '';//令牌
    protected $secret = '';//每个用用的secret
    protected $agent_id;//应用id
    protected $agent_name;//应用名称

    /**
     * WxcorpConfig constructor.
     *
     * @param string $config     微信企业号配置名称
     * @param string $agent_name 应用key
     *
     * @throws WxcorpException
     */
    public function __construct($agent_name, $config = '')
    {
        parent::__construct($config);
        if (!isset($this->agent_list[$agent_name])) {
            throw new wxcorpException('该应用配置不存在~', 'AGENT_CONFIG_NOT_FUND');
        }
        $this->agent_name = $agent_name;
        $this->token      = $this->agent_list[$agent_name]['token'];
        if (!$this->token) {
            throw new wxcorpException('该应用配置TOKEN不存在~', 'AGENT_CONFIG_TOKEN_NOT_FUND');
        }
        $this->secret = $this->agent_list[$agent_name]['secret'];
        if (!$this->secret) {
            throw new wxcorpException('该应用配置SECRET不存在~', 'AGENT_CONFIG_SECRET_NOT_FUND');
        }
        if (strlen($this->secret) != 43) {
            throw new wxcorpException('secret 长度不合法', 'SECRET_LENGTH_INVALID');
        }

        $this->agent_id = $this->agent_list[$agent_name]['id'];
        if (is_null($this->agent_id)) {
            throw new wxcorpException('该应用配置ID不存在~' . json_encode($this->agent_list), 'AGENT_CONFIG_ID_NOT_FUND');
        }
    }

    /**
     * getCode 引导关注者打开如下页面
     *
     * @author chenchao
     *
     * @param  string $redirect_url 跳转的页面
     * @param string  $scope        scope
     * @param string  $state        state
     */
    public function getCode($redirect_url, $scope = 'snsapi_base', $state = '')
    {
        $request_url = sprintf(self::URL_CODE, $this->corpid, urlencode($redirect_url), $scope, $state);
        $this->log($request_url, 'redirect');
        header('Location: ' . $request_url);
    }

    /**
     * getUserId 根据code获取成员信息
     *
     * @author chenchao
     *
     * @return array
     * @throws WxcorpException
     */
    public function getUserId()
    {
        if ($_GET['code']) {
            $request_url   = sprintf(self::URL_USERID, $this->getAccessToken(), $_GET['code']);
            $json_response = $this->curl($request_url);
            $content       = json_decode($json_response, true);
            $this->log($content);
            if ($content['OpenId']) {
                throw new wxcorpException('您还不是企业号成员~', 223456);
            } elseif (!$content['OpenId'] && !$content['UserId']) {
                throw new wxcorpException('无法获取用户信息~', 534645);
            }

            return $content['UserId'];
        } else {
            $this->log('获取openid code');
        }
    }

    /**
     * getOpenIdByUserId 根据 企业号userid获取openid
     *
     * @author chenchao
     *
     * @param string $user_id user_id
     *
     * @return string
     * @throws wxcorpException
     */
    public function getOpenIdByUserId($user_id)
    {
        $request_url   = sprintf(self::URL_USERID_TO_OPENID, $this->getAccessToken());
        $json_response = $this->curlPostJson($request_url, ['userid' => $user_id]);
        if (!$json_response) {
            throw new wxcorpException("请求 OpenId 失败~", 'REQUEST_OPENID_FAILED');
        }
        $content = json_decode($json_response, true);

        if (isset($content['openid'])) {
            return $content['openid'];
        }
        throw new wxcorpException("请先去关注安心公寓企业号哦~", 'SUBSCRIBE_FIRST');
    }

    /**
     * getUserIdByOpenId 根据openid获取 企业号userid
     *
     * @author chenchao
     *
     * @param string $open_id open_id
     *
     * @return string
     * @throws wxcorpException
     */
    public function getUserIdByOpenId($open_id)
    {
        $request_url = sprintf(self::URL_OPENID_TO_USERID, $this->getAccessToken());

        $json_response = $this->curlPostJson($request_url, ['openid' => $open_id]);
        if (!$json_response) {
            throw new wxcorpException("请求 UserId 失败~", 12309);
        }
        $content = json_decode($json_response, true);

        if (isset($content['userid'])) {
            return $content['userid'];
        }

        throw new wxcorpException("请求 UserId json字符串 异常~" . var_export($content, true), 31254);
    }

    /**
     * getCallbackIpList 获取微信服务器ip段
     *
     * @author chenchao
     * @return array
     * @throws WxcorpException
     */
    public function getCallbackIpList()
    {
        $key   = 'wxiplist_' . $this->corpname;
        $cache = $this->getCache($key);
        if ($cache) {
            return $cache;
        }
        $json_response = $this->curl(sprintf(self::URL_IPLIST, $this->getAccessToken()));
        if (!$json_response) {
            throw new wxcorpException("请求微信服务器的ip列表失败~");
        }

        $content = json_decode($json_response, true);
        if (isset($content['ip_list'])) {
            $this->setCache($key, (array)$content['ip_list']);

            return (array)$content['ip_list'];
        }
        throw new wxcorpException("请求微信服务器的ip列表 异常~ " . $content['errcode'] . '==>' . $content['errmsg']);
    }

    /**
     * 将企业微信回复用户的消息加密打包.
     * <ol>
     *    <li>对要发送的消息进行AES-CBC加密</li>
     *    <li>生成安全签名</li>
     *    <li>将消息密文和安全签名打包成xml格式</li>
     * </ol>
     *
     * @param string $sReplyMsg   string 企业微信待回复用户的消息，xml格式的字符串
     * @param string $sTimeStamp  string 时间戳，可以自己生成，也可以用URL参数的timestamp
     * @param string $sNonce      string 随机串，可以自己生成，也可以用URL参数的nonce
     * @param string $sEncryptMsg string 加密后的可以直接回复用户的密文，包括msg_signature, timestamp, nonce, encrypt的xml格式的字符串,
     *
     */
    public function encryptMsg($sReplyMsg, $sTimeStamp, $sNonce, &$sEncryptMsg)
    {
        $pc = new Prpcrypt($this->secret);

        //加密
        $encrypt = $pc->encrypt($sReplyMsg, $this->corpid);

        if ($sTimeStamp == null) {
            $sTimeStamp = time();
        }

        //生成安全签名
        $signature = $this->getSHA1($this->token, $sTimeStamp, $sNonce, $encrypt);

        //生成发送的xml
        $sEncryptMsg = XMLParse::generate($encrypt, $signature, $sTimeStamp, $sNonce);
    }

    /**
     * 用SHA1算法生成安全签名
     *
     * @param string $token       票据
     * @param string $timestamp   时间戳
     * @param string $nonce       随机字符串
     * @param string $encrypt_msg 密文消息
     *
     * @return string
     */
    protected function getSHA1($token, $timestamp, $nonce, $encrypt_msg)
    {
        //排序
        $array = [$encrypt_msg, $token, $timestamp, $nonce];
        sort($array, SORT_STRING);
        $str = implode($array);

        return sha1($str);
    }

    /**
     * 检验消息的真实性，并且获取解密后的明文.
     * <ol>
     *    <li>利用收到的密文生成安全签名，进行签名验证</li>
     *    <li>若验证通过，则提取xml中的加密消息</li>
     *    <li>对消息进行解密</li>
     * </ol>
     *
     * @param string $msgSignature string 签名串，对应URL参数的msg_signature
     * @param string $timestamp    string 时间戳 对应URL参数的timestamp
     * @param string $nonce        string 随机串，对应URL参数的nonce
     * @param string $xmltext      string 密文，对应POST请求的数据
     *
     * @return string 解密后的原文
     * @throws wxcorpException
     */
    public function decryptMsg($msgSignature, $timestamp, $nonce, $xmltext)
    {
        if (strlen($this->secret) != 43) {
            throw new wxcorpException('secret 长度不合法', 'SECRET_LENGTH_INVALID');
        }

        $pc = new prpCrypt($this->secret);

        //提取密文
        $xmlparse = new XMLParse;
        list($encrypt,) = $xmlparse->extract($xmltext);

        if ($timestamp == null) {
            $timestamp = time();
        }

        //验证安全签名
        $signature = $this->getSHA1($this->token, $timestamp, $nonce, $encrypt);

        if ($signature != $msgSignature) {
            throw new wxcorpException('签名非法~', 'SINGATURE_INVALID');
        }

        return $pc->decrypt($encrypt, $this->corpid);

    }
}