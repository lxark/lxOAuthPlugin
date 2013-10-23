<?php

/**
 * Subclass for representing a row from the 'oauth_server_consumer' table.
 *
 * 
 *
 * @package plugins.lxOAuthPlugin.lib.model
 */ 
class OAuthServerConsumer extends BaseOAuthServerConsumer
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE   = 1;
}
