<?php

/**
 * Subclass for performing query and update operations on the 'oauth_server_token' table.
 *
 * 
 *
 * @package plugins.lxOAuthPlugin.lib.model
 */ 
class OAuthServerTokenPeer extends BaseOAuthServerTokenPeer
{
    /**
     * Return available tokens types in an array
     *
     * @return array
     */
    public static function getAvailableTypes()
    {
        return array(
            OAuthServerToken::TYPE_ACCESS,
            OAuthServerToken::TYPE_REQUEST,
        );
    }

    /**
     * Retrieve a token, optionally by type
     *
     * @param string $tokenString Token
     * @param string $type        Token type
     *
     * @return OAuthServerToken|null
     */
    public static function retrieveByToken($tokenString, $type = null)
    {
        $criteria = self::getTokenAndTypeCriteria($tokenString, $type);

        return self::doSelectOne($criteria);
    }

    /**
     * Delete by user id
     *
     * @param string $tokenString Token
     * @param string $type        Token type
     *
     * @return int affected rows
     */
    public static function deleteByToken($tokenString, $type = null)
    {
        $criteria = self::getTokenAndTypeCriteria($tokenString, $type);

        return self::doDelete($criteria);
    }


    /**
     * Return Criteria for token string and optional type
     *
     * @param string $tokenString Token
     * @param string $type        Token type
     *
     * @return Criteria
     */
    public static function getTokenAndTypeCriteria($tokenString, $type = null)
    {
        $c = new Criteria();
        if (null !== $type && in_array($type, self::getAvailableTypes()))
        {
            $c->add(OAuthServerTokenPeer::TYPE, $type);
        }

        $c->add(OAuthServerTokenPeer::TOKEN, $tokenString);

        return $c;
    }
}
