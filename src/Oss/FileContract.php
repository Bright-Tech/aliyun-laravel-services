<?php
/**
 * Created by PhpStorm.
 * User: samxiao
 * Date: 2018/6/11
 * Time: 下午2:37
 */

namespace Bright\Aliyun\Oss;

use Illuminate\Contracts\Support\Arrayable;
/**
 * Class FileContract
 * aliyun 服务中使用 oss的标准结构
 *
 * @package Bright\Aliyun\Oss
 */
class FileContract implements Arrayable
{

    public $location = '';
    public $bucket = '';
    public $object = '';

    public function __construct($location, $bucket, $object)
    {
        $this->location = $location;
        $this->bucket = $bucket;
        $this->object = urlencode($object);
    }

    public function toArray()
    {
        return [
            'Location' => $this->location,
            'Bucket' => $this->bucket,
            'Object' => $this->object
        ];
    }
}