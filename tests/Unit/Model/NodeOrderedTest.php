<?php

namespace Exolnet\ClosureTable\Tests\Unit\Model;

use Exolnet\ClosureTable\Models\NodeOrdered;
use Exolnet\ClosureTable\Tests\Mocks\NodeOrderedMock;
use Exolnet\ClosureTable\Tests\Unit\UnitTestCase;

class NodeOrderedTest extends UnitTestCase
{
    /**
     * @var \Exolnet\ClosureTable\Tests\Mocks\NodeOrderedMock
     */
    protected $model;

    public function setUp(): void
    {
        $this->model = new NodeOrderedMock();
    }

    public function testItIsInitializable()
    {
        $this->assertInstanceOf(NodeOrdered::class, $this->model);
    }

    public function testMoveAsFirstChildThrowsExceptionForNonExistentNodes()
    {
        $this->expectException(\Exolnet\ClosureTable\Exceptions\MoveNotPossibleException::class);

        $parent = new NodeOrderedMock();
        $child = new NodeOrderedMock();

        $child->moveAsFirstChild($parent);
    }

    public function testMoveAsLastChildThrowsExceptionForNonExistentNodes()
    {
        $this->expectException(\Exolnet\ClosureTable\Exceptions\MoveNotPossibleException::class);

        $parent = new NodeOrderedMock();
        $child = new NodeOrderedMock();

        $child->moveAsLastChild($parent);
    }

    public function testMoveAsFirstChildThrowsExceptionForSameNode()
    {
        $this->expectException(\Exolnet\ClosureTable\Exceptions\SameNodeException::class);

        $node = new NodeOrderedMock();
        $node->id = 1; // Simulate existing node
        $node->exists = true; // Mark as existing

        $node->moveAsFirstChild($node);
    }

    public function testMoveAsLastChildThrowsExceptionForSameNode()
    {
        $this->expectException(\Exolnet\ClosureTable\Exceptions\SameNodeException::class);

        $node = new NodeOrderedMock();
        $node->id = 1; // Simulate existing node
        $node->exists = true; // Mark as existing

        $node->moveAsLastChild($node);
    }

    public function testGetPositionColumn()
    {
        $this->assertEquals('position', $this->model->getPositionColumn());
    }

    public function testGetQualifiedPositionColumn()
    {
        $this->assertEquals($this->model->getTable() . '.position', $this->model->getQualifiedPositionColumn());
    }
}
