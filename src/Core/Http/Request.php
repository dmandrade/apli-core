<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file Request.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 05/09/18 at 10:48
 */

namespace Apli\Core\Http;

use Apli\Core\Http\Traits\InteractsWithInput;
use Apli\Http\DefaultServerRequest;
use Apli\Http\Message\ServerRequest;
use Apli\Http\ServerRequestFactory;
use Apli\Support\Arr;
use Apli\Support\Arrayable;
use Apli\Support\Traits\Macroable;
use ArrayAccess;

/**
 * Class Request
 * @package Apli\Core\Http
 */
class Request extends DefaultServerRequest implements Arrayable, ArrayAccess
{
    use Macroable, InteractsWithInput;

    /**
     * The decoded JSON content for the request.
     *
     * @var array|null
     */
    protected $json;

    /**
     * All of the converted files for the request.
     *
     * @var array
     */
    protected $convertedFiles;

    /**
     * Create a new Illuminate HTTP request from server variables.
     *
     * @return static
     */
    public static function capture()
    {
        return static::createFromBase(ServerRequestFactory::createFromGlobals());
    }


    /**
     * Create an Illuminate request from a Symfony instance.
     *
     * @param  ServerRequest  $request
     * @return self
     */
    public static function createFromBase(ServerRequest $request)
    {
        if ($request instanceof static) {
            return $request;
        }

        $request = (new static)->duplicate(
            $request->getServerParams(), $request->getUploadedFiles(), $request->getCookieParams(),
            $request->getQueryParams(), $request->getHeaders(), $request->getParsedBody(), $request->getProtocolVersion(),
            $request->getMethod(), $request->getUri(), $request->getBody()
        );

        return $request;
    }

    /**
     * @param array|null $serverParams
     * @param array|null $uploadedFiles
     * @param array|null $cookies
     * @param array|null $queryParams
     * @param null|array|object $parsedBody
     * @param string $protocol
     * @return Request
     */
    public function duplicate(array $serverParams = null, array $uploadedFiles = null, array $cookies = null, array $queryParams = null, array $headers = null , $parsedBody, $protocol, $method, $uri, $stream)
    {
        /** @var self $dup */
        $dup = clone $this;

        $dup->setHeaders($headers);
        $dup->serverParams  = $serverParams;
        $dup->uploadedFiles = $uploadedFiles;
        $dup->cookieParams  = $cookies;
        $dup->queryParams   = $queryParams;
        $dup->parsedBody    = $parsedBody;
        $dup->protocol      = $protocol;
        $dup->method      = $method;
        $dup->uri      = $uri;
        $dup->stream      = $stream;

        return $dup;
    }

    /**
     * Filter the given array of files, removing any empty values.
     *
     * @param  mixed  $files
     * @return mixed
     */
    protected function filterFiles($files)
    {
        if (! $files) {
            return;
        }

        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $files[$key] = $this->filterFiles($files[$key]);
            }

            if (empty($files[$key])) {
                unset($files[$key]);
            }
        }

        return $files;
    }

    /**
     * Get the input source for the request.
     *
     * @return array
     */
    protected function getInputSource()
    {
        if ($this->isJson()) {
            return $this->json();
        }

        return in_array($this->getRealMethod(), ['GET', 'HEAD']) ? $this->queryParams : $this->parsedBody;
    }

    /**
     * Gets the "real" request method.
     *
     * @return string The request method
     *
     * @see getMethod()
     */
    public function getRealMethod()
    {
        return $this->getMethod();
    }

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson()
    {
        $haystack = $this->getHeaderLine('CONTENT_TYPE');
        $needles = ['/json', '+json'];

        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the JSON payload for the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function json($key = null, $default = null)
    {
        if (! isset($this->json)) {
            $this->json = (array) json_decode($this->stream->getContents(), true);
        }

        if (is_null($key)) {
            return $this->json;
        }

        return Arr::get($this->json, $key, $default);
    }

    /**
     * Determine if the request is the result of an AJAX call.
     *
     * @return bool
     */
    public function ajax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * It works if your JavaScript library sets an X-Requested-With HTTP header.
     * It is known to work with common JavaScript frameworks:
     *
     * @see http://en.wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
     *
     * @return bool true if the request is an XMLHttpRequest, false otherwise
     */
    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == $this->getHeader('X-Requested-With');
    }

    /**
     * Determine if the request is the result of an PJAX call.
     *
     * @return bool
     */
    public function pjax()
    {
        return $this->getHeader('X-PJAX') == true;
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }

    /**
     * This method belongs to Symfony HttpFoundation and is not usually needed when using Laravel.
     *
     * Instead, you may use the "input" method.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this !== $result = Arr::get($this->attributes, $key, $this)) {
            return $result;
        }

        if ($this !== $result = Arr::get($this->queryParams, $key, $this)) {
            return $result;
        }

        if ($this !== $result = Arr::get($this->parsedBody, $key, $this)) {
            return $result;
        }

        return $default;
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    public function input($key = null, $default = null)
    {
        return Arr::get(
            $this->getInputSource() + $this->queryParams, $key, $default
        );
    }

    /**
     * Get all of the input and files for the request.
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function all($keys = null)
    {
        $input = array_replace_recursive($this->input(), $this->allFiles());

        if (! $keys) {
            return $input;
        }

        $results = [];

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            Arr::set($results, $key, Arr::get($input, $key));
        }

        return $results;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists(
            $offset,
            $this->all()
        );
    }

    /**
     * Get the value at the given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Remove the value at the given offset.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * Check if an input element is set on the request.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return ! is_null($this->__get($key));
    }

    /**
     * Get an input element from the request.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->all())) {
            return Arr::get($this->all(), $key);
        }

        return $this->route($key);
    }
}
