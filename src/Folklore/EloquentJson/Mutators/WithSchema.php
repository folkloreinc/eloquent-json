<?php

namespace Folklore\EloquentJson\Mutators;

use Folklore\EloquentJson\Contracts\Support\JsonMutator;
use Folklore\EloquentJson\Nodes\SchemaNodesCollection;
use Folklore\EloquentJson\Mutators\Concerns\InteractsWithMutator;
use Folklore\EloquentJson\Mutators\Concerns\MutateFromNodes;
use Illuminate\Support\Collection;

class WithSchema implements JsonMutator
{
    use InteractsWithMutator, MutateFromNodes;

    protected $mutator;

    protected $schema;

    public function __construct($mutator, $schema = null)
    {
        $this->mutator = $mutator;
        $this->schema = $schema;
    }

    public function withSchema($schema)
    {
        $this->schema = $schema;
        return $this;
    }

    protected function getNodes($model, $attribute, $value)
    {
        $attributeSchema = $model->getAttributeJsonSchema($attribute);
        return SchemaNodesCollection::makeFromSchema(
            $attributeSchema,
            $value
        )->filterSchema($this->schema);
    }

    public function __call($method, $args)
    {
        $mutator = $this->getMutator($this->mutator);
        return call_user_func_array([$mutator, $method], $args);
    }
}
