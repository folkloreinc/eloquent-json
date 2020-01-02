<?php

namespace Folklore\EloquentJson\Tests\Unit\Mutators;

use Folklore\EloquentJson\Tests\TestCase;
use Folklore\EloquentJson\Mutators\WithPath;
use TestMutator;
use ModelWithSchema;

/**
 * @coversDefaultClass Folklore\EloquentJson\Mutators\WithPath
 */
class WithPathTest extends TestCase
{
    /**
     * @dataProvider mutateProvider
     * @test
     */
    public function testMutate($value, $path, $multiple = false)
    {
        $methods = ['mutateToValue', 'mutateToAttribute'];
        foreach ($methods as $method) {
            $model = new ModelWithSchema();
            $attribute = 'data';
            $testMutator = $this->createMock(TestMutator::class);
            $valueAtPath = data_get($value, $path);
            $methodMock = $testMutator
                ->expects(
                    $multiple
                        ? $this->exactly(sizeof($valueAtPath))
                        : $this->once()
                )
                ->method($method);
            $methodMock->will(
                $multiple
                    ? call_user_func_array(
                        [$this, 'onConsecutiveCalls'],
                        $valueAtPath
                    )
                    : $this->returnValue($valueAtPath)
            );
            if ($multiple) {
                call_user_func_array(
                    [$methodMock, 'withConsecutive'],
                    array_map(function ($valueAtPath) use ($model, $attribute) {
                        return [
                            $this->equalTo($model),
                            $this->equalTo($attribute),
                            $this->equalTo($valueAtPath)
                        ];
                    }, $valueAtPath)
                );
            } else {
                $methodMock->with(
                    $this->equalTo($model),
                    $this->equalTo($attribute),
                    $this->equalTo($valueAtPath)
                );
            }
            $mutator = new WithPath($testMutator, $path);
            $newValue = $mutator->{$method}($model, $attribute, $value);
            $this->assertEquals($value, $newValue);
        }
    }

    public function mutateProvider()
    {
        return [
            [
                [
                    'key' => 'value'
                ],
                'key'
            ],
            [
                [
                    'items' => ['item 1', 'item 2', 'item 3']
                ],
                'items.*',
                true
            ],
            [
                [
                    'items' => ['item 1', 'item 2', 'item 3']
                ],
                'items.0'
            ]
        ];
    }
}
