<?php
/**
 * This class gives signature for object data dumper.
 * Dumpers are needed to export objects.
 * 
 * @author Alix Chaysinh <alix.chaysinh@gmail.com>
 * @since  2013-07-09 11:12
 */
interface ObjectDumperInterface
{
    /**
     * Extract array data from an object
     *
     * @param mixed $object
     *
     * @return array
     */
    public function dump($object);
}