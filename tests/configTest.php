<?php
/**
 * Created by PhpStorm.
 * User: chenmingming
 * Date: 2016/12/8
 * Time: 14:29
 */

namespace mmxs\wxcorp\tests;

use mmxs\wxcorp\config;
use mmxs\wxcorp\sendMsg;

class configTest extends \PHPUnit_Framework_TestCase
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
        $obj    = new config('poke', $config);
    }

    public function test2()
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
            'callback'   => [
            ],
        ];
        $obj    = new sendMsg('poke', $config);

        $obj->setToUser('chenmingming')
            ->sendText('hello world');

    }
}
