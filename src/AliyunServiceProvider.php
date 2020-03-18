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
use Bright\Aliyun\Sms\Sms;
use Illuminate\Support\Facades\Storage;
use OSS\OssClient;
use League\Flysystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use ApolloPY\Flysystem\AliyunOss\Plugins\PutFile;
use ApolloPY\Flysystem\AliyunOss\Plugins\SignedDownloadUrl;

class AliyunServiceProvider extends ServiceProvider
{
    protected $services = [
        'oss',
        'dysms'
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * 配置文件
         */
        $source = realpath(__DIR__ . '/config/aliyun.php');
        $this->publishes([
            $source => config_path('aliyun.php'),
        ]);

        /**
         * 注册 Oss File System
         */
        Storage::extend('oss', function ($app, $options) {
            $config = config('aliyun.oss');
            $accessId = $config['access_key_id'];
            $accessKey = $config['access_key_secret'];
            $endPoint = $config['location'].'.aliyuncs.com';
            $bucket = $config['bucket'];
            $prefix = null;
            if (isset($config['prefix'])) {
                $prefix = $config['prefix'];
            }
            $client = new OssClient($accessId, $accessKey, $endPoint);
            $adapter = new AliyunOssAdapter($client, $endPoint, $bucket, $prefix, $options);
            $filesystem = new Filesystem($adapter);
//            $filesystem->addPlugin(new PutFile());
//            $filesystem->addPlugin(new SignedDownloadUrl());
            return $filesystem;
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Oss
         */
        $this->app->singleton("aliyun.oss", function ($app) {
            $config = config('aliyun.oss');
            return new OssClient($config['access_key_id'], $config['access_key_secret'],
                $config['location'] . '.aliyuncs.com');
        });
        $this->app->singleton("aliyun.oss.post", function ($app) {
            $config = config('aliyun.oss');
            return new PostService($config['access_key_id'], $config['access_key_secret'], $config['callback'],
                $config['location'].'.aliyuncs.com', $config['bucket']);
        });
        /**
         * 短信服务
         */
        $this->app->singleton("aliyun.dysms", function ($app) {
            $config = config('aliyun.dysms');
            return new Sms($config['access_key_id'], $config['access_key_secret'], $config['region']);
        });
        /**
         * 媒体转码
         */
        $this->app->singleton("aliyun.mts", function ($app) {
            $config = config('aliyun.mts');
            return new Mts\MtsService($config['access_key_id'], $config['access_key_secret'], $config['region_id'],
                $config['default_pipeline_id'], $config['default_transcode_template_id']);
        });
    }


}