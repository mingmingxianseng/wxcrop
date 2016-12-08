<?php
/**
 * Created by PhpStorm.
 * User: chenmingming
 * Date: 2016/12/8
 * Time: 14:08
 */

namespace mmxs\wxcorp;

class callback extends config
{
    /**
     * @desc   verifyIp 验证微信服务器ip
     * @author chenmingming
     * @throws wxcorpException
     */
    public function verifyIp()
    {
        $flag = false;
        foreach ($this->getCallbackIpList() as $ip) {
            if (strpos($ip, '.') > 0) {
                if ($this->ipInNetWork($_SERVER['REMOTE_ADDR'], $ip)) {
                    $flag = true;
                    break;
                }
            } elseif ($ip == $_SERVER['REMOTE_ADDR']) {
                $flag = true;
                break;
            }
        }
        if ($flag === false) {
            throw new wxcorpException('非微信服务器请求', 'WECHAT_IP_INVALID');
        }
    }

    /**
     * ip_in_network 验证ip段
     *
     * @author chenchao
     *
     * @param string $ip      ip地址
     * @param string $network ip地址
     *
     * @return bool
     */
    private function ipInNetWork($ip, $network)
    {
        $ip            = (double)(sprintf("%u", ip2long($ip)));
        $s             = explode('/', $network);
        $network_start = (double)(sprintf("%u", ip2long($s[0])));
        $network_len   = pow(2, 32 - $s[1]);
        $network_end   = $network_start + $network_len - 1;

        if ($ip >= $network_start && $ip <= $network_end) {
            return true;
        }

        return false;
    }

    /**
     * @desc   verify 验证微信服务器
     * @author chenmingming
     * @return string
     * @throws wxcorpException
     */
    public function verify()
    {
        $sVerifyMsgSig    = $_GET['msg_signature'];
        $sVerifyTimeStamp = $_GET['timestamp'];
        $sVerifyNonce     = $_GET['nonce'];
        $sVerifyEchoStr   = $_GET['echostr'];

        $pc        = new prpCrypt($this->secret);
        $signature = $this->getSHA1($this->token, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr);

        if ($signature != $sVerifyMsgSig) {
            throw new wxcorpException('签名非法~', 'SINGATURE_INVALID');
        }

        return $pc->decrypt($sVerifyEchoStr, $this->corpid);
    }
}