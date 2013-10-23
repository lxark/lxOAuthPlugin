<?php

/**
 * Subclass for performing query and update operations on the 'oauth_server_nonce' table.
 *
 * 
 *
 * @package plugins.lxOAuthPlugin.lib.model
 */ 
class OAuthServerNoncePeer extends BaseOAuthServerNoncePeer
{
    /**
     * Retrieve a nonce object by nonce
     *
     * @param string $nonce nonce
     *
     * @return OAuthServerNonce|null
     */
    public static function retrieveByNonce($nonce)
    {
        $c = new Criteria();
        $c->add(OAuthServerNoncePeer::NONCE, $nonce);

        return OAuthServerNoncePeer::doSelectOne($c);
    }
}
