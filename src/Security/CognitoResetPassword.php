<?php

namespace App\Security;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Aws\Result;
use Symfony\Component\HttpFoundation\Request;

class CognitoResetPassword
{
    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private readonly CognitoIdentityProviderClient $client,
        private readonly string $clientId,
    )
    {
    }
    public function submitForgotPassword($data): Result|string
    {
        $username = $data['username'];
        try {
            return $this->client->forgotPassword([
                'ClientId' => $this->clientId,
                'Username' => $username,
            ]);
        } catch (CognitoIdentityProviderException $e) {
            return $e->getMessage();
        }
    }
    public function submitPasswordReset($data): Result|string
    {
        $username = $data['username'];
        $password = $data['plainPassword'];
        $code = $data['reset_code'];
        try {
            return $this->client->confirmForgotPassword([
                'ClientId' => $this->clientId,
                'Username' => $username,
                'ConfirmationCode' => $code,
                'Password' => $password,
            ]);
        } catch (CognitoIdentityProviderException $e) {
            return $e->getMessage();
        }
    }
}
