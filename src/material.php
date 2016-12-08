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
 * Class WxcorpMaterial 素材管理
 *
 * @package Sdxapp\Wxcorp
 */
class WxcorpMaterial
{
    private $token;
    //图片
    const MEDIA_TYPE_IMAGE = "image";
    //语音
    const MEDIA_TYPE_VOICE = "voice";
    //视频
    const MEDIA_TYPE_VIDEO = "video";
    //普通文件
    const MEDIA_TYPE_FILE = "file";
    //图文
    const MEDIA_TYPE_MPNEWS = "mpnews";

    const URL_UPLOAD_TEMPORARY = "https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s";
    const URL_UPLOAD_PERMANENT = "https://qyapi.weixin.qq.com/cgi-bin/material/add_material?agentid=%s&access_token=%s&type=%s";
    const URL_UPLOAD_PERMANENT_NEWS = "https://qyapi.weixin.qq.com/cgi-bin/material/add_mpnews?access_token=%s";
    const URL_GET_TEMPORARY_MATERIAL = "https://qyapi.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s";
    const URL_GET_PERMANENT_MATERIAL = "https://qyapi.weixin.qq.com/cgi-bin/material/get?access_token=%s&media_id=%s&agentid=%s";
    const URL_DEL_PERMANENT_MATERIAL = "https://qyapi.weixin.qq.com/cgi-bin/material/del?access_token=%s&media_id=%s&agentid=%s";
    const URL_GET_COUNT = "https://qyapi.weixin.qq.com/cgi-bin/material/get_count?access_token=%s&agentid=%s";
    const URL_GET_LIST = "https://qyapi.weixin.qq.com/cgi-bin/material/batchget?access_token=%s";

    /**
     * WxcorpMaterial 构造函数
     *
     * @param string $token AccessToken
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * uploadTemporaryMaterial 上传临时素材
     *
     * @author chenchao
     *
     * @param string $type      媒体文件类型，分别有图片（image）、语音（voice）、视频（video），普通文件(file)
     * @param string $file_path 要上传文件的绝对路径
     *
     * @return string media_id
     * @throws AnxinException
     */
    public function uploadTemporaryMaterial($type, $file_path)
    {
        if (empty($type) || empty($file_path)) {
            throw new AnxinException('type、file_path 不能为空!', 857563);
        }
        if (!file_exists($file_path)) {
            throw new AnxinException('file_path 路径无效!', 948480);
        }

        $data = ['media' => "@" . $file_path];

        $request_url = sprintf(self::URL_UPLOAD_TEMPORARY, $this->token, $type);

        $json_response = curl($request_url, $data, ['upload-file' => 'upload-file']);
        $result        = json_decode($json_response, true);
        if ($result) {
            if (isset($result['errcode']) && intval($result['errcode']) != 0) {
                throw new AnxinException($result['errmsg'], $result['errcode']);
            } else {
                return $result['media_id'];
            }
        } else {
            throw new AnxinException('上传失败!', 554736);
        }
    }

    /**
     * getTemporaryMaterial 获取临时素材的下载地址
     *
     * @author chenchao
     *
     * @param string $media_id 素材资源标识ID
     *
     * @return string 返回带有素材下载地址地json字符串
     */
    public function getTemporaryMaterial($media_id)
    {
        if (empty($media_id)) {
            return json_encode(['success' => false, 'errmsg' => 'Media_id 不能为空!', 'errcode' => 357865]);
        }

        $request_url = sprintf(self::URL_GET_TEMPORARY_MATERIAL, $this->token, $media_id);

        return json_encode(['success' => true, 'errorcode' => 0, 'url' => $request_url]);
    }

    /**
     * uploadTemporaryMaterial 上传永久素材
     *
     * @author chenchao
     *
     * @param int    $agent_id  企业应用的id
     * @param string $type      媒体文件类型，分别有图片（image）、语音（voice）、视频（video），普通文件(file)
     * @param string $file_path 要上传文件的绝对路径
     *
     * @throws AnxinException
     */
    public function uploadPermanentMaterial($agent_id, $type, $file_path)
    {
        if (empty($type) || empty($file_path)) {
            throw new AnxinException('type、file_path 不能为空!', 324525);
        }
        if (!file_exists($file_path)) {
            throw new AnxinException('file_path 路径无效!', 464564);
        }

        $data          = ['media' => "@" . $file_path];
        $request_url   = sprintf(self::URL_UPLOAD_TEMPORARY, $agent_id, $this->token, $type);
        $json_response = curlPostJson($request_url, $data);
        $result        = json_decode($json_response, true);

        if ($result) {
            if ($result['errcode'] != 0) {
                throw new AnxinException('上传失败!', 765544);
            }
        } else {
            throw new AnxinException('上传失败!', 333578);
        }
    }

