<?php

namespace Bright\Aliyun;

//include_once 'Core/Config.php';

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Clients\AccessKeyClient;
use AlibabaCloud\Client\Result\Result;
use Bright\Aliyun\Oss\FileContract as OssFile;
use AlibabaCloud\Mts as Mts;
use phpDocumentor\Reflection\Types\Integer;

/**
 * Media Transcoding Service(Mts) 的客户端类，封装了用户通过Mts API对储存在OSS上的媒体文件的各种操作，
 * 用户通过MtsService实例可以进行Snapshot，Transcode，Template, AnalysisJob, Pipeline等操作，具体
 * 的接口规则可以参考官方Mts API文档
 *
 * Class MtsService
 * @package Mts
 *
 * Link https://help.aliyun.com/document_detail/29232.html
 */

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class BaseService extends BaseServiceProvider
{
    /**
     * 服务中心（华东1，华东2，华北1等），例如：cn-hangzhou
     * @var
     */
    public $regionId = 'cn-shanghai';


    /**
     * 阿里云颁发给用户的访问服务所用的密钥ID。
     * @var
     */
    public $accessKeyId;


    /**
     * 阿里云颁发给用户的访问服务所用的密钥
     * @var
     */
    public $accessKeySecret;




    /**
     * @var AccessKeyClient
     */
    public $client = null;



    /**
     * @return AccessKeyClient
     */
    protected function getClient()
    {
        $this->client = AlibabaCloud::accessKeyClient($this->accessKeyId, $this->accessKeySecret);
        $this->client->regionId($this->regionId)->asDefaultClient();
        return $this->client;
    }


}
