<?php

use Illuminate\Database\Eloquent\Model;
use Folklore\EloquentJson\Support\HasJsonSchemas;
use Folklore\EloquentJson\Contracts\Support\HasJsonSchemas as HasJsonSchemasContract;

class Child extends Model implements HasJsonSchemasContract
{
    use HasJsonSchemas;

    protected $table = 'children';

    protected $casts = [
        'data' => 'json'
    ];

    protected function getDataJsonSchema()
    {
        return $this->jsonSchema(ChildDataSchema::class);
    }
}
