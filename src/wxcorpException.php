<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/4 0004
 * Time: 下午 4:17
 */

namespace mmxs\wxcorp;

class wxcorpException extends \Exception
{
    /**
     * @var string 错误码
     */
    private $errno;

    /**
     * @var string 错误详情
     */
    private $detail;

    /**
     * wxcorpException constructor.
     *
     * @param string $message
     * @param string $code
     * @param string $detail 错误详情
     */
    public function __construct($message, $code = '', $detail = '')
    {
        if (is_array($message)) {
            list($msg, $errno) = $message;
            parent::__construct($msg, $code);
            $this->errno = $errno ?: $code;
        } else {
            $this->errno = (string)$code;
            parent::__construct($message, (int)$code);
        }
        $this->detail = $detail;
    }

    /**
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @return string
     */
    public function getErrno()
    {
        return $this->errno;
    }

}