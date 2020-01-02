<?php

namespace Folklore\EloquentJson\Support;

use Folklore\EloquentJson\Contracts\Support\JsonSchema as JsonSchemaContract;
use Folklore\EloquentJson\Contracts\JsonSchemaValidator;
use Folklore\EloquentJson\ValidationException;
use Illuminate\Support\Str;
use LogicException;

trait HasJsonSchemas
{
    /**
     * Method to create a JsonSchema attribute
     * @param  string|JsonSchemaContract $schema The schema
     * @return JsonSchemaContract
     */
    protected function jsonSchema($schema)
    {
        return is_string($schema) ? resolve($schema) : $schema;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasJsonSchema($key)) {
            $this->validateJsonSchemaAttribute($key, $value);
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * Validate data against JSON Schema
     *
     * @return void
     */
    protected function validateJsonSchemaAttribute($key, $value)
    {
        $validator = resolve(JsonSchemaValidator::class);
        $schema = $this->getAttributeJsonSchema($key);
        if (!$validator->validateSchema($schema, $value)) {
            throw new ValidationException($validator->getMessages(), $key);
        }
    }

    /**
     * Get the JSON Schema Attribute for an attribute
     *
     * @param  string  $key
     * @return \Folklore\EloquentJson\Contracts\Support\JsonSchema|null
     */
    public function getAttributeJsonSchema($key)
    {
        return $this->getJsonSchemaFromMethod($key);
    }

    /**
     * Determine whether an attribute has a JSON Schema
     *
     * @param  string  $key
     * @return bool
     */
    public function hasJsonSchema($key)
    {
        $methodName = $this->getAttributeJsonSchemaMethod($key);
        return method_exists($this, $methodName);
    }

    /**
     * Get a json schema value from a method.
     *
     * @param  string  $method
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getJsonSchemaFromMethod($key)
    {
        $methodName = $this->getAttributeJsonSchemaMethod($key);
        $schema = $this->{$methodName}();

        if (!$schema instanceof JsonSchemaContract) {
            throw new LogicException(
                sprintf(
                    '%s::%s must return a JsonSchema instance.',
                    static::class,
                    $method
                )
            );
        }

        return $schema;
    }

    protected function getAttributeJsonSchemaMethod($key)
    {
        return 'get' . Str::studly($key) . 'JsonSchema';
    }
}
