<?php

/**
 * This class extract data from a Propel Object object
 *
 * @author Alix Chaysinh <alix.chaysinh@gmail.com>
 * @since  2013-09-26
 */
class PropelObjectDumper extends AbstractObjectDumper
{

    /**
     * Extract data from an object
     *
     * @param BaseObject $object Propel Object
     *
     * @throws LogicException
     *
     * @return array
     */
    public function dump($object)
    {
        if (!$object instanceof BaseObject) {
            throw new LogicException(sprintf('%s is not a Propel object to dump', $object));
        }

        // Dump all object data
        $data = $object->toArray(BasePeer::TYPE_FIELDNAME);

        // Dump Relations objects and collections
        $data = $this->dumpRelations($object, $data);

        // Encode all recursively in uft8
        $data = $this->encodeAllUtf8($data);

        return $data;
    }


    /**
     * Dump Object relations
     *
     * @param BaseObject  $object Object
     * @param array       $data   An array to store relations dump
     *
     * @return array
     */
    protected function dumpRelations(BaseObject $object, $data)
    {
        $relations = $this->getObjectRelations($object);

        // Dump object or array of objects
        foreach ($relations as $key => $relation) {
            if (is_object($relation)) {
                $data[$key] = $this->dumpSingleRelation($relation);
            } elseif (is_array($relation)) {
                foreach ($relation as $item) {
                    if (!isset($data[$key])) {
                        $data[$key] = array();
                    }
                    $data[$key][] = $this->dumpSingleRelation($item);
                }
            }
        }

        return $data;
    }


    /**
     * Dump Object's one single relation
     *
     * @param BaseObject $object
     *
     * @return mixed
     */
    protected function dumpSingleRelation(BaseObject $object)
    {

        return $this->getObjectId($object);
    }

    /**
     * Return object relations in an array with
     *
     * @param BaseObject $object Object
     *
     * @return array
     */
    protected function getObjectRelations(BaseObject $object)
    {

        return array();
    }
}