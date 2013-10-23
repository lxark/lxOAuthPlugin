<?php

/**
 * Basic OAuthServer actions
 *
 * @package lxOAuthPlugin
 *
 * @author Alix Chaysinh <alix.chaysinh@gmail.com>
 * @since  2013-09-10
 */
require_once __DIR__.'/../vendor/oauth/OAuth.php';

class BaseOAuthActions extends sfActions
{
    /**
     * Return an instance of OAuthServer
     *
     * @return OAuthServer
     */
    protected function getOAuthServer()
    {

        return lxOAuthServer::getInstance();
    }
}
