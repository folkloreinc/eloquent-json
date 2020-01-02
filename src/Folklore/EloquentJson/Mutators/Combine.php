<?php

namespace Folklore\EloquentJson\Mutators;

use Folklore\EloquentJson\Contracts\Support\JsonMutator;
use Folklore\EloquentJson\Mutators\Concerns\InteractsWithMutator;
use Illuminate\Support\Collection;

class Combine implements JsonMutator
{
    use InteractsWithMutator;

    protected $mutators = [];

    protected $reverseWhenMutateToValue = false;
    protected $reverseWhenMutateToAttribute = false;

    public function __construct($mutator, ...$mutators)
    {
        $mutators = is_array($mutator) ? $mutator : array_merge([$mutator], $mutators);
        $this->mutators = new Collection($mutators);
    }

    public function mutateToAttribute($model, $key, $value)
    {
        $mutators = $this->reverseWhenMutateToAttribute
            ? $this->mutators->reverse()
            : $this->mutators;
        return $mutators->reduce(function ($newValue, $mutator) use (
            $model,
            $key
        ) {
            $mutator = $this->getMutator($mutator);
            return method_exists($mutator, 'mutateToAttribute')
                ? $mutator->mutateToAttribute($model, $key, $newValue)
                : $newValue;
        },
        $value);
    }

    public function mutateToValue($model, $key, $value)
    {
        $mutators = $this->reverseWhenMutateToValue
            ? $this->mutators->reverse()
            : $this->mutators;
        return $mutators->reduce(function ($newValue, $mutator) use (
            $model,
            $key
        ) {
            $mutator = $this->getMutator($mutator);
            return method_exists($mutator, 'mutateToValue')
                ? $mutator->mutateToValue($model, $key, $newValue)
                : $newValue;
        },
        $value);
    }

    public function beforeSave($model, $key, $value)
    {
        return $this->mutators->reduce(function ($newValue, $mutator) use (
            $model,
            $key
        ) {
            $mutator = $this->getMutator($mutator);
            return method_exists($mutator, 'beforeSave')
                ? $mutator->beforeSave($model, $key, $newValue)
                : $newValue;
        },
        $value);
    }

    public function afterSave($model, $key, $value)
    {
        return $this->mutators->reduce(function ($newValue, $mutator) use (
            $model,
            $key
        ) {
            $mutator = $this->getMutator($mutator);
            return method_exists($mutator, 'afterSave')
                ? $mutator->afterSave($model, $key, $newValue)
                : $newValue;
        },
        $value);
    }

    public function reverseWhenMutateToValue()
    {
        $this->reverseWhenMutateToValue = true;
        return $this;
    }

    public function reverseWhenMutateToAttribute()
    {
        $this->reverseWhenMutateToAttribute = true;
        return $this;
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->mutators, $method], $args);
    }
}
