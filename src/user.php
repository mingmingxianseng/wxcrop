<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/23 0023
 * Time: 下午 3:00
 */

namespace Sdxapp\Wxcorp;

use Sdxapp\AppException;
use Sdxapp\Account\Model\UserModel;
use Sdxapp\Log;

/**
 * Class WxcorpUser 成员管理
 *
 * @package Sdxapp\Wxcorp
 */
class WxcorpUser
{

    const URL_SIMPLELIST = "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=%s&department_id=%s";
    const URL_LIST = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token=%s&department_id=%s";
    const URL_GET_USER = "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=%s&userid=%s";
    const URL_CREATE = "https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token=%s";
    const URL_UPDATE = "https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token=%s";
    const URL_DELETE = "https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token=%s&userid=%s";
    const URL_BATCHDELETE = "https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?access_token=%s";

    protected $user_id;//用户id
    protected $name;//用户名称
    protected $email;//用户邮箱
    protected $department = [];//用户部门数组
    protected $mobile;//用户手机号
    protected $gender;//用户性别
    protected $avatar;//用户头像地址
    protected $status;//用户状态
    protected $extattr;//用户其他信息

    /**
     * @var WxcorpConfig
     */
    static private $wxcorpConfigObj;

    /**
     * WxcorpUser 构造函数
     *
     * @param string $userid 用户id 手机号码
     *
     * @throws WxcorpException
     */
    public function __construct($userid)
    {
        if (empty($userid)) {
            throw new WxcorpException("userid 不能为空!~", 232379);
        }

        $request_url   = sprintf(self::URL_GET_USER, self::getWxcorpConfigObj()->getAccessToken(), $userid);
        $json_response = curl($request_url);
        $result        = json_decode($json_response, true);

        if ($result) {
            if ($result['errcode'] == 0) {
                $this->init($result);
            } else {
                throw new WxcorpException($result['errcode'] . ' ' . $result['errmsg'], 776433);
            }
        } else {
            throw new WxcorpException("获取失败!~", 887564);
        }
    }

