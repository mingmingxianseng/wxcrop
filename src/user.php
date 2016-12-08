<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/23 0023
 * Time: 下午 3:00
 */

namespace mmxs\wxcorp;

/**
 * Class user 成员管理
 */
class user extends base
{
    const SEX_MAN = 1;//男性
    const SEX_WOMAN = 2;//女性

    protected $userid;//用户id
    protected $name;//用户名称
    protected $email;//用户邮箱
    protected $department;//用户部门数组
    protected $position;//职位信息
    protected $mobile;//用户手机号
    protected $weixinid;//微信号
    protected $gender;//用户性别
    protected $avatar;//用户头像地址
    protected $openid;//用户openid
    protected $status;//用户状态
    protected $extattr;//用户其他信息
    protected $avatar_mediaid;//成员头像的mediaid，通过多媒体接口上传图片获得的mediaid

    /**
     * WxcorpUser 构造函数
     *
     * @param string $userid 用户id 手机号码
     * @param array  $config 微信企业号配置
     *
     * @throws wxcorpException
     */
    public function __construct($userid = '', $config = '')
    {
        parent::__construct($config);

        $userid && $this->init($userid);
    }

    /**
     * @param string $openid openid
     *
     * @return $this
     */
    public function setOpenid($openid)
    {
        $this->openid = $openid;

        return $this;
    }

    /**
     * @desc   parseParams 渲染变量
     * @author chenmingming
     *
     * @param array $data 用户信息数组
     */
    protected function parseParams($data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * @desc   requestUserInfo 请求用户信息
     * @author chenmingming
     *
     * @param string $user_id 用户id
     *
     * @return array
     */
    private function requestUserInfo($user_id)
    {
        $url           = "https://qyapi.weixin.qq.com/cgi-bin/user/get"
            . "?access_token={$this->getAccessToken()}&userid={$user_id}";
        $json_response = $this->curl($url);

        return $this->parseResponse($json_response);
    }

    /**
     * @desc   init
     * @author chenmingming
     *
     * @param string $user_id 用户id
     *
     * @return $this
     */
    public function init($user_id)
    {
        $data = $this->requestUserInfo($user_id);
        $this->parseParams($data);

        return $this;
    }

    /**
     * @desc   delete
     * @author chenmingming
     *
     * @return array
     */
    public function delete()
    {
        $url      = "https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token={$this->getAccessToken()}&userid={$this->userid}";
        $response = $this->curl($url);

        return $this->parseResponse($response);
    }

    /**
     * isExistWxUser @desc 判断是否存在该微信账户
     *
     * @author string
     *
     * @param string $userId 用户id(手机号)
     *
     * @return bool
     */
    public function isExist($userId)
    {
        if (empty($userId)) {
            return false;
        }
        try {
            $this->requestUserInfo($userId);

            return true;
        } catch (wxcorpException $e) {
            return false;
        }

    }

    /**
     * @return array
     */
    public function getExtattr()
    {
        return $this->extattr;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * getOpenidByUserId @desc 通过user_id获取open_id
     *
     * @author chenmingming
     *
     * @return string
     * @throws wxcorpException
     */
    public function getOpenId()
    {
        if (is_null($this->openid)) {
            if (!$this->userid) {
                return null;
            }
            $this->openid = $this->userid2Openid($this->userid);
        }

        return $this->openid;
    }

    /**
     * @desc   openid2Userid
     * @author chenmingming
     *
     * @param string $openid openid
     *
     * @return string
     */
    private function openid2Userid($openid)
    {
        $url      = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_userid?access_token={$this->getAccessToken()}";
        $data     = ['openid' => $openid];
        $response = $this->curlPostJson($url, $data);

        $rs = $this->parseResponse($response);

        return $rs['userid'];
    }

    /**
     * @desc   userid2Openid
     * @author chenmingming
     *
     * @param string $userid  用户id
     * @param string $agentid 整型，需要发送红包的应用ID，若只是使用微信支付和企业转账，则无需该参数
     *
     * @return string
     */
    private function userid2Openid($userid, $agentid = '')
    {
        $url  = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token={$this->getAccessToken()}";
        $data = ['userid' => $userid];
        $agentid && $data['agentid'] = $agentid;
        $response = $this->curlPostJson($url, $data);

        $rs = $this->parseResponse($response);

        return $rs['openid'];
    }

    /**
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $avatar_mediaid 媒体id
     *
     * @return user
     */
    public function setAvatarMediaid($avatar_mediaid)
    {
        $this->avatar_mediaid = $avatar_mediaid;

        return $this;
    }

    /**
     * @return array
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUserid()
    {
        if (is_null($this->userid)) {
            if (!$this->openid) {
                return null;
            }
            $this->userid = $this->openid2Userid($this->openid);
        }

        return $this->userid;
    }

    /**
     * @param string $userid 用户id
     *
     * @return user
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * @param string $name 用户名称
     *
     * @return user
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $email 用户邮箱
     *
     * @return user
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param array $department 部门
     *
     * @return user
     */
    public function setDepartment($department)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @param string $position 职位信息
     *
     * @return user
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @param string $mobile 手机号码
     *
     * @return user
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @param string $weixinid 微信号
     *
     * @return user
     */
    public function setWeixinid($weixinid)
    {
        $this->weixinid = $weixinid;

        return $this;
    }

    /**
     * @desc 设置性别
     *
     * @param int $gender 性别
     *
     * @return user
     */
    public function setGender($gender = self::SEX_MAN)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @param string $extattr json
     *
     * @return user
     */
    public function setExtattr($extattr)
    {
        $this->extattr = $extattr;

        return $this;
    }

    /**
     * @desc   create
     * @author chenmingming
     *
     * @return array
     * @throws wxcorpException
     */
    public function create()
    {
        $mustKeyMap = ['userid', 'name', 'department'];
        $allKeyMap  = array_merge($mustKeyMap, ['position', 'mobile', 'gender', 'email', 'weixinid', 'extattr', 'avatar_mediaid']);
        foreach ($mustKeyMap as $key) {
            if (is_null($this->$key)) {
                throw new wxcorpException("{$key}不能为空", 'CREATE_PARAM_EMPTY');
            }
        }
        $data = [];
        foreach ($allKeyMap as $k) {
            if (!is_null($this->$k)) {
                $data[$k] = $this->$k;
            }
        }
        $response = $this->curlPostJson("https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token={$this->getAccessToken()}", $data);

        return $this->parseResponse($response);
    }

    /**
     * updateUser 更新成员
     *
     * @author   chenmingming
     */
    public function update()
    {
        if (!$this->userid) {
            throw new wxcorpException('更新用户信息必须指定用户id', 'USERID_MISSING');
        }
        $allKeyMap = ['userid', 'name', 'department', 'position', 'mobile', 'gender', 'email', 'weixinid', 'extattr', 'avatar_mediaid'];

        $data = [];
        foreach ($allKeyMap as $k) {
            if (!is_null($this->$k)) {
                $data[$k] = $this->$k;
            }
        }
        $response = $this->curlPostJson("https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token={$this->getAccessToken()}", $data);

        return $this->parseResponse($response);
    }
}