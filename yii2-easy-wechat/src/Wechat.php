<?php

namespace Myshero\Wechat;

use yii\base\Component;
use yii\helpers\Url;
use EasyWeChat\Factory;

/**
 * Class Wechat
 * @package Myshero\Wechat
 * @property \EasyWeChat\OfficialAccount\Application $officialAccount       公众号
 * @property \EasyWeChat\MiniProgram\Application $miniProgram               小程序
 * @property \EasyWeChat\OpenPlatform\Application $openPlatform             开放平台
 * @property \EasyWeChat\Payment\Application $payment                       微信支付
 * @property \EasyWeChat\BasicService\Application $basicService
 * @property \EasyWeChat\Work\Application $work                             企业微信
 *
 * @property \Overtrue\Socialite\User $user                                 用户信息
 * @property \EasyWeChat\OfficialAccount\User\UserClient $userDetail        用户详情
 */
class Wechat extends Component
{
    public $config;

    private $officialAccount;
    private $payment;
    private $miniProgram;
    private $openPlatform;
    private $basicService;
    private $work;

    private $key_prefix = 'myshero_wechat_';

    /**
     * @var string openid
     */
    public $id;

    /**
     * 检测是否已经微信授权登录
     * @return bool
     */
    public function isGuest()
    {
        $userInfo = $this->getUser();
        return $userInfo === null ? true : false;
    }

    /**
     * Judge whether the request comes from WeChat browser
     * @return bool
     */
    public function getIsWechat()
    {
        return strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger") !== false;
    }


    // ===================   用户信息   ===================

    /**
     * 用户授权登录后 openid
     * @return string | null
     */
    public function getId()
    {
        return $this->id ?? $this->getUser()->getId();
    }

    /**
     * 用户授权登录后的用户信息
     * @return null|\Overtrue\Socialite\User
     */
    public function getUser()
    {
        $userInfo = \Yii::$app->session->get($this->key_prefix . 'user_info', null);
        return $userInfo;
    }

    /**
     * 更详尽的用户信息
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function getUserDetail()
    {
        return $this->getOfficialAccount()->user->get($this->getId());
    }

    // ===================   用户授权   ===================

    /**
     * 准备授权调用
     * @param null $redirectUrl set the default redirect Url from config
     * @param null|Request $request
     * @return $this
     */
    public function prepareOauth($redirectUrl = null, $request = null)
    {
        Url::remember(\Yii::$app->request->url);
        $response = $this->getOfficialAccount()->oauth;
        if ($request instanceof Symfony\Component\HttpFoundation\Request)
            $response->setRequest($request);
        if (!empty($redirectUrl))
            $response->setRedirectUrl($redirectUrl);
        return $response->redirect()->send();
    }

    /**
     * 授权后 回调 action 内调用
     */
    public function oauth()
    {
        $oauth = $this->getOfficialAccount()->oauth;
        $user = $oauth->user();
        \Yii::$app->session->set($this->key_prefix . 'user_info', $user);
        $this->id = $user->getId();
        return \Yii::$app->response->redirect(Url::previous())->send();
    }


    // ===================   初始化APP   ===================

    /**
     * 微信公众号
     * @return \EasyWeChat\OfficialAccount\Application|mixed
     */
    public function getOfficialAccount()
    {
        return $this->officialAccount ?? call_user_func(function () {
                $this->officialAccount = Factory::officialAccount($this->config);
                return $this->officialAccount;
            });
    }

    /**
     * 微信基础接口 不建议使用
     * see more https://www.easywechat.com/docs/master/zh-CN/official-account/base
     * @return \EasyWeChat\BasicService\Application|mixed
     */
    public function getBasicService()
    {
        return $this->basicService ?? call_user_func(function () {
                $this->basicService = Factory::basicService($this->config);
                return $this->basicService;
            });
    }

    /**
     * 企业微信
     * @return \EasyWeChat\Work\Application|mixed
     */
    public function getWork()
    {
        return $this->work ?? call_user_func(function () {
                $this->work = Factory::work($this->config);
                return $this->work;
            });
    }

    /**
     * 开放平台
     * @return \EasyWeChat\OpenPlatform\Application|mixed
     */
    public function getOpenPlatform()
    {
        return $this->openPlatform ?? call_user_func(function () {
                $this->openPlatform = Factory::openPlatform($this->config);
                return $this->openPlatform;
            });
    }

    public function getMiniProgram()
    {
        return $this->miniProgram ?? call_user_func(function () {
                $this->miniProgram = Factory::miniProgram($this->config);
                return $this->miniProgram;
            });
    }

    /**
     * 微信支付
     * @return \EasyWeChat\Payment\Application|mixed
     */
    public function getPayment()
    {
        return $this->payment ?? call_user_func(function () {
                $this->payment = Factory::payment($this->config);
                return $this->payment;
            });
    }

}