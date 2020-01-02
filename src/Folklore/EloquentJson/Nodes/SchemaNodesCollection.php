<?php

namespace Folklore\EloquentJson\Nodes;

use Folklore\EloquentJson\Contracts\Support\JsonSchema as JsonSchemaContract;
use Illuminate\Support\Arr;

class SchemaNodesCollection extends NodesCollection
{
    public static function makeFromSchema($schema, $data = null, $root = null)
    {
        $schema = is_string($schema) ? resolve($schema) : $schema;
        $nodes = static::getNodesFromSchema($schema, $root);
        if (is_null($data)) {
            return $nodes;
        }
        $dataArray = json_decode(json_encode($data), true);
        $dataPaths = is_array($dataArray)
            ? array_keys(Arr::dot($dataArray))
            : [];
        return $nodes->reduce(function ($collection, $node) use ($dataPaths) {
            $paths = static::getMatchingPaths($dataPaths, $node->path());
            foreach ($paths as $path) {
                $newNode = new SchemaNode($path, $node->schema());
                $collection->push($newNode);
            }
            return $collection;
        }, new static());
    }

    protected static function getNodesFromSchema(
        JsonSchemaContract $schema,
        $root = null
    ) {
        $type = $schema->getType();

        // get properties
        $properties = [];
        if ($type === 'object') {
            $properties = $schema->getProperties();
        } elseif ($type === 'array') {
            $items = $schema->getItems();
            if (
                $items instanceof JsonSchemaContract ||
                (is_array($items) && isset($items['type']))
            ) {
                $properties = [
                    '*' => $items
                ];
            } else {
                $properties = $items;
            }
        }

        $nodes = new static();
        foreach ($properties as $propertyPath => $propertySchema) {
            $nodes->push(new SchemaNode($propertyPath, $propertySchema));
            if ($propertySchema instanceof JsonSchemaContract) {
                $propertyNodes = static::getNodes($propertySchema)->prependPath(
                    $propertyPath
                );
                $nodes = $nodes->merge($propertyNodes);
            }
        }
        return $root !== null ? $nodes->fromPath($root) : $nodes;
    }

    protected static function getMatchingPaths($dataPaths, $path)
    {
        if (sizeof(explode('*', $path)) <= 1) {
            return [$path];
        }

        $matchingPaths = [];
        $pattern =
            !empty($path) && $path !== '*'
                ? '/^(' . str_replace('\*', '[^\.]+', preg_quote($path)) . ')/'
                : '/^(.*)/';
        foreach ($dataPaths as $dataPath) {
            if (preg_match($pattern, $dataPath, $matches)) {
                if (!in_array($matches[1], $matchingPaths)) {
                    $matchingPaths[] = $matches[1];
                }
            }
        }
        return $matchingPaths;
    }

    public function filterSchema($schema)
    {
        return $this->filter(function ($node) use ($schema) {
            return $node->isSchema($schema);
        })->values();
    }
}
