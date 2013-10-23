<?php

/**
 * Subclass for performing query and update operations on the 'oauth_server_consumer' table.
 *
 * 
 *
 * @package plugins.lxOAuthPlugin.lib.model
 */ 
class OAuthServerConsumerPeer extends BaseOAuthServerConsumerPeer
{
    /**
     * Return available tokens types in an array
     *
     * @return array
     */
    public static function getStatusNames()
    {
        return array(
            OAuthServerConsumer::STATUS_ACTIVE => 'Active',
            OAuthServerConsumer::STATUS_INACTIVE => 'Inactive',
        );
    }

    /**
     * Retrieve a valid consumer by consumer key
     *
     * @param string $consumerKey consumer key
     *
     * @return OAuthServerConsumer|null
     */
    public static function retrieveValidByConsumerKey($consumerKey)
    {
        $c = new Criteria();
        $c->add(OAuthServerConsumerPeer::CONSUMER_KEY, $consumerKey);
        $c->add(OAuthServerConsumerPeer::STATUS, OAuthServerConsumer::STATUS_ACTIVE);

        // If expire is set, check if it is after
        $c1 = $c->getNewCriterion(OAuthServerConsumerPeer::EXPIRE, null, Criteria::ISNULL);
        $c2 = $c->getNewCriterion(OAuthServerConsumerPeer::EXPIRE, Criteria::CURRENT_TIMESTAMP, Criteria::GREATER_THAN);
        $c1->addOr($c2);

        $c->add($c1);

        return OAuthServerConsumerPeer::doSelectOne($c);
    }


    /**
     * Retrieve oauth server consumers with number of valid access token
     *
     * @param Criteria $criteria
     * @param null $con
     *
     * @return array
     */
    public static function doSelectRsAdmin(Criteria $criteria, $con = null)
    {
        // Join
        $joinOn = OAuthServerTokenPeer::STATUS.'='.OAuthServerToken::STATUS_AUTHORIZED.
            ' AND '.OAuthServerTokenPeer::TYPE.'="'.OAuthServerToken::TYPE_ACCESS.'"'.
            ' AND ('.OAuthServerTokenPeer::EXPIRE.' is null'.
                ' OR '.OAuthServerTokenPeer::EXPIRE.'>'.Criteria::CURRENT_TIMESTAMP.')';

        $criteria->addJoin(OAuthServerConsumerPeer::ID, OAuthServerTokenPeer::CONSUMER_ID.' AND '. $joinOn, Criteria::LEFT_JOIN);

        // Group by
        $criteria->addGroupByColumn(OAuthServerConsumerPeer::ID);
        $criteria->addAsColumn('nb_valid_access_token', 'COUNT('.OAuthServerTokenPeer::ID.')');

        $oRs = parent::doSelectRS($criteria, $con);
        $oRs->seek(0);
        $oRs->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $aResults = array();

        $statusNames = self::getStatusNames();
        while($oRs->next())
        {
            $statusName = (null !== $oRs->get('STATUS') && isset($statusNames[$oRs->get('STATUS')]))
                ? __($statusNames[$oRs->get('STATUS')])
                : $oRs->get('STATUS');

            $aResults[] = array(
                'id'                    => $oRs->get('ID'),
                'name'                  => $oRs->get('NAME'),
                'uri'                   => $oRs->get('URI'),
                'status'                => $statusName,
                'expire'                => $oRs->get('EXPIRE'),
                'consumer_key'          => $oRs->get('CONSUMER_KEY'),
                'consumer_secret'       => $oRs->get('CONSUMER_SECRET'),
                'created_at'            => $oRs->get('CREATED_AT'),
                'updated_at'            => $oRs->get('UPDATED_AT'),
                'nb_valid_access_token' => $oRs->get('nb_valid_access_token'),
            );
        }
        return $aResults;
    }
}
