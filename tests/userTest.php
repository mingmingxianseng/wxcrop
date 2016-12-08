<?php
/**
 * Created by PhpStorm.
 * User: chenmingming
 * Date: 2016/12/8
 * Time: 16:14
 */

namespace mmxs\wxcorp\tests;

use mmxs\wxcorp\user;
use mmxs\wxcorp\wxcorpException;

class userTest extends \PHPUnit_Framework_TestCase
{

    public function test1()
    {
        $config = [
            'name'       => 'anxin',
            //微信公众号相关
            'corpid'     => 'wx0a9547f47e11ed6c',//企业号id
            'corpsecret' => 'GCVBsPQK4ndnqXtDM0doul12H1V-kNwJBG8B4ULoP-nlMwZttCm9gx8KObIL8pRU',//密钥
            'agent_list' => [
                'list' => [
                    'id'     => 0,
                    'name'   => '通讯录小助手',
                    'token'  => 'lG21',
                    'secret' => 'DOGzyTEBLs1SN9cfaaIkef9TG6TTdhx8ADws88CIC8e',
                ],
                'poke' => [
                    'id'     => 1,
                    'name'   => '通讯录小助手',
                    'token'  => 'lG21',
                    'secret' => 'DOGzyTEBLs1SN9cfaaIkef9TG6TTdhx8ADws88CIC8e',
                ],
            ],
        ];
        $obj    = new user('', $config);
        try {
            echo $obj->init('176021687929')->getOpenId();

            echo $obj->init('176021687929')->getName();
        } catch (wxcorpException $e) {
            var_dump($e->getDetail());
        }

    }

}
