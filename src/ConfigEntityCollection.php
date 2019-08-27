<?php


namespace EasySwoole\ConfigEntity;


use Iterator;
use JsonSerializable;
use ReflectionException;
use Serializable;

/**
 * Class ConfigEntityCollection
 *
 * @package EasySwoole\ConfigEntity
 */
class ConfigEntityCollection implements Iterator, JsonSerializable, Serializable
{
    /** @var array config集合 */
    private $entityCollection = [];

    /**
     * ConfigEntityCollection constructor.
     *
     * @param array|null $entityCollection
     * @throws ReflectionException
     */
    public function __construct(array $entityCollection = null)
    {
        if (!empty($entityCollection)) {
            /** @var string|array $entity */
            foreach ($entityCollection as $entity) {
                if (is_array($entity)) {
                    $this->push(new ConfigEntity($entity));
                } else {
                    $this->push((new ConfigEntity())->unserialize($entity));
                }
            }
        }
    }

    /**
     * push
     *
     * @param ConfigEntity $entity
     */
    public function push(ConfigEntity $entity): void
    {
        $this->entityCollection[$entity->getKey()] = $entity;
    }

    /**
     * getConfigEntity
     *
     * @param string $key
     * @return ConfigEntity|null
     */
    public function getConfigEntity(string $key): ?ConfigEntity
    {
        return $this->entityCollection[$key] ?? null;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function getValues(): array
    {
        $tmpArray = [];
        /** @var ConfigEntity $entity */
        foreach ($this->entityCollection as $entity) {
            $tmpArray[$entity->getKey()] = $entity->getValue();
        }

        return $tmpArray;
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    final public function jsonSerialize(): array
    {
        $tmpArray = [];
        /** @var ConfigEntity $entity */
        foreach ($this->entityCollection as $entity) {
            $tmpArray[] = $entity->serialize();
        }
        return $tmpArray;
    }

    /**
     * __toString
     *
     * @return false|string
     */
    final public function __toString()
    {
        return json_encode($this->jsonSerialize(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * serialize
     *
     * @return false|string
     */
    final public function serialize()
    {
        return $this->__toString();
    }

    /**
     * unserialize
     *
     * @param string $serialized
     * @return $this|void
     * @throws ReflectionException
     */
    final public function unserialize($serialized)
    {
        $entityCollection = json_decode($serialized, true);
        $this->__construct($entityCollection);
        return $this;
    }

    public function current()
    {
        return current($this->entityCollection);
    }

    public function next()
    {
        next($this->entityCollection);
    }

    public function key()
    {
        return key($this->entityCollection);
    }

    public function valid()
    {
        return !empty($this->entityCollection[$this->key()]);
    }

    public function rewind()
    {
        reset($this->entityCollection);
    }
}