<?php

namespace CodexSoft\Context;

/**
 * ParameterBag is a container for key/value pairs.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ParameterBag implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * Parameter storage.
     */
    protected $parameters;

    /**
     * @param array $parameters An array of parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns the parameters.
     *
     * @return array An array of parameters
     */
    public function all(): array
    {
        return $this->parameters;
    }

    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    public function keys(): array
    {
        return array_keys($this->parameters);
    }

    /**
     * Replaces the current parameters by a new set.
     *
     * @param array $parameters An array of parameters
     * @return static
     */
    public function replace(array $parameters = []): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Adds parameters.
     *
     * @param array $parameters An array of parameters
     * @return static
     */
    public function add(array $parameters = []): self
    {
        $this->parameters = array_replace($this->parameters, $parameters);
        return $this;
    }

    /**
     * Returns a parameter by name.
     *
     * @param string $key     The key
     * @param mixed  $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return \array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    /**
     * Sets a parameter by name.
     *
     * @param string $key The key
     * @param mixed $value The value
     *
     * @return static
     */
    public function set($key, $value): self
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * Returns true if the parameter is defined.
     *
     * @param string $key The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function has($key): bool
    {
        return \array_key_exists($key, $this->parameters);
    }

    /**
     * Removes a parameter.
     *
     * @param string $key The key
     *
     * @return static
     */
    public function remove($key): self
    {
        unset($this->parameters[$key]);
        return $this;
    }

    /**
     * Returns the alphabetic characters of the parameter value.
     *
     * @param string $key     The parameter key
     * @param string $default The default value if the parameter key does not exist
     *
     * @return string The filtered value
     */
    public function getAlpha($key, $default = ''): string
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the alphabetic characters and digits of the parameter value.
     *
     * @param string $key     The parameter key
     * @param string $default The default value if the parameter key does not exist
     *
     * @return string The filtered value
     */
    public function getAlnum($key, $default = ''): string
    {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the digits of the parameter value.
     *
     * @param string $key     The parameter key
     * @param string $default The default value if the parameter key does not exist
     *
     * @return string The filtered value
     */
    public function getDigits($key, $default = ''): string
    {
        // we need to remove - and + because they're allowed in the filter
        return str_replace(['-', '+'], '', $this->filter($key, $default, FILTER_SANITIZE_NUMBER_INT));
    }

    /**
     * Returns the parameter value converted to integer.
     *
     * @param string $key     The parameter key
     * @param int    $default The default value if the parameter key does not exist
     *
     * @return int The filtered value
     */
    public function getInt($key, $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    /**
     * Returns the parameter value converted to boolean.
     *
     * @param string $key     The parameter key
     * @param bool   $default The default value if the parameter key does not exist
     *
     * @return bool The filtered value
     */
    public function getBoolean($key, $default = false): bool
    {
        return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Filter key.
     *
     * @param string $key     Key
     * @param mixed  $default Default = null
     * @param int    $filter  FILTER_* constant
     * @param mixed  $options Filter options
     *
     * @see https://php.net/filter-var
     *
     * @return mixed
     */
    public function filter($key, $default = null, $filter = FILTER_DEFAULT, $options = [])
    {
        $value = $this->get($key, $default);

        // Always turn $options into an array - this allows filter_var option shortcuts.
        if (!\is_array($options) && $options) {
            $options = ['flags' => $options];
        }

        // Add a convenience check for arrays.
        if (\is_array($value) && !isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        return filter_var($value, $filter, $options);
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    public function count(): int
    {
        return \count($this->parameters);
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->parameters[] = $value;
        } else {
            $this->set($offset,$value);
        }
    }

    public function offsetExists($offset) {
        return $this->has($offset);
    }

    public function offsetUnset($offset) {
        $this->remove($offset);
    }

    public function offsetGet($offset) { // todo: throw exception?
        return $this->get($offset);
    }

    /**
     * @param array ...$keys
     *
     * @return bool
     */
    public function hasNotEmpty( ...$keys ): bool
    {

        if ( count($keys) == 1 && is_array( $keys[0] ) )
            $keys = $keys[0];

        foreach ( $keys as $key ) {
            if ( !$this->has( $key ) ) return false;
            if ( !$this->get( $key ) ) return false;
        }

        return true;

    }

    /**
     * @param array $array
     *
     * @return static
     */
    public static function from(array $array): self
    {
        return new static( $array );
    }

    /**
     * @param array $array
     *
     * @return static
     */
    public function mergeWith( array $array ): self
    {
        $this->parameters = array_merge($this->parameters, $array);
        return $this;
    }

    /**
     * @return static
     */
    public function unique(): self
    {
        $this->parameters = array_unique($this->parameters);
        return $this;
    }

    /**
     * @param string|\Closure $filterFunction
     *
     * @return static
     */
    public function filterCustom($filterFunction): self
    {
        $this->parameters = array_filter($this->parameters, $filterFunction);
        return $this;
    }

}
