<?php

namespace Folklore\EloquentJson;

use Folklore\EloquentJson\Contracts\Support\HasJsonMutators;

class MutatorsObserver
{
    public function saving(HasJsonMutators $model)
    {
        $model->beforeSaveJsonMutators();
    }

    public function saved(HasJsonMutators $model)
    {
        $model->afterSaveJsonMutators();
    }
}
