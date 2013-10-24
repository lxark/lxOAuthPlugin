lxOAuthPlugin
=============

Plugin for OAuth 1.0 Server setup for symfony 1.0 Propel 1.0

Installation
------------

1. Create a new symfony application
2. Regenerate your models
3. Use a routing file similar to routing.yml.sample
4. Use a concrete class of lxOAuthSecurityFilter as a security filter
5. Enable OAuthServer module in your app and override actions and templates to your convenience


API Action
----------

You can use the presented API actions as a base for your api modules

1. Use a routing file similar to routing.yml.api.sample in your api application
2. Create a module with an action that override OAuthApiActions.class.php
3. Set protected attributes $peerClass, $peerMethod
4. Set is_oauth_secure to true in your module security.yml

Inspired by
----------

+ https://github.com/Tillid/tiDoctrineOAuthServerPlugin
+ https://github.com/chok/sfOAuthPlugin