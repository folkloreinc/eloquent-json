<?php

namespace Folklore\EloquentJson\Tests\Feature;

use Folklore\EloquentJson\Tests\TestCase;
use Folklore\EloquentJson\Tests\RunMigrationsTrait;
use ModelWithSchema;

class ModelWithSchemaTest extends TestCase
{
    use RunMigrationsTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    /**
     * Test setting valid data
     */
    public function testSetValid()
    {
        $data = [
            'id' => 1234,
            'title' => 'Title',
            'description' => 'Description'
        ];
        $model = new ModelWithSchema();
        $model->data = $data;
        $this->assertEquals($data, $model->data);
    }

    /**
     * Test setting invalid data
     */
    public function testSetInvalid()
    {
        $this->expectException(
            \Folklore\EloquentJson\ValidationException::class
        );
        $this->expectExceptionMessageRegExp('/data\.id/');
        $this->expectExceptionMessageRegExp('/data.title/');
        $model = new ModelWithSchema();
        $model->data = [
            'id' => '1234',
            'title' => 1234,
            'description' => 'Description'
        ];
    }
}
