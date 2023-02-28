<?php

namespace App\Security;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class CognitoAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private readonly CognitoIdentityProviderClient $client,
        private readonly string $userPoolId,
        private readonly string $clientId,
        private readonly UserProviderInterface        $userProvider,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    )
    {
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('email');
        $password = $request->request->get('password');

        $result = $this->client->initiateAuth([
            'AuthFlow' => 'USER_PASSWORD_AUTH',
            'ClientId' => $this->clientId,
            'UserPoolId' => $this->userPoolId,
            'AuthParameters' => [
                'USERNAME' => $username,
                'PASSWORD' => $password,
            ],
        ]);

        if (!$result->hasKey('AuthenticationResult') || !$result->get('AuthenticationResult')['AccessToken']) {
            throw new CustomUserMessageAuthenticationException('Invalid credentials.');
        }

        $token = $result->get('AuthenticationResult')['AccessToken'];
        return new SelfValidatingPassport(
            new UserBadge($token, function() use ($token, $username) {
                // NOTE: here you can load/save user from storage such as database
                return $this->userProvider->loadUserByIdentifier($username);
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
    /*
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    */
    return null;
    }


    public function supports(Request $request): ?bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }
}
