# yii2-easy-wechat

借鉴了一些其他 `EasyWeChat SDK for Yii2`，但好像都是针对 `EasyWeChat V3` 版本的。

本渣瞎捣鼓了一个针对 `EasyWeChat V4` 的，欢迎各路大佬指点。

Thank you. 

###Installation

``composer require myshero/yii2-easy-wechat --dev``

###Configuration

YII2 配置添加 `wechat` 组件

[更多参数说明](https://www.easywechat.com/docs/master/zh-CN/official-account/configuration)

````
'components' => [
    // ...
    'wechat'=>[
        'class'=>'Myshero\Wechat\Wechat',
        // 微信参数
        'config' => [
            'app_id' => 'app_id',
            'secret' => 'secret',
            'log' => [
                'level' => 'debug',
                'permission' => 0777,
                'file' => Yii::getAlias("@frontend") . '/runtime/wechat.log',
            ],
            /**
             * OAuth 配置
             *
             * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
             * callback：OAuth授权完成后的回调页地址
             */
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => 'wechat/oauth',
            ],
        ],
    ],
    // ...
],
````

### Usage

```
// here are some representative examples that will help you:

// 微信网页授权:
public function actionIndex()
{
    $wechat = \Yii::$app->wechat;
    if ($wechat->isGuest()) {
        // 跳转微信服务器授权
        return $wechat->prepareOauth();
    }else{
        // do something ...
    }
}

/**
 * 授权回调
 */
public function actionOauth()
{
    $wechat = \Yii::$app->wechat;
    return $wechat->oauth();
}

------

\Yii::$app->wechat->officialAccount;  // 公众号
\Yii::$app->wechat->miniProgram;      // 小程序
\Yii::$app->wechat->openPlatform;     // 开放平台
\Yii::$app->wechat->payment;          // 微信支付
\Yii::$app->wechat->work;             // 企业微信

```

###More documentation
see [EasyWeChat Docs.](https://www.easywechat.com/)

Thanks to `overtrue/wechat`