<?php
/**
 * This class handles data. 
 * Don't slack on the description.
 * 
 * @author Alix Chaysinh <alix.chaysinh@gmail.com>
 * @since  2013-09-19
 */
class lxOAuthDataStore extends OAuthDataStore
{

    /**
     * Return OAuthConsumer
     *
     * @param string $consumer_key Consumer key
     *
     * @return OAuthConsumer|null
     */
    function lookup_consumer($consumer_key)
    {
        $oasc = $this->findDatabaseValidConsumer($consumer_key);

        return ($oasc)
            ? new OAuthConsumer($oasc->getConsumerKey(), $oasc->getConsumerSecret())
            : null;
    }


    /**
     * Return token
     *
     * @param OAuthConsumer $consumer   Consumer
     * @param string        $token_type Token type, access or request
     * @param $token        $token      Token
     *
     * @return OAuthToken|null
     */
    function lookup_token($consumer, $token_type, $token)
    {
        $oast = $this->findDatabaseToken($token, $token_type);

        if ($oast
            && $consumer->key === $oast->getOAuthServerConsumer()->getConsumerKey())
        {

            return new OAuthToken($oast->getToken(), $oast->getTokenSecret());
        }

        return null;
    }


    /**
     * Check if a nonce has already been used
     * Also, insert nonce in database if not found
     *
     * @param OAuthConsumer $consumer   Consumer
     * @param string        $token      Token key
     * @param string        $nonce      Nonce
     * @param string        $timestamp  Timestamp
     *
     * @return bool
     */
    function lookup_nonce($consumer, $token, $nonce, $timestamp)
    {
        $found = (bool) $this->findDatabaseNonce($nonce);

        // Not the best place to insert in database but can't help it
        if (!$found && $token !== null)
        {
            $oasn = new OAuthServerNonce();
            $oasn->setNonce($nonce);
            $oasn->setToken($token->key);

            // request timestamp in created at
            $oasn->setCreatedAt(date('Y-m-d H:i:s', (int) $timestamp));

            // Set consumer
            $oasc = $this->findDatabaseValidConsumer($consumer->key);
            if ($oasc)
            {
                $oasn->setConsumerId($oasc->getId());
            }

            $oasn->save();
        }

        return $found;
    }

    /**
     * Generate a request token
     *
     * @param  $consumer
     * @param callable $callback
     *
     * @return OAuthToken|void
     */
    function new_request_token($consumer, $callback = null)
    {
        // Create database Token
        $oast = new OAuthServerToken();
        $oast->setType(OAuthServerToken::TYPE_REQUEST);
        $oast->setStatus(OAuthServerToken::STATUS_UNAUTHORIZED);
        $oast->setToken($this->generateToken());
        $oast->setTokenSecret($this->generateTokenSecret());
        $oast->setCallbackUrl($callback);
        $oast->setOAuthVersion(1);
        $oast->setExpire(strtotime(sfConfig::get('app_oauth_token_expiration_time', '+4 hours')));

        // Set consumer
        $oasc = $this->findDatabaseValidConsumer($consumer->key);
        if ($oasc)
        {
            $oast->setConsumerId($oasc->getId());
        }

        $oast->save();

        // Return library token
        return new OAuthToken($oast->getToken(), $oast->getTokenSecret());
    }

    /**
     * @param $token
     * @param $consumer
     * @param null $verifier
     *
     * @return null|OAuthToken|void
     */
    function new_access_token($token, $consumer, $verifier = null)
    {
        $requestToken = $this->findDatabaseToken($token->key, OAuthServerToken::TYPE_REQUEST);

        // Request token
        if ($requestToken && $requestToken->isAuthorizedRequestToken())
        {
            // Create database Token
            $oast = new OAuthServerToken();
            $oast->setType(OAuthServerToken::TYPE_ACCESS);
            $oast->setStatus(OAuthServerToken::STATUS_AUTHORIZED);
            $oast->setToken($this->generateToken());
            $oast->setTokenSecret($this->generateTokenSecret());
            $oast->setUserId($requestToken->getUserId());
            $oast->setOAuthVersion(1);
            $oast->setExpire(strtotime(sfConfig::get('app_oauth_token_expiration_time', '+4 hours')));

            // Set consumer
            $oasc = $this->findDatabaseValidConsumer($consumer->key);
            if ($oasc)
            {
                $oast->setConsumerId($oasc->getId());
            }

            // Save...
            $oast->save();

            // Delete request token
            $requestToken->delete();

            return new OAuthToken($oast->getToken(), $oast->getTokenSecret());
        }

        return null;
    }


    /**
     * Find valid consumer in database
     *
     * @param string $consumerKey consumer key
     *
     * @return OAuthServerConsumer|null
     */
    protected function findDatabaseValidConsumer($consumerKey)
    {

        return OAuthServerConsumerPeer::retrieveValidByConsumerKey($consumerKey);
    }

    /**
     * Find token in database
     *
     * @param string $tokenKey Token
     * @param string $type     Token type, request or access
     *
     * @return OAuthServerNonce|null
     */
    protected function findDatabaseToken($tokenKey, $type)
    {

        return OAuthServerTokenPeer::retrieveByToken($tokenKey, $type);
    }

    /**
     * Find nonce in database
     *
     * @param string $nonce Nonce
     *
     * @return OAuthServerNonce|null
     */
    protected function findDatabaseNonce($nonce)
    {

        return OAuthServerNoncePeer::retrieveByNonce($nonce);
    }


    /**
     * Generate token
     *
     * @return string
     */
    public function generateToken()
    {

        return $this->generateKey(true, sfConfig::get('app_oauth_token_key_length', 22));
    }


    /**
     * Generate token secret
     *
     * @return string
     */
    public function generateTokenSecret()
    {

        return $this->generateKey(false, sfConfig::get('app_oauth_token_secret_length', null));
    }


    /**
     * Generate oauth verifier
     *
     * @return string
     */
    public function generateVerifier()
    {

        return $this->generateKey(false, sfConfig::get('app_oauth_verifiers_length', 10));
    }

    /**
     * Generate a random key
     *
     * @param bool $unique unique
     * @param null $length
     *
     * @return string
     */
    public function generateKey($unique = false, $length = null)
    {
        // Generate key
        $key = md5(uniqid(rand(), true));
        if ($unique)
        {
            list($usec, $sec) = explode(' ', microtime());
            $key .= dechex($usec).dechex($sec);
        }

        // Shorten it... still pretty unique
        if (is_int($length))
        {
            $key = substr($key, 0, $length);
        }

        return $key;
    }
}