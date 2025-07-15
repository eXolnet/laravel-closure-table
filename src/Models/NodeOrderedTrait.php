<?php

namespace Exolnet\ClosureTable\Models;

use Exolnet\ClosureTable\Contracts\NodeInterface;
use Exolnet\ClosureTable\Contracts\NodeOrderedInterface;
use Exolnet\ClosureTable\Exceptions\ClosureTableException;
use Exolnet\ClosureTable\Exceptions\MoveNotPossibleException;
use Exolnet\ClosureTable\Exceptions\SameNodeException;
use Illuminate\Support\Facades\DB;

trait NodeOrderedTrait
{
    /**
     * The position column for ordering.
     *
     * @var string
     */
    protected string $position_column = 'position';

    /**
     * Get the position column name.
     *
     * @return string
     */
    public function getPositionColumn(): string
    {
        return $this->position_column;
    }

    /**
     * Get the qualified position column name.
     *
     * @return string
     */
    public function getQualifiedPositionColumn(): string
    {
        return $this->getTable() . '.' . $this->getPositionColumn();
    }

    /**
     * Remove all relations from the node and it's descendants with the
     * ancestors to make it a root node.
     *
     * @return $this|null
     */
    public function makeRoot(): ?NodeOrderedInterface
    {
        if (! $this->exists) {
            throw new MoveNotPossibleException();
        }

        $ancestor_ids = $this->getAncestors()->modelKeys();
        $depth        = count($ancestor_ids);

        // Already root
        if ($depth === 0) {
            return null;
        }

        $subtree_ids = $this->getSubtree()->modelKeys();

        // Remove useless relations
        $this->path()->newPivotStatement()
            ->whereIn($this->getClosureAncestorColumn(), $ancestor_ids)
            ->whereIn($this->getClosureDescendantColumn(), $subtree_ids)
            ->delete();

        return $this;
    }

    /**
     * Move a node as a child of a given node.
     *
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return \Exolnet\ClosureTable\Contracts\NodeOrderedInterface
     */
    public function moveAsChildOf(NodeInterface $ofNode): NodeOrderedInterface
    {
        if (! $this->exists || ! $ofNode->exists) {
            throw new MoveNotPossibleException();
        }

        $this->assertIsDifferentNode($this, $ofNode);

        // Detach the node from its ancestors
        $this->makeRoot();

        // Attach the node to its new ancestors
        $table        = $this->getClosureTable();
        $ancestor     = $this->getClosureAncestorColumn();
        $descendant   = $this->getClosureDescendantColumn();
        $depth        = $this->getClosureDepthColumn();

        $ancestorId   = $ofNode->getKey();
        $descendantId = $this->getKey();

        $query = "
			INSERT INTO {$table} ({$ancestor}, {$descendant}, {$depth})
			SELECT supertbl.{$ancestor}, subtbl.{$descendant}, supertbl.{$depth}+subtbl.{$depth}+1
			FROM {$table} as supertbl
			CROSS JOIN {$table} as subtbl
			WHERE supertbl.{$descendant} = {$ancestorId}
			AND subtbl.{$ancestor} = {$descendantId}
			";

        DB::statement($query);

        $this->refresh();

        return $this;
    }

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $nodeA
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $nodeB
     */
    protected function assertIsDifferentNode(NodeInterface $nodeA, NodeInterface $nodeB)
    {
        if ($nodeA->getId() === $nodeB->getId()) {
            throw new SameNodeException(
                'Node A(id=' . $nodeA->getId() . ') and B(id=' . $nodeB->getId() . ') must be different ' .
                '(they are the same at this point).'
            );
        }
    }

