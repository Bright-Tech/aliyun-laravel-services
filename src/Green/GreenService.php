<?php

namespace Bright\Aliyun\Green;

//include_once 'Core/Config.php';

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Result\Result;
use Bright\Aliyun\BaseService;

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
class GreenService extends BaseService
{
    /**
     * Mts 服务中心（华东1，华东2，华北1等），例如：cn-hangzhou
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
     * @var
     */
    public $client = null;

    /**
     * MtsService constructor.
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @param string $regionId
     */
    public function __construct(
        string $accessKeyId,
        string $accessKeySecret,
        string $regionId
    ) {
        $this->regionId = $regionId;
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
    }

    public function videoAsyncScan(array $scenes, array $tasks, array $audioScenes): Result
    {
        return $this->request('/green/video/asyncscan', [
            'scenes' => $scenes,
            'audioScenes' => $audioScenes,
            'tasks' => $tasks
        ]);

    }

    public function videoAsyncScanResults(array $taskIdArr): Result
    {
        return $this->request('/green/video/results', $taskIdArr);
    }

    protected function request($action, array $body, $method = 'POST'): Result
    {
        $this->getClient();
        $result = AlibabaCloud::roa()
            ->product('Green')
            // ->scheme('https') // https | http
            ->version('2018-05-09')
            ->pathPattern($action)
            ->method($method)
            ->options([
                'query' => [],
            ])
            ->body(json_encode($body))
            ->request();
        return $result;

    }

}
