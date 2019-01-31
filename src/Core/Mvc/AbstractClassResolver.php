<?php
/**
 * Part of Apli project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Apli\Core\Mvc;

use Apli\Support\Queue\PriorityQueue;

/**
 * The AbstractResolver class.
 */
abstract class AbstractClassResolver
{

    /**
     * Property namespaces.
     *
     * @var  PriorityQueue
     */
    protected $namespaces = [];

    /**
     * Property aliases.
     *
     * @var  array
     */
    protected $classAliases = [];

    /**
     * Property baseClass.
     *
     * @var  string
     */
    protected $baseClass;

    /**
     * ControllerResolver constructor.
     *
     * @param array           $namespaces
     */
    public function __construct($namespaces = [])
    {
        $this->setNamespaces($namespaces);
    }

    /**
     * Resolve class path.
     *
     * @param   string $name
     *
     * @return  string
     * @throws \DomainException
     */
    public function resolve($name)
    {
        if (class_exists($name)) {
            return $name;
        }

        $name = static::normalise($name);

        $namespaces = clone $this->namespaces;

        $this->registerDefaultNamespace($namespaces);

        foreach (clone $namespaces as $ns) {
            $class = $ns . '\\' . $name;

            if (class_exists($class = $this->resolveClassAlias($class))) {
                if ($this->baseClass && !is_subclass_of($class, $this->baseClass)) {
                    throw new \DomainException(sprintf(
                        'Class: "%s" should be sub class of %s',
                        $class,
                        $this->baseClass
                    ));
                }

                return $class;
            }
        }

        throw new \DomainException(sprintf(
            'Can not find any classes with name: "%s" in package: "%s", namespaces: ( %s ).',
            $name,
            $this->package->getName(),
            implode(" |\n ", $namespaces->toArray())
        ));
    }

    /**
     * create
     *
     * @param string $name
     * @param array  ...$args
     *
     * @return  object
     * @throws \DomainException
     */
    public function create($name, ...$args)
    {
        $class = $this->resolve($name);

        return new $class(...$args);
    }

    /**
     * If didn't found any exists class, fallback to default class which in current package..
     *
     * @return string Found class name.
     */
    abstract protected function getDefaultNamespace();

    /**
     * registerDefaultNamespace
     *
     * @param PriorityQueue $namespaces
     * @param int           $priority
     *
     * @return  static
     */
    protected function registerDefaultNamespace(PriorityQueue $namespaces, $priority = PriorityQueue::NORMAL)
    {
        $namespaces->insert($this->getDefaultNamespace(), $priority);

        return $this;
    }

    /**
     * addNamespace
     *
     * @param string $namespace
     * @param int    $priority
     *
     * @return  static
     */
    public function addNamespace($namespace, $priority = PriorityQueue::NORMAL)
    {
        $namespace = static::normalise($namespace);

        $this->namespaces->insert($namespace, $priority);

        return $this;
    }

    /**
     * Method to get property Namespaces
     *
     * @return  PriorityQueue
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Method to set property namespaces
     *
     * @param   array|PriorityQueue $namespaces
     *
     * @return  static  Return self to support chaining.
     */
    public function setNamespaces($namespaces)
    {
        if (!$namespaces instanceof PriorityQueue) {
            $namespaces = new PriorityQueue($namespaces);
        }

        $this->namespaces = $namespaces;

        return $this;
    }

    /**
     * dumpNamespaces
     *
     * @return  array
     */
    public function dumpNamespaces()
    {
        return $this->namespaces->toArray();
    }

    /**
     * reset
     *
     * @return  static
     */
    public function reset()
    {
        $this->setNamespaces(new PriorityQueue);
        $this->setClassAliases([]);

        return $this;
    }

    /**
     * normalise
     *
     * @param   string $name
     *
     * @return  string
     */
    public static function normalise($name)
    {
        $name = str_replace('.', '\\', $name);

        return $name;
    }

    /**
     * resolveClassAlias
     *
     * @param   string $alias
     *
     * @return  string
     */
    public function resolveClassAlias($alias)
    {
        $alias = static::normalise($alias);

        if (isset($this->classAliases[$alias])) {
            return $this->classAliases[$alias];
        }

        return $alias;
    }

    /**
     * addClassAlias
     *
     * @param   string $alias
     * @param   string $class
     *
     * @return  static
     */
    public function addClassAlias($alias, $class)
    {
        $alias = static::normalise($alias);
        $class = static::normalise($class);

        $this->classAliases[$alias] = $class;

        return $this;
    }

    /**
     * Method to get property Aliases
     *
     * @return  array
     */
    public function getClassAliases()
    {
        return $this->classAliases;
    }

    /**
     * Method to set property aliases
     *
     * @param   array $classAliases
     *
     * @return  static  Return self to support chaining.
     */
    public function setClassAliases(array $classAliases)
    {
        $this->classAliases = $classAliases;

        return $this;
    }
}
