<?php

namespace Folklore\EloquentJson\Schemas;

use Folklore\EloquentJson\Support\JsonSchema;

class Type
{
    public static function __callStatic($method, $arguments)
    {
        $schema = [
            'type' => $method
        ];
        return new JsonSchema(
            sizeof($arguments) ? array_merge($schema, ...$arguments) : $schema
        );
    }
}
