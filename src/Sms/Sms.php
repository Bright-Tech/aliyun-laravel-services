<?php
/**
 * Created by PhpStorm.
 * User: samxiao
 * Date: 2018/2/3
 * Time: 下午2:30
 */

namespace Bright\Aliyun\Sms;

use Bright\Aliyun\Core\Config;
use Bright\Aliyun\Core\DefaultAcsClient;
use Bright\Aliyun\Core\Profile\DefaultProfile;
use Bright\Aliyun\Sms\Request\V20170525\SendBatchSmsRequest;
use Bright\Aliyun\Sms\Request\V20170525\SendSmsRequest;

class Sms
{

    //产品名称:云通信流量服务API产品,开发者无需替换
    protected $product = "Dysmsapi";

    //产品域名,开发者无需替换
    protected $domain = "dysmsapi.aliyuncs.com";

    // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
    protected $accessKeyId = "yourAccessKeyId"; // AccessKeyId

    protected $accessKeySecret = "yourAccessKeySecret"; // AccessKeySecret

    // 暂时不支持多Region
    protected $region = "cn-hangzhou";

    // 服务结点
    protected $endPointName = "cn-hangzhou";

    protected $acsClient = null;

    /**
     * Sms constructor.
     * @param $accessKeyId
     * @param $accessKeySecret
     */
    public function __construct($accessKeyId, $accessKeySecret, $region)
    {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $this->region = $region;
        $this->endPointName = $region;

        Config::load();

        //初始化acsClient,暂不支持region化
        $profile = DefaultProfile::getProfile($this->region, $accessKeyId, $accessKeySecret);

        // 增加服务结点
        DefaultProfile::addEndpoint($this->endPointName, $this->region, $this->product, $this->domain);

        // 初始化AcsClient用于发起请求
        $this->acsClient = new DefaultAcsClient($profile);
    }

    /**
     * 发送短信
     *
     * @param string $phoneNumber 必填，设置短信接收号码
     * @param string $signName 必填，设置签名名称，应严格按"签名名称"填写
     * @param string $templateCode 必填，设置模板CODE，
     * @param array $templateParams 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
     * @param string $outId 可选，设置流水号
     * @param string $upExtendCode 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
     * @return mixed
     */
    public function send(
        $phoneNumber,
        $signName,
        $templateCode,
        array $templateParams = [],
        $outId = '',
        $upExtendCode = ''
    ) {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($phoneNumber);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($signName);

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($templateCode);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode($templateParams, JSON_UNESCAPED_UNICODE));

        // 可选，设置流水号
        $request->setOutId($outId);

        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
        $request->setSmsUpExtendCode($upExtendCode);

        // 发起访问请求
        $acsResponse = $this->acsClient->getAcsResponse($request);

        return $acsResponse;
    }

    /**
     * 批量发送短信
     *
     * @param array $phoneNumbers 必填，设置短信接收号码 上限为100个手机号码
     * @param array $signNames 必填:短信签名-支持不同的号码发送不同的短信签名
     * @param string $templateCode 必填，设置模板CODE，
     * @param array $templateParams 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项  支持不同的号码对应不同参数
     * @return mixed
     */
    public function sendBatch(
        array $phoneNumbers,
        array $signNames,
        $templateCode,
        array $templateParams = []
    ) {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendBatchSmsRequest();

        // 必填:待发送手机号。支持JSON格式的批量调用，批量上限为100个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
        $request->setPhoneNumberJson(json_encode($phoneNumbers, JSON_UNESCAPED_UNICODE));

        // 必填:短信签名-支持不同的号码发送不同的短信签名
        $request->setSignNameJson(json_encode($signNames, JSON_UNESCAPED_UNICODE));

        // 必填:短信模板-可在短信控制台中找到
        $request->setTemplateCode($templateCode);

        // 必填:模板中的变量替换JSON串,如模板内容为"亲爱的${name},您的验证码为${code}"时,此处的值为
        // 友情提示:如果JSON中需要带换行符,请参照标准的JSON协议对换行符的要求,比如短信内容中包含\r\n的情况在JSON中需要表示成\\r\\n,否则会导致JSON在服务端解析失败
        //        array(
        //            array(
        //                "name" => "Tom",
        //                "code" => "123",
        //            ),
        //            array(
        //                "name" => "Jack",
        //                "code" => "456",
        //            ),
        //        )
        $request->setTemplateParamJson(json_encode($templateParams, JSON_UNESCAPED_UNICODE));

        // 可选-上行短信扩展码(扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段)
        // $request->setSmsUpExtendCodeJson("[\"90997\",\"90998\"]");

        // 发起访问请求
        $acsResponse = $this->acsClient->getAcsResponse($request);

        return $acsResponse;
    }
}