<?php

namespace CodexSoft\Context;

use CodexSoft\Context\Exception\NotResolvedException;

/**
 * Can be used as a layered dependency container
 * Improves testability, can be faked e.t.c.
 */
class LayersManager
{

    public const ACCEPT_SAME = 1;
    public const ACCEPT_PARENTS = 2;
    public const ACCEPT_CHILDREN = 3;
    public const ACCEPT_BOTH = 4;

    /**
     * @var ServiceContainer[]
     */
    private $serviceContainers = [];

    /**
     * @var ServiceContainer
     */
    private $current;

    /**
     * @param array $services
     * @param bool $isolated
     */
    public function create(array $services, $isolated = false): void
    {

        $layer = new ServiceContainer();

        $services = Context::normalizeDependencies($services);

        // если контекст не изолированный, то мержим его поверх последнего контекста
        if (!$isolated && $this->current) {
            $services = array_replace($this->current->all(), $services);
        }
        $layer->setIsolated($isolated)->add($services);
        $this->current = $layer;
        $this->serviceContainers[] = $layer;

    }

    public function destroy(): void
    {

        if ($this->serviceContainers) {
            array_pop($this->serviceContainers);
        }

        $this->current = \count($this->serviceContainers)
            ? array_values(array_slice($this->serviceContainers, -1))[0]
            : null;

    }

    /**
     * @return ServiceContainer
     */
    public function actual(): ServiceContainer
    {

        if (!$this->current) {
            $this->create([]);
        }

        return $this->current;

    }

    /**
     *
     * @return ServiceContainer|null
     */
    public function base(): ?ServiceContainer
    {
        if (\count($this->serviceContainers)) {
            return $this->serviceContainers[0];
        }
        return null;
    }

    /**
     * @param $className
     * @param int $mode
     *
     * @return mixed|null
     */
    public function resolveOrNull($className, $mode = self::ACCEPT_SAME)
    {
        try {
            return $this->resolve($className, $mode);
        } catch (NotResolvedException $e) {
            return null;
        }
    }

    /**
     * @param string $searchKey идентификатор или класс объекта искомой зависимости
     * @param int $mode 1 - SAME, 2 - ACCEPT PARENT, 3 - ACCEPT CHILD, 4 - ACCEPT BOTH
     *
     * @return mixed|null
     * @throws NotResolvedException
     */
    public function resolve($searchKey, $mode = self::ACCEPT_SAME)
    {

        // todo: accept interface?

        foreach (array_reverse($this->serviceContainers) as $serviceLayer) {

            /** @var ServiceContainer $serviceLayer */
            $dependency = null;

            // если ищем класс
            if (class_exists($searchKey)) {

                foreach ($serviceLayer->all() as $key => $value) {

                    // распаковываем зависимость
                    if ($value instanceof \Closure) // точно?..
                    {
                        $value = $serviceLayer[$searchKey] = $value();
                    }

                    // если нужно точное соответствие класса
                    if (( $mode === self::ACCEPT_SAME ) && \is_object($value) && \get_class($value) !== $searchKey) {
                        continue;
                    }
                    /** @noinspection RedundantElseClauseInspection */
                    // если подойдет объект искомого класса или один из родительских
                    elseif (( $mode === self::ACCEPT_PARENTS )
                        && !self::isSameOrExtends($searchKey, $value)) {
                        continue;
                    } // если подойдет объект искомого класса или один из дочерних
                    elseif (( $mode === self::ACCEPT_CHILDREN )
                        && !self::isSameOrExtends($value, $searchKey)) {
                        continue;
                    } // если подойдет объект искомого класса, один из дочерних или один из родительских
                    elseif (( $mode === self::ACCEPT_BOTH )
                        && !self::isSameOrExtends($value, $searchKey)
                        && !self::isSameOrExtends($searchKey, $value)) {
                        continue;
                    }

                    // зависимость удовлетворяет параметрам
                    $dependency = $value;
                    break;

                } // итерация по элементам слоя зависимостей

            } else { // если ищем по имени
                if (!$serviceLayer->has($searchKey)) {
                    continue;
                } // зависимости с искомым именем не найдено, опускаемся на следующий слой
                $dependency = $serviceLayer->get($searchKey);
            }

            if (!$dependency) {
                continue;
            }

            return $dependency;

        } // итерация по слою зависимостей

        throw new NotResolvedException("Service with name {$searchKey} was not resolved!");

    }

    /**
     * Copied from Classes::isSameOrExtends (codexsoft/code)
     * @param $ancestor
     * @param $parent
     *
     * @return bool
     */
    private static function isSameOrExtends($ancestor, $parent): bool
    {
        $ancestorClass = \is_object( $ancestor ) ? \get_class( $ancestor ) : $ancestor;
        $parentClass = \is_object( $parent ) ? \get_class( $parent ) : $parent;
        return ( $ancestorClass === $parentClass || is_subclass_of($ancestorClass,$parentClass) );
    }

}