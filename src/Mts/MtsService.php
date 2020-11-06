<?php

namespace Bright\Aliyun\Mts;

//include_once 'Core/Config.php';

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Result\Result;
use AlibabaCloud\Mts as Mts;
use Bright\Aliyun\Oss\FileContract as OssFile;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
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
class MtsService extends BaseServiceProvider
{
    /**
     * Mts 服务中心（华东1，华东2，华北1等），例如：cn-hangzhou
     * @var
     */
    public $mtsRegion = 'cn-shanghai';


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
     * 转码所用管道id
     * @var
     */
    public $pipelineId;

    /**
     * 转码模板id
     * @var
     */
    public $transcodeTemplateId;
//
//
//    /**
//     * 水印模板id
//     * @var
//     */
//    public $watermark_template_id;
//
//
//    /**
//     * 输入媒体OSS Bucket
//     * @var
//     */
//    public $input_bucket;
//
//
//    /**
//     * 输出媒体OSS Bucket
//     * @var
//     */
//    public $output_bucket;


    /**
     * @var
     */
    public $client = null;

    /**
     * MtsService constructor.
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @param string $regionId
     * @param string $pipelineId
     * @param string $transcodeTemplateId
     */
    public function __construct(
        string $accessKeyId,
        string $accessKeySecret,
        string $regionId,
        string $pipelineId = '',
        string $transcodeTemplateId = ''
    ) {
        $this->mtsRegion = $regionId;
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $this->pipelineId = $pipelineId;
        $this->transcodeTemplateId = $transcodeTemplateId;
//        $this->watermark_template_id = env('ALIYUN_MTS_WATERMARK_TEMPLATE_ID', '');

    }

    /**
     * @return mixed
     */
    public function getPipelineId()
    {
        return $this->pipelineId;
    }

    /**
     * @param mixed $pipelineId
     */
    public function setPipelineId($pipelineId)
    {
        $this->pipelineId = $pipelineId;
    }

    /**
     * @return mixed
     */
    public function getTranscodeTemplateId()
    {
        return $this->transcodeTemplateId;
    }

    /**
     * @param mixed $transcodeTemplateId
     */
    public function setTranscodeTemplateId($transcodeTemplateId)
    {
        $this->transcodeTemplateId = $transcodeTemplateId;
    }

    /**
     * @return \DefaultAcsClient
     */
    protected function getClient()
    {
        $this->client = AlibabaCloud::accessKeyClient($this->accessKeyId, $this->accessKeySecret);
        $this->client->regionId($this->mtsRegion)->asDefaultClient();
//        if ($this->client == null) {
//            $profile = \DefaultProfile::getProfile($this->mtsRegion, $this->accessKeyId,
//                $this->accessKeySecret);
//            $this->client = new \DefaultAcsClient($profile);
//        }
        return $this->client;
    }


    /**
     * 提交同步截图作业
     * 截图作业由输入文件及截图配置构成，得到输入文件按截图配置截取的图片。
     * @param OssFile $input
     * @param OssFile $output
     * @param Integer $time 截取视频第{$time}毫秒的图片
     * @return \AlibabaCloud\Client\Result\Result
     *
     * https://help.aliyun.com/document_detail/29232.html
     */
    public function submitSyncSnapshotJob(OssFile $input, OssFile $output, $time = 5000): Result
    {
        $snapshotConfig = array(
            'OutputFile' => $output->toArray(),
            'Time' => $time
        );
        return $this->request('SubmitSnapshotJob', [
            'RegionId' => $this->mtsRegion,
            'SnapshotConfig' => json_encode($snapshotConfig),
            'Input' => json_encode($input->toArray()),
        ]);
    }


    /**
     * 转码工作流程
     * @param $input_file
     * @param $watermark_file
     *
     * @deprecated
     */
    public function transcodeJobFlow($input_file, $watermark_file)
    {
        $this->systemTemplateJobFlow($input_file, $watermark_file);

        $this->userCustomTemplateJobFlow($input_file, $watermark_file);
    }


