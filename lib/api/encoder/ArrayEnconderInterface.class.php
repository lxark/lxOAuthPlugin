<?php
/**
 * This is the interface for associative array encoding
 *
 * @author Alix Chaysinh <alix.chaysinh@gmail.com>
 * @since  2013-09-26
 */
interface ArrayEncoderInterface
{
    /**
     * Encode data
     *
     * @param array $data
     *
     * @return mixed
     */
    public function encode(array $data);
}