<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/23 0023
 * Time: 下午 3:00
 */

namespace Sdxapp\Wxcorp;

/**
 * Class WxcorpDepartment 部门管理
 *
 * @package Sdxapp\Wxcorp
 */
class WxcorpDepartment
{
    private $token;

    const URL_CREATE = "https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token=%s";
    const URL_UPDATE = "https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token=%s";
    const URL_DELETE = "https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token=&s&id=%s";
    const URL_LIST = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=%s";

    /**
     * WxcorpDepartment 构造函数
     *
     * @param string $token AccessToken
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * createDepartment 创建部门
     *
     * @author chenchao
     *
     * @param string $name     部门名称
     * @param int    $parentid 父部门ID，默认根部门1
     * @param bool   $order    排序
     * @param bool   $id       指定部门ID
     *
     * @return string
     */
    public function createDepartment($name, $parentid = 1, $order = false, $id = false)
    {
        if (empty($name) && empty($parentid)) {
            return json_encode(['success' => false, 'errmsg' => '部门名称、父部门ID 不能为空!', 'errcode' => 465463]);
        }

        $data = ['name' => $name, 'parentid' => $parentid];
        if (!empty($order)) {
            $data['order'] = $order;
        }
        if (!empty($id)) {
            $data['id'] = $id;
        }

        $request_url   = sprintf(self::URL_CREATE, $this->token);
        $json_response = curlPostJson($request_url, $data);
        $result        = json_decode($json_response, true);

        if ($result) {
            if ($result['errcode'] == 0) {
                $result['success'] = true;
                $result['name']    = $name;

                return json_encode($result);
            } else {
                $result['success'] = false;

                return json_encode($result);
            }
        } else {
            return json_encode(['success' => false, 'errmsg' => '创建失败!', 'errcode' => 832826]);
        }
    }

    /**
     * updateDepartment 更新部门（如果非必须的字段未指定，则不更新该字段之前的设置值）
     *
     * @author chenchao
     *
     * @param int  $id       部门id
     * @param bool $name     部门名称
     * @param bool $parentid 父部门ID
     * @param bool $order    排序
     *
     * @return string
     */
    public function updateDepartment($id, $name = false, $parentid = false, $order = false)
    {
        if (empty($id)) {
            return json_encode(['success' => false, 'errmsg' => 'Department ID 不能为空!', 'errcode' => 123654]);
        }

        $data = ['id' => $id];
        if (!empty($name)) {
            $data['name'] = $name;
        }
        if (!empty($order)) {
            $data['order'] = $order;
        }
        if (!empty($parentid)) {
            $data['parentid'] = $parentid;
        }

        $request_url   = sprintf(self::URL_UPDATE, $this->token);
        $json_response = curlPostJson($request_url, $data);
        $result        = json_decode($json_response, true);

        if ($result) {
            if ($result['errcode'] == 0) {
                $result['success'] = true;
                $result['name']    = $name;

                return json_encode($result);
            } else {
                $result['success'] = false;

                return json_encode($result);
            }
        } else {
            return json_encode(['success' => false, 'errmsg' => '更新失败!', 'errcode' => 545362]);
        }
    }

    /**
     * deleteDepartment 删除部门
     *
     * @author chenchao
     *
     * @param int $id 部门ID
     *
     * @return string
     */
    public function deleteDepartment($id)
    {
        if (empty($id)) {
            return json_encode(['success' => false, 'errmsg' => '部门ID 不能为空!', 'errcode' => 473521]);
        }

        $request_url   = sprintf(self::URL_UPDATE, $this->token, $id);
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
            return json_encode(['success' => false, 'errmsg' => '删除失败!', 'errcode' => 836432]);
        }
    }

    /**
     * getDepartmentList 获取部门列表
     *
     * @author chenchao
     *
     * @param bool $id 部门ID
     *
     * @return string
     */
    public function getDepartmentList($id = false)
    {
        $data = ['access_token' => $this->token];
        if (!empty($id)) {
            $data['id'] = $id;
        }
        $request_url = sprintf(self::URL_LIST, $this->token);
        if (!empty($id)) {
            $request_url .= '&id=' . $id;
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
            return json_encode(['success' => false, 'errmsg' => '查询失败!', 'errcode' => 883562, 'department' => []]);
        }
    }

}