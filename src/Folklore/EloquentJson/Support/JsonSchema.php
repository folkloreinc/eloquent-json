<?php

namespace Folklore\EloquentJson\Support;

use Folklore\EloquentJson\Contracts\Support\JsonSchema as JsonSchemaContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;
use ArrayAccess;
use JsonSerializable;

class JsonSchema implements ArrayAccess, JsonSerializable, JsonSchemaContract
{
    protected $schema = 'http://json-schema.org/draft-07/schema#';

    protected $id = null;

    protected $type = 'object';

    protected $title;

    protected $description;

    protected $nullable = true;

    protected $properties;

    protected $required;

    protected $default;

    protected $items;

    protected $enum;

    protected $attributes = [];

    protected $schemaAttributes = [
        '$schema',
        '$id',
        'description',
        'title',
        'nullable',
        'type',
        'properties',
        'required',
        'default',
        'items',
        'enum'
    ];

    public function __construct($schema = [])
    {
        $this->setSchema($schema);
    }

    public function setSchema($schema)
    {
        foreach ($this->schemaAttributes as $attribute) {
            if (isset($schema[$attribute])) {
                $this->{$attribute} = $schema[$attribute];
            }
        }
        $this->attributes = Arr::except($schema, $this->schemaAttributes);
        return $this;
    }

    public function getId()
    {
        return $this->getSchemaAttribute('$id');
    }

    public function setId($value)
    {
        return $this->setSchemaAttribute('$id', $value);
    }

    public function getType()
    {
        return $this->getSchemaAttribute('type');
    }

    public function setType($value)
    {
        return $this->setSchemaAttribute('type', $value);
    }

    public function getProperties()
    {
        $types = (array) $this->getType();
        if (!in_array('object', $types)) {
            return null;
        }

        $properties = $this->getSchemaAttribute('properties');

        if (is_null($properties)) {
            return [];
        }

        $propertiesResolved = [];
        foreach ($properties as $name => $value) {
            if (is_string($value)) {
                $propertiesResolved[$name] = resolve($value);
            } elseif (is_array($value)) {
                $property = new self();
                $property->setSchema($value);
                $propertiesResolved[$name] = $property;
            } else {
                $propertiesResolved[$name] = $value;
            }
        }

        return $propertiesResolved;
    }

    public function setProperties($value)
    {
        return $this->setSchemaAttribute('properties', $value);
    }

    public function addProperty($key, $value)
    {
        if (!isset($this->properties)) {
            $this->properties = [];
        }
        $this->properties[$key] = $value;
        return $this;
    }

    public function getItems()
    {
        $types = (array) $this->getType();
        if (!in_array('array', $types)) {
            return null;
        }

        if (method_exists($this, 'items')) {
            $items = $this->items();
        } else {
            $items = isset($this->items) ? $this->items : [];
        }

        if (is_string($items)) {
            return resolve($items);
        } elseif (
            $items instanceof JsonSchemaContract ||
            (is_array($items) && isset($items['type']))
        ) {
            return $items;
        }

        $itemsResolved = [];
        foreach ($items as $name => $value) {
            $itemsResolved[$name] = is_string($value)
                ? resolve($value)
                : $value;
        }

        return $itemsResolved;
    }

    public function setItems($value)
    {
        return $this->setSchemaAttribute('items', $value);
    }

    public function getDefault()
    {
        return $this->getSchemaAttribute('default');
    }

    public function setDefault($value)
    {
        return $this->setSchemaAttribute('default', $value);
    }

    public function getRequired()
    {
        return $this->getSchemaAttribute('required');
    }

    public function setRequired($value)
    {
        return $this->setSchemaAttribute('required', $value);
    }

    public function getEnum()
    {
        return $this->getSchemaAttribute('enum');
    }

    public function setEnum($value)
    {
        return $this->setSchemaAttribute('enum', $value);
    }

    public function getAttributes()
    {
        return $this->getSchemaAttribute('attributes');
    }

    public function setAttributes($value)
    {
        return $this->setSchemaAttribute('attributes', $value);
    }

    public function get($key)
    {
        $methodName = 'get' . Str::studly($key);
        return method_exists($this, $methodName)
            ? $this->{$methodName}()
            : Arr::get($this->attributes, $key);
    }

    public function set($key, $value)
    {
        $methodName = 'set' . Str::studly($key);
        return method_exists($this, $methodName)
            ? $this->{$methodName}($value)
            : Arr::set($this->attributes, $key, $value);
    }

    protected function getSchemaAttribute($key)
    {
        $key = preg_replace('/^\$/', '', $key);
        $value = isset($this->{$key}) ? $this->{$key} : null;
        if (method_exists($this, $key)) {
            if (is_array($value)) {
                $value = array_merge($this->{$key}($value), $value);
            } else {
                $value = $this->{$key}($value);
            }
        }
        return $value;
    }

    protected function setSchemaAttribute($key, $value)
    {
        $key = preg_replace('/^\$/', '', $key);
        $this->{$key} = $value;
        return $this;
    }

    public function toArray()
    {
        $nullable = $this->getNullable();
        $type = $this->getType();
        $id = $this->getId();

        $schema = [];
        $schema['$schema'] = $this->schema;
        if (isset($id)) {
            $schema['$id'] = $id;
        }
        $schema['type'] = $nullable
            ? array_merge(['null'], (array) $type)
            : $type;

        $properties = $this->getProperties();
        if (isset($properties)) {
            $schema['properties'] = [];
            foreach ($properties as $name => $value) {
                $schema['properties'][$name] =
                    $value instanceof Arrayable ? $value->toArray() : $value;
            }
        }

        $items = $this->getItems();
        if (isset($items)) {
            $schema['items'] =
                $items instanceof Arrayable ? $items->toArray() : $items;
        }

        $schemaAttributes = Arr::except(
            $this->schemaAttributes,
            array_keys($schema)
        );
        foreach ($schemaAttributes as $attribute) {
            $value = $this->get($attribute);
            if (isset($value) && !isset($schema[$attribute])) {
                $schema[$attribute] = $value;
            }
        }

        $attributes = $this->getAttributes();

        return array_merge($schema, $attributes);
    }

    public function toObject()
    {
        return json_decode(json_encode($this->toArray()));
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the Fluent instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset}) || isset($this->attributes[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed   $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Unset the value at the given offset.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (isset($this->{$offset})) {
            unset($this->{$offset});
        } elseif (isset($this->attributes[$offset])) {
            unset($this->attributes[$offset]);
        }
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->{$key}) || isset($this->attributes[$key]);
    }

    /**
     * Dynamically unset an attribute.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        if (isset($this->{$key})) {
            unset($this->{$key});
        } elseif (isset($this->attributes[$key])) {
            unset($this->attributes[$key]);
        }
    }

    /**
     * Dynamically call schema attributes accessors
     *
     * @param  string  $key
     * @return void
     */
    public function __call($method, $parameters)
    {
        if (preg_match('/^(get|set|with)([A-Z].*)$/i', $method, $matches)) {
            $methodAttribute = Str::snake($matches[2]);
            $methodPrefix = $matches[1] === 'with' ? 'set' : $matches[1];
            return $this->{$methodPrefix}($methodAttribute);
        }
    }
}
