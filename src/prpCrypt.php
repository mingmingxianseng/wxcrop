<?php
/**
 * Created by PhpStorm.
 * User: chenmingming
 * Date: 2016/12/7
 * Time: 11:50
 */

namespace mmxs\wxcorp;

class PrpCrypt
{
    public $key;

    public function __construct($k)
    {
        $this->key = base64_decode($k . "=");
    }

    /**
     * 对明文进行加密
     *
     * @param string $text 需要加密的明文
     *
     * @return string 加密后的密文
     */
    public function encrypt($text, $corpid)
    {

        //获得16位随机字符串，填充到明文之前
        $random = $this->getRandomStr();
        $text   = $random . pack("N", strlen($text)) . $text . $corpid;
        // 网络字节序
        mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $iv     = substr($this->key, 0, 16);
        //使用自定义的填充方式对明文进行补位填充
        $pkc_encoder = new PKCS7Encoder;
        $text        = $pkc_encoder->encode($text);
        mcrypt_generic_init($module, $this->key, $iv);
        //加密
        $encrypted = mcrypt_generic($module, $text);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);

        //print(base64_encode($encrypted));
        //使用BASE64对加密后的字符串进行编码
        return base64_encode($encrypted);

    }

    /**
     * 对密文进行解密
     *
     * @param string $encrypted 需要解密的密文
     *
     * @return string 解密得到的明文
     */
    public function decrypt($encrypted, $corpid)
    {

        //使用BASE64对需要解密的字符串进行解码
        $ciphertext_dec = base64_decode($encrypted);
        $module         = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $iv             = substr($this->key, 0, 16);
        mcrypt_generic_init($module, $this->key, $iv);

        //解密
        $decrypted = mdecrypt_generic($module, $ciphertext_dec);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);

        //去除补位字符
        $result = PKCS7Encoder::decode($decrypted);
        //去除16位随机字符串,网络字节序和AppId
        if (strlen($result) < 16)
            return "";
        $content     = substr($result, 16, strlen($result));
        $len_list    = unpack("N", substr($content, 0, 4));
        $xml_len     = $len_list[1];
        $xml_content = substr($content, 4, $xml_len);
        $from_corpid = substr($content, $xml_len + 4);
        if ($from_corpid != $corpid)
            throw new WxcorpException('decrypt failed');

        return $xml_content;

    }

    /**
     * 随机生成16位字符串
     *
     * @return string 生成的字符串
     */
    function getRandomStr()
    {

        $str     = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max     = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }

        return $str;
    }
}

/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
    public static $block_size = 32;

    /**
     * 对需要加密的明文进行填充补位
     *
     * @param string $text 需要进行填充补位操作的明文
     *
     * @return string 补齐明文字符串
     */
    static function encode($text)
    {
        $block_size  = PKCS7Encoder::$block_size;
        $text_length = strlen($text);
        //计算需要填充的位数
        $amount_to_pad = PKCS7Encoder::$block_size - ($text_length % PKCS7Encoder::$block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = $block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp     = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }

        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     *
     * @param string $text 解密后的明文
     *
     * @return string 删除填充补位后的明文
     */
    static function decode($text)
    {

        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > PKCS7Encoder::$block_size) {
            $pad = 0;
        }

        return substr($text, 0, (strlen($text) - $pad));
    }

}