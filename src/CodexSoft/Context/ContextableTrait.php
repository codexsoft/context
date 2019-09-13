<?php

namespace CodexSoft\Context;

/**
 * Can be used as a PHPDocumented shortcut to instance of a specific class by resolving it in
 * current context layer
 */
trait ContextableTrait
{

    /**
     * @return static
     * @throws Exception\NotResolvedException
     */
    public static function instance()
    {
        return Context::get(static::class);
    }

    /**
     * @return static
     * @throws Exception\NotResolvedException
     */
    public static function instanceOrParent()
    {
        return Context::get(static::class, LayersManager::ACCEPT_PARENTS);
    }

    /**
     * @return static
     * @throws Exception\NotResolvedException
     */
    public static function instanceOrChild()
    {
        return Context::get(static::class, LayersManager::ACCEPT_CHILDREN);
    }

    /**
     * @return null|static
     */
    public static function instanceOrChildOrNull()
    {
        try {
            return self::instanceOrChild();
        } catch (Exception\NotResolvedException $e) {
            return null;
        }
    }

    /**
     * @return null|static
     */
    public static function instanceOrParentOrNull()
    {
        try {
            return self::instanceOrParent();
        } catch (Exception\NotResolvedException $e) {
            return null;
        }
    }

    /**
     * @return null|static
     */
    public static function instanceOrNull()
    {
        try {
            return self::instance();
        } catch (Exception\NotResolvedException $e) {
            return null;
        }
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return static
     */
    public static function fromContext()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return self::instance();
    }

    /**
     * just an alias
     *
     * @return static
     */
    public static function get()
    {
        return self::fromContext();
    }

}