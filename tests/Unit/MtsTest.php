<?php

namespace Tests\Unit;


use Bright\Aliyun\Mts\GreenService;
use Bright\Aliyun\Mts\MtsService;
use Bright\Aliyun\Oss\FileContract;
use PHPUnit\Framework\TestCase;

class MtsTest extends TestCase
{

    /**
     * @var GreenService
     */
    public $mtsService;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        /**
         * ALIYUN_MTS_REGION_ID=cn-shanghai
         * ALIYUN_MTS_ACCESS_ID=
         * ALIYUN_MTS_ACCESS_KEY=
         * ALIYUN_MTS_PIPELINE_ID=3500393f3f5b4a9c99ee078d550eed90
         */
        $mtsRegion = 'cn-shanghai';
        $accessKeyId = '';
        $accessKeySecret = '';
        $this->mtsService = new MtsService($accessKeyId, $accessKeySecret, $mtsRegion,
            '3500393f3f5b4a9c99ee078d550eed90');
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSubmitSyncSnapshotJob()
    {
        $input = new FileContract('oss-cn-shanghai', 'bucket-test-xyj',
            'testing/WeChatSight4.mp4');
        $output = new FileContract('oss-cn-shanghai', 'bucket-test-xyj',
            'testing/WeChatSight4_cover.png');
        $result = $this->mtsService->submitSyncSnapshotJob($input, $output);
        print_r($result->getBody());
//        if($result->isSuccess()){
//            print_r($result->getBody());
//        }else{
//            print_r('failed');
//            print_r($result->getBody());
//        }

        $this->assertEquals('Success', $result->State);
    }

    public function testSubmitMediaInfoJob()
    {
        $input = new FileContract('oss-cn-shanghai', 'bucket-test-xyj',
            'testing/WeChatSight4.mp4');
        $result = $this->mtsService->submitMediaInfoJob($input);
        $this->assertEquals(195.350000, $result->MediaInfoJob->Properties->Duration);
    }

    public function testSubmitOneOutputSyncTranscodeJob()
    {
        $input = new FileContract('oss-cn-shanghai', 'bucket-test-xyj',
            'testing/WeChatSight4.mp4');

        $output = new FileContract('oss-cn-shanghai', 'bucket-test-xyj',
            'testing/WeChatSight4_tran.mp4');
        $result = $this->mtsService->submitOneOutputSyncTranscodeJob($input, $output, 'S00000001-200020');
        var_dump($result);
        $this->assertEquals('TranscodeSuccess', $result->State);
    }

}
