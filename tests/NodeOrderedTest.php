<?php

namespace Exolnet\ClosureTable\Tests;

use Exolnet\ClosureTable\Tests\Mocks\NodeOrderedMock;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;

class NodeOrderedTest extends TestCase
{
    protected NodeOrderedMock $node;

    protected function setUp(): void
    {
        parent::setUp();

        $this->node = new NodeOrderedMock();
    }

    protected function setUpDatabase(Application $app)
    {
        parent::setUpDatabase($app);

        // Create table for ordered node mocks with position column
        $app['db']->connection()->getSchemaBuilder()->create('node_ordered_mocks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        $app['db']->connection()->getSchemaBuilder()->create('node_ordered_mock_closures', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('ancestor_id');
            $table->unsignedBigInteger('descendant_id');
            $table->unsignedBigInteger('depth')->default(0);

            $table->foreign('ancestor_id')
                ->references('id')->on('node_ordered_mocks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('descendant_id')
                ->references('id')->on('node_ordered_mocks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * @return \Exolnet\ClosureTable\Tests\Mocks\NodeOrderedMock
     */
    protected function generateNode(string $name = null): NodeOrderedMock
    {
        $node = new NodeOrderedMock();
        if ($name) {
            $node->name = $name;
        }
        $node->save();

        return $node;
    }

    protected function getClosureCount(): int
    {
        return DB::table('node_ordered_mock_closures')->count();
    }

    protected function getNodeCount(): int
    {
        return NodeOrderedMock::all()->count();
    }

    public function testMoveAsFirstChildWhenParentHasNoChildren()
    {
        $parent = $this->generateNode('parent');
        $child = $this->generateNode('child');

        $child->moveAsFirstChild($parent);

        $this->assertEquals($parent->id, $child->fresh()->getParent()->id);
        $this->assertEquals(0, $child->fresh()->position);
        $this->assertEquals(1, $parent->fresh()->countChildren());
    }

    public function testMoveAsFirstChildWhenParentHasChildren()
    {
        $parent = $this->generateNode('parent');
        $existingChild1 = $this->generateNode('existing1');
        $existingChild2 = $this->generateNode('existing2');
        $newChild = $this->generateNode('new');

        // Set up existing children with positions
        $existingChild1->moveAsChildOf($parent);
        $existingChild1->position = 5;
        $existingChild1->save();

        $existingChild2->moveAsChildOf($parent);
        $existingChild2->position = 10;
        $existingChild2->save();

        // Move new child as first child
        $newChild->moveAsFirstChild($parent);

        $this->assertEquals($parent->id, $newChild->fresh()->getParent()->id);
        $this->assertEquals(4, $newChild->fresh()->position); // 5 - 1 = 4 (before existing minimum)
        $this->assertEquals(3, $parent->fresh()->countChildren());
    }

    public function testMoveAsLastChildWhenParentHasNoChildren()
    {
        $parent = $this->generateNode('parent');
        $child = $this->generateNode('child');

        $child->moveAsLastChild($parent);

        $this->assertEquals($parent->id, $child->fresh()->getParent()->id);
        $this->assertEquals(0, $child->fresh()->position);
        $this->assertEquals(1, $parent->fresh()->countChildren());
    }

    public function testMoveAsLastChildWhenParentHasChildren()
    {
        $parent = $this->generateNode('parent');
        $existingChild1 = $this->generateNode('existing1');
        $existingChild2 = $this->generateNode('existing2');
        $newChild = $this->generateNode('new');

        // Set up existing children with positions
        $existingChild1->moveAsChildOf($parent);
        $existingChild1->position = 5;
        $existingChild1->save();

        $existingChild2->moveAsChildOf($parent);
        $existingChild2->position = 10;
        $existingChild2->save();

        // Move new child as last child
        $newChild->moveAsLastChild($parent);

        $this->assertEquals($parent->id, $newChild->fresh()->getParent()->id);
        $this->assertEquals(11, $newChild->fresh()->position); // 10 + 1 = 11 (after existing maximum)
        $this->assertEquals(3, $parent->fresh()->countChildren());
    }

    public function testMoveAsFirstChildWithRootLevelNode()
    {
        // This specifically tests the issue: "Unable to move a node as first child or last child when this node is on the root level of the tree"
        $parent = $this->generateNode('parent');
        $rootNode = $this->generateNode('root_node'); // This starts as a root node

        // Verify the root node is actually at root level
        $this->assertTrue($rootNode->isRoot());
        $this->assertEquals(0, $rootNode->getDepth());

        // Move the root node as first child of another node
        $rootNode->moveAsFirstChild($parent);

        // Verify the move was successful
        $this->assertFalse($rootNode->fresh()->isRoot());
        $this->assertEquals(1, $rootNode->fresh()->getDepth());
        $this->assertEquals($parent->id, $rootNode->fresh()->getParent()->id);
        $this->assertEquals(1, $parent->fresh()->countChildren());
    }

    public function testMoveAsLastChildWithRootLevelNode()
    {
        // This specifically tests the issue: "Unable to move a node as first child or last child when this node is on the root level of the tree"
        $parent = $this->generateNode('parent');
        $rootNode = $this->generateNode('root_node'); // This starts as a root node

        // Verify the root node is actually at root level
        $this->assertTrue($rootNode->isRoot());
        $this->assertEquals(0, $rootNode->getDepth());

        // Move the root node as last child of another node
        $rootNode->moveAsLastChild($parent);

        // Verify the move was successful
        $this->assertFalse($rootNode->fresh()->isRoot());
        $this->assertEquals(1, $rootNode->fresh()->getDepth());
        $this->assertEquals($parent->id, $rootNode->fresh()->getParent()->id);
        $this->assertEquals(1, $parent->fresh()->countChildren());
    }

    public function testMakeFirstRootWithExistingRoots()
    {
        $existingRoot1 = $this->generateNode('root1');
        $existingRoot1->position = 5;
        $existingRoot1->save();

        $existingRoot2 = $this->generateNode('root2');
        $existingRoot2->position = 10;
        $existingRoot2->save();

        $newRoot = $this->generateNode('new_root');
        $newRoot->makeFirstRoot();

        $this->assertTrue($newRoot->fresh()->isRoot());
        $this->assertEquals(4, $newRoot->fresh()->position); // 5 - 1 = 4 (before existing minimum)
    }

    public function testMakeLastRootWithExistingRoots()
    {
        $existingRoot1 = $this->generateNode('root1');
        $existingRoot1->position = 5;
        $existingRoot1->save();

        $existingRoot2 = $this->generateNode('root2');
        $existingRoot2->position = 10;
        $existingRoot2->save();

        $newRoot = $this->generateNode('new_root');
        $newRoot->makeLastRoot();

        $this->assertTrue($newRoot->fresh()->isRoot());
        $this->assertEquals(11, $newRoot->fresh()->position); // 10 + 1 = 11 (after existing maximum)
    }

    public function testComplexScenarioWithMixedOperations()
    {
        // Create a complex tree structure and test various move operations
        $root = $this->generateNode('root');
        $child1 = $this->generateNode('child1');
        $child2 = $this->generateNode('child2');
        $child3 = $this->generateNode('child3');
        
        // Add children in specific order
        $child2->moveAsLastChild($root);
        $child1->moveAsFirstChild($root);
        $child3->moveAsLastChild($root);

        // Verify order: child1 (first), child2 (middle), child3 (last)
        $children = $root->fresh()->children()->orderBy('position')->get();
        $this->assertEquals('child1', $children[0]->name);
        $this->assertEquals('child2', $children[1]->name);
        $this->assertEquals('child3', $children[2]->name);

        // Verify positions are in ascending order
        $this->assertLessThan($children[1]->position, $children[0]->position);
        $this->assertLessThan($children[2]->position, $children[1]->position);
    }
}