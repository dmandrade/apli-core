<?php
/**
 *  Copyright (c) 2019 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file HttpFactory.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 17/11/19 at 18:36
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 17/11/2019
 * Time: 18:36
 */

namespace Apli\Core\Http;

use Apli\Http\HttpFactory as BaseHttpFactory;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface,
    ServerRequestInterface
};

class HttpFactory extends BaseHttpFactory
{
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request($method, $uri);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response($code, [], null, $reasonPhrase);
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new Request($method, $uri, [], null, static::marshalProtocolVersionFromSapi($serverParams), $serverParams);
    }
}
