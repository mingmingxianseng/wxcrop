<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/23 0023
 * Time: 下午 3:00
 */

namespace Sdxapp\Wxcorp;

/**
 * Class WxcorpChat 会话管理
 *
 * @package Sdxapp\Wxcorp
 */
class WxcorpChat
{
    private $token;

    //单聊
    const RECEIVER_TYPE_SINGLE = "single";
    //群聊
    const RECEIVER_TYPE_GROUP = "group";

    const URL_CREATE = "https://qyapi.weixin.qq.com/cgi-bin/chat/create?access_token=%s";
    const URL_GET = "https://qyapi.weixin.qq.com/cgi-bin/chat/get?access_token=%s&chatid=%s";
    const URL_UPDATE = "https://qyapi.weixin.qq.com/cgi-bin/chat/create?access_token=%s";
    const URL_QUIT = "https://qyapi.weixin.qq.com/cgi-bin/chat/quit?access_token=%s";
    const URL_CLEAR = "https://qyapi.weixin.qq.com/cgi-bin/chat/clearnotify?access_token=%s";
    const URL_SEND = "https://qyapi.weixin.qq.com/cgi-bin/chat/send?access_token=%s";
    const URL_SETMUTE = "https://qyapi.weixin.qq.com/cgi-bin/chat/setmute?access_token=%s";

