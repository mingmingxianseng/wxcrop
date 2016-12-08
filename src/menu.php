<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/23 0023
 * Time: 下午 3:00
 */

namespace Sdxapp\Wxcorp;

/**
 * Class WxcorpMeun 菜单管理
 *
 * @package Sdxapp\Wxcorp
 */
class WxcorpMeun
{
    private $token;

    const URL_CREATE = "https://qyapi.weixin.qq.com/cgi-bin/menu/create?access_token=%s&agentid=%s";
    const URL_DELETE = "https://qyapi.weixin.qq.com/cgi-bin/menu/delete?access_token=%s&agentid=%s";
    const URL_GET = "https://qyapi.weixin.qq.com/cgi-bin/menu/get?access_token=%s&agentid=%s";

    /**
     * WxcorpMeun 构造函数
     *
     * @param string $token AccessToken
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * createMenu 创建应用的自定义菜单
     *
     * @author chenchao
     *
     * @param int   $agent_id    企业应用ID
     * @param array $button_list 菜单数组
     *
     * @return string
     */
    public function createMenu($agent_id, $button_list)
    {
        if (intval($agent_id) < 0) {
            return json_encode(['success' => false, 'errmsg' => 'agent_id 必须大于0!', 'errcode' => 645332]);
        }
        if (empty($button_list) || !is_array($button_list)) {
            return json_encode(['success' => false, 'errmsg' => 'button_list 格式不合法!', 'errcode' => 896764]);
        }

        $data = ['button' => $button_list];

        $request_url   = sprintf(self::URL_CREATE, $this->token, $agent_id);
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
            return json_encode(['success' => false, 'errmsg' => '操作失败!', 'errcode' => 324563]);
        }
    }

    /**
     * deleteMenu 删除企业应用自定义菜单
     *
     * @author chenchao
     *
     * @param int $agent_id 企业应用ID
     *
     * @return string
     */
    public function deleteMenu($agent_id)
    {
        if (intval($agent_id) < 0) {
            return json_encode(['success' => false, 'errmsg' => 'agent_id 必须大于0!', 'errcode' => 654732]);
        }

        $request_url   = sprintf(self::URL_DELETE, $this->token, $agent_id);
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
            return json_encode(['success' => false, 'errmsg' => '操作失败!', 'errcode' => 654571]);
        }
    }

    /**
     * getMenu 根据应用ID获取其自定义菜单
     *
     * @author chenchao
     *
     * @param int $agent_id 企业应用ID
     *
     * @return string
     */
    public function getMenu($agent_id)
    {
        if (intval($agent_id) < 0) {
            return json_encode(['success' => false, 'errmsg' => 'agent_id 必须大于0!', 'errcode' => 569282]);
        }

        $request_url   = sprintf(self::URL_GET, $this->token, $agent_id);
        $json_response = curl($request_url);
        $result        = json_decode($json_response, true);

        if ($result) {
            if (!isset($result['errcode'])) {
                $result['success'] = true;

                return json_encode($result);
            } else {
                $result['success'] = false;

                return json_encode($result);
            }
        } else {
            return json_encode(['success' => false, 'errmsg' => '操作失败!', 'errcode' => 876632]);
        }
    }

}