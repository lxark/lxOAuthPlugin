<?php
/**
 * Json array encoder
 *
 * @author Alix Chaysinh <alix.chaysinh@gmail.com>
 * @since  2013-09-26
 */
class JsonArrayEncoder implements ArrayEncoderInterface
{
    /**
     * Encode data in json
     *
     * @param array $data
     *
     * @return string
     */
    public function encode(array $data)
    {

        return json_encode($data);
    }
}