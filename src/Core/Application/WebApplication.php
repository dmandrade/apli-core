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
use Apli\Core\Http\HttpFactory;
use Apli\Data\Map;
use Apli\Environment\Environment;
use Apli\Http\HttpFactoryInterface;
use Apli\Uri\UriException;
use Apli\Router\Router;
use Exception;

/**
 * Class WebApplication
 *
 * @property-read  Router  $router
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
     * Property router.
     *
     * @var  Router
     */
    protected $router;

    /**
     * @var array
     */
    protected $allowGetProperties = [
        'config',
        'router',
        'logger'
    ];

    /**
     * WebApplication constructor.
     *
     * @param HttpFactoryInterface|null $httpFactory
     * @param Map|null                    $config
     * @param Environment|null            $environment
     * @throws UriException
     */
    public function __construct(
        HttpFactoryInterface $httpFactory = null,
        Map $config = null,
        Environment $environment = null
    )
    {
        parent::__construct($httpFactory, $config, $environment);

        $this->name     = $this->config->get('name', $this->name);
        $this->rootPath = $this->config->get('path.root', $this->rootPath);
        $this->set('execution.start', microtime(true));
        $this->set('execution.memory', memory_get_usage());
    }

    /**
     * Method to dispatch http route request
     *
     * @return mixed
     * @throws Exception
     */
    public function doExecute()
    {
        $route = explode('/', trim(@$_GET['r'], '/'));

        if ($this->router === null) {
            throw (new Exception('this->Router was not set', - 2));
        }

        return $this->getRouter()->callRoute($route);
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        if (!$this->router) {
            $this->router = new Router();
        }

        return $this->router;
    }

    /**
     * Method to get property Mode
     *
     * @return  string
     */
    public function getMode(): string
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
    public function setMode($mode): self
    {
        $this->mode = $mode;

        return $this;
    }
}
