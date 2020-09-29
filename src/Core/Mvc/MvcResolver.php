<?php
/**
 *  Copyright (c) 2019 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file MvcResolver.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 31/01/19 at 14:45
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 31/01/2019
 * Time: 14:45
 */

namespace Apli\Core\Mvc;

/**
 * Class MvcResolver
 * @package Apli\Core\Mvc
 */
class MvcResolver
{

    /**
     * Property viewResolver.
     *
     * @var  ViewResolver
     */
    protected $viewResolver;


    /**
     * MvcResolver constructor.
     */
    public function __construct() {
        $this->viewResolver       = new ViewResolver();
    }

    /**
     * Method to get property ViewResolver
     *
     * @return  ViewResolver
     */
    public function getViewResolver()
    {
        return $this->viewResolver;
    }

    /**
     * Method to set property viewResolver
     *
     * @param   ViewResolver $viewResolver
     *
     * @return  static  Return self to support chaining.
     */
    public function setViewResolver($viewResolver)
    {
        $this->viewResolver = $viewResolver;

        return $this;
    }

    public function guessName($class, $backwards = 2, $default = 'default')
    {
        if (!is_string($class)) {
            $class = get_class($class);
        }

        $class = explode('\\', $class);

        $name = null;

        foreach (range(1, $backwards) as $i) {
            $name = array_pop($class);
        }

        $name = $name ?: $default;

        return strtolower($name);
    }
}
