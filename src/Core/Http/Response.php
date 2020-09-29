<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file Response.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 05/09/18 at 10:48
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 05/09/2018
 * Time: 10:48
 */

namespace Apli\Core\Http;


use Apli\Http\Response\DefaultResponse;
use Apli\Support\Arrayable;
use Apli\Support\Jsonable;
use Apli\Support\Traits\Macroable;
use ArrayObject;
use JsonSerializable;
use function is_array;

/**
 * Class Response
 * @package Apli\Core\Http
 */
class Response extends DefaultResponse
{
    use Macroable;

    /**
     * @var mixed
     */
    protected $original;

    /**
     * Set the content on the response.
     *
     * @param  mixed  $content
     * @return $this
     */
    public function setContent($content): self
    {
        $this->original = $content;

        // If the content is "JSONable" we will set the appropriate header and convert
        // the content to JSON. This is useful when returning something like models
        // from routes that will be automatically transformed to their JSON form.
        if ($this->shouldBeJson($content)) {
            $this->withHeader('Content-Type', 'application/json');

            $content = $this->morphToJson($content);
        }

        $this->getBody()->write($content);

        return $this;
    }

    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed  $content
     * @return bool
     */
    protected function shouldBeJson($content): bool
    {
        return $content instanceof Arrayable ||
            $content instanceof Jsonable ||
            $content instanceof ArrayObject ||
            $content instanceof JsonSerializable ||
            is_array($content);
    }

    /**
     * Morph the given content into JSON.
     *
     * @param  mixed   $content
     * @return string
     */
    protected function morphToJson($content): string
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        }

        if ($content instanceof Arrayable) {
            $content = $content->toArray();
        }

        return json_encode($content);
    }
}
