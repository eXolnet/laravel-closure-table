<?php

namespace Exolnet\ClosureTable\Tests\Unit\Model;

use Exolnet\ClosureTable\Models\NodeUnordered;
use Exolnet\ClosureTable\Tests\Mocks\NodeMock;
use Exolnet\ClosureTable\Tests\Unit\UnitTestCase;

class NodeUnorderedTest extends UnitTestCase
{
    /**
     * @var \Exolnet\ClosureTable\Tests\Mocks\NodeMock
     */
    protected $model;

    public function setUp(): void
    {
        $this->model = new NodeMock();
    }

    public function testItIsInitializable()
    {
        $this->assertInstanceOf(NodeUnordered::class, $this->model);
    }
}
