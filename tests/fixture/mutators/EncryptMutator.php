<?php

use Folklore\EloquentJson\Support\JsonMutator;

class EncryptMutator extends JsonMutator
{
    public function mutateToAttribute($model, $attribute, $value)
    {
        return encrypt($value);
    }

    public function mutateToValue($model, $attribute, $value)
    {
        return decrypt($value);
    }
}
