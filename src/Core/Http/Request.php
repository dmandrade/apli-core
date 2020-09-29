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
use Psr\Http\Message\ServerRequestInterface;
use Apli\Support\Arr;
use Apli\Support\Arrayable;
use Apli\Support\Traits\Macroable;
use ArrayAccess;
use function is_array;
use function in_array;
use function mb_strpos;
use function json_decode;

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
     * @var array
     */
    protected $routeParams = [];


    /**
     * Create request from a ServerRequestInterface instance.
     *
     * @param  ServerRequestInterface  $request
     * @return self
     */
    public static function create(ServerRequestInterface $request): self
    {
        if ($request instanceof static) {
            return $request;
        }

        return (new static)->duplicate(
            $request->getServerParams(), $request->getUploadedFiles(), $request->getCookieParams(),
            $request->getQueryParams(), $request->getHeaders(), $request->getParsedBody(), $request->getProtocolVersion(),
            $request->getMethod(), $request->getUri(), $request->getBody()
        );
    }

    /**
     * @param array|null $serverParams
     * @param array|null $uploadedFiles
     * @param array|null $cookies
     * @param array|null $queryParams
     * @param array|null $headers
     * @param            $parsedBody
     * @param            $protocol
     * @param            $method
     * @param            $uri
     * @param            $stream
     * @return Request
     */
    public function duplicate(
        array $serverParams = null,
        array $uploadedFiles = null,
        array $cookies = null,
        array $queryParams = null,
        array $headers = null,
        $parsedBody,
        $protocol,
        $method,
        $uri,
        $stream)
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
     * @return array
     */
    protected function filterFiles(array $files): array
    {
        if ($files) {
            foreach ($files as $key => $file) {
                if (is_array($file)) {
                    $files[$key] = $this->filterFiles($files[$key]);
                }

                if (empty($files[$key])) {
                    unset($files[$key]);
                }
            }
        }

        return $files;
    }

    /**
     * Get the input source for the request.
     *
     * @return array
     */
    protected function getInputSource(): array
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
    public function getRealMethod(): string
    {
        return $this->getMethod();
    }

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson(): bool
    {
        $haystack = $this->getHeaderLine('CONTENT_TYPE');
        $needles = ['/json', '+json'];

        foreach ($needles as $needle) {
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
        if ($this->json === null) {
            $this->json = (array) json_decode($this->stream->getContents(), true);
        }

        if ($key === null) {
            return $this->json;
        }

        return Arr::get($this->json, $key, $default);
    }

    /**
     * Determine if the request is the result of an AJAX call.
     *
     * @return bool
     */
    public function ajax(): bool
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
    public function isXmlHttpRequest(): bool
    {
        return 'XMLHttpRequest' === $this->getHeader('X-Requested-With');
    }

    /**
     * Determine if the request is the result of an PJAX call.
     *
     * @return bool
     */
    public function pjax(): bool
    {
        return $this->getHeader('X-PJAX') === true;
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url(): string
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }
}
