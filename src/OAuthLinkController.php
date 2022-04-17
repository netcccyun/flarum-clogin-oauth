<?php

namespace Cccyun\Oauth;

use Exception;
use Flarum\User\LoginProvider;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Illuminate\Support\Arr;

class OAuthLinkController implements RequestHandlerInterface
{
    /**
     * @var LoginProvider
     */
    protected $loginProvider;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @param LoginProvider $loginProvider
     * @param SettingsRepositoryInterface $settings
     * @param UrlGenerator $url
     */
    public function __construct(LoginProvider $loginProvider, SettingsRepositoryInterface $settings, UrlGenerator $url)
    {
        $this->loginProvider = $loginProvider;
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
        $queryParams = $request->getQueryParams();
        $type = Arr::get($queryParams, 'type');
        if(!$type){
            throw new Exception('Invalid type');
        }

        $actor = $request->getAttribute('actor');
        $actorLoginProviders = $actor->loginProviders()->where('provider', $type)->first();
        if ($actorLoginProviders) {
            return $this->makeResponse('already_linked');
        }

        $callback = $this->url->to('api')->route('oauth.link');
        $provider   = new CloginOauth([
            'apiurl' => $this->settings->get('cccyun-clogin-oauth.appurl'),
            'appid' => $this->settings->get('cccyun-clogin-oauth.appid'),
            'appkey' => $this->settings->get('cccyun-clogin-oauth.appkey'),
            'callback' => $callback,
        ]);

        $session = $request->getAttribute('session');
        
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
        $identifier = $userinfo['social_uid'];


        $isExists = $this->loginProvider->where([
            ['provider', $type],
            ['identifier', $identifier]
        ])->exists();
        if ($isExists) {
            return $this->makeResponse('already_used');
        }

        $created = $actor->loginProviders()->create([
            'provider' => $type,
            'identifier' => $identifier
        ]);

        return $this->makeResponse($created ? 'done' : 'error');
    }

    private function makeResponse($returnCode = 'done'): HtmlResponse
    {
        $content = "<script>window.close();window.opener.app.oauth.linkDone('{$returnCode}');</script>";

        return new HtmlResponse($content);
    }
}