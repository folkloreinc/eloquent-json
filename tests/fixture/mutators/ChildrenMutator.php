<?php

use Folklore\EloquentJson\Support\JsonMutator;
use Folklore\EloquentJson\Support\SchemaNodesCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ChildrenMutator extends JsonMutator
{
    protected $schema = ChildSchema::class;

    protected $relation = 'children';

    protected $shouldSyncRelations = false;

    protected $relationsCache = [];

    public function mutateToAttribute($model, $attribute, $value)
    {
        // Get nodes using a schema
        $schema = $model->getAttributeJsonSchema($attribute);
        $nodes = SchemaNodesCollection::makeFromSchema(
            $schema,
            $value
        )->filterSchema($this->schema);

        // Replace model at each node with key
        foreach ($nodes as $node) {
            $path = $node->path();
            $relation = data_get($value, $path);
            if (!is_null($relation) && $relation instanceof Model) {
                data_set($value, $path, $relation->getKey());
                $this->addRelationToCache($relation, $attribute, $path);
            }
        }
        return $value;
    }

    public function mutateToValue($model, $attribute, $value)
    {
        // Get nodes using a schema
        $schema = $model->getAttributeJsonSchema($attribute);
        $nodes = SchemaNodesCollection::makeFromSchema(
            $schema,
            $value
        )->filterSchema($this->schema);

        // Replace model at each node with key
        foreach ($nodes as $node) {
            $path = $node->path();
            $nodeValue = data_get($value, $path);
            if (!is_null($nodeValue) && !($nodeValue instanceof Model)) {
                $relation = $this->getRelationFromCache($attribute, $path);
                if (is_null($relation)) {
                    $relation = $this->getRelationFromModel($model, $nodeValue);
                }
                data_set($value, $path, $relation);
            }
        }
        return $value;
    }

    public function beforeSave($model, $attribute, $value)
    {
        $this->shouldSyncRelations = $model->isDirty($attribute);
    }

    public function afterSave($model, $attribute, $value)
    {
        if (!$this->shouldSyncRelations) {
            return;
        }
        $model->load($this->relation);
        $newRelations = $this->getRelationsFromCache($attribute);
        $this->syncRelations($model, $newRelations);
    }

    protected function addRelationToCache($relation, $attribute, $path)
    {
        if (!isset($this->relationsCache[$attribute])) {
            $this->relationsCache[$attribute] = [];
        }
        $this->relationsCache[$attribute][$path] = $relation;
    }

    protected function getRelationFromCache($attribute, $path)
    {
        return isset($this->relationsCache[$attribute][$path])
            ? $this->relationsCache[$attribute][$path]
            : null;
    }

    protected function getRelationsFromCache($attribute)
    {
        return isset($this->relationsCache[$attribute])
            ? array_values($this->relationsCache[$attribute])
            : [];
    }

    protected function syncRelations($model, $newRelations)
    {
        $relation = $model->{$this->relation}();
        if ($relation instanceof BelongsToMany) {
            $relation->sync(
                array_map(function ($model) {
                    return $model->getKey();
                }, $newRelations)
            );
        } else {
            $relation->saveMany($newRelations);
        }
    }

    protected function getRelationFromModel($model, $key)
    {
        $relations = $model->{$this->relation};
        if ($relations instanceof Collection) {
            return $relations->first(function ($relation) use ($key) {
                return $relation->getKey() === $key;
            });
        }
        return $relations instanceof Model && $relations->getKey() === $key
            ? $relations
            : null;
    }
}
