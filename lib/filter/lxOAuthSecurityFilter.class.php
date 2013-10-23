<?php

/**
 * This filter handles oauth request and response if oauth config is set:
 *   - check the request for right parameters
 *   - add header for response
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWebDebugFilter.class.php 16942 2009-04-03 14:48:17Z fabien $
 */
abstract class lxOAuthSecurityFilter extends sfSecurityFilter
{
    /**
     * Executes this filter.
     *
     * @param sfFilterChain $filterChain A sfFilterChain instance
     *
     * @throws sfStopException
     */
    public function execute($filterChain)
    {
        // get the cool stuff
        $context    = $this->getContext();
        $controller = $context->getController();
        $user       = $context->getUser();

        // get the current action instance
        $actionEntry    = $controller->getActionStack()->getLastEntry();
        $actionInstance = $actionEntry->getActionInstance();

        $oauthSecured   = $this->isOAuthSecure($this->getContext()->getActionName(), $actionInstance->getSecurityConfiguration());

        if ($this->isOAuthRequest($context) && $oauthSecured)
        {
            // Oauth Check, don't check again if any forward
            if ($this->isFirstCall())
            {
                $this->authenticateUser();
                $this->editOAuthResponse();
            }

            // Authenticated ?
            if (!$user->isAuthenticated())
            {
                $this->forwardOAuthError('Not authenticated');
            }

            // Authorized ?
            $credential = $actionInstance->getCredential();
            if ($credential !== null && !$user->hasCredential($credential))
            {
                $this->forwardOAuthError('Wrong credentials');
            }
        }
        elseif (!$this->isOAuthRequest($context) && $actionInstance->isSecure())
        {
            // Authenticated ?
            if (!$user->isAuthenticated())
            {
                $controller->forward(sfConfig::get('sf_login_module'), sfConfig::get('sf_login_action'));
                throw new sfStopException();
            }

            // Authorized ?
            $credential = $actionInstance->getCredential();
            if ($credential !== null && !$user->hasCredential($credential))
            {
                $controller->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
                throw new sfStopException();
            }
        }

        // Disable debug
        sfConfig::set('sf_web_debug', false);

        $filterChain->execute();
    }

    /**
     * Check OAuth configuration from security.yml
     *
     * @param string $actionName     Current action name
     * @param array  $securityConfig Security config file
     *
     * @return void
     */
    protected function isOAuthSecure($actionName, $securityConfig)
    {
        $actionName = strtolower($actionName);

        return ((isset($securityConfig['all']['is_oauth_secure']) && 'true' == $securityConfig['all']['is_oauth_secure'])
            || (isset($securityConfig[$actionName]['is_oauth_secure']) && 'true' == $securityConfig[$actionName]['is_oauth_secure']));
    }

    /**
     * Check if request is guessed as oauth request
     *
     * @return bool
     */
    protected function isOAuthRequest()
    {
        $headers = getallheaders();
        if (isset($headers['Authorization']))
        {
            return (bool) preg_match('/oauth_token/i', $headers['Authorization']);
        }

        return false;
    }

    /**
     * Add header for oauth
     *
     * @return void
     */
    protected function editOAuthResponse()
    {
        $context = $this->getContext();

        $context->getResponse()->addHttpMeta('X-XRDS-Location', $context->getRequest()->getHost());
    }

    /**
     * Check OAuth request parameters and authenticate user
     *
     * @throws sfStopException
     * @return void
     */
    protected function authenticateUser()
    {
        $OAuthRequest = OAuthRequest::from_request();
        try
        {
            // Check request parameters anf get access token
            list($consumer, $token) = lxOAuthServer::getInstance()->verify_request($OAuthRequest);
            $oast = OAuthServerTokenPeer::retrieveByToken($token->key, OAuthServerToken::TYPE_ACCESS);

            // Authenticate user if access token authorized
            if (OAuthServerToken::STATUS_AUTHORIZED === $oast->getStatus())
            {
                $this->authenticate();
            }

        }
        catch (Exception $e)
        {
            $this->forwardOAuthError($e->getMessage());
        }
    }

    /**
     * Forward to OAuthServer / Error
     *
     * @param string $message
     *
     * @throws sfStopException
     */
    protected function forwardOAuthError($message)
    {
        $context = $this->getContext();
        $request = $context->getRequest();

        $request->setAttribute('code', 401);
        $request->setAttribute('message', $message);
        $request->setAttribute('content-type', 'text/plain');
        $context->getController()->forward('OAuthServer', 'error');

        throw new sfStopException();
    }


    /**
     * Authenticate user
     *
     * @return void
     */
    abstract protected function authenticate();
}
