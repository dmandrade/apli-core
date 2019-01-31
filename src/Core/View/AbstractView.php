<?php
/**
 *  Copyright (c) 2019 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file AbstractView.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 30/06/18 at 13:09
 */

namespace Apli\Core\View;


/**
 * The AbstractView class.
 *
 * @property-read  ViewModel|mixed $model   The ViewModel object.
 * @property-read  Structure       $config  Config object.
 * @property-read  PackageRouter   $router  Router object.
 *
 * @since  2.1.5.3
 */
abstract class AbstractView implements \ArrayAccess
{

    /**
     * Property data.
     *
     * @var  array
     */
    protected $data;

    /**
     * Is a property exists or not.
     *
     * @param mixed $offset Offset key.
     *
     * @return  boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Get a property.
     *
     * @param mixed $offset Offset key.
     *
     * @throws  \InvalidArgumentException
     * @return  mixed The value to return.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set a value to property.
     *
     * @param mixed $offset Offset key.
     * @param mixed $value  The value to set.
     *
     * @throws  \InvalidArgumentException
     * @return  void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Unset a property.
     *
     * @param mixed $offset Offset key to unset.
     *
     * @throws  \InvalidArgumentException
     * @return  void
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }
}
