<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file WebApplication.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 04/09/18 at 10:28
 */

namespace Apli\Core\Application;

use Apli\Application\AbstractWebApplication;
use Apli\Core\Http\Request;
use Apli\Data\Map;
use Apli\Environment\Environment;
use Apli\Http\Message\Response;
use Apli\Http\Message\ServerRequest;
use Apli\Router\Router;

/**
 * Class WebApplication
 *
 * @property-read  Emitter  $emitter
 *
 * @package Apli\Core\Application
 */
class WebApplication extends AbstractWebApplication
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'web';

    /**
     * Property mode.
     *
     * @var  string
     */
    protected $mode;

    /**
     * Property configPath.
     *
     * @var  string
     */
    protected $rootPath;

    /**
     * Property middlewares.
     *
     * @var  Psr7ChainBuilder
     */
    protected $middlewares;

    /**
     * Property router.
     *
     * @var  Router
     */
    protected $router;

    /**
     * AbstractWebApplication constructor.
     * @param ServerRequest|null $request
     * @param Map|null     $config
     * @param Environment|null   $environment
     */
    public function __construct(
        ServerRequest $request = null,
        Map $config = null,
        Environment $environment = null
    )
    {
        $request = $request ?: Request::capture();
        $this->config = $config instanceof Map ? $config : new Map();
        $this->name     = $this->config->get('name', $this->name);
        $this->rootPath = $this->config->get('path.root', $this->rootPath);

        parent::__construct($request, $this->config, $environment);

        $this->set('execution.start', microtime(true));
        $this->set('execution.memory', memory_get_usage());
    }

    /**
     * Custom initialisation method.
     *
     * Called at the end of the AbstractApplication::__construct() method.
     * This is for developers to inject initialisation code for their application classes.
     *
     * @return void
     */
    protected function init()
    {
    }

    /**
     * Method to dispatch http reoute request
     *
     * @return Response
     */
    public function doExecute()
    {
        return $this->router->dispatch($this->request);
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        if (!$this->router) {
            /** @var Router $router */
            $router = new Router();

            $this->router = $router;
        }

        return $this->router;
    }

    /**
     * Method to get property Mode
     *
     * @return  string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Method to set property mode
     *
     * @param   string $mode
     *
     * @return  static  Return self to support chaining.
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * is utilized for reading data from inaccessible members.
     *
     * @param   $name  string
     *
     * @return  mixed
     */
    public function __get($name)
    {

        if ($name === 'router') {
            return $this->getRouter();
        }

        if ($name === 'logger') {
            return $this->getLogger();
        }

        return parent::__get($name);
    }
}
