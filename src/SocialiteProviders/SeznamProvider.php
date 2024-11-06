<?php

namespace InternetGuru\LaravelAuth\SocialiteProviders;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class SeznamProvider extends AbstractProvider
{
    const IDENTIFIER = 'SEZNAM';

    /**
     * {@inheritdoc}
     */
    protected $scopes = ['identity'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://login.szn.cz/api/v1/oauth/auth', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://login.szn.cz/api/v1/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://login.szn.cz/api/v1/user', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['oauth_user_id'],
            'nickname' => $user['username'],
            'name' => $user['firstname'] . ' ' . $user['lastname'],
            'email' => $user['email'],
        ]);
    }
}
