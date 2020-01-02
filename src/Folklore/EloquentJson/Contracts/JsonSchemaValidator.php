<?php

namespace Folklore\EloquentJson\Contracts;

interface JsonSchemaValidator
{
    public function validateSchema($schema, $value);

    public function validate($attribute, $value, $parameters, $validator);

    public function getMessages();
}
