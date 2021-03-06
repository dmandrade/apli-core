<?php
/**
 * Part of Apli project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Apli\Core\Mvc;

use Apli\Core\View\AbstractView;

/**
 * Class ViewResolver
 * @package Apli\Core\Mvc
 */
class ViewResolver extends AbstractClassResolver
{
    /**
     * Property baseClass.
     *
     * @var  string
     */
    protected $baseClass = AbstractView::class;

    /**
     * Get container key prefix.
     *
     * @return  string
     */
    public static function getPrefix()
    {
        return 'view';
    }

    /**
     * If didn't found any exists class, fallback to default class which in current package..
     *
     * @return string Found class name.
     */
    protected function getDefaultNamespace()
    {
        return 'Main\View';
    }
}
