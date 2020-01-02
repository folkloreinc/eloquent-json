<?php

namespace Folklore\EloquentJson\Nodes;

class SchemaNode extends Node
{
    protected $path;

    protected $schema;

    public function __construct($path, $schema = null)
    {
        $this->path = $path;
        $this->schema = $schema;
    }

    public function schema()
    {
        return $this->schema;
    }

    public function isSchema($schema)
    {
        $schemaClass = is_object($schema) ? get_class($schema) : $schema;
        return $this->schema === $schemaClass ||
            $this->schema instanceof $schemaClass;
    }
}
