<?php

use Illuminate\Database\Eloquent\Model;
use Folklore\EloquentJson\Support\HasJsonSchemas;
use Folklore\EloquentJson\Support\HasJsonMutators;
use Folklore\EloquentJson\Contracts\Support\HasJsonSchemas as HasJsonSchemasContract;
use Folklore\EloquentJson\Contracts\Support\HasJsonMutators as HasJsonMutatorsContract;

class ModelWithMutator extends Model implements
    HasJsonSchemasContract,
    HasJsonMutatorsContract
{
    use HasJsonSchemas, HasJsonMutators;

    protected $table = 'models';

    protected $casts = [
        'data' => 'json'
    ];

    public function children()
    {
        return $this->belongsToMany(Child::class, 'models_children_pivot', 'model_id');
    }

    protected function getDataJsonMutator()
    {
        return $this->jsonMutator(ChildrenMutator::class);
        // return $this->jsonMutator(
        //     ChildrenMutator::class,
        //     EncryptMutator::class
        // )->reverseWhenMutateToValue();
    }

    protected function getDataJsonSchema()
    {
        return $this->jsonSchema(ModelDataWithChildrenSchema::class);
    }
}