    /**
     * 系统预置模板转码流程
     * @param $input_file
     * @param $watermark_file
     *
     * @deprecated
     */
    public function systemTemplateJobFlow($input_file, $watermark_file)
    {
        $analysis_id = $this->submitAnalysisJob($input_file, $this->pipeline_id);
        $analysis_job = $this->waitAnalysisJobComplete($analysis_id);
        $template_ids = $this->getSupportTemplateIds($analysis_job);

        # 可能会有多个系统模板，这里采用推荐的第一个系统模板进行转码
        $transcode_job_id = $this->submitTranscodeJob($input_file, $watermark_file, $template_ids[0]);
        $transcode_job = $this->waitTranscodeJobComplete($transcode_job_id);

        print 'Transcode success, the target file url is http://' .
            $transcode_job->{'Output'}->{'OutputFile'}->{'Bucket'} . '.' .
            $transcode_job->{'Output'}->{'OutputFile'}->{'Location'} . '.aliyuncs.com/' .
            urldecode($transcode_job->{'Output'}->{'OutputFile'}->{'Object'}) . "\n";
    }


    /**
     * 预置模板分析作业
     * 预置模板分析作业由输入文件及分析配置构成，分析得到可用的预置模板。
     * @param $input_file
     * @return mixed
     *
     *
     * @deprecated
     */
    public function submitAnalysisJob($input_file)
    {
        $request = new Mts\SubmitAnalysisJobRequest();
        $request->setAcceptFormat('JSON');
        $inputFile = [
            'Bucket' => $this->input_bucket,
            'Location' => $this->oss_region,
            'Object' => $input_file
        ];
        $request->setInput(json_encode($inputFile));
        $request->setPriority(5);
        $request->setUserData('SubmitAnalysisJob userData');
        $request->setPipelineId($this->pipeline_id);
        $response = $this->client->getAcsResponse($request);

        return $response->{'AnalysisJob'}->{'Id'};
    }


    /**
     * 返回分析作业分析结果
     * @param $analysis_id
     * @return null
     *
     * @deprecated
     */
    public function waitAnalysisJobComplete($analysis_id)
    {
        while (true) {
            $request = new Mts\QueryAnalysisJobListRequest();
            $request->setAcceptFormat('JSON');
            $request->setAnalysisJobIds($analysis_id);

            $response = $this->client->getAcsResponse($request);
            $state = $response->{'AnalysisJobList'}->{'AnalysisJob'}[0]->{'State'};
            if ($state != 'Success') {
                if ($state == 'Submitted' or $state == 'Analyzing') {
                    sleep(5);
                } elseif ($state == 'Fail') {
                    print 'AnalysisJob is failed!';
                    return null;
                }
            } else {
                return $response->{'AnalysisJobList'}->{'AnalysisJob'}[0];
            }
        }
        return null;
    }


    /**
     * 返回可供选择的预置静态模板id
     * 通过分析作业，对input_file动态推荐合适的预制模板
     * @param $analysis_job
     * @return array
     *
     * @deprecated
     */
    public function getSupportTemplateIds($analysis_job)
    {
        $result = array();

        foreach ($analysis_job->{'TemplateList'}->{'Template'} as $template) {
            $result[] = $template->{'Id'};
        }

        return $result;
    }

    /**
     * 提交同步单输出转码作业
     *
     * @param OssFile $input
     * @param OssFile $output
     * @param $templateId
     * @return mixed
     * @throws \Exception
     */
    public function submitOneOutputSyncTranscodeJob(OssFile $input, OssFile $output, $templateId)
    {
        $transcodeResponse = $this->submitOneOutputTranscodeJob($input, $output, $templateId);
        return $this->waitTranscodeJobComplete($transcodeResponse->JobResultList->JobResult[0]->Job->JobId);
    }

    /**
     * 提交单输出转码作业
     *
     * @param OssFile $input
     * @param OssFile $output
     * @param $templateId
     * @return mixed
     * @throws \Exception
     */
    public function submitOneOutputTranscodeJob(OssFile $input, OssFile $output, $templateId)
    {
        if (empty($output)) {
            throw new \Exception('输出数组不能为空');
        }
        $outputsOption = array();
        $outputsOption[] = [
            'OutputObject' => $output->object,
            'TemplateId' => $templateId,
            //     'WaterMarks' => $watermark_config
        ];

        return $this->request('SubmitJobs', [
            'RegionId' => $this->mtsRegion,
            'Outputs' => json_encode($outputsOption),
            'OutputBucket' => $output->bucket,
            'PipelineId' => $this->pipelineId,
            'Input' => json_encode($input->toArray()),
            'OutputLocation' => $output->location,
        ]);
    }

