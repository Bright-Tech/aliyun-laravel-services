<?php
/**
 * Created by PhpStorm.
 * User: samxiao
 * Date: 2018/4/4
 * Time: 下午6:03
 */

namespace Bright\Aliyun;

use Bright\Aliyun\Oss\AliyunOssAdapter;
use Bright\Aliyun\Oss\PostService;
use Illuminate\Support\Facades\Storage;
use OSS\OssClient;
use League\Flysystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class AliyunServiceFacade extends ServiceProvider
{
    /**
     * 默认为 Server.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'aliyun.oss';
    }

    /**
     * Oss
     * @return OssClient
     */
    public static function oss()
    {
        return app('aliyun.oss');
    }

    /**
     * Oss Post
     * @return PostService
     */
    public static function ossPost()
    {
        return app('aliyun.oss.post');
    }

    /**
     * Mts
     * @return Mts\GreenService
     */
    public static function Mts()
    {
        return app('aliyun.mts');
    }

    /**
     * Sms
     * @return Sms
     */
    public static function Sms()
    {
        return app('aliyun.dysms');
    }
}
