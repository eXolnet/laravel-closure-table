<?php

namespace Exolnet\ClosureTable\Tests;

use Exolnet\ClosureTable\Exceptions\DeleteNotPossibleException;
use Exolnet\ClosureTable\Exceptions\MoveNotPossibleException;
use Exolnet\ClosureTable\Exceptions\SameNodeException;
use Exolnet\ClosureTable\Tests\Mocks\NodeMock;
use Illuminate\Support\Facades\DB;

class NodeUnorderedTest extends TestCase
{
    protected NodeMock $node;

    protected function setUp(): void
    {
        parent::setUp();

        $this->node = new NodeMock();
    }

    /**
     * @return \Exolnet\ClosureTable\Tests\Mocks\NodeMock
     */
    protected function generateNode(): NodeMock
    {
        $node = new NodeMock();
        $node->save();

        return $node;
    }

    /**
     *    N1      N7
     *   /  \     |
     *  N2  N3    N8
     *     /  \
     *    N4  N5
     *        |
     *        N6.
     */
    protected function generateTree(): array
    {
        $node1 = $this->generateNode();
        $node2 = $this->generateNode()->moveAsChildOf($node1);
        $node3 = $this->generateNode()->moveAsChildOf($node1);
        $node4 = $this->generateNode()->moveAsChildOf($node3);
        $node5 = $this->generateNode()->moveAsChildOf($node3);
        $node6 = $this->generateNode()->moveAsChildOf($node5);
        $node7 = $this->generateNode();
        $node8 = $this->generateNode()->moveAsChildOf($node7);

        return [
            1 => $node1,
            $node2,
            $node3,
            $node4,
            $node5,
            $node6,
            $node7,
            $node8,
        ];
    }

    protected function getClosureCount(): int
    {
        return DB::table('node_mock_closures')->count();
    }

    protected function getNodeCount(): int
    {
        return NodeMock::all()->count();
    }

    public function testTreeCreation(): void
    {
        $this->generateTree();

        $this->assertEquals(8, $this->getNodeCount());
        $this->assertEquals(18, $this->getClosureCount());
    }

    public function testANodeCanBeDeleted()
    {
        $node = $this->generateNode();

        $deleted = $node->delete();

        $this->assertTrue($deleted);

        $this->assertFalse($node->exists);

        $this->assertEquals(0, $this->getNodeCount());
        $this->assertEquals(1, $this->getClosureCount());
    }

    public function testANodeDeletionDestroysItsClosure()
    {
        $node = $this->generateNode();

        $node->delete();

        $expected = [];

        $this->assertEquals($expected, $node->getPath()->modelKeys());
        $this->assertEquals($expected, $node->getSubtree()->modelKeys());
    }

    public function testANodeNotEmptyCannotBeDeleted()
    {
        $nodes = $this->generateTree();

        $this->expectException(
            DeleteNotPossibleException::class
        );

        $nodes[3]->delete();
    }

    public function testDeleteKeepDescendants()
    {
        /** @var array<NodeMock> $nodes */
        $nodes = $this->generateTree();

        $nodes[3]->deleteKeepDescendants();

        $this->assertEquals(7, $this->getNodeCount());
        $this->assertEquals(15, $this->getClosureCount());

        $this->assertEquals([2, 4, 5, 6], $nodes[1]->getDescendants()->modelKeys());
    }

    public function testDeleteSubtree()
    {
        $nodes = $this->generateTree();

        $nodes[3]->deleteSubtree();

        $this->assertEquals(4, $this->getNodeCount());
        $this->assertEquals(18, $this->getClosureCount());

        $this->assertEquals([2], $nodes[1]->getDescendants()->modelKeys());
    }

    public function testDeleteDescendants()
    {
        $nodes = $this->generateTree();

        $nodes[3]->deleteDescendants();

        $this->assertEquals(5, $this->getNodeCount());
        $this->assertEquals(18, $this->getClosureCount());

        $this->assertEquals([2, 3], $nodes[1]->getDescendants()->modelKeys());
    }

    //==========================================================================
    // Moves
    //==========================================================================