    /**
     * 提交转码作业
     *
     * 一个转码输出会生成一个转码作业，接口返回转码作业列表。作业会添加到管道中被调度执行，
     * 执行完成后需要调用“查询转码作业”接口轮询作业执行结果，也可使用异步通知机制。
     * 使用预置模板对输入文件进行转码，须要先调用“提交模板分析作业”接口（SubmitAnalysisJob），
     * 分析作业成功完成后可以通过调用“查询模板分析作业”接口（QueryAnalysisJobList）获取该输入文件的可用预置模版列表。
     * 若提交的转码作业中指定的预置模板不在可用的预置模板列表中，则转码作业会失败。
     *
     * @param OssFile $input
     * @param OssFile[] $outputs Bucket 和 Location 只使用数组第一个元素的 Bucket 和 Location
     * @param $templateId
     * @return mixed
     */
    public function submitTranscodeJob(OssFile $input, array $outputs, $templateId)
    {
        //水印设置
        //  $watermark_config = array();
        //   $watermark_config[] = array(
        //       'InputFile' => json_encode($watermark_file),
        //       'WaterMarkTemplateId' => $this->watermark_template_id
        //   );

        if (empty($outputs)) {
            throw new \Exception('输出数组不能为空');
        }

        $request = new Mts\SubmitJobsRequest();
        $outputsOption = array();
        $request->setOutputBucket($outputs[0]->bucket);
        $request->setOutputLocation($outputs[0]->location);
        foreach ($outputs as $output) {
            $outputsOption[] = [
                'OutputObject' => $output->object,
                'TemplateId' => $templateId,
                //     'WaterMarks' => $watermark_config
            ];
        }

        $request->setAcceptFormat('JSON');
        $request->setInput(json_encode($input->toArray()));
        $request->setOutputs(json_encode($outputs));
        $request->setPipelineId($this->pipelineId);

        $response = $this->client->getAcsResponse($request);
        return $response;
    }


    /**
     * 返回转码作业结果
     * @param $transcodeJobId
     * @return null
     */
    public function waitTranscodeJobComplete($transcodeJobId)
    {
        while (true) {
            $response = $this->request('QueryJobList',[
                'RegionId' => $this->mtsRegion,
                'JobIds' => $transcodeJobId,
            ])->toArray();
            $state = $response['JobList']['Job'][0]]['State'];
            if ($state != 'TranscodeSuccess') {
                if ($state == 'Submitted' or $state == 'Transcoding') {
                    sleep(1);
                } elseif ($state == 'TranscodeFail') {
                    print 'Transcode is failed!';
                    return null;
                }
            } else {
                return $response['JobList']['Job'][0];
            }
        }
        return null;
    }


    /**
     * 用户自定义模板转码流程
     * @param $input_file
     * @param $watermark_file
     */
    public function userCustomTemplateJobFlow($input_file, $watermark_file)
    {
        $transcode_job_id = $this->submitTranscodeJob($input_file, $watermark_file, $this->transcode_template_id);
        $transcode_job = $this->waitTranscodeJobComplete($transcode_job_id);

        print 'Transcode success, the target file url is http://' .
            $transcode_job->{'Output'}->{'OutputFile'}->{'Bucket'} . '.' .
            $transcode_job->{'Output'}->{'OutputFile'}->{'Location'} . '.aliyuncs.com/' .
            urldecode($transcode_job->{'Output'}->{'OutputFile'}->{'Object'}) . "\n";
    }

    /**
     * 提交媒体信息作业接口，媒体处理服务会对输入文件进行媒体信息分析，同步返回输入文件的媒体信息；可通过“查询媒体信息作业”接口得到媒体信息分析结果。
     *
     * @param OssFile $input
     * @return mixed
     */
    public function submitMediaInfoJob(OssFile $input)
    {

        $result = $this->request('SubmitMediaInfoJob', [
            'RegionId' => $this->mtsRegion,
            'Input' => json_encode($input->toArray())
        ]);

//        $request = new Mts\SubmitMediaInfoJobRequest();
//        $request->setAcceptFormat('JSON');
//        $request->setInput(json_encode($input->toArray()));
//        $request->setUserData('SubmitMediaInfoJob userData');
//        $request->setPipelineId($this->pipelineId);
//        $response = $this->getClient()->getAcsResponse($request);
        return $result;
    }

    protected function request($action, array $query, $method = 'POST'): Result
    {
        $this->getClient();
        $result = AlibabaCloud::rpc()
            ->product('Mts')
            // ->scheme('https') // https | http
            ->version('2014-06-18')
            ->action($action)
            ->method($method)
            ->host("mts.{$this->mtsRegion}.aliyuncs.com")
            ->options([
                'query' => $query,
            ])
            ->request();
        return $result;

    }
}
