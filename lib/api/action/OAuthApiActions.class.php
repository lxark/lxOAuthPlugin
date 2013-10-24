<?php

/**
 * Base for OAuth action for api
 *
 * @package    easybench
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 3335 2007-01-23 16:19:56Z fabien $
 */
abstract class OAuthApiActions extends BaseOAuthActions
{
    // Need to be implemented to list items
    protected $objectPrimaryKey;
    protected $peerClass;
    protected $peerMethod = 'doSelect';

    protected $listLimit = 20;

    /**
     * Post Execute
     *
     * @return void
     */
    public function postExecute()
    {
        $this->redefineResponseContentType($this->getRequestParameter('sf_format', 'json'));
    }

    /**
     * Redefine response content type
     *
     * @param string $format format
     *
     * @return void
     */
    protected function redefineResponseContentType($format)
    {
        switch ($format)
        {
            case 'xml':
            case 'json':
            default:
                $this->getResponse()->setContentType('application/json');
                break;
        }
    }

    /**
     * Return list of resources
     *
     * @return Traversable
     */
    protected function getCollection()
    {
        // execute query with the criteria
        $c = $this->getCriteria($this->getRequest());

        return call_user_func_array(array($this->peerClass, $this->peerMethod), array($c));
    }


    /**
     * Create criteria depending on filters in request
     *
     * @param sfRequest $request
     *
     * @return Criteria
     */
    protected function getCriteria(sfRequest $request)
    {
        // Check filters in query
        $fieldNames   = call_user_func_array(array($this->peerClass, 'getFieldNames'), array(BasePeer::TYPE_FIELDNAME));
        $dbFieldNames = call_user_func_array(array($this->peerClass, 'getFieldNames'), array(BasePeer::TYPE_COLNAME));

        $filters = array_intersect_key(
            $request->getParameterHolder()->getAll(),
            array_flip($fieldNames));

        // Force "id" parameter as primary_key if primary key is not already in filter
        if ($request->getParameter('id') && !isset($filters[$this->objectPrimaryKey]))
        {
            $filters[$this->objectPrimaryKey] = $request->getParameter('id');
        }

        // Create query depending on filters
        $c = new Criteria();
        $c->setLimit($this->listLimit);
        $c->setOffset(($request->getParameter('page', 1) - 1) * $this->listLimit);

        foreach ($filters as $filter => $value)
        {
            $keyFieldName = array_search($filter, $fieldNames);
            $dbFieldName  = $dbFieldNames[$keyFieldName];

            $c->add($dbFieldName, $value);
        }

        return $c;
    }

    /**
     * Return dumper to dump collections'items
     *
     * @return ObjectDumperInterface
     */
    protected function getDumper()
    {
        return new LinkingPropelObjectDumper($this->getController());
    }

    /**
     * Return encoder
     *
     * @param string $format encoding format
     *
     * @return ArrayEncoderInterface
     */
    protected function getEncoder($format)
    {
        switch ($format)
        {
            case 'xml':
            case 'json':
            default:
                $encoder = new JsonArrayEncoder();
                break;
        }

        return $encoder;
    }

    /**
     * Return a dumped collection
     *
     * @return Traversable
     */
    protected function getDumpedCollection()
    {
        $collection = $this->getCollection();
        $dumper     = $this->getDumper();

        $dumpedCollection = array();
        foreach($collection as $item)
        {
            $dumpedCollection[] = $dumper->dump($item);
        }

        return $dumpedCollection;
    }

    /**
     * Return an encoded collection
     *
     * @param string $format encoding format
     *
     * @return mixed
     */
    protected function getEncodedCollection($format)
    {
        $encoder    = $this->getEncoder($format);
        $collection = $this->getDumpedCollection();

        return $encoder->encode($collection);
    }

    /**
     * Return list of Piece
     *
     * @return string
     */
    public function executeIndex()
    {
        return $this->forward($this->getModuleName(), 'list');
    }

    /**
     * Return list of Piece
     *
     * @return string
     */
    public function executeList()
    {
        $collection = $this->getEncodedCollection($this->getRequestParameter('sf_format', 'json'));

        return $this->renderText($collection);
    }
}
