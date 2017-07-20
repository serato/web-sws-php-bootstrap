<?php
namespace Serato\SwsApp\Test\Slim\Middleware\AccessScopes;

use Serato\Jwt\AccessToken as JwtAccessToken;

/**
 * A mock Access Token for testing purposes
 */
class AccessToken extends JwtAccessToken
{
    /**
     */
    final public function create(
        array   $audience,
        int     $expires,
        array   $customClaims
    ) : AccessToken {
        $this->createTokenWithKms(
            'my-kms-master-key-id',
            $customClaims['app_id'],
            $audience,
            self::TOKEN_CLAIM_SUB,
            time(),
            $expires,
            $customClaims,
            self::TOKEN_SIGNING_KEY_ID
        );
        return $this;
    }
}
