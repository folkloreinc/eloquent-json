<?php

use Folklore\EloquentJson\Support\JsonSchema;
use Folklore\EloquentJson\Schemas\Type;

class ModelDataSchema extends JsonSchema
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
