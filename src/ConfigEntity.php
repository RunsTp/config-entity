<?php


namespace EasySwoole\ConfigEntity;


use JsonSerializable;
use ReflectionClass;
use ReflectionException;
use Serializable;

/**
 * Class ConfigEntity
 *
 * @package EasySwoole\ConfigEntity
 */
class ConfigEntity implements JsonSerializable, Serializable
{
    /** @var string */
    protected $key;
    /** @var string */
    protected $name;
    /** @var string */
    protected $type;
    /** @var mixed */
    protected $value;

    /**
     * ConfigEntity constructor.
     *
     * @param array|null $config
     * @throws ReflectionException
     */
    public function __construct(array $config = null)
    {
        if (!is_null($config)) {
            foreach ($config as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }

            if (!empty($this->type) && class_exists($this->type)) {
                /** 如果是 ConfigEntityCollection 则 new */
                if ($this->type === ConfigEntityCollection::class) {
                    if (is_array($this->value)) {
                        $this->value = new ConfigEntityCollection($this->value);
                    } elseif (is_string($this->value)) {
                        $this->value = (new ConfigEntityCollection)->unserialize($this->value);
                    }
                    return;
                }

                /** 使用反射判断type */
                $ref = new ReflectionClass($this->type);
                if ($ref->isSubclassOf('EasySwoole\Spl\SplBean') && is_string($this->value)) {
                    $this->value = new $this->type(json_decode($this->value, true));
                    return;
                }

                if ($ref->isSubclassOf(Serializable::class) && is_string($this->value)) {
                    $this->value = (new $this->type())->unserialize($this->value);
                    return;
                }
            }
        }
    }

    /**
     * getKey
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * setKey
     *
     * @param string $key
     * @return ConfigEntity
     */
    public function setKey(string $key): ConfigEntity
    {
        $this->key = $key;
        return $this;
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * setName
     *
     * @param string $name
     * @return ConfigEntity
     */
    public function setName(string $name): ConfigEntity
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * setValue
     *
     * @param $value
     * @return ConfigEntity
     */
    public function setValue($value): ConfigEntity
    {
        $this->value = $value;
        return $this;
    }

    /**
     * getType
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * setType
     *
     * @param string $type
     * @return ConfigEntity
     */
    public function setType(string $type): ConfigEntity
    {
        $this->type = $type;
        return $this;
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    final public function jsonSerialize(): array
    {
        $tmpArray = [];
        foreach ($this as $key => $value) {
            if ($value instanceof JsonSerializable) {
                $value = $value->jsonSerialize();
            } elseif ($value instanceof Serializable) {
                $value = $value->serialize();
            }
            $tmpArray[$key] = $value;
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
        $array = json_decode($serialized, true);
        $this->__construct($array);
        return $this;
    }
}