<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/23 0023
 * Time: 下午 3:00
 */

namespace wxcorp;

/**
 * Class WxcorpAgent 应用管理
 *
 * @package Sdxapp\Wxcorp
 */
class agent
{
    private $token;

    const URL_GET = "https://qyapi.weixin.qq.com/cgi-bin/agent/get?access_token=%s&agentid=%s";
    const URL_SET = "https://qyapi.weixin.qq.com/cgi-bin/agent/set?access_token=%s";
    const URL_LIST = "https://qyapi.weixin.qq.com/cgi-bin/agent/list?access_token=%s";

    /**
     * WxcorpAgent 构造函数
     *
     * @param string $token AccessToken
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * getAgentByID 根据应用ID获取应用详情
     *
     * @author chenchao
     *
     * @param int $agent_id 应用ID
     *
     * @return string
     */
    public function getAgentByID($agent_id)
    {
        if (intval($agent_id) < 0) {
            return json_encode(['success' => false, 'errmsg' => 'agent_id 必须大于0!', 'errcode' => 967367]);
        }

        $request_url   = sprintf(self::URL_GET, $this->token, $agent_id);
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
            return json_encode(['success' => false, 'errmsg' => '获取失败!', 'errcode' => 863561]);
        }
    }

    /**
     * setAgentByID 根据应用ID设置应用信息
     *
     * @author chenchao
     *
     * @param int  $agent_id             应用ID
     * @param bool $name                 企业应用名称
     * @param bool $description          企业应用详情
     * @param bool $logo_mediaid         企业应用头像的mediaid
     * @param bool $redirect_domain      企业应用可信域名
     * @param int  $is_reportuser        是否接收用户变更通知 0不接收/1接收
     * @param int  $is_reportenter       是否上报用户进入应用事件 0不接收/1接收
     * @param int  $report_location_flag 企业应用是否打开地理位置上报  0不上报/1进入会话上报/2持续上报
     *
     * @return string
     */
    public function setAgentByID($agent_id, $name = false, $description = false, $logo_mediaid = false, $redirect_domain = false, $is_reportuser = -1, $is_reportenter = -1, $report_location_flag = -1)
    {
        if (intval($agent_id) < 0) {
            return json_encode(['success' => false, 'errmsg' => 'agent_id 必须大于0!', 'errcode' => 864534]);
        }

        $request_url = sprintf(self::URL_GET, $this->token);
        $data        = ['agentid' => $agent_id];
        if (!empty($name)) {
            $data['name'] = $name;
        }
        if (!empty($description)) {
            $data['description'] = $description;
        }
        if (!empty($logo_mediaid)) {
            $data['logo_mediaid'] = $logo_mediaid;
        }
        if (!empty($redirect_domain)) {
            $data['redirect_domain'] = $redirect_domain;
        }
        if (intval($is_reportuser) >= 0 && intval($is_reportuser) <= 1) {
            $data['isreportuser'] = $is_reportuser;
        }
        if (intval($is_reportenter) >= 0 && intval($is_reportenter) <= 1) {
            $data['isreportenter'] = $is_reportenter;
        }
        if (intval($report_location_flag) >= 0 && intval($report_location_flag) <= 2) {
            $data['report_location_flag'] = $report_location_flag;
        }

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
            return json_encode(['success' => false, 'errmsg' => '更新失败!', 'errcode' => 234897]);
        }
    }

    /**
     * getAgentList 获取企业应用列表
     *
     * @author chenchao
     * @return string
     */
    public function getAgentList()
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
            return json_encode(['success' => false, 'errmsg' => '获取失败!', 'errcode' => 232358, 'agentlist' => []]);
        }
    }
}