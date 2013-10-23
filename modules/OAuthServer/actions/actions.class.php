<?php

/**
 * Exemple of OAuthServer actions that extends BaseOAuthServerActions
 * Your OAuthServerActions must be in your application
 * use a require_once to load BaseOAuthServerActions
 *
 * @package lxOAuthPlugin
 *
 * @author Alix Chaysinh <alix.chaysinh@gmail.com>
 * @since  2013-09-10
 */

require_once __DIR__.'/../lib/BaseOAuthServerActions.class.php';

class OAuthServerActions extends BaseOAuthServerActions
{
    /**
     * Process login form
     * To be defined
     *
     * @param sfWebRequest $request
     *
     * @return boolean
     */
    protected function processLoginForm(sfWebRequest $request)
    {

    }

    /**
     * Process authorize form
     * To be defined
     *
     * @param sfWebRequest $request
     *
     * @return boolean|null authorize, refuse or nothing
     */
    protected function processAuthorizeForm(sfWebRequest $request)
    {

    }
}