    public function testItFailsToMoveToItself()
    {
        $root = $this->generateNode();

        $this->expectException(
            SameNodeException::class
        );

        $root->moveAsChildOf($root);
    }

    public function testMoveAsChild()
    {
        $root = $this->generateNode();
        $this->assertEquals(1, $this->getClosureCount());

        $node1 = $this->generateNode()->moveAsChildOf($root);
        $this->assertEquals(3, $this->getClosureCount());

        $node2 = $this->generateNode()->moveAsChildOf($node1);
        $this->assertEquals(6, $this->getClosureCount());

        // Root
        $this->assertEquals(1, $root->countChildren());
        $this->assertEquals(2, $root->countDescendants());

        // Level 1
        $this->assertFalse($node1->isRoot());
        $this->assertEquals(1, $node1->getParent()->getKey());
        $this->assertEquals(1, $node1->countAncestors());
        $this->assertEquals(1, $node1->countDescendants());
        $this->assertEquals(1, $node1->getDepth());

        // Level 2
        $this->assertFalse($node2->isRoot());
        $this->assertEquals(2, $node2->getParent()->getKey());
        $this->assertEquals(2, $node2->countAncestors());
        $this->assertEquals(0, $node2->countDescendants());
        $this->assertEquals(2, $node2->getDepth());
    }

    public function testMoveAsChildANonCreatedNodeShouldThrow()
    {
        $node = new NodeMock();

        $this->expectException(
            MoveNotPossibleException::class
        );

        $node->moveAsChildOf($node);
    }

    public function testMakeRootANonCreatedNodeShouldThrow()
    {
        $node = new NodeMock();

        $this->expectException(
            MoveNotPossibleException::class
        );

        $node->makeRoot();
    }

    public function testMoveAsParentANonCreatedNodeShouldThrow()
    {
        $node = new NodeMock();

        $this->expectException(
            MoveNotPossibleException::class
        );

        $node->moveAsParentOf($node);
    }

    public function testMoveAsSiblingANonCreatedNodeShouldThrow()
    {
        $node = new NodeMock();

        $this->expectException(
            MoveNotPossibleException::class
        );

        $node->moveAsSiblingOf($node);
    }

    public function testMoveAsRootOfANonCreatedNodeShouldThrow()
    {
        $node = new NodeMock();

        $this->expectException(
            MoveNotPossibleException::class
        );

        $node->moveAsRootOf($node);
    }

    //==========================================================================
    // Relations
    //==========================================================================

