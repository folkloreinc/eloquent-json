<?php

namespace Folklore\EloquentJson\Tests\Unit\Support;

use Folklore\EloquentJson\Tests\TestCase;
use Folklore\EloquentJson\Support\JsonSchema;

/**
 * @coversDefaultClass Folklore\EloquentJson\Support\JsonSchema
 */
class JsonSchemaTest extends TestCase
{
    /**
     * Test getting id
     *
     * @covers ::setId
     * @covers ::getId
     * @test
     *
     */
    public function testId()
    {
        $id = 'schemaid';
        $schema = new JsonSchema();
        $schema->setId($id);
        $this->assertEquals($id, $schema->getId());
    }

    /**
     * Test getting type
     *
     * @covers ::setType
     * @covers ::getType
     * @test
     *
     */
    public function testType()
    {
        $type = 'integer';
        $schema = new JsonSchema();
        $schema->setType($type);
        $this->assertEquals($type, $schema->getType());
    }

    /**
     * Test getting properties
     *
     * @covers ::setProperties
     * @covers ::getProperties
     * @dataProvider propertiesProvider
     * @test
     *
     */
    public function testProperties($dataProperty)
    {
        $properties = [
            'data' => $dataProperty
        ];
        $schema = new JsonSchema();
        $schema->setProperties($properties);
        $returnedProperties = $schema->getProperties();
        $this->assertArrayHasKey('data', $returnedProperties);
        $this->assertInstanceOf(JsonSchema::class, $returnedProperties['data']);
        $this->assertEquals(
            $properties['data']['type'],
            $returnedProperties['data']['type']
        );
    }

    public function propertiesProvider()
    {
        return [
            [
                [
                    'type' => 'integer'
                ]
            ],
            [new JsonSchema()]
        ];
    }

    /**
     * Test to array
     *
     * @covers ::toArray
     * @covers ::getProperties
     * @test
     *
     */
    public function testToArray()
    {
        $data = [
            'properties' => [
                'data' => [
                    'type' => 'integer'
                ]
            ]
        ];
        $schema = new JsonSchema($data);
        $schemaArray = $schema->toArray();
        $this->assertEquals(array_keys($data['properties']), array_keys($schemaArray['properties']));
    }
}
