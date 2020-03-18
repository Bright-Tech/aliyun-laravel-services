# aliyun-laravel-services
Aliyun services for Laravel

## Inspired By
- [apollopy/flysystem-aliyun-oss](https://github.com/apollopy/flysystem-aliyun-oss)
 
## Require
- Laravel 5.5+
## 安装
在 composer 中 增加 
```
"require"{
    ...
    "bright-tech/laravel-aliyun": "dev-master",
    ...
},
...
"repositories"{
    ...
    "aliyun": {
        "type": "vcs",
        "url": "https://github.com/Bright-Tech/aliyun-laravel-services"
    }
    ...
}
```
执行  `composer update`
## 配置文件
可以将项目中的 config/aliyun.php 直接拷贝的目标项目
## Oss 使用方式
`BrightAliyun::oss`
## Oss Post 使用方式
Oss Post是为表单上传提供的支持类

```\BrightAliyun::ossPost()->generateFullPostFormData([参数])```

#### 使用 Oss 作为 File Storage Disk
修改 File Storage 配置 （config/filesystems.php）,增加如下代码段
```
...
'oss' => [
    'driver' => 'oss', 
    'cdn' => '', //如果需要在url中返回cdn的地址需要在此项设置cdn的host
    'ssl' => false
],
...
```
