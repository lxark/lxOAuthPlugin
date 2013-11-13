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
    /**
     * Need to be implemented via module.yml
     * @var string $model model name
     *
     */
    protected $model;

    /**
     * Need to be implemented via module.yml
     * @var string $modelPK model name primary key
     */
    protected $modelPK;

    /**
     * @var string peer class of the model
     */
    protected $peerClass;

    /**
     * @var sfPropelPager $pager Pagination
     */
    protected $pager;

    /**
     * Pre Execute
     * Parse module.yml
     *
     * @return void
     */
    public function preExecute()
    {
        $this->model     = sfConfig::get('mod_'.$this->getModuleName().'_model');
        $this->modelPK   = sfConfig::get('mod_'.$this->getModuleName().'_model_pk');
        $this->peerClass = $this->model.'Peer';
    }

    /**
     * Post Execute
     * Change content type header
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
        if ($request->getParameter('id') && !isset($filters[$this->modelPK]))
        {
            $filters[$this->modelPK] = $request->getParameter('id');
        }

        // Create query depending on filters
        $c = new Criteria();
        foreach ($filters as $filter => $value)
        {
            $keyFieldName = array_search($filter, $fieldNames);
            if (is_int($keyFieldName))
            {
                $dbFieldName = $dbFieldNames[$keyFieldName];
                $c->add($dbFieldName, $value);
            }
        }

        return $c;
    }

    /**
     * Get limit for query
     *
     * @param sfRequest $request
     *
     * @return int
     */
    protected function getMaxLimit(sfRequest $request)
    {
        // Limit, limit the limit
        $maxLimit = sfConfig::get('app_list_limit', sfConfig::get('mod_'.$this->getModuleName().'_list_limit', 100));
        $limit    = $request->getParameter('limit', $maxLimit);
        if ($limit > $maxLimit)
        {
            $limit = $maxLimit;
        }

        return $limit;
    }

    /**
     * Return list of resources as pager
     *
     * @param sfRequest $request
     *
     * @return sfPropelPager
     */
    protected function getPager(sfRequest $request)
    {
        if (null === $this->pager)
        {
            $this->pager = new sfPropelPager($this->model, $this->getMaxLimit($request));
            $this->pager->setPeerMethod(sfConfig::get('mod_'.$this->getModuleName().'_list_peer_method', 'doSelect'));
            $this->pager->setPeerCountMethod(sfConfig::get('mod_'.$this->getModuleName().'_list_peer_count_method', 'doCount'));
            $this->pager->setCriteria($this->getCriteria($request));
            $this->pager->setPage($request->getParameter('page', 1));
            $this->pager->init();
        }

        return $this->pager;
    }

    /**
     * Return a dumped collection
     *
     * @param sfRequest $request
     *
     * @return array
     */
    protected function getDumpedCollection(sfRequest $request)
    {
        $dumper           = $this->getDumper();
        $pager            = $this->getPager($request);
        $collection       = $pager->getResults();
        $dumpedCollection = array();

        foreach($collection as $item)
        {
            $dumpedCollection[] = $dumper->dump($item);
        }

        return $dumpedCollection;
    }

    /**
     * Return pagination data
     *
     * @param sfRequest $request
     *
     * @return array
     */
    protected function getPaginationData(sfRequest $request)
    {
        $pager = $this->getPager($request);
        return array(
            'total'        => $pager->getNbResults(),
            'nb_items'     => (int) $pager->getMaxPerPage(),
            'current_page' => $pager->getPage(),
            'last_page'    => $pager->getLastPage(),
        );
    }

    /**
     * Return list of objects
     *
     * @return string
     */
    public function executeIndex()
    {
        return $this->forward($this->getModuleName(), 'list');
    }

    /**
     * Return list of objects
     * criteria -> paginate -> dump -> encode
     *
     * @return string
     */
    public function executeList()
    {
        // Decoded data
        $request = $this->getRequest();
        $data    = array(
            'data'       => $this->getDumpedCollection($request),
            'pagination' => $this->getPaginationData($request),
        );

        // Encoded data
        $encoder = $this->getEncoder($request->getParameter('sf_format', 'json'));
        $data    = $encoder->encode($data);

        return $this->renderText($data);
    }
}
