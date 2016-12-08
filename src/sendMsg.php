<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/23 0023
 * Time: 下午 3:00
 */

namespace Sdxapp\Wxcorp;

use Sdxapp\AnxinPro\AnxinException;
use Sdxapp\Log;

/**
 * Class WxcorpSendMsg 消息管理
 *
 * @package Sdxapp\Wxcorp
 */
class WxcorpSendMsg extends WxcorpConfig
{
    const URL_SEND = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=%s";

    //文字消息
    const  MSGTYPE_TEXT = 'text';
    //图片消息
    const  MSGTYPE_IMAGE = "image";
    //语音
    const  MSGTYPE_VOICE = "voice";
    //视频
    const  MSGTYPE_VIDEO = "video";
    //普通文件
    const  MSGTYPE_FILE = "file";
    //图文
    const  MSGTYPE_NEWS = "news";
    const  MSGTYPE_MPNEWS = "mpnews";

    //给所有人发送消息
    const ALL_USER = '@all';

    private $toUser;
    private $toParty;
    private $toTag;
    private $safe = 0;
    private $jsonData = [];

    /**
     * @param string $toUser 目标用户
     */
    public function setToUser($toUser)
    {
        $this->toUser = $this->parseUser($toUser);

        return $this;
    }

    /**
     * @param string $toParty
     */
    public function setToParty($toParty)
    {
        $this->toParty = $this->parseUser($toParty);

        return $this;
    }

    /**
     * @param string $toTag
     */
    public function setToTag($toTag)
    {
        $this->toTag = $this->parseUser($toTag);

        return $this;
    }

    /**
     * @desc   clear 清空
     * @author chenmingming
     * @return $this
     */
    public function clear()
    {
        $this->toParty  = '';
        $this->toUser   = '';
        $this->toTag    = '';
        $this->safe     = 0;
        $this->jsonData = [
            'agentid' => $this->agent_id,
        ];

        return $this;
    }

    /**
     * @param int $safe 是否保密
     */
    public function setSafe($safe = 0)
    {
        $this->safe = $safe;

        return $this;
    }

    /**
     * @desc   sendText 发送文字消息
     * @author chenmingming
     *
     * @param string $content 消息内容
     *
     * @return string
     */
    public function sendText($content)
    {
        $this->jsonData            = [
            'text' => ['content' => $content],
        ];
        $this->jsonData['msgtype'] = self::MSGTYPE_TEXT;

        return $this->send();
    }

    /**
     * @desc   send
     * @author chenmingming
     *
     *
     * @return string
     * @throws WxcorpException
     */
    private function send()
    {
        $this->getUser();
        $this->jsonData['agentid'] = $this->agent_id;
        $json_response             = $this->curlJson(sprintf(self::URL_SEND, $this->getAccessToken()), $this->jsonData);

        $this->log($json_response, __METHOD__);
        $result = json_decode($json_response, true);
        if ($result) {
            if ($result['errcode'] == 0) {
                return $result;
            } else {
                throw new WxcorpException($result['errmsg'], $result['errmsg']);
            }
        } else {
            throw new WxcorpException('请求发送数据结果为空', 'REQUEST_EMPTY');
        }
    }

    /**
     * @desc   curlJson
     * @author chenmingming
     *
     * @param string $url  请求地址
     * @param mixed  $data 发送的数据
     *
     * @return mixed
     */
    private function curlJson($url, $data)
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
     * @desc   setUser
     * @author chenmingming
     * @return void
     */
    private function getUser()
    {
        unset($this->jsonData['totag']);
        unset($this->jsonData['toparty']);
        unset($this->jsonData['touser']);
        if ($this->toUser == self::ALL_USER) {
            $this->jsonData['touser'] = self::ALL_USER;

        } else {
            $this->toUser && $this->jsonData['touser'] = $this->toUser;
            $this->toTag && $this->jsonData['toparty'] = $this->toTag;
            $this->toParty && $this->jsonData['totag'] = $this->toParty;
        }

    }

    /**
     * sendImage 发送图片
     *
     * @author chenchao
     *
     * @param int $media_id 媒体文件id，可以调用上传临时素材或者永久素材接口获取
     *
     * @return array
     */
    public function sendImage($media_id)
    {
        $this->jsonData = [
            self::MSGTYPE_IMAGE => ['media_id' => $media_id],
            'msgtype'           => self::MSGTYPE_IMAGE,
        ];
        $this->send();
    }

    /**
     * sendVoice 发送语音
     *
     * @author chenchao
     *
     * @param int $media_id 媒体文件id，可以调用上传临时素材或者永久素材接口获取
     *
     * @return string
     */
    public function sendVoice($media_id)
    {

        $this->jsonData = [
            self::MSGTYPE_VOICE => ['media_id' => $media_id],
            'msgtype'           => self::MSGTYPE_VOICE,
        ];
        $this->send();
    }

    /**
     * sendVideo 发送视频
     *
     * @author chenchao
     *
     * @param int    $media_id    媒体文件id，可以调用上传临时素材或者永久素材接口获取
     * @param string $title       视频消息的标题
     * @param string $description 视频消息的描述
     *
     * @return string
     */
    public function sendVideo($media_id, $title = '', $description = '')
    {
        $this->jsonData = [
            self::MSGTYPE_VOICE => [
                'media_id' => $media_id,
            ],
            'msgtype'           => self::MSGTYPE_VOICE,
        ];
        $this->send();

        if ($title) {
            $this->jsonData[self::MSGTYPE_VIDEO]['title'] = $title;
        }
        if ($description) {
            $this->jsonData[self::MSGTYPE_VIDEO]['description'] = $description;
        }
        $this->send();
    }

