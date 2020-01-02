<?php

namespace Folklore\EloquentJson\Contracts\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

interface JsonSchema extends Arrayable, Jsonable
{
    public function getId();

    public function setId($value);

    public function getType();

    public function setType($value);

    public function getProperties();

    public function setProperties($value);

    public function getItems();

    public function setItems($value);

    public function getDefault();

    public function setDefault($value);

    public function getRequired();

    public function setRequired($value);

    public function getEnum();

    public function setEnum($value);

    public function getAttributes();

    public function setAttributes($value);
}
