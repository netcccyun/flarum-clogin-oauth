<?php

namespace Cccyun\Oauth;

use Exception;
use Flarum\Forum\Auth\Registration;
use Flarum\Forum\Auth\ResponseFactory;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class OAuthLoginController implements RequestHandlerInterface
{
    /**
     * @var ResponseFactory
     */
    protected $response;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @param ResponseFactory $response
     * @param SettingsRepositoryInterface $settings
     * @param UrlGenerator $url
     */
    public function __construct(ResponseFactory $response, SettingsRepositoryInterface $settings, UrlGenerator $url)
    {
        $this->response = $response;
        $this->settings = $settings;
        $this->url      = $url;
    }


    /**
     * @param Request $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(Request $request): ResponseInterface
    {

        $callback = $this->url->to('api')->route('oauth.login');
        $provider   = new CloginOauth([
            'apiurl' => $this->settings->get('cccyun-clogin-oauth.appurl'),
            'appid' => $this->settings->get('cccyun-clogin-oauth.appid'),
            'appkey' => $this->settings->get('cccyun-clogin-oauth.appkey'),
            'callback' => $callback,
        ]);

        $session = $request->getAttribute('session');
        $queryParams = $request->getQueryParams();
        $type = Arr::get($queryParams, 'type');
        if(!$type){
            throw new Exception('Invalid type');
        }

        $code = Arr::get($queryParams, 'code');
        if (!$code) {
            $state = md5(uniqid(rand(), TRUE));
            $authUrl = $provider->login($type, $state);
            $session->put('oauth2state', $state);
            return new RedirectResponse($authUrl);
        }

        $state = Arr::get($queryParams, 'state');

        // var_dump($state,$session->get('oauth2state'));

        if (!$state || $state !== $session->get('oauth2state')) {
            $session->remove('oauth2state');
            throw new Exception('Invalid state');
        }

        $userinfo = $provider->callback($code);

        $loginResultRes = $this->response->make(
            $type,
            $userinfo["social_uid"],
            function (Registration $registration) use ($userinfo) {
                $registration
                    ->suggestUsername($this->UserNameMatch($userinfo["nickname"]))
                    ->setPayload($userinfo);
                if(!empty($userinfo['faceimg']))
                    $registration->provideAvatar($userinfo['faceimg']);
            }
        );

        return $loginResultRes;
    }

    public function UserNameMatch($str)
    {
        preg_match_all('/[\x{4e00}-\x{9fa5}a-zA-Z0-9]/u', $str, $result);
        return implode('', $result[0]);
    }
}