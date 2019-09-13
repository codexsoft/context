<?php

namespace CodexSoft\Context;

/**
 * Fascade for using layered service containers
 */
class Context
{

    /** @var LayersManager */
    protected static $layersManager;

    /**
     * @return LayersManager
     */
    public static function getLayersManager(): LayersManager
    {
        if (!static::$layersManager instanceof LayersManager) {
            static::$layersManager = new LayersManager;
        }

        return static::$layersManager;
    }

    /**
     * @param array $dependencies
     *
     * @return array
     * @internal
     */
    public static function normalizeDependencies(array $dependencies): array
    {
        $result = [];
        foreach ($dependencies as $key => $value) {

            if (\is_int($key) && \is_object($value)) {
                $result[\get_class($value)] = $value;
            } else {
                $result[$key] = $value;
            }

        }

        return $result;
    }

    /**
     * @param object|object[] $instances
     * @return void
     * Чтобы работало предсказуемо и заменяло существующие, надо указывать обязательно string keys!
     */
    public static function mergeWith($instances): void
    {
        $parameter = \is_object($instances) ? [$instances] : $instances;
        $parameter = static::normalizeDependencies($parameter);
        static::getLayersManager()->actual()->mergeWith($parameter);
    }

    /**
     * Returns resolved instance of a given class, using provided matching mode
     * Throws exception if not found
     *
     * @param $class
     * @param int $mode
     *
     * @return mixed|null
     * @throws Exception\NotResolvedException
     */
    public static function get($class, $mode = LayersManager::ACCEPT_SAME)
    {
        return static::getLayersManager()->resolve($class, $mode);
    }

    /**
     * Returns resolved instance of a given class, using provided matching mode
     * Returns null if not found
     *
     * @param $class
     * @param int $mode
     *
     * @return mixed|null
     */
    public static function getOrNull($class, $mode = LayersManager::ACCEPT_SAME)
    {
        return static::getLayersManager()->resolveOrNull($class, $mode);
    }

    /**
     * @return ServiceContainer|null
     */
    public static function actual(): ?ServiceContainer
    {
        return static::getLayersManager()->actual();
    }

    /**
     * Destroys current context (if any)
     */
    public static function destroy(): void
    {
        static::getLayersManager()->destroy();
    }

    /**
     * Returns first (base) layer
     *
     * @return ServiceContainer|null
     */
    public static function base(): ?ServiceContainer
    {
        return static::getLayersManager()->base();
    }

    /**
     * Creates [isolated] layer
     *
     * @param array $dependencies
     * @param bool $isolated
     */
    public static function create(array $dependencies, $isolated = false): void
    {
        static::getLayersManager()->create($dependencies, $isolated);
    }

}