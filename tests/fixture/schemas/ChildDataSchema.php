<?php

use Folklore\EloquentJson\Support\JsonSchema;
use Folklore\EloquentJson\Schemas\Type;

class ChildDataSchema extends JsonSchema
{
    protected function properties()
    {
        return [
            'id' => Type::integer(),
            'title' => Type::string(),
            'description' => Type::string(),
        ];
    }
}