    private function assertPath($expected, $node)
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            $node->getPath()->modelKeys()
        );

        $this->assertEquals(count($expected), $node->countPath());
    }

    public function testGetPath()
    {
        $nodes = $this->generateTree();

        $this->assertPath([1], $nodes[1]);
        $this->assertPath([1, 2], $nodes[2]);
        $this->assertPath([1, 3], $nodes[3]);
        $this->assertPath([1, 3, 4], $nodes[4]);
        $this->assertPath([1, 3, 5], $nodes[5]);
        $this->assertPath([1, 3, 5, 6], $nodes[6]);
        $this->assertPath([7], $nodes[7]);
        $this->assertPath([7, 8], $nodes[8]);
    }

    private function assertSubtree($expected, $node)
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            $node->getSubtree()->modelKeys()
        );
    }

    public function testGetSubtree()
    {
        $nodes = $this->generateTree();

        $this->assertSubtree([1, 2, 3, 4, 5, 6], $nodes[1]);
        $this->assertSubtree([2], $nodes[2]);
        $this->assertSubtree([3, 4, 5, 6], $nodes[3]);
        $this->assertSubtree([4], $nodes[4]);
        $this->assertSubtree([5, 6], $nodes[5]);
        $this->assertSubtree([6], $nodes[6]);
        $this->assertSubtree([7, 8], $nodes[7]);
        $this->assertSubtree([8], $nodes[8]);
    }

    private function assertAncestors(array $expected, NodeMock $node)
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            $node->getAncestors()->modelKeys()
        );

        $this->assertEquals(count($expected), $node->countAncestors());

        if (count($expected) > 0) {
            $this->assertTrue($node->hasAncestors());
        } else {
            $this->assertFalse($node->hasAncestors());
        }

        if (count($expected) === 0) {
            $this->assertTrue($node->isRoot());
        } else {
            $this->assertFalse($node->isRoot());
        }
    }

    public function testGetAncestors()
    {
        $nodes = $this->generateTree();

        $this->assertAncestors([], $nodes[1]);
        $this->assertAncestors([1], $nodes[2]);
        $this->assertAncestors([1], $nodes[3]);
        $this->assertAncestors([1, 3], $nodes[4]);
        $this->assertAncestors([1, 3], $nodes[5]);
        $this->assertAncestors([1, 3, 5], $nodes[6]);
        $this->assertAncestors([], $nodes[7]);
        $this->assertAncestors([7], $nodes[8]);
    }

    private function assertParent($expected, $node)
    {
        $parent = $node->getParent();
        $actual = $parent !== null ? $parent->getKey() : null;

        $this->assertEquals($expected, $actual);

        $isRoot = $expected === null;
        $this->assertEquals(!$isRoot, $node->isChild());
        $this->assertEquals(!$isRoot, $node->hasParent());
    }

    public function testGetParent()
    {
        $nodes = $this->generateTree();

        $this->assertParent(null, $nodes[1]);
        $this->assertParent(1, $nodes[2]);
        $this->assertParent(1, $nodes[3]);
        $this->assertParent(3, $nodes[4]);
        $this->assertParent(3, $nodes[5]);
        $this->assertParent(5, $nodes[6]);
        $this->assertParent(null, $nodes[7]);
        $this->assertParent(7, $nodes[8]);
    }

    private function assertRoot($expected, $node)
    {
        $root = $node->getRoot();
        $actual = $root !== null ? $root->getKey() : null;

        $this->assertEquals($expected, $actual);
    }

    public function testGetRoot()
    {
        $nodes = $this->generateTree();

        $this->assertRoot(null, $nodes[1]);
        $this->assertRoot(1, $nodes[2]);
        $this->assertRoot(1, $nodes[3]);
        $this->assertRoot(1, $nodes[4]);
        $this->assertRoot(1, $nodes[5]);
        $this->assertRoot(1, $nodes[6]);
        $this->assertRoot(null, $nodes[7]);
        $this->assertRoot(7, $nodes[8]);
    }

    private function assertDescendants($expected, $node)
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            $node->getDescendants()->modelKeys()
        );

        $this->assertEquals(count($expected), $node->countDescendants());
        $this->assertEquals(count($expected) > 0, $node->hasDescendants());

        if (count($expected) === 0) {
            $this->assertTrue($node->isLeaf());
            $this->assertFalse($node->isParent());
        } else {
            $this->assertFalse($node->isLeaf());
            $this->assertTrue($node->isParent());
        }
    }

    public function testGetDescendants()
    {
        $nodes = $this->generateTree();

        $this->assertDescendants([2, 3, 4, 5, 6], $nodes[1]);
        $this->assertDescendants([], $nodes[2]);
        $this->assertDescendants([4, 5, 6], $nodes[3]);
        $this->assertDescendants([], $nodes[4]);
        $this->assertDescendants([6], $nodes[5]);
        $this->assertDescendants([], $nodes[6]);
        $this->assertDescendants([8], $nodes[7]);
        $this->assertDescendants([], $nodes[8]);
    }

    private function assertChildren($expected, $node)
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            $node->getChildren()->modelKeys()
        );

        $this->assertEquals(count($expected), $node->countChildren());
        $this->assertEquals(count($expected) > 0, $node->hasChildren());
    }

    public function testGetChildren()
    {
        $nodes = $this->generateTree();

        $this->assertChildren([2, 3], $nodes[1]);
        $this->assertChildren([], $nodes[2]);
        $this->assertChildren([4, 5], $nodes[3]);
        $this->assertChildren([], $nodes[4]);
        $this->assertChildren([6], $nodes[5]);
        $this->assertChildren([], $nodes[6]);
        $this->assertChildren([8], $nodes[7]);
        $this->assertChildren([], $nodes[8]);
    }

    private function assertNeighbourhood(array $expected, NodeMock $node)
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            $node->getNeighbourhood()->modelKeys()
        );
        $this->assertEquals(count($expected), $node->countNeighbourhood());
        $this->assertEquals(count($expected) > 0, $node->hasNeighbourhood());
    }

    public function testGetNeighbourhood()
    {
        $nodes = $this->generateTree();

        $this->assertNeighbourhood([1, 2, 3, 4, 5, 6, 7, 8], $nodes[1]);
        $this->assertNeighbourhood([2, 3, 4, 5, 6], $nodes[2]);
        $this->assertNeighbourhood([2, 3, 4, 5, 6], $nodes[3]);
        $this->assertNeighbourhood([4, 5, 6], $nodes[4]);
        $this->assertNeighbourhood([4, 5, 6], $nodes[5]);
        $this->assertNeighbourhood([6], $nodes[6]);
        $this->assertNeighbourhood([1, 2, 3, 4, 5, 6, 7, 8], $nodes[7]);
        $this->assertNeighbourhood([8], $nodes[8]);
    }

    private function assertSiblings($expected, $node)
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            $node->getSiblings()->modelKeys()
        );

        $this->assertEquals(count($expected), $node->countSiblings());
        $this->assertEquals(count($expected) > 0, $node->hasSiblings());
    }

    public function testGetSiblings()
    {
        $nodes = $this->generateTree();

        $this->assertSiblings([7], $nodes[1]);
        $this->assertSiblings([3], $nodes[2]);
        $this->assertSiblings([2], $nodes[3]);
        $this->assertSiblings([5], $nodes[4]);
        $this->assertSiblings([4], $nodes[5]);
        $this->assertSiblings([], $nodes[6]);
        $this->assertSiblings([1], $nodes[7]);
        $this->assertSiblings([], $nodes[8]);
    }

    private function assertDepth($expected, $node)
    {
        $this->assertEquals($expected, $node->getDepth());
    }

    public function testGetDepth()
    {
        $nodes = $this->generateTree();

        $this->assertDepth(0, $nodes[1]);
        $this->assertDepth(1, $nodes[2]);
        $this->assertDepth(1, $nodes[3]);
        $this->assertDepth(2, $nodes[4]);
        $this->assertDepth(2, $nodes[5]);
        $this->assertDepth(3, $nodes[6]);
        $this->assertDepth(0, $nodes[7]);
        $this->assertDepth(1, $nodes[8]);
    }

    private function assertRoots(array $expected)
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            $this->node->getRoots()->modelKeys()
        );

        $this->assertEquals(count($expected), $this->node->countRoots());
        $this->assertEquals(count($expected) > 0, $this->node->hasRoots());
    }

    public function testGetRoots()
    {
        $this->generateTree();

        $this->assertRoots([1, 7]);
    }

    private function assertLeaves($expected)
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            $this->node->getLeaves()->modelKeys()
        );

        $this->assertEquals(count($expected), $this->node->countLeaves());
        $this->assertEquals(count($expected) > 0, $this->node->hasLeaves());
    }

    public function testGetLeaves()
    {
        $this->assertLeaves([]);

        $nodes = $this->generateTree();

        $this->assertLeaves([2, 4, 6, 8], $nodes[1]);
    }

    public function testInsertAsRoot()
    {
        $nodes = $this->generateTree();

        /** @var NodeMock $node */
        $node = $nodes[8];
        $this->assertFalse($node->isRoot());

        $node->insertAsRoot();

        $this->assertTrue($node->isRoot());
    }

    public function testInsertAsChildOf()
    {
        $nodes = $this->generateTree();

        $node = new NodeMock();
        $node->save();

        $this->assertFalse($node->isChild());

        $this->assertEquals(0, $nodes[2]->countChildren());

        $node->insertAsChildOf($nodes[2]);
        $this->assertTrue($node->isChild());
        $this->assertEquals(1, $nodes[2]->countChildren());

        $this->assertEquals($nodes[2]->id, $node->parent()->first()->id);
        $node->children()->first();
    }
}
