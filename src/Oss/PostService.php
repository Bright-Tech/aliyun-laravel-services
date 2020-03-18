<?php

namespace Bright\Aliyun\Oss;

/**
 * Class PostService
 * 针对表单上传的支持类
 *
 * @package Bright\Aliyun\Oss
 */
class PostService
{
    protected $accessKeyId;
    protected $accessKeySecret;
    protected $endpoint;
    protected $bucket;
    protected $http = 'http';

    protected $callbackUrl = '';

    protected $callbackBody = 'object=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}';
    /**
     * 发起回调请求的Content-Type，支持application/x-www-form-urlencoded和application/json，默认为前者。
     * 如果为application/x-www-form-urlencoded，则callbackBody中的变量将会被经过url编码的值替换掉
     * 如果为application/json，则会按照json格式替换其中的变量。
     *
     * @var string
     */
    protected $callbackBodyType = 'application/x-www-form-urlencoded';

    /**
     * Policy 超时时间。单位 秒
     * 设置该policy超时时间是300s. 即这个policy过了这个有效时间，将不能访问
     *
     * @var int
     */
    protected $policyExpireIn = 300;

    /**
     * @var array
     */
    protected $policyConditions = [
        ['content-length-range', 0, 1048576000], //最大文件大小.用户可以自己设置
        //[ 'starts-with', '$key', $fullname], //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
    ];

    /**
     * OssPostClient constructor.
     *
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @param string $callbackUrl
     * @param string $endpoint
     * @param string $bucket
     */
    public function __construct($accessKeyId, $accessKeySecret, $callbackUrl, $endpoint, $bucket)
    {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $this->callbackUrl = $callbackUrl;
        $this->endpoint = $endpoint;
        $this->bucket = $bucket;
    }

    /**
     * @return string
     */
    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    /**
     * @param string $callbackUrl
     */
    public function setCallbackUrl(string $callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * @param array $params
     * @return array
     */
    public function generateFullPostFormData($params = [])
    {
        $policy = $this->generatePostPolicy();
        $callback = $this->generateCallback($params);

        return array_merge([
            'OSSAccessKeyId' => $this->accessKeyId,
            'host' => $this->http . '://' . $this->bucket . '.' . $this->endpoint,
        ], $policy, $callback);
    }

    /**
     * 生成 Callback
     * @link https://help.aliyun.com/document_detail/31989.html
     *
     * @param array $params callback-var 中的参数
     * 程序会默认在参数前增加 x: ，不需要用户自行添加
     *
     * @return array
     */
    public function generateCallback($params = [])
    {
        $returnArr = [];
        $callbackParam = [
            'callbackUrl' => $this->callbackUrl,
            'callbackBody' => $this->callbackBody,
            'callbackBodyType' => $this->callbackBodyType
        ];
        foreach ($params as $key => $value) {
            $key = strtolower($key);
            $paramKey = 'x:' . $key;
            $returnArr[$paramKey] = $value;
            $callbackParam['callbackBody'] .= '&' . $key . '=${' . $paramKey . '}';
        }
        $base64_callback_body = base64_encode(json_encode($callbackParam));
        $returnArr['callback'] = $base64_callback_body;

        return $returnArr;
    }

    /**
     * 生成 PostPolicy
     * @link https://help.aliyun.com/document_detail/31988.html#h2-post-policy2
     *
     * @return array
     */
    public function generatePostPolicy()
    {
        $now = time();
        $end = $now + $this->policyExpireIn;
        $arr = array('expiration' => $this->gmtISO8601($end), 'conditions' => $this->policyConditions);
        $base64Policy = base64_encode(json_encode($arr));

        //这个参数是设置用户上传指定的前缀
        return $response = [
            'policy' => $base64Policy,
            'signature' => base64_encode(hash_hmac('sha1', $base64Policy, $this->accessKeySecret, true)),
            'expire' => $end,
        ];
    }


    /**
     * 生成 ISO8601 GMT时间
     *
     * @param $time
     * @return string
     */
    protected function gmtISO8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }
}