    /**
     * WxcorpChat 构造函数
     *
     * @param string $token AccessToken
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * createChat 创建会话
     *
     * @author chenchao
     *
     * @param int    $chat_id   会话id
     * @param string $name      会话标题
     * @param string $owner     管理员userid
     * @param array  $user_list 会话成员列表
     *
     * @return string
     */
    public function createChat($chat_id, $name, $owner, $user_list)
    {
        if (empty($chat_id) || empty($name) || empty($owner) || empty($user_list)) {
            return json_encode(['success' => false, 'errmsg' => 'chat_id、name、owner、user_list 不能为空!', 'errcode' => 435457]);
        }
        if (!is_array($user_list)) {
            return json_encode(['success' => false, 'errmsg' => 'user_list 必须是数组!', 'errcode' => 547322]);
        }
        if (!array_search($owner, $user_list)) {
            $user_list[] = $owner;
        }
        if (count($user_list) < 3 || count($user_list) > 1000) {
            return json_encode(['success' => false, 'errmsg' => '成员数量必须在3~1000之间!', 'errcode' => 883927]);
        }

        $data          = ['chatid' => $chat_id, 'name' => $name, 'owner' => $owner, 'userlist' => $user_list];
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
            return json_encode(['success' => false, 'errmsg' => '创建失败!', 'errcode' => 773525]);
        }
    }

    /**
     * getChat 根据会话ID获取会话信息
     *
     * @author chenchao
     *
     * @param int $chat_id 会话ID
     *
     * @return string
     */
    public function getChat($chat_id)
    {
        if (empty($chat_id)) {
            return json_encode(['success' => false, 'errmsg' => 'chat_id 不能为空!', 'errcode' => 118625]);
        }

        $request_url   = sprintf(self::URL_GET, $this->token, $chat_id);
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
            return json_encode(['success' => false, 'errmsg' => '获取失败!', 'errcode' => 232344]);
        }
    }

    /**
     * changeChat 修改会话信息
     *
     * @author chenchao
     *
     * @param int    $chat_id       会话ID
     * @param string $op_user       操作人userid
     * @param bool   $name          标题
     * @param bool   $owner         管理员userid
     * @param array  $add_user_list 会话新增成员列表
     * @param array  $del_user_list 会话退出成员列表
     *
     * @return string
     */
    public function changeChat($chat_id, $op_user, $name = false, $owner = false, $add_user_list = [], $del_user_list = [])
    {
        if (empty($chat_id) || empty($op_user)) {
            return json_encode(['success' => false, 'errmsg' => 'chat_id、op_user 不能为空!', 'errcode' => 543645]);
        }

        $data = ['chatid' => $chat_id, 'op_user' => $op_user];
        if (!empty($name)) {
            $data['name'] = $name;
        }
        if (!empty($owner)) {
            $data['owner'] = $owner;
        }
        if (!empty($add_user_list)) {
            $data['add_user_list'] = $add_user_list;
        }
        if (!empty($del_user_list)) {
            $data['del_user_list'] = $del_user_list;
        }
        $request_url   = sprintf(self::URL_UPDATE, $this->token);
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
            return json_encode(['success' => false, 'errmsg' => '修改失败!', 'errcode' => 983737]);
        }
    }

    /**
     * quitChat退出会话
     *
     * @author chenchao
     *
     * @param int    $chat_id 会话ID
     * @param string $op_user 操作人userid
     *
     * @return string
     */
    public function quitChat($chat_id, $op_user)
    {
        if (empty($chat_id) || empty($op_user)) {
            return json_encode(['success' => false, 'errmsg' => 'Chat_id、op_user 不能为空!', 'errcode' => 623286]);
        }

        $data          = ['chatid' => $chat_id, 'op_user' => $op_user];
        $request_url   = sprintf(self::URL_QUIT, $this->token);
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
            return json_encode(['success' => false, 'errmsg' => '退出失败!', 'errcode' => 732744]);
        }
    }

    /**
     * clearNotify 清除会话未读状态
     *
     * @author chenchao
     *
     * @param string $op_user 会话所有者的userid
     * @param string $type    会话类型：single|group，分别表示：单聊|群聊
     * @param string $id      会话值，为userid|chatid，分别表示：成员id|会话id
     *
     * @return string
     */
    public function clearNotify($op_user, $type, $id)
    {
        if (empty($op_user) || empty($id)) {
            return json_encode(['success' => false, 'errmsg' => 'op_user、 id 不能为空!', 'errcode' => 737362]);
        }
        if ($type != self::RECEIVER_TYPE_SINGLE && $type != self::RECEIVER_TYPE_GROUP) {
            return json_encode(['success' => false, 'errmsg' => 'type 不合法!', 'errcode' => 993722]);
        }

        $data          = ['op_user' => $op_user, 'chat' => ['type' => $type, 'id' => $id]];
        $request_url   = sprintf(self::URL_CLEAR, $this->token);
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
            return json_encode(['success' => false, 'errmsg' => '操作失败!', 'errcode' => 663736]);
        }
    }

    /**
     * sendText 发送文字及表情消息
     *
     * @author chenchao
     *
     * @param string $type    接收人类型：single|group，分别表示：单聊|群聊
     * @param string $id      接收人的值，为userid|chatid，分别表示：成员id|会话id
     * @param string $sender  发送人的userid
     * @param string $content 消息内容
     *
     * @return string
     */
    public function sendText($type, $id, $sender, $content)
    {
        if (empty($id) || empty($content) || empty($sender)) {
            return json_encode(['success' => false, 'errmsg' => 'id、sender、content 不能为空!', 'errcode' => 756433]);
        }
        if ($type != self::RECEIVER_TYPE_SINGLE && $type != self::RECEIVER_TYPE_GROUP) {
            return json_encode(['success' => false, 'errmsg' => 'type 不合法!', 'errcode' => -2]);
        }

        $result = $this->sendMessage($type, $id, $sender, "text", $content);
        if ($result) {
            if ($result['errcode'] == 0) {
                $result['success'] = true;

                return json_encode($result);
            } else {
                $result['success'] = false;

                return json_encode($result);
            }
        } else {
            return json_encode(['success' => false, 'errmsg' => '发送失败!', 'errcode' => 763622]);
        }
    }

    /**
     * sendImage 发送图片消息
     *
     * @author chenchao
     *
     * @param string $type     接收人类型：single|group，分别表示：单聊|群聊
     * @param string $id       接收人的值，为userid|chatid，分别表示：成员id|会话id
     * @param string $sender   发送人的userid
     * @param string $media_id 图片媒体文件id，可以调用上传素材文件接口获取
     *
     * @return string
     */
    public function sendImage($type, $id, $sender, $media_id)
    {
        if (empty($id) || empty($media_id) || empty($sender)) {
            return json_encode(['success' => false, 'errmsg' => 'id、sender、content 不能为空!', 'errcode' => 545674]);
        }
        if ($type != self::RECEIVER_TYPE_SINGLE && $type != self::RECEIVER_TYPE_GROUP) {
            return json_encode(['success' => false, 'errmsg' => 'type 不合法!', 'errcode' => 234256]);
        }

        $result = $this->sendMessage($type, $id, $sender, "image", $media_id);
        if ($result) {
            if ($result['errcode'] == 0) {
                $result['success'] = true;

                return json_encode($result);
            } else {
                $result['success'] = false;

                return json_encode($result);
            }
        } else {
            return json_encode(['success' => false, 'errmsg' => '发送失败!', 'errcode' => 325656]);
        }
    }

    /**
     * sendFile 发送文件消息
     *
     * @author chenchao
     *
     * @param string $type     接收人类型：single|group，分别表示：单聊|群聊
     * @param string $id       接收人的值，为userid|chatid，分别表示：成员id|会话id
     * @param string $sender   发送人的userid
     * @param string $media_id 图片媒体文件id，可以调用上传素材文件接口获取
     *
     * @return string
     */
    public function sendFile($type, $id, $sender, $media_id)
    {
        if (empty($id) || empty($media_id) || empty($sender)) {
            return json_encode(['success' => false, 'errmsg' => 'id、sender、content 不能为空!', 'errcode' => 567867]);
        }
        if ($type != self::RECEIVER_TYPE_SINGLE && $type != self::RECEIVER_TYPE_GROUP) {
            return json_encode(['success' => false, 'errmsg' => 'type 不合法!', 'errcode' => 324563]);
        }

        $result = $this->sendMessage($type, $id, $sender, "file", $media_id);
        if ($result) {
            if ($result['errcode'] == 0) {
                $result['success'] = true;

                return json_encode($result);
            } else {
                $result['success'] = false;

                return json_encode($result);
            }
        } else {
            return json_encode(['success' => false, 'errmsg' => '发送失败!', 'errcode' => 242457]);
        }
    }

    /**
     * sendMessage 发送消息
     *
     * @author chenchao
     *
     * @param string $type                接收人类型：single|group，分别表示：单聊|群聊
     * @param string $id                  接收人的值，为userid|chatid，分别表示：成员id|会话id
     * @param string $sender              发送人的userid
     * @param string $msgtype             消息类型 text/image/file
     * @param string $content_or_media_id 消息内容 消息类型为text时，该值为文本，消息类型为image或file时，为素材ID
     *
     * @return bool
     */
    private function sendMessage($type, $id, $sender, $msgtype, $content_or_media_id)
    {
        if (empty($id) || empty($sender) || empty($content_or_media_id) || empty($msgtype)) {
            return false;
        }
        if ($type != self::RECEIVER_TYPE_SINGLE && $type != self::RECEIVER_TYPE_GROUP) {
            return false;
        }

        $data = ['receiver' => ['type' => $type, 'id' => $id], 'sender' => $sender, 'msgtype' => $msgtype];
        switch ($msgtype) {
            case 'text':
                $data['text'] = ['content' => $content_or_media_id];
                break;
            case 'image':
                $data['image'] = ['media_id' => $content_or_media_id];
                break;
            case 'file':
                $data['file'] = ['media_id' => $content_or_media_id];
                break;
            default:
                return false;
                break;
        }

        $request_url = sprintf(self::URL_SEND, $this->token);

        $json_response = curlPostJson($request_url, $data);

        return json_decode($json_response, true);
    }

    /**
     * setMute 设置成员新消息免打扰
     *
     * @author chenchao
     *
     * @param array $user_mute_list 成员新消息免打扰参数
     *
     * @return string
     */
    public function setMute($user_mute_list)
    {
        if (empty($user_mute_list)) {
            return json_encode(['success' => false, 'errmsg' => 'user_mute_list 不能为空!', 'errcode' => 342353]);
        }
        if (!is_array($user_mute_list)) {
            return json_encode(['success' => false, 'errmsg' => 'user_mute_list 必须是数组!', 'errcode' => 465643]);
        }

        $data = ['user_mute_list' => $user_mute_list];

        $request_url   = sprintf(self::URL_SETMUTE, $this->token);
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
            return json_encode(['success' => false, 'errmsg' => '设置失败!', 'errcode' => 797563]);
        }
    }
}