    /**
     * isExistWxUser @desc 判断是否存在该微信账户
     *
     * @author liulian
     *
     * @param string $userId 用户id(手机号)
     *
     * @return bool
     * @throws WxcorpException
     */
    static public function isExistWxUser($userId)
    {
        if (empty($userId)) {
            throw new WxcorpException("userid 不能为空!~", 232379);
        }

        $request_url   = sprintf(self::URL_GET_USER, self::getWxcorpConfigObj()->getAccessToken(), $userId);
        $json_response = curl($request_url);
        $result        = json_decode($json_response, true);

        if ($result['errcode'] == 0) {
            return true;
        } else {
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
     * @author liulian
     *
     * @param int $user_id user_id
     *
     * @return string
     * @throws WxcorpException
     */
    static public function getOpenidByUserId($user_id)
    {
        return self::getWxcorpConfigObj()->getOpenIdByUserId($user_id);
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
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * init @desc 初始化
     *
     * @author liulian
     *
     * @param array $data 数据
     */
    private function init($data)
    {
        $this->user_id    = $data['userid'];
        $this->name       = $data['name'];
        $this->email      = $data['email'];
        $this->department = $data['department'];
        $this->mobile     = $data['mobile'];
        $this->gender     = $data['gender'];
        $this->avatar     = $data['avatar'];
        $this->status     = $data['status'];
        $this->extattr    = $data['extattr'];
    }

    /**
     * @desc   getWxcorpConfigObj
     * @author chenmingming
     * @return WxcorpConfig
     */
    static private function getWxcorpConfigObj()
    {
        if (is_null(self::$wxcorpConfigObj)) {
            self::$wxcorpConfigObj = new WxcorpConfig();
        }

        return self::$wxcorpConfigObj;
    }

    /**
     * createUser 创建成员
     *
     * @author chenchao
     *
     * @param string $userid         成员UserID
     * @param string $name           成员名称
     * @param int    $departmentid   成员所属部门id列表
     * @param bool   $mobile         手机号码
     * @param bool   $email          邮箱
     * @param bool   $weixinid       微信号
     * @param bool   $position       职位信息
     * @param bool   $gender         性别 1男/2女
     * @param bool   $avatar_mediaid 成员头像的mediaid
     * @param array  $extattr        扩展属性
     *
     * @return string
     * @throws WxcorpException
     */
    static public function createUser($userid, $name, $departmentid, $mobile = false, $email = false, $weixinid = false, $position = false, $gender = false, $avatar_mediaid = false, $extattr = [])
    {
        if (empty($userid) || empty($name) || empty($departmentid)) {
            throw new WxcorpException('userid、name、departmentid 不能为空!', 688983);
        }

        $request_url = sprintf(self::URL_CREATE, self::getWxcorpConfigObj()->getAccessToken());
        $data        = ['userid' => $userid, 'name' => $name, 'department' => $departmentid];
        if (!empty($mobile)) {
            $data['mobile'] = $mobile;
        }
        if (!empty($email)) {
            $data['email'] = $email;
        }
        if (!empty($weixinid)) {
            $data['weixinid'] = $weixinid;
        }
        if (empty($mobile) && empty($email) && empty($weixinid)) {
            throw new WxcorpException('mobile、weixinid、email三者不能同时为空 不能为空!', 534533);
        }
        if (!empty($position)) {
            $data['position'] = $position;
        }
        if (!empty($gender)) {
            $data['gender'] = $gender;
        }
        if (!empty($avatar_mediaid)) {
            $data['avatar_mediaid'] = $avatar_mediaid;
        }
        if (!empty($extattr) && is_array($extattr)) {
            $temp = [];
            foreach ($extattr as $key => $value) {
                $temp[] = ['name' => $key, 'value' => $value];
            }
            $data['extattr'] = ['attrs' => $temp];
        }
        $json_response = curlPostJson($request_url, $data);
        $result        = json_decode($json_response, true);
        if ($result) {
            if ($result['errcode'] != 0) {
                throw new AppException($result['errmsg'], 643532);
            }
        } else {
            throw new AppException('创建成员失败!', 236795);
        }
    }

    /**
     * updateUser 更新成员
     *
     * @author   chenchao
     *
     * @param string $userid         成员UserID
     * @param bool   $name           成员名称
     * @param bool   $mobile         手机号码
     * @param bool   $email          邮箱
     * @param bool   $position       职位信息
     * @param bool   $gender         性别 1男/2女
     * @param bool   $weixinid       微信号
     * @param int    $enable         启用/禁用成员 1启用/2禁用
     * @param bool   $avatar_mediaid 成员头像的mediaid
     * @param array  $extattr        扩展属性
     *
     * @return string
     * @throws AppException
     * @internal param bool $departmentid 成员所属部门id列表
     */
    static public function updateUser($userid, $name = false, $mobile = false, $email = false, $position = false, $gender = false, $weixinid = false, $enable = 1, $avatar_mediaid = false, $extattr = [])
    {
        if (empty($userid)) {
            throw new AppException("用户唯一id不能为空", 463566);
        }

        $request_url = sprintf(self::URL_UPDATE, self::getWxcorpConfigObj()->getAccessToken());

        $data = ['userid' => $userid, 'enable' => $enable];

        if (!empty($name)) {
            $data['name'] = $name;
        }
        if ($position) {
            $data['position'] = $position;
        }
        if ($mobile) {
            $data['mobile'] = $mobile;
        }
        if ($gender) {
            $data['gender'] = $gender;
        }
        if ($email) {
            $data['email'] = $email;
        }
        if ($weixinid) {
            $data['weixinid'] = $weixinid;
        }
        if (empty($mobile) && empty($email) && empty($weixinid)) {
            throw new AppException("mobile、weixinid、email三者不能同时为空", 534123);
        }

        if (is_array($extattr)) {
            $temp = [];
            foreach ($extattr as $key => $value) {
                $temp[] = ['name' => $key, 'value' => $value];
            }
            $data['extattr'] = ['attrs' => $temp];
        }

        $json_response = curlPostJson($request_url, $data);
        $result        = json_decode($json_response, true);
        if ($result && $result['errcode'] != 0) {
            throw new AppException('更新失败 ' . $result['errmsg'], $result['errcode']);
        }
        throw new AppException("更新失败", 56001);
    }

    /**
     * deleteUser 删除成员
     *
     * @author chenchao
     *
     * @param string $userid 成员UserID
     *
     * @return string
     */
    public function deleteUser($userid)
    {
        if (empty($userid)) {
            throw new AppException("用户id必须传入", 1111);
        }

        $request_url   = sprintf(self::URL_DELETE, self::getWxcorpConfigObj()->getAccessToken(), $userid);
        $json_response = curl($request_url);
        $result        = json_decode($json_response, true);

        if ($result) {
            if ($result['errcode'] == 0) {
                $result['success'] = true;
                $result['userid']  = $userid;

                return json_encode($result);
            } else {
                $result['success'] = false;

                return json_encode($result);
            }
        } else {
            return json_encode(['success' => false, 'errmsg' => '删除失败!', 'errcode' => 986726]);
        }
    }

    /**
     * batchDeleteUsers 批量删除成员
     *
     * @author chenchao
     *
     * @param array $useridlist 成员UserID数组
     *
     * @return string
     */
    public function batchDeleteUsers($useridlist)
    {
        if (empty($useridlist)) {
            return json_encode(['success' => false, 'errmsg' => 'useridlist 不能为空!', 'errcode' => 234235]);
        } else if (!is_array($useridlist)) {
            return json_encode(['success' => false, 'errmsg' => 'useridlist 必须是数组!', 'errcode' => 225678]);
        }

        $data          = ['useridlist' => $useridlist];
        $request_url   = sprintf(self::URL_BATCHDELETE, self::getWxcorpConfigObj()->getAccessToken());
        $json_response = curlPostJson($request_url, $data);
        $result        = json_decode($json_response, true);

        if ($result) {
            if ($result['errcode'] == 0) {
                $result['success'] = true;

                return json_encode($result);
            } else {
                $result['success'] = false;

                return json_encode($result);
            }
        } else {
            return json_encode(['success' => false, 'errmsg' => '删除失败!', 'errcode' => 434557]);
        }
    }

    /**
     * getUserByID 根据成员ID获取用户详细信息
     *
     * @author chenchao
     *
     * @param string $userid 成员ID
     *
     * @throws WxcorpException
     * @throws \Sdxapp\AppException
     */
    public function getUserByID($userid)
    {
        if (empty($userid)) {
            throw new WxcorpException("userid 不能为空!~", 232379);
        }

        $request_url   = sprintf(self::URL_GET_USER, self::getWxcorpConfigObj()->getAccessToken(), $userid);
        $json_response = curl($request_url);
        $result        = json_decode($json_response, true);
        if ($result) {
            if ($result['errcode'] == 0) {
                return $result;
            } else {
                throw new WxcorpException($result['errcode'] . ' ' . $result['errmsg'], 776433);
            }
        } else {
            throw new WxcorpException("获取失败!~", 887564);
        }
    }

    /**
     * getUserList 根据部门ID获取用户列表
     *
     * @author chenchao
     *
     * @param int $department_id 部门ID
     * @param int $fetch_child   是否递归获取子部门下面的成员
     * @param int $status        0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加
     *
     * @return string
     */
    public function getUserList($department_id = 1, $fetch_child = 0, $status = 0)
    {
        if (intval($department_id) < 1) {
            return json_encode(['success' => false, 'errmsg' => 'department_id 必须大于0!', 'errcode' => 324543, 'userlist' => []]);
        }

        $request_url = sprintf(self::URL_SIMPLELIST, self::getWxcorpConfigObj()->getAccessToken(), $department_id);
        if (intval($fetch_child) > -1) {
            $request_url .= '&fetch_child=' . $fetch_child;
        }
        if (intval($status) > -1) {
            $request_url .= '&status' . $status;
        }
        $json_response = curl($request_url);
        $result        = json_decode($json_response, true);

        if ($result) {
            if ($result['errcode'] == 0) {
                $result['success'] = true;

                return json_encode($result);
            } else {
                $result['success'] = false;

                return json_encode($result);
            }
        } else {
            return json_encode(['success' => false, 'errmsg' => '获取失败!', 'errcode' => 786432, 'userlist' => []]);
        }
    }

    /**
     * getUserListDetails 根据部门ID获取用户列表（详情）
     *
     * @author chenchao
     *
     * @param int $department_id 部门ID
     * @param int $fetch_child   是否递归获取子部门下面的成员
     * @param int $status        0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加
     *
     * @return string
     */
    public function getUserListDetails($department_id = 1, $fetch_child = 0, $status = 0)
    {
        if (intval($department_id) < 1) {
            return json_encode(['success' => false, 'errmsg' => 'department_id 必须大于0!', 'errcode' => 686745, 'userlist' => []]);
        }

        $request_url = sprintf(self::URL_LIST, self::getWxcorpConfigObj()->getAccessToken(), $department_id);
        if (intval($fetch_child) > -1) {
            $request_url .= '&fetch_child=' . $fetch_child;
        }
        if (intval($status) > -1) {
            $request_url .= '&status' . $status;
        }
        $json_response = curl($request_url);
        $result        = json_decode($json_response, true);

        if ($result) {
            if ($result['errcode'] == 0) {
                $result['success'] = true;

                return json_encode($result);
            } else {
                $result['success'] = false;

                return json_encode($result);
            }
        } else {
            return json_encode(['success' => false, 'errmsg' => '获取失败!', 'errcode' => 239365, 'userlist' => []]);
        }
    }

}