    /**
     * sendFile 发送文件
     *
     * @author chenchao
     *
     * @param int $media_id 媒体文件id，可以调用上传临时素材或者永久素材接口获取
     *
     * @return array
     */
    public function sendFile($media_id)
    {
        $this->jsonData = [
            self::MSGTYPE_FILE => ['media_id' => $media_id],
            'msgtype'          => self::MSGTYPE_FILE,
        ];
        $this->send();
    }

    /**
     * sendNews 发送news信息
     *
     * @author chenchao
     *
     * @return string
     * @throws WxcorpException
     */
    public function sendNews()
    {
        $this->jsonData = [
            self::MSGTYPE_NEWS => ['articles' => $this->articles],
            'msgtype'          => self::MSGTYPE_FILE,
        ];
        $this->send();
    }

    /**
     * sendMpnewsByMediaID 通过media_id发送图文消息
     *
     * @author chenchao
     *
     * @param int    $agent_id 企业应用的id
     * @param int    $media_id 媒体文件id，可以调用上传临时素材或者永久素材接口获取
     * @param string $to_user  成员ID列表，一维数组传递['1', '2', ...]，最多1000个，默认发送给全部成员
     * @param array  $to_party 部门ID列表，一维数组传递['1', '2', ...]，最多1000个
     * @param array  $to_tag   标签ID列表，一维数组传递['1', '2', ...]，最多1000个
     * @param int    $safe     表示是否是保密消息，0表示否，1表示是，默认0
     *
     * @return string
     */
    public function sendMpnewsByMediaID($agent_id, $media_id, $to_user = "@all", $to_party = [], $to_tag = [], $safe = 0)
    {
        if (intval($agent_id) < 0 || empty($media_id)) {
            return json_encode(['success' => false, 'errmsg' => 'agent_id 或 media_id 为空!', 'errcode' => 265789]);
        }

        $json_response = $this->sendImageVoiceFileMpnews($agent_id, $media_id, WxcorpMaterial:: MSGTYPE_MPNEWS, $to_user, $to_party, $to_tag, $safe);
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
            return json_encode(['success' => false, 'errmsg' => '发送失败 !', 'errcode' => 282374]);
        }
    }

    /**
     * sendImageVoiceFileMpnews 发送图片、文件、音频、图文消息
     *
     * @author chenchao
     *
     * @param int    $agent_id 企业应用的id
     * @param int    $media_id 媒体文件id，可以调用上传临时素材或者永久素材接口获取
     * @param string $msg_type 消息类型，取值范围为Material类中的类常量
     * @param string $to_user  成员ID列表，一维数组传递['1', '2', ...]，最多1000个，默认发送给全部成员
     * @param array  $to_party 部门ID列表，一维数组传递['1', '2', ...]，最多1000个
     * @param array  $to_tag   标签ID列表，一维数组传递['1', '2', ...]，最多1000个
     * @param int    $safe     表示是否是保密消息，0表示否，1表示是，默认0
     *
     * @return bool
     */
    private function sendImageVoiceFileMpnews($agent_id, $media_id, $msg_type, $to_user = "@all", $to_party = [], $to_tag = [], $safe = 0)
    {
        if (intval($agent_id) < 0 || empty($media_id) || empty($msg_type)) {
            return false;
        }

        $data = ['agentid' => $agent_id];
        switch ($msg_type) {
            case WxcorpMaterial:: MSGTYPE_FILE :
                $data['file'] = ['media_id' => $media_id];
                break;
            case WxcorpMaterial:: MSGTYPE_IMAGE :
                $data['image'] = ['media_id' => $media_id];
                break;
            case WxcorpMaterial:: MSGTYPE_VOICE :
                $data['voice'] = ['media_id' => $media_id];
                break;
            case WxcorpMaterial:: MSGTYPE_MPNEWS:
                $data['mpnews'] = ['media_id' => $media_id];
                break;
            default :
                return false;
                break;
        }
        $data['msgtype'] = $msg_type;
        $data['touser']  = $this->getToUserList($to_user);
        if (($tmp = $this->getToList($to_party))) {
            $data['toparty'] = $tmp;
        }
        if (($tmp = $this->getToList($to_tag))) {
            $data['totag'] = $tmp;
        }
        if (intval($safe) == 1) {
            $data['safe'] = 1;
        }

        $request_url = sprintf(self::URL_SEND, $this->token);
        $result      = curlPostJson($request_url, $data);

        return $result;
    }

    /**
     * getToUserList 根据成员ID列表返回微信需要的成员列表格式字符串
     *
     * @author chenchao
     *
     * @param string $to_user 成员ID列表，一维数组传递['1', '2', ...]，最多1000个，默认发送给全部成员
     *
     * @return string
     */
    private function getToUserList($to_user = "@all")
    {
        $data = "";
        if (is_array($to_user)) {
            $first = true;
            foreach ($to_user as $value) {
                if ($first) {
                    $data  = $value;
                    $first = false;
                } else {
                    $data .= "|" . $value;
                }
            }
        } else {
            $data = $to_user;
        }

        return $data;
    }

    /**
     * getToList 根据传入的数组，返回微信需要的列表格式字符串
     *
     * @author chenchao
     *
     * @param array|string $toArray 一维数组传递['1', '2', ...]，最多1000个
     *
     * @return string
     */
    private function parseUser($toArray)
    {
        if ($toArray) {
            if (is_array($toArray))
                return implode('|', $toArray);
            elseif (is_string($toArray)) {
                return $toArray;
            }
        }

        return '';
    }
}