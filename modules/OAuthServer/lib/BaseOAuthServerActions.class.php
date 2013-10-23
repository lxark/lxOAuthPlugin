<?php

/**
 * OAuthServer actions
 *
 * @package lxOAuthPlugin
 *
 * @author Alix Chaysinh <alix.chaysinh@gmail.com>
 * @since  2013-09-10
 */
require_once __DIR__.'/../../../lib/vendor/oauth/OAuth.php';

abstract class BaseOAuthServerActions extends BaseOAuthActions
{
    public function preExecute()
    {
        sfConfig::set('sf_web_debug', false);
    }

    /**
     * Request Token
     */
    public function executeRequestToken()
    {
        try
        {
            $request = OAuthRequest::from_request();
            $token = $this->getOAuthServer()->fetch_request_token($request);

            $content = http_build_query(array(
                'oauth_token'        => OAuthUtil::urlencode_rfc3986($token->key),
                'oauth_token_secret' => OAuthUtil::urlencode_rfc3986($token->secret)
            ), '', '&');
        }
        catch (OAuthException $e)
        {
            $this->getResponse()->setStatusCode(401);
            $content = $e->getMessage();
        }

        return $this->renderText($content);
    }

    /**
     * Authorize page
     */
    public function executeAuthorize()
    {
        // Find request token
        $token = OAuthServerTokenPeer::retrieveByToken(
            $this->getRequestParameter('oauth_token'), OAuthServerToken::TYPE_REQUEST);

        if (!$token) {
            $this->getResponse()->setStatusCode(404);

            return $this->renderText('Token not found');
        }

        if (!$this->getUser()->isAuthenticated())
        {
            $this->setTemplate('authorizeLoginForm');
        }

        return sfView::INPUT;
    }


    /**
     * Validate if it is POST:
     * - the login form authorization page
     * - the basic form authorization page
     *
     */
    public function validateAuthorize()
    {
        $request = $this->getRequest();

        // If POST request
        if (sfWebRequest::POST === $request->getMethod())
        {
            // process login form if user authenticated
            if (!$this->getUser()->isAuthenticated())
            {
                $logged = $this->processLoginForm($request);
                if ($logged)
                {
                    $token = $this->retrieveAndAuthorizeRequestToken(
                        $request->getParameter('oauth_token'),
                        $this->getUser()->getCompte()->getCompteId()
                    );

                    $this->redirectIfCallbackUrl($token);
                }
            }
            // process authorize form if user NOT authenticated
            else
            {
                $authorized = $this->processAuthorizeForm($request);

                // Accepted : authorize token and redirect
                if (true === $authorized)
                {
                    $token = $this->retrieveAndAuthorizeRequestToken(
                        $request->getParameter('oauth_token'),
                        $this->getUser()->getCompte()->getCompteId()
                    );

                    $this->redirectIfCallbackUrl($token);
                }
                // Refused : delete token and display refused template via handleError
                elseif (false === $authorized)
                {
                    $token = OAuthServerTokenPeer::retrieveByToken($request->getParameter('oauth_token'));

                    if ($token)
                    {
                        $token->delete();
                    }

                    $request->setAttribute('forceTemplate', 'Refused');

                    return false;
                }
            }

            return !$request->hasErrors();
        }

        return true;
    }

    /**
     * Handle error :
     *  - the login form authorization page
     *  - the refused authorization page
     */
    public function handleErrorAuthorize()
    {
        // If authorization refused
        $forcedTemplate = $this->getRequest()->getAttribute('forceTemplate');
        if ($forcedTemplate)
        {
            return $forcedTemplate;
        }

        // If login form has error
        if (!$this->getUser()->isAuthenticated())
        {
            $this->setTemplate('authorizeLoginForm');
        }


        return sfView::INPUT;
    }


    /**
     * Access token
     */
    public function executeAccessToken()
    {
        try
        {
            $request = OAuthRequest::from_request();

            $token = $this->getOAuthServer()->fetch_access_token($request);

            $content = http_build_query(array(
                'oauth_token'        => OAuthUtil::urlencode_rfc3986($token->key),
                'oauth_token_secret' => OAuthUtil::urlencode_rfc3986($token->secret)
            ), '', '&');

        }
        catch (OAuthException $e)
        {
            $this->getResponse()->setStatusCode(401);
            $content = $e->getMessage();
        }

        return $this->renderText($content);
    }


    /**
     * Revoke access token
     */
    public function executeRevoke()
    {
        try
        {
            $request = OAuthRequest::from_request();
            list($consumer, $token) = $this->getOAuthServer()->verify_request($request);
        }
        catch (OAuthException $e)
        {
            $this->getResponse()->setStatusCode(401);
            $content = array(
                'status'  => 'failure',
                'message' => $e->getMessage()
            );
        }

        // Delete token
        if (isset($token))
        {
            // Retrieve token in database to get user id
            $deleted = OAuthServerTokenPeer::deleteByToken($token->key, OAuthServerToken::TYPE_ACCESS);
            $content = ($deleted > 0)
                ? array('status' => 'success', 'message' => 'Authorization revoked!')
                : array('status' => 'fail', 'message' => 'Could not find tokens');
        }

        return $this->renderText(json_encode($content));
    }



    /**
     * Test action
     *
     * @author Alix Chaysinh <alix.chaysinh@gmail.com>
     * @since  2013-09-10
     */
    public function executeTestCall()
    {
        try
        {
            $request = OAuthRequest::from_request();
            list($consumer, $token) = $this->getOAuthServer()->verify_request($request);

            $content = array(
                'status'  => 'success',
                'message' => 'Hellow Orld !'
            );
        }
        catch (OAuthException $e)
        {
            $this->getResponse()->setStatusCode(401);
            $content = array(
                'status'  => 'failure',
                'message' => $e->getMessage()
            );
        }

        // Response
        $this->getResponse()->setHttpHeader('Content-Type', 'application/json');

        return $this->renderText(json_encode($content));
    }


    /**
     * Error
     *
     * @author Alix Chaysinh <alix.chaysinh@gmail.com>
     * @since  2013-09-10
     */
    public function executeError()
    {
        $code        = $this->getRequest()->getAttribute('code', 200);
        $message     = $this->getRequest()->getAttribute('message', 'error');
        $contentType = $this->getRequest()->getAttribute('content-type', 'text/html');

        $this->getResponse()->setStatusCode($code);
        $this->getResponse()->setContentType($contentType);

        return $this->renderText($message);
    }

    /**
     * Return an instance of OAuthServer
     *
     * @return OAuthServer
     */
    protected function getOAuthServer()
    {
        $OAuthServer = new OAuthServer(new lxOAuthDataStore());
        $OAuthServer->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());
        $OAuthServer->add_signature_method(new OAuthSignatureMethod_PLAINTEXT());

        return $OAuthServer;
    }

    /**
     * Process login form
     *
     * @param  sfWebRequest $request
     *
     * @return boolean
     */
    abstract protected function processLoginForm(sfWebRequest $request);

    /**
     * Process authorize form
     *
     * @param  sfWebRequest $request
     *
     * @return boolean|null authorize, refuse or nothing
     */
    abstract protected function processAuthorizeForm(sfWebRequest $request);

    /**
     * Retrieve token and authorize it, and bind it to a user
     *
     * @param string  $token  request token
     * @param integer $userId user id
     *
     * @return OAuthServerToken
     */
    protected function retrieveAndAuthorizeRequestToken($token, $userId = null)
    {
        $token = OAuthServerTokenPeer::retrieveByToken($token, OAuthServerToken::TYPE_REQUEST);
        if ($token)
        {
            $token->authorize($userId, true);
        }

        return $token;
    }

    /**
     * Redirect to callback url
     *
     * @param OAuthServerToken token
     *
     * @return void
     */
    protected function redirectIfCallbackUrl(OAuthServerToken $token)
    {
        if ($token->getCallbackUrl())
        {
            // Generate verifier
            $oauthDataStore = new lxOAuthDataStore();
            $verifier = $oauthDataStore->generateVerifier();

            // Redirect
            $paramConnector = (false === strpos($token->getCallbackUrl(), '?')) ? '?' : '&';
            $url = $token->getCallbackUrl().$paramConnector."oauth_token=".$token->getToken()."&oauth_verifier=".$verifier;
            $this->redirect($url);
        }
    }
}
