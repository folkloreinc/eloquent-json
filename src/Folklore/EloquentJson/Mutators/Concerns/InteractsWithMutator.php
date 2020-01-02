<?php

namespace Folklore\EloquentJson\Mutators\Concerns;

trait InteractsWithMutator
{
    protected $mutatorsCache = [];

    protected function getMutator($mutator = null)
    {
        if (is_null($mutator)) {
            $mutator = $this->mutator;
        }
        if (is_string($mutator)) {
            if (!isset($this->mutatorsCache[$mutator])) {
                $this->mutatorsCache[$mutator] = resolve($mutator);
            }
            return $this->mutatorsCache[$mutator];
        }
        return $mutator;
    }
}
