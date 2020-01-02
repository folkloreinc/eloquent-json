<?php

namespace Folklore\EloquentJson\Contracts\Support;

interface HasJsonMutators
{
    public function beforeSaveJsonMutators();

    public function afterSaveJsonMutators();

    public function hasJsonMutator($key);

    public function hasJsonGetMutator($key);

    public function hasJsonSetMutator($key);
}
