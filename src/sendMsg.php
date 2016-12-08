<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/23 0023
 * Time: 下午 3:00
 */

namespace mmxs\wxcorp;

/**
 * Class sendMsg 消息管理
 *
 * @package wxcrop
 */
class sendMsg extends config
{
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
    private $articles;

    /**
     * @param string $toUser 目标用户
     *
     * @return $this
     */
    public function setToUser($toUser)
    {
        $this->toUser = $this->parseUser($toUser);

        return $this;
    }

    /**
     * @param string $toParty 目标分组
     *
     * @return $this
     */
    public function setToParty($toParty)
    {
        $this->toParty = $this->parseUser($toParty);

        return $this;
    }

    /**
     * @param string $toTag 标签
     *
     * @return $this
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
        $this->articles = null;

        return $this;
    }

    /**
     * @param int $safe 是否保密
     *
     * @return $this
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
     * @return array
     * @throws wxcorpException
     */
    private function send()
    {
        $this->getUser();
        $this->jsonData['agentid'] = $this->agent_id;
        $json_response             = $this->curlPostJson(
            "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={$this->getAccessToken()}",
            $this->jsonData
        );

        $this->log($json_response, __METHOD__);
        $this->clear();

        return $this->parseResponse($json_response);
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
     * @desc   setArticle 设置图文
     * @author chenmingming
     *
     * @param string $title       标题
     * @param string $description 描述
     * @param string $url         跳转地址
     * @param string $picurl      图片地址
     */
    public function setArticle($title, $description, $url, $picurl)
    {
        $this->articles[] = [
            'title'       => $title,
            'description' => $description,
            'url'         => $url,
            'picurl'      => $picurl,
        ];

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
     * @param string $title       图文消息标题
     * @param string $description 描述
     * @param string $url         点击跳转地址
     * @param string $picurl      图片地址 不填则不显示图片
     *
     * @return string
     *
     * @throws wxcorpException
     */

    public function sendNews($title = '', $description = '', $url = '', $picurl = '')
    {
        $this->jsonData = [
            self::MSGTYPE_NEWS => ['articles' => $this->articles],
            'msgtype'          => self::MSGTYPE_FILE,
        ];
        $title && $this->jsonData['title'] = $title;
        $description && $this->jsonData['description'] = $description;
        $url && $this->jsonData['url'] = $url;
        $picurl && $this->jsonData['picurl'] = $picurl;
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