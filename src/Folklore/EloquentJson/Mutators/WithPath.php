<?php

namespace Folklore\EloquentJson\Mutators;

use Folklore\EloquentJson\Contracts\Support\JsonMutator;
use Folklore\EloquentJson\Nodes\NodesCollection;
use Folklore\EloquentJson\Mutators\Concerns\InteractsWithMutator;
use Folklore\EloquentJson\Mutators\Concerns\MutateFromNodes;
use Illuminate\Support\Collection;

class WithPath implements JsonMutator
{
    use InteractsWithMutator, MutateFromNodes;

    protected $mutator;

    protected $paths;

    public function __construct($mutator, $paths = [])
    {
        $this->mutator = $mutator;
        $this->paths = (array) $paths;
    }

    public function withPath($path, ...$paths)
    {
        $this->paths = is_array($path) ? $path : array_merge([$path], $paths);
        return $this;
    }

    protected function getNodes($model, $attribute, $value)
    {
        return NodesCollection::makeFromData($value)->filterFromPath(
            $this->paths
        );
    }

    public function __call($method, $args)
    {
        $mutator = $this->getMutator();
        return call_user_func_array([$mutator, $method], $args);
    }
}
