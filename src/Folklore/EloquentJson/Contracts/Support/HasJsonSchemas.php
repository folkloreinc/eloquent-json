<?php

namespace Folklore\EloquentJson\Contracts\Support;

interface HasJsonSchemas
{
    public function hasJsonSchema($key);

    public function getAttributeJsonSchema($key);
}
