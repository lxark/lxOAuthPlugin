<?php
/**
 * Singleton of a OAuthServer to fetch from anywhere
 * YES a fucking singleton
 * 
 * @author Alix Chaysinh <alix.chaysinh@gmail.com>
 * @since  2013-09-19
 */
class lxOAuthServer extends OAuthServer
{
    /**
     * @var OAuthServer OAuthServer
     */
    protected static $instance;

    /**
     * Return the instance of OAuthServer
     *
     * @return lxOAuthServer
     */
    public static function getInstance()
    {
        if (null === self::$instance)
        {
            $OAuthServer = new self(new lxOAuthDataStore());
            $OAuthServer->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());
            $OAuthServer->add_signature_method(new OAuthSignatureMethod_PLAINTEXT());

            self::$instance = $OAuthServer;
        }

        return self::$instance;
    }


    /**
     * Return DataStore
     *
     * @return lxOAuthDataStore
     */
    public function getDataStore()
    {
        return $this->data_store;
    }

    /**
     * Prevent cloning
     *
     * @return OAuthServer
     */
    public function __clone()
    {

        return self::getInstance();
    }
}