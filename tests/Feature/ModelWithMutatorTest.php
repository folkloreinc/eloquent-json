<?php

namespace Folklore\EloquentJson\Tests\Feature;

use Folklore\EloquentJson\Tests\TestCase;
use Folklore\EloquentJson\Tests\RunMigrationsTrait;
use ModelWithMutator;
use Child;

class ModelWithMutatorTest extends TestCase
{
    use RunMigrationsTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    /**
     * Test children attach and detach
     */
    public function testChildren()
    {
        $childData = [
            'id' => 1234,
            'title' => 'Child Title',
            'description' => 'Child Description'
        ];

        $child1 = new Child();
        $child1->data = $childData;
        $child1->save();

        $child2 = new Child();
        $child2->data = $childData;
        $child2->save();

        $children = [$child1, $child2];

        $data = [
            'id' => 1234,
            'title' => 'Title',
            'description' => 'Description',
            'children' => $children
        ];
        $model = new ModelWithMutator();
        $model->data = $data;
        $model->save();

        // Check relations
        $model = ModelWithMutator::find($model->getKey());
        $model->load('children');
        foreach ($children as $index => $child) {
            $this->assertEquals(
                $child->getKey(),
                $model->data['children'][$index]->getKey()
            );
        }
        $this->assertEquals(sizeof($children), sizeof($model->children));

        $newData = [
            'id' => 1234,
            'title' => 'Title',
            'description' => 'Description'
        ];
        $model->data = $newData;
        $model->save();

        $model = ModelWithMutator::find($model->getKey());
        $model->load('children');
        $this->assertFalse(
            isset($model->data['children'])
        );
        $this->assertEquals(0, sizeof($model->children));

        $children = array_reverse($children);
        $model = new ModelWithMutator();
        $model->data = array_merge($data, [
            'children' => $children,
        ]);
        $model->save();

        // Check relations
        $model = ModelWithMutator::find($model->getKey());
        $model->load('children');
        foreach ($children as $index => $child) {
            $this->assertEquals(
                $child->getKey(),
                $model->data['children'][$index]->getKey()
            );
        }
        $this->assertEquals(sizeof($children), sizeof($model->children));
    }
}
