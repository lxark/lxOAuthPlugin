<?php
/**
 * This is an abstract class for object dumper
 *
 * @author Alix Chaysinh <alix.chaysinh@gmail.com>
 * @since  2013-09-26
 */
abstract class AbstractObjectDumper implements ObjectDumperInterface
{
    /**
     * Return object id
     *
     * @param mixed $object Object
     *
     * @return mixed
     */
    public function getObjectId($object)
    {
        return $object->getPrimaryKey();
    }

    /**
     * Encode all strings in array in utf8
     *
     * @param array $data
     *
     * @return array
     */
    protected function encodeAllUtf8(array $data)
    {
        $recursiveUtf8Encode = function ($value) use (&$recursiveUtf8Encode) {
            if (is_string($value) && !preg_match('//u', $value)) {
                return utf8_encode($value);
            } elseif (is_array($value)) {
                return array_map($recursiveUtf8Encode, $value);
            } else {
                return $value;
            }
        };

        return array_map($recursiveUtf8Encode, $data);
    }
}