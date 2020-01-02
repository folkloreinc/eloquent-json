<?php

namespace Folklore\EloquentJson\Mutators\Concerns;

trait MutateFromNodes
{
    public function mutateToAttribute($model, $key, $value)
    {
        $mutator = $this->getMutator();
        return method_exists($mutator, 'mutateToAttribute')
            ? $this->getNodes($model, $key, $value)->reduce(function (
                $newValue,
                $node
            ) use ($mutator, $model, $key) {
                $path = $node->path();
                $valueAtPath = data_get($newValue, $path);
                $newValueAtPath = $mutator->mutateToAttribute(
                    $model,
                    $key,
                    $valueAtPath
                );
                data_set($newValue, $path, $newValueAtPath);
                return $newValue;
            },
            $value)
            : $value;
    }

    public function mutateToValue($model, $key, $value)
    {
        $mutator = $this->getMutator();
        return method_exists($mutator, 'mutateToValue')
            ? $this->getNodes($model, $key, $value)->reduce(function (
                $newValue,
                $node
            ) use ($mutator, $model, $key) {
                $path = $node->path();
                $valueAtPath = data_get($newValue, $path);
                $newValueAtPath = $mutator->mutateToValue(
                    $model,
                    $key,
                    $valueAtPath
                );
                data_set($newValue, $path, $newValueAtPath);
                return $newValue;
            },
            $value)
            : $value;
    }

    public function beforeSave($model, $key, $value)
    {
        $mutator = $this->getMutator();
        if (method_exists($mutator, 'beforeSave')) {
            return $this->getNodes($model, $key, $value)->reduce(function (
                $newValue,
                $node
            ) use ($mutator, $model, $key) {
                $path = $node->path();
                $valueAtPath = data_get($newValue, $path);
                $newValueAtPath = $mutator->beforeSave(
                    $model,
                    $key,
                    $valueAtPath
                );
                if (!is_null($newValueAtPath)) {
                    data_set($newValue, $path, $newValueAtPath);
                }
                return $newValue;
            },
            $value);
        }
    }

    public function afterSave($model, $key, $value)
    {
        $mutator = $this->getMutator();
        if (method_exists($mutator, 'afterSave')) {
            return $this->getNodes($model, $key, $value)->reduce(function (
                $newValue,
                $node
            ) use ($mutator, $model, $key) {
                $path = $node->path();
                $valueAtPath = data_get($newValue, $path);
                $mutator->afterSave($model, $key, $valueAtPath);
                return $newValue;
            },
            $value);
        }
    }
}
