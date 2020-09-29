<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file AbstractController.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 05/09/18 at 12:35
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 05/09/2018
 * Time: 12:35
 */

namespace Apli\Core\Controller;


use Apli\Core\Http\Request;
use Apli\Core\Http\Response;
use Apli\Core\Mvc\MvcResolver;
use Apli\IO\Input;
use ErrorException;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class AbstractController
 * @package Apli\Core\Controller
 */
abstract class AbstractController
{

    /**
     * MVC group name.
     *
     * @var  string
     */
    protected $name;

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var array
     */
    protected $params;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var MvcResolver
     */
    protected $mvcResolver;

    /**
     * @var Input
     */
    protected $input;

    /**
     * AbstractController constructor.
     */
    public function __construct()
    {
        $this->response = new Response();
        $this->init();
    }

    protected function getMvcResolver()
    {
        if(!$this->mvcResolver) {
            $this->mvcResolver = new MvcResolver();
        }

        return $this->mvcResolver;
    }

    /**
     * Init this class.
     *
     * @return  void
     */
    protected function init()
    {
        // Override it if you need.
    }


    /**
     * @param Request $request
     * @param mixed   ...$params
     * @return mixed
     * @throws Throwable
     */
    public function __invoke($request, $params) {
        $result = false;
        try {
            $this->request = $request;
            $this->params = $params;

            $this->prepareExecute();

            $result = $this->postExecute($this->doExecute());
        } catch (Exception $e) {
            $this->processFailure($e);
        } catch (Throwable $t) {
            // You can do some error handling in processFailure(), for example: rollback the transaction.
            $this->processFailure(new ErrorException($t->getMessage(), $t->getCode(), E_ERROR, $t->getFile(),
                $t->getLine(), $t));

            throw $t;
        }

        if ($result === false) {
            $this->processFailure(new Exception('Unknown Error'));
        }

        return $this->processSuccess($result);
    }

    /**
     * @param null   $name
     * @param string $format
     * @return mixed
     */
    public function getView($name = null, $format = 'html')
    {
        $name = $name ?: $this->getName();

        // Find if package exists
        $viewName = sprintf('%s\%s%sView', ucfirst($name), ucfirst($name), ucfirst($format));

        // Use MvcResolver to find view class.
        $class = $this->getMvcResolver()->getViewResolver()->resolve($viewName);

        return new $class;
    }

    /**
     * @param int $backwards
     * @return string
     */
    public function getName($backwards = 2): string
    {
        if(!$this->name) {
            $this->name = $this->getMvcResolver()->guessName(static::class, $backwards);
        }

        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Input
     */
    public function getInput(): Input
    {
        if (!$this->input) {
            $input = new Input(array_merge((array) $this->request, $this->params));
            $input->request = new Input((array) $this->request);
            $input->route = new Input($this->params);
            $this->setInput($input);
        }

        return $this->input;
    }

    /**
     * Method to set property input
     *
     * @param   Input $input
     *
     * @return  static  Return self to support chaining.
     */
    protected function setInput(Input $input): self
    {
        $this->input = $input;

        return $this;
    }

    /**
     * A hook before main process executing.
     */
    protected function prepareExecute(): void
    {
    }

    /**
     * The main execution process.
     *
     * @return mixed
     */
    abstract protected function doExecute();

    /**
     * A hook after main process executing.
     *
     * @param null    $result
     * @return mixed
     */
    protected function postExecute($result = null)
    {
        return $result;
    }

    /**
     * Process failure.
     *
     * @param Exception $e
     *
     * @throws Exception
     */
    public function processFailure(Exception $e = null): void
    {
        throw $e;
    }

    /**
     * Process success.
     *
     * @param  mixed $result
     *
     * @return mixed
     */
    public function processSuccess($result)
    {
        return $this->response->setContent($result);
    }

    /**
     * Method to get property Request
     *
     * @return  RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Method to set property request
     *
     * @param   RequestInterface $request
     *
     * @return  static  Return self to support chaining.
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Method to get property Response
     *
     * @return  ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Method to set property response
     *
     * @param   ResponseInterface $response
     *
     * @return  static  Return self to support chaining.
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }
}