    /**
     * getPermanentMaterial 获取永久素材
     *
     * @author chenchao
     *
     * @param string $media_id 素材资源标识ID
     * @param int    $agent_id 企业应用ID
     *
     * @return string 当素材为图文素材时，返回图文消息的json字符串，当素材为其他素材时，返回带有素材下载地址的json字符串
     */
    public function getPermanentMaterial($media_id, $agent_id)
    {
        if (empty($media_id) || intval($agent_id) < 0) {
            return json_encode(['success' => false, 'errmsg' => 'media_id、agentid 不能为空!', 'errcode' => 684433]);
        }

        $request_url = sprintf(self::URL_GET_PERMANENT_MATERIAL, $this->token, $media_id, $agent_id);

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
            return json_encode(['success' => true, 'errorcode' => 0, 'url' => $request_url]);
        }
    }

    /**
     * deletePermanentMaterial 删除永久素材
     *
     * @author chenchao
     *
     * @param string $media_id 素材资源标识ID
     * @param int    $agent_id 企业应用的id
     *
     * @return string
     */
    public function deletePermanentMaterial($media_id, $agent_id)
    {
        if (empty($media_id) || intval($agent_id) < 0) {
            return json_encode(['success' => false, 'errmsg' => 'media_id、agentid 不能为空!', 'errcode' => 927623]);
        }

        $request_url   = sprintf(self::URL_DEL_PERMANENT_MATERIAL, $this->token, $media_id, $agent_id);
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
            return json_encode(['success' => false, 'errmsg' => '删除失败!', 'errcode' => 324567]);
        }
    }

    /**
     * getMaterialCount 根据应用ID获取其下的素材总数以及每种类型素材的数目
     *
     * @author chenchao
     *
     * @param int $agent_id 企业应用的id
     *
     * @return string
     */
    public function getMaterialCount($agent_id)
    {
        if (intval($agent_id) < 0) {
            return json_encode(['success' => false, 'errmsg' => 'agentid 不能为空!', 'errcode' => 658534]);
        }

        $request_url   = sprintf(self::URL_GET_COUNT, $this->token, $agent_id);
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
            return json_encode(['success' => false, 'errmsg' => '查询失败!', 'errcode' => 685463]);
        }
    }

    /**
     * getMaterialList 根据应用ID和素材类型获取素材素材列表
     *
     * @author chenchao
     *
     * @param string $type     素材类型，取值范围为本类的类常量
     * @param int    $agent_id 企业应用的id
     * @param int    $count    返回素材的数量，取值在1到50之间
     * @param int    $offset   从该类型素材的该偏移位置开始返回
     *
     * @return string
     */
    public function getMaterialList($type, $agent_id, $count, $offset = 0)
    {
        if (intval($agent_id) < 0 || empty($type)) {
            return json_encode(['success' => false, 'errmsg' => 'agentid、type 不能为空!', 'errcode' => 453443]);
        }
        if (intval($offset) < 0) {
            return json_encode(['success' => false, 'errmsg' => 'offset 必须大于0!', 'errcode' => 454655]);
        }
        if (intval($count) < 1 || intval($count) > 50) {
            return json_encode(['success' => false, 'errmsg' => 'count 必须在1~50之间!', 'errcode' => 876876]);
        }

        $data          = ['type' => $type, 'agentid' => $agent_id, 'offset' => $offset, 'count' => $count];
        $request_url   = sprintf(self::URL_GET_LIST, $this->token);
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
            return json_encode(['success' => false, 'errmsg' => '获取失败!', 'errcode' => 213453]);
        }
    }

    /**
     * uploadPermanentGraphicMaterial 上传永久图文素材
     *
     * @author chenchao
     *
     * @param  int  $agent_id 企业应用的id
     * @param array $articles 图文消息,二维数组
     *
     * @return string
     */
    public function uploadPermanentGraphicMaterial($agent_id, $articles = [])
    {
        if (empty($agent_id) || empty($articles)) {
            return json_encode(['success' => false, 'errmsg' => 'agent_id、articles 不能为空!', 'errcode' => 754656]);
        }

        $data = ['agentid' => $agent_id, 'mpnews' => ['articles' => $articles]];

        $request_url   = sprintf(self::URL_UPLOAD_PERMANENT_NEWS, $this->token);
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
            return json_encode(['success' => false, 'errmsg' => '上传失败!', 'errcode' => 464332]);
        }
    }
}