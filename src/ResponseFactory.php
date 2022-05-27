<?php
namespace Cccyun\Oauth;

use Flarum\Forum\Auth\Registration;
use Flarum\Http\RememberAccessToken;
use Flarum\Http\Rememberer;
use Flarum\User\LoginProvider;
use Flarum\User\RegistrationToken;
use Flarum\User\User;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResponseFactory
{
    /**
     * @var Rememberer
     */
    protected $rememberer;

    protected $translator;

    /**
     * @param Rememberer $rememberer
     */
    public function __construct(Rememberer $rememberer, TranslatorInterface $translator)
    {
        $this->rememberer = $rememberer;
        $this->translator = $translator;
    }

    public function make(string $provider, string $identifier, callable $configureRegistration): ResponseInterface
    {
        if ($user = LoginProvider::logIn($provider, $identifier)) {
            return $this->makeLoggedInResponse($user);
        }

        $configureRegistration($registration = new Registration);

        $provided = $registration->getProvided();

        if (! empty($provided['email']) && $user = User::where(Arr::only($provided, 'email'))->first()) {
            $user->loginProviders()->create(compact('provider', 'identifier'));

            return $this->makeLoggedInResponse($user);
        }

        $token = RegistrationToken::generate($provider, $identifier, $provided, $registration->getPayload());
        $token->save();

        return $this->makeResponse(array_merge(
            $provided,
            $registration->getSuggested(),
            [
                'token' => $token->token,
                'provided' => array_keys($provided)
            ]
        ));
    }

    private function makeResponse(array $payload): HtmlResponse
    {
        if(preg_match('/Android|SymbianOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Windows Phone|Midp/', $_SERVER['HTTP_USER_AGENT'])){
            if(isset($payload['loggedIn']) && $payload['loggedIn']){
                $content = sprintf(
                    '<script>window.location.href = "/"; window.app.authenticationComplete(%s);</script>',
                    json_encode($payload)
                );
            }else{
                $message = $this->translator->trans('cccyun-clogin-oauth.forum.alerts.unlinked');
                $content = sprintf(
                    '<script>alert("%s"); window.location.href = "/"; window.app.authenticationComplete(%s);</script>',
                    $message,
                    json_encode($payload)
                );
            }
        }else{
            $content = sprintf(
                '<script>window.close(); window.opener.app.authenticationComplete(%s);</script>',
                json_encode($payload)
            );
        }

        return new HtmlResponse($content);
    }

    private function makeLoggedInResponse(User $user)
    {
        $response = $this->makeResponse(['loggedIn' => true]);

        $token = RememberAccessToken::generate($user->id);

        return $this->rememberer->remember($response, $token);
    }

}
