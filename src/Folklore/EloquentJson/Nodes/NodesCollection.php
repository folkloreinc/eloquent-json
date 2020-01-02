<?php

namespace Folklore\EloquentJson\Nodes;

use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

class NodesCollection extends Collection
{
    public static function makeFromData($data, $root = null)
    {
        $nodes = new static();
        $paths = array_keys(Arr::dot($data));
        foreach ($paths as $path) {
            $nodes->push(new Node($path));
        }
        return $root !== null ? $nodes->fromPath($root) : $nodes;
    }

    public function prependPath($path)
    {
        return $this->map(function ($node) use ($path) {
            $newNode = clone $node;
            return $newNode->prependPath($path);
        });
    }

    public function fromPath($path)
    {
        return $this->reduce(function ($collection, $node) use ($path) {
            if ($node->isPath($path . '.*')) {
                $node->removePath($path);
                $collection->push($node);
            }
            return $collection;
        }, new static());
    }

    public function filterFromPath($path, ...$paths)
    {
        $paths = new Collection(
            is_array($path) ? $path : array_merge([$path], $paths)
        );
        return $this->filter(function ($node) use ($paths) {
            return $paths->reduce(function ($inPath, $path) use ($node) {
                return $inPath || $node->isPath($path);
            }, false);
        })->values();
    }
}
