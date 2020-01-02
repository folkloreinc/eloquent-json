<?php

use Folklore\EloquentJson\Support\JsonSchema;
use Folklore\EloquentJson\Schemas\Type;

class ChildrenSchema extends JsonSchema
{
    protected function type()
    {
        return 'array';
    }

    protected function items()
    {
        return ChildSchema::class;
    }
}
