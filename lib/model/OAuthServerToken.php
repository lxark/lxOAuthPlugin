<?php

/**
 * Subclass for representing a row from the 'oauth_server_token' table.
 *
 * 
 *
 * @package plugins.lxOAuthPlugin.lib.model
 */ 
class OAuthServerToken extends BaseOAuthServerToken
{
    const TYPE_REQUEST = 'request';
    const TYPE_ACCESS  = 'access';

    const STATUS_UNAUTHORIZED = 0;
    const STATUS_AUTHORIZED   = 1;

    /**
     * Authorize a token
     * bind it to a user or not
     * save oit or not
     *
     * @param integer $userId user id
     * @param bool    $save   save or not
     */
    public function authorize($userId = null, $save = true)
    {
        if ($userId)
        {
            $this->setUserId($userId);
        }

        $this->setStatus(OAuthServerToken::STATUS_AUTHORIZED);

        if (true === $save)
        {
            $this->save();
        }
    }

    /**
     * Check if token is an authorized and unexpired request token
     *
     * @return bool
     */
    public function isAuthorizedRequestToken()
    {
        return ($this->getType() === self::TYPE_REQUEST
            && $this->getStatus() === self::STATUS_AUTHORIZED
            && (
                $this->getExpire() && strtotime($this->getExpire()) > time()
                    || !$this->getExpire()
            )
        );
    }
}
