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
use Apli\Data\Map;
use Apli\Environment\Environment;
use Apli\Http\Emitter\SapiEmitter;
use Apli\Http\HttpFactoryInterface;
use Apli\Uri\UriException;
use Apli\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

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
     * Property router.
     *
     * @var  Router
     */
    protected $router;
    /**
     * @var ServerRequestInterface
     */
    protected $request;

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

        $this->set('execution.start', microtime(true));
        $this->set('execution.memory', memory_get_usage());
    }

    /**
     * Prepare execute hook.
     *
     * @return void
     */
    protected function init(): void
    {
        $this->request = $this->serverRequestCreator->fromGlobals();
        $this->getEmitter()->push(new SapiEmitter());
    }

    /**
     * Method to dispatch http route request
     *
     * @return mixed
     */
    public function doExecute()
    {
        return $this->getRouter()->handle($this->request);
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
}