    /**
     * Move this node as the first child of the given node.
     *
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsFirstChild(NodeInterface $ofNode): NodeOrderedInterface
    {
        if (!$this->exists || !$ofNode->exists) {
            throw new MoveNotPossibleException();
        }

        $this->assertIsDifferentNode($this, $ofNode);

        // First move as child to establish the relationship
        $this->moveAsChildOf($ofNode);

        // Then adjust position to be first
        $this->moveToFirstPosition($ofNode);

        return $this;
    }

    /**
     * Move this node as the last child of the given node.
     *
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsLastChild(NodeInterface $ofNode): NodeOrderedInterface
    {
        if (!$this->exists || !$ofNode->exists) {
            throw new MoveNotPossibleException();
        }

        $this->assertIsDifferentNode($this, $ofNode);

        // First move as child to establish the relationship
        $this->moveAsChildOf($ofNode);

        // Then adjust position to be last
        $this->moveToLastPosition($ofNode);

        return $this;
    }

    /**
     * Move this node to the first position among its siblings.
     *
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $parent
     */
    protected function moveToFirstPosition(NodeInterface $parent): void
    {
        // Get the minimum position of existing children
        $minPosition = $parent->children()
            ->where($this->getQualifiedKeyName(), '!=', $this->getKey())
            ->min($this->getPositionColumn());

        // Set position to be before the first child
        $newPosition = $minPosition !== null ? $minPosition - 1 : 0;
        $this->setAttribute($this->getPositionColumn(), $newPosition);
        $this->save();
    }

    /**
     * Move this node to the last position among its siblings.
     *
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $parent
     */
    protected function moveToLastPosition(NodeInterface $parent): void
    {
        // Get the maximum position of existing children
        $maxPosition = $parent->children()
            ->where($this->getQualifiedKeyName(), '!=', $this->getKey())
            ->max($this->getPositionColumn());

        // Set position to be after the last child
        $newPosition = $maxPosition !== null ? $maxPosition + 1 : 0;
        $this->setAttribute($this->getPositionColumn(), $newPosition);
        $this->save();
    }

    /**
     * Make this node the first root.
     *
     * @return $this
     */
    public function makeFirstRoot(): NodeOrderedInterface
    {
        $this->makeRoot();

        // Get the minimum position of existing roots
        $minPosition = static::roots()
            ->where($this->getQualifiedKeyName(), '!=', $this->getKey())
            ->min($this->getPositionColumn());

        // Set position to be before the first root
        $newPosition = $minPosition !== null ? $minPosition - 1 : 0;
        $this->setAttribute($this->getPositionColumn(), $newPosition);
        $this->save();

        return $this;
    }

    /**
     * Make this node the last root.
     *
     * @return $this
     */
    public function makeLastRoot(): NodeOrderedInterface
    {
        $this->makeRoot();

        // Get the maximum position of existing roots
        $maxPosition = static::roots()
            ->where($this->getQualifiedKeyName(), '!=', $this->getKey())
            ->max($this->getPositionColumn());

        // Set position to be after the last root
        $newPosition = $maxPosition !== null ? $maxPosition + 1 : 0;
        $this->setAttribute($this->getPositionColumn(), $newPosition);
        $this->save();

        return $this;
    }

    /**
     * @return $this
     */
    public function insertAsRoot(): NodeOrderedInterface
    {
        if (! $this->save()) {
            throw new ClosureTableException('Unable to save to node.');
        }

        return $this->makeRoot();
    }

    /**
     * Placeholder implementations for remaining NodeOrderedInterface methods.
     * These would need proper implementation based on specific requirements.
     */

    public function moveAsParent(NodeInterface $ofNode): NodeOrderedInterface
    {
        // Implementation would be similar to moveAsParentOf but with ordering
        throw new \BadMethodCallException('Method not yet implemented');
    }

    public function moveBefore(NodeInterface $node): NodeOrderedInterface
    {
        // Implementation would move this node to position just before the given node
        throw new \BadMethodCallException('Method not yet implemented');
    }

    public function moveAfter(NodeInterface $node): NodeOrderedInterface
    {
        // Implementation would move this node to position just after the given node
        throw new \BadMethodCallException('Method not yet implemented');
    }

    public function moveAsFirstSibling(NodeInterface $ofNode): NodeOrderedInterface
    {
        // Implementation would move this node as first sibling of the given node
        throw new \BadMethodCallException('Method not yet implemented');
    }

    public function moveAsLastSibling(NodeInterface $ofNode): NodeOrderedInterface
    {
        // Implementation would move this node as last sibling of the given node
        throw new \BadMethodCallException('Method not yet implemented');
    }
}
