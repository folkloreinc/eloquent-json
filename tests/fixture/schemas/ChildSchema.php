<?php

use Folklore\EloquentJson\Support\JsonSchema;
use Folklore\EloquentJson\Schemas\Type;

class ChildSchema extends JsonSchema
{
    protected function properties()
    {
        return [
            'id' => Type::integer(),
        ];
    }
}
