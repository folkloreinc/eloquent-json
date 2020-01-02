<?php

use Folklore\EloquentJson\Support\JsonMutator;

class TestMutator extends JsonMutator
{
    public function mutateToAttribute($model, $attribute, $value)
    {
        return $value;
    }

    public function mutateToValue($model, $attribute, $value)
    {
        return $value;
    }
}
