<?php

return [
    'oss' => [
        'access_key_id' => env('ALIYUN_OSS_ACCESS_KEY_ID', ''),
        'access_key_secret' => env('ALIYUN_OSS_ACCESS_KEY_SECRET', ''),
        'location' => env('ALIYUN_OSS_LOCATION', 'oss-cn-shanghai'),
        'callback' => env('ALIYUN_OSS_CALLBACK', ''),
        'bucket' => env('ALIYUN_OSS_BUCKET', '')
    ],
    'dysms' => [
        'access_key_id' => env('ALIYUN_SMS_ACCESS_ID', ''),
        'access_key_secret' => env('ALIYUN_SMS_ACCESS_KEY', ''),
    ],
    'mts' => [
        'region_id' => env('ALIYUN_MTS_REGION_ID', 'cn-hangzhou'),
        'access_key_id' => env('ALIYUN_MTS_ACCESS_ID', ''),
        'access_key_secret' => env('ALIYUN_MTS_ACCESS_KEY', ''),
        'default_pipeline_id' => env('ALIYUN_MTS_DEFAULT_PIPELINE_ID', ''),
        'default_transcode_template_id' => env('ALIYUN_MTS_DEFAULT_TRANSCODE_TEMPLATE_ID', ''),
    ],
    'green' => [
        'region_id' => env('ALIYUN_GREEN_REGION_ID', 'cn-hangzhou'),
        'access_key_id' => env('ALIYUN_MTS_ACCESS_ID', ''),
        'access_key_secret' => env('ALIYUN_MTS_ACCESS_KEY', ''),
    ]
];
