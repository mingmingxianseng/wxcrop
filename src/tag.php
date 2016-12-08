<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/23 0023
 * Time: 下午 3:00
 */

namespace Sdxapp\Wxcorp;

/**
 * Class WxcorpTag 标签管理
 *
 * @package Sdxapp\Wxcorp
 */
class WxcorpTag
{
    private $token;

    const URL_CREATE = "https://qyapi.weixin.qq.com/cgi-bin/tag/create?access_token=%s";
    const URL_UPDATE = "https://qyapi.weixin.qq.com/cgi-bin/tag/update?access_token=%s";
    const URL_DELETE = "https://qyapi.weixin.qq.com/cgi-bin/tag/delete?access_token=%s&tagid=%s";
    const URL_GET = "https://qyapi.weixin.qq.com/cgi-bin/tag/get?access_token=%s&tagid=%s";
    const URL_ADD = "https://qyapi.weixin.qq.com/cgi-bin/tag/addtagusers?access_token=%s";
    const URL_DELETE_USER = "https://qyapi.weixin.qq.com/cgi-bin/tag/deltagusers?access_token=%s";
    const URL_LIST = "https://qyapi.weixin.qq.com/cgi-bin/tag/list?access_token=%s";

    /**
     * WxcorpTag 构造函数
     *
     * @param string $token AccessToken
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * createTag 创建标签
     *
     * @author chenchao
     *
     * @param string $tag_name 标签名称
     * @param int    $tag_id   标签id
     *
     * @return string
     */
    public function createTag($tag_name, $tag_id = 0)
    {
        if (empty($tag_name)) {
            return json_encode(['success' => false, 'errmsg' => 'tag_name 不能为空!', 'errcode' => 487786]);
        }

        $data = ['tagname' => $tag_name];
        if (intval($tag_id) > 0) {
            $data['tagid'] = $tag_id;
        }
        $request_url   = sprintf(self::URL_CREATE, $this->token);
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
            return json_encode(['success' => false, 'errmsg' => '创建失败!', 'errcode' => 245477]);
        }
    }

    /**
     * updateTag 更新标签名字
     *
     * @author chenchao
     *
     * @param int $tag_id 标签ID
     * @param string $tag_name 标签名称
     *
     * @return string
     */
    public function updateTag($tag_id, $tag_name)
    {
        if (intval($tag_id) < 1 || empty($tag_name)) {
            return json_encode(['success' => false, 'errmsg' => 'tag_name、tag_id 不能为空!', 'errcode' => 133567]);
        }

        $data          = ['tagid' => $tag_id, 'tagname' => $tag_name];
        $request_url   = sprintf(self::URL_CREATE, $this->token);
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
            return json_encode(['success' => false, 'errmsg' => '更新失败!', 'errcode' => 232356]);
        }
    }

    /**
     * deleteTag 根据标签ID删除标签
     *
     * @author chenchao
     *
     * @param int $tag_id 标签ID
     *
     * @return string
     */
    public function deleteTag($tag_id)
    {
        if (intval($tag_id) < 1) {
            return json_encode(['success' => false, 'errmsg' => 'tag_id 必须大于0!', 'errcode' => 334578]);
        }
        $request_url   = sprintf(self::URL_DELETE, $this->token, $tag_id);
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
            return json_encode(['success' => false, 'errmsg' => '删除失败!', 'errcode' => 922324]);
        }
    }

    /**
     * getTagUsers 根据标签ID获取标签下的成员
     *
     * @author chenchao
     *
     * @param int $tag_id 标签ID
     *
     * @return string
     */
    public function getTagUsers($tag_id)
    {
        if (intval($tag_id) < 1) {
            return json_encode(['success' => false, 'errmsg' => 'tag_id 必须大于0!', 'errcode' => 998765, 'userlist' => [], 'partylist' => []]);
        }

        $request_url   = sprintf(self::URL_GET, $this->token, $tag_id);
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
            return json_encode(['success' => false, 'errmsg' => '获取失败!', 'errcode' => 113345, 'taglist' => [], 'partylist' => []]);
        }
    }

    /**
     * addUserToTag 向指定标签内添加成员或部门
     *
     * @author chenchao
     *
     * @param int   $tag_id    标签ID
     * @param array $userlist  企业成员ID列表
     * @param array $partylist 企业部门ID列表 userlist、partylist不能同时为空
     *
     * @return string
     */
    public function addUserToTag($tag_id, $userlist = [], $partylist = [])
    {
        if (intval($tag_id) < 1) {
            return json_encode(['success' => false, 'errmsg' => 'tag_id 不能为0!', 'errcode' => 223354]);
        }

        $data = ['tagid' => $tag_id];
        if (!empty($userlist) && is_array($userlist) && count($userlist)) {
            $data['userlist'] = $userlist;
        }
        if (!empty($partylist) && is_array($partylist) && count($partylist)) {
            $data['partylist'] = $partylist;
        }

        $request_url   = sprintf(self::URL_ADD, $this->token);
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
            return json_encode(['success' => false, 'errmsg' => '添加成员失败!', 'errcode' => 667804]);
        }
    }

    /**
     * deleteUserFromTag 删除标签成员
     *
     * @author chenchao
     *
     * @param int   $tag_id    标签ID
     * @param array $userlist  企业成员ID列表
     * @param array $partylist 企业部门ID列表 userlist、partylist不能同时为空
     *
     * @return string
     */
    public function deleteUserFromTag($tag_id, $userlist = [], $partylist = [])
    {
        if (intval($tag_id) < 1) {
            return json_encode(['success' => false, 'errmsg' => 'tag_id 不能为0!', 'errcode' => 143554]);
        }

        $data = ['tagid' => $tag_id];
        if (!empty($userlist) && is_array($userlist) && count($userlist)) {
            $data['userlist'] = $userlist;
        }
        if (!empty($partylist) && is_array($partylist) && count($partylist)) {
            $data['partylist'] = $partylist;
        }

        $request_url   = sprintf(self::URL_DELETE_USER, $this->token);
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
            return json_encode(['success' => false, 'errmsg' => '操作失败!', 'errcode' => 932624]);
        }
    }

    /**
     * getTagList 获取标签列表
     *
     * @author chenchao
     * @return string
     */
    public function getTagList()
    {
        $request_url   = sprintf(self::URL_LIST, $this->token);
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
            return json_encode(['success' => false, 'errmsg' => '获取失败!', 'errcode' => 455647, 'taglist' => []]);
        }
    }
}