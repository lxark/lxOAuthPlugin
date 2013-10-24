<?php

/**
 * This class extract data from a Propel Object object
 * with a linking
 *
 * @author Alix Chaysinh <alix.chaysinh@gmail.com>
 * @since  2013-09-26
 */
class LinkingPropelObjectDumper extends PropelObjectDumper
{
    /**
     * @var sfWebController $controller needed to generate urls
     */
    protected $controller;

    /**
     * Constructor
     *
     * @param sfWebController $controller Controller to generate url
     */
    public function __construct(sfWebController $controller = null)
    {
        $this->controller = $controller;
    }

    /**
     * Dump Object's one single relation
     *
     * @param BaseObject $object
     *
     * @return array
     */
    protected function dumpSingleRelation(BaseObject $object)
    {
        // If config cannot handle url generation, only dump object
        if (!$this->controller)
        {
            return parent::dumpSingleRelation($object);
        }

        $routeParams = '@link_'.strtolower(get_class($object)).'?id='.$this->getObjectId($object);

        return array(
            'id'   => $this->getObjectId($object),
            'href' => $this->generateUrl($routeParams),
        );
    }

    /**
     * Generate an url
     *
     * @param mixed $params   Parameters
     * @param bool  $absolute Absolute url
     *
     * @return string
     */
    protected function generateUrl($params, $absolute = true)
    {

        return $this->controller->genUrl($params, $absolute);
    }
}