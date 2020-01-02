<?php

namespace Folklore\EloquentJson\Support;

use Folklore\EloquentJson\Contracts\Support\JsonMutator as JsonMutatorContract;
use Folklore\EloquentJson\MutatorsObserver;
use Folklore\EloquentJson\ValidationException;
use Folklore\EloquentJson\Mutators\Combine as CombineMutator;
use Illuminate\Support\Str;
use LogicException;

trait HasJsonMutators
{
    protected $jsonMutatorsCache = [];

    public static function bootHasJsonMutators()
    {
        static::observe(MutatorsObserver::class);
    }

    /**
     * Method which get called before the model is saving
     * @return void
     */
    public function beforeSaveJsonMutators()
    {
        foreach (array_keys($this->attributes) as $key) {
            if ($this->hasJsonMutator($key)) {
                $mutator = $this->getAttributeJsonMutator($key);
                if (method_exists($mutator, 'beforeSave')) {
                    $value = $this->getAttributeValue($key);
                    $newValue = $mutator->beforeSave($this, $key, $value);
                    if (!is_null($newValue) && $newValue !== false) {
                        $this->setAttribute($key, $newValue);
                    }
                }
            }
        }
    }

    /**
     * Method which get called after the model is saved
     * @return void
     */
    public function afterSaveJsonMutators()
    {
        foreach (array_keys($this->attributes) as $key) {
            if ($this->hasJsonMutator($key)) {
                $mutator = $this->getAttributeJsonMutator($key);
                if (method_exists($mutator, 'afterSave')) {
                    $value = $this->getAttributeValue($key);
                    $mutator->afterSave($this, $key, $value);
                }
            }
        }
    }

    /**
     * Method to create a JsonMutator attribute
     * @param  string|JsonMutatorContract $mutators The mutators
     * @return JsonMutatorContract
     */
    protected function jsonMutator($mutator, ...$mutators)
    {
        if (sizeof($mutators) > 0) {
            return new CombineMutator(array_merge([$mutator], $mutators));
        }
        return is_string($mutator) ? resolve($mutator) : $mutator;
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return $this->hasJsonGetMutator($key) || parent::hasSetMutator($key);
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        if ($this->hasJsonGetMutator($key)) {
            $value = $this->castAttribute($key, $value);
            return $this->mutateJsonAttributeToValue($key, $value);
        }
        return parent::mutateAttribute($key, $value);
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        return $this->hasJsonSetMutator($key) || parent::hasSetMutator($key);
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function setMutatedAttributeValue($key, $value)
    {
        if ($this->hasJsonSetMutator($key)) {
            $this->attributes[$key] = $this->castAttributeAsJson(
                $key,
                $this->mutateValueToJsonAttribute($key, $value)
            );
            return $this;
        }
        return parent::setMutatedAttributeValue($key, $value);
    }

    /**
     * Get the JSON Mutator for an attribute
     *
     * @param  string  $key
     * @return \Folklore\EloquentJson\Contracts\Support\JsonMutator|null
     */
    protected function getAttributeJsonMutator($key)
    {
        if (!isset($this->jsonMutatorsCache[$key])) {
            $this->jsonMutatorsCache[$key] = $this->getJsonMutatorFromMethod(
                $key
            );
        }
        return $this->jsonMutatorsCache[$key];
    }

    /**
     * Determine whether an attribute has a JSON mutator
     *
     * @param  string  $key
     * @return bool
     */
    public function hasJsonMutator($key)
    {
        return method_exists($this, $this->getAttributeJsonMutatorMethod($key));
    }

    /**
     * Determine whether an attribute has a JSON get mutator
     *
     * @param  string  $key
     * @return bool
     */
    public function hasJsonGetMutator($key)
    {
        return $this->hasJsonMutator($key) &&
            method_exists(
                $this->getAttributeJsonMutator($key),
                'mutateToValue'
            );
    }

    /**
     * Determine whether an attribute has a JSON set mutator
     *
     * @param  string  $key
     * @return bool
     */
    public function hasJsonSetMutator($key)
    {
        return $this->hasJsonMutator($key) &&
            method_exists(
                $this->getAttributeJsonMutator($key),
                'mutateToValue'
            );
    }

    /**
     * Mutate a JSON attribute to value
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed $value
     */
    protected function mutateJsonAttributeToValue($key, $value)
    {
        return $this->getAttributeJsonMutator($key)->mutateToValue(
            $this,
            $key,
            $value
        );
    }

    /**
     * Mutate a value to JSON attribute
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed $value
     */
    protected function mutateValueToJsonAttribute($key, $value)
    {
        return $this->getAttributeJsonMutator($key)->mutateToAttribute(
            $this,
            $key,
            $value
        );
    }

    /**
     * Get a json mutator value from a method.
     *
     * @param  string  $method
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getJsonMutatorFromMethod($key)
    {
        $method = $this->getAttributeJsonMutatorMethod($key);
        $mutator = $this->{$method}();

        if (!$mutator instanceof JsonMutatorContract) {
            throw new LogicException(
                sprintf(
                    '%s::%s must return an instance that implements the JsonMutator contract.',
                    static::class,
                    $method
                )
            );
        }

        return $mutator;
    }

    /**
     * Get the name of the method
     *
     * @param  string  $key The attribute name
     * @return string The name of the method
     */
    protected function getAttributeJsonMutatorMethod($key)
    {
        return 'get' . Str::studly($key) . 'JsonMutator';
    }
}
