<?php

use Illuminate\Database\Eloquent\Model;
use Folklore\EloquentJson\Support\HasJsonSchemas;
use Folklore\EloquentJson\Contracts\Support\HasJsonSchemas as HasJsonSchemasContract;

class ModelWithSchema extends Model implements HasJsonSchemasContract
{
    use HasJsonSchemas;

    protected $table = 'models';

    protected $casts = [
        'data' => 'json'
    ];

    protected function getDataJsonSchema()
    {
        return $this->jsonSchema(ModelDataSchema::class);
    }
}
