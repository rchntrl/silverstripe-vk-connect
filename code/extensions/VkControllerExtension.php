<?php

/**
 * @package vkconnect
 */
class VkControllerExtension  extends DataExtension
{
    /**
     * @config
     * @var bool $create_member
     */
    private static $create_member = true;

    /**
     * @config
     * @var array $member_groups
     */
    private static $member_groups = array();

    /**
     *
     * @config
     * @var array $permissions
     */
    private static $permissions = array(
        'email'
    );

    /**
     * @config
     * @var bool $sync_member_details
     */
    private static $sync_member_details = true;

    /**
     * @config
     * @var string $api_secret
     */
    private static $api_secret = "";

    /**
     * @config
     * @var string $app_id
     */
    private static $app_id = "";

    /**
     * @var
     */
    private $session;


    /**
     * @var string
     */
    const SESSION_REDIRECT_URL_FLAG = 'redirectvkuser';

    /**
     * @var string
     */
    const VK_ACCESS_TOKEN = 'vkaccesstoken';

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $appId = Config::inst()->get(
            'VkControllerExtension', 'app_id'
        );

        $secret = Config::inst()->get(
            'VkControllerExtension', 'api_secret'
        );


        if (!$appId || !$secret) {
            return null;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            Session::start();
        }
    }

    /**
     * @return stdClass
     */
    public function getVkSession()
    {
        if (!$this->session) {
            $accessToken = Session::get(
                VkControllerExtension::VK_ACCESS_TOKEN
            );

            if ($accessToken) {
                $this->session = $accessToken;
            }
        }
        return $this->session;
    }

    /**
     * @return string
     */
    public function getVkLoginLink()
    {
        // save the url that this page is on to session. The user will be 
        // redirected back here.
        Session::set(self::SESSION_REDIRECT_URL_FLAG, $this->getCurrentPageUrl());
        $scope = Config::inst()->get('VkControllerExtension', 'permissions');

        if (!$scope) {
            $scope = array();
        }
        $secret = Config::inst()->get('VkControllerExtension', 'api_secret');

        $params =  array(
            'client_id'=> $this->getVkAppId(),
            'scope'=> $scope,
            'redirect_uri'=> $this->getVkCallbackLink(),
            'response_type' => 'code',
            'v' => '5.28',
            'state' => 'SESSION_STATE'
        );

        return 'https://oauth.vk.com/authorize?' . http_build_query($params);
    }

    /**
     * @return string
     */
    public function getVkAppId()
    {
        return Config::inst()->get('VkControllerExtension', 'app_id');
    }

    /**
     * @return string
     */
    public function getCurrentPageUrl()
    {
        $url = Director::protocol() . "$_SERVER[HTTP_HOST]";
        $pos = strpos($_SERVER['REQUEST_URI'], '?');

        $get = $_GET;

        // tidy up get.
        unset($get['code']);
        unset($get['state']);
        unset($get['url']);

        // if the current page is the login page and the page contains a back
        // URL then we want to redirect the user to that instead.
        if (isset($get['BackURL'])) {
            $last = strlen($get['BackURL']);
            $end = ($pos = strpos($get['BackURL'], '?')) ? $pos : $last;
            $url .= substr($get['BackURL'], 0, $end);

            unset($get['BackURL']);
        } elseif ($pos !== false) {
            $url .= substr($_SERVER['REQUEST_URI'], 0, $pos);
        } else {
            $url .= $_SERVER['REQUEST_URI'];
        }

        $qs = http_build_query($get);
        $url .= ($qs) ? "?$qs" : '';

        return $url;
    }

    /**
     * @return string
     */
    public function getVkCallbackLink()
    {
        return Controller::join_links(
            Director::absoluteBaseUrl(),
            'VkConnectAuthCallback/connect'
        );
    }

    /**
     * @return mixed|string
     * @throws Exception
     */
    public function getUserInfo()
    {
        $session = $this->getVkSession();
        $params = array(
            'uids' => $session->user_id,
            'fields' => 'uid,email,first_name,last_name,nickname,screen_name,sex,bdate,city,country,timezone,photo',
            'access_token' => $session->access_token
        );
        $response = $this->call('https://api.vk.com/method/users.get', $params);

        return $response->response[0];
    }

    public function getAccessToken($code)
    {
        $appId = Config::inst()->get('VkControllerExtension', 'app_id');
        $secret = Config::inst()->get('VkControllerExtension', 'api_secret');
        $params = array(
            'client_id' => $appId,
            'client_secret' => $secret,
            'code' => $code,
            'redirect_uri' => $this->getVkCallbackLink(),
        );

        $response = $this->call('https://oauth.vk.com/access_token', $params);
        if (empty($response->access_token)) {
            throw new \Exception('VK API error');
        }
        Session::set(self::VK_ACCESS_TOKEN, $response);

        return $response;
    }

    /**
     * @param string $url
     * @param array $params
     * @return mixed|string
     * @throws Exception
     */
    private function call($url, array $params)
    {
        $response = array();
        $urlToken = $url . '?' . urldecode(http_build_query($params));
        $ctx = stream_context_create(
            array(
                'http' => array(
                    'header'=>"Content-type: application/x-www-form-urlencoded",
                    'method'=>'GET'
                )
            )
        );
        try {
            $response = file_get_contents($urlToken, 0, $ctx);
            $response = json_decode($response);
        } catch (\exception $ex) {
            throw $ex;
        }

        if (!$response || JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('Response parse error');
        }

        if (!empty($response->error->error_code) && !empty($response->error->error_msg)) {
            throw new \Exception($response->error->error_msg, $response->error->error_code);
        }
        return $response;
    }
}
