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
        $this->mvcResolver = new MvcResolver();
        $this->init();
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
     * @return bool
     * @throws \Throwable
     */
    public function __invoke(Request $request) {
        try {
            $this->request = $request;

            $this->prepareExecute();

            $result = $this->doExecute();

            $result = $this->postExecute($result);
        } catch (ValidateFailException $e) {
            return $this->processFailure($e);
        } catch (\Exception $e) {
            throw $e;
        } catch (\Throwable $t) {
            // You can do some error handling in processFailure(), for example: rollback the transaction.
            $this->processFailure(new \ErrorException($t->getMessage(), $t->getCode(), E_ERROR, $t->getFile(),
                $t->getLine(), $t));

            throw $t;
        }

        if ($result === false) {
            // You can do some error handling in processFailure(), for example: rollback the transaction.
            return $this->processFailure(new \Exception('Unknown Error'));
        }

        // Now we return result to package that it will handle response.
        return $this->processSuccess($result);
    }

    /**
     * Get view object
     *
     * @param null   $name
     * @param string $format
     */
    public function getView($name = null, $format = 'html')
    {
        $name = $name ?: $this->getName();

        // Find if package exists
        $viewName = sprintf('%s\%s%sView', ucfirst($name), ucfirst($name), ucfirst($format));

        // Use MvcResolver to find view class.
        $class = $this->mvcResolver->getViewResolver()->resolve($viewName);

        return new $class;
    }

    /**
     * @param int $backwards
     * @return string
     */
    public function getName($backwards = 2)
    {
        if(!$this->name) {
            $this->name = $this->mvcResolver->guessName(static::class, $backwards);
        }

        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Input
     */
    public function getInput()
    {
        if (!$this->input) {
            $this->input = new Input;
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
    public function setInput(Input $input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * A hook before main process executing.
     *
     * @return mixed
     */
    protected function prepareExecute()
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
     * @param \Exception $e
     *
     * @throws \Exception
     */
    public function processFailure(\Exception $e = null)
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
     * @return  Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Method to set property request
     *
     * @param   Request $request
     *
     * @return  static  Return self to support chaining.
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Method to get property Response
     *
     * @return  Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Method to set property response
     *
     * @param   Response $response
     *
     * @return  static  Return self to support chaining.
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }
}
