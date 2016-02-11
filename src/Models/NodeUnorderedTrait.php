<?php namespace Exolnet\ClosureTable\Models;

use Exolnet\ClosureTable\Exceptions\ClosureTableException;
use Exolnet\ClosureTable\Exceptions\MoveNotPossibleException;
use Exolnet\ClosureTable\Exceptions\SameNodeException;

trait NodeUnorderedTrait {
	/**
	 * Remove all relations from the node and it's descendants with the
	 * ancestors to make it a root node.
	 *
	 * @return $this
	 * @todo   Could optimize queries (three queries into one).
	 */
	public function makeRoot()
	{
		if ( ! $this->exists) {
			throw new MoveNotPossibleException;
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
	 * @return $this
	 */
	public function insertAsRoot()
	{
		if ( ! $this->save()) {
			throw new ClosureTableException('Unable to save to node.');
		}

		return $this->makeRoot();
	}

	/**
	 * Move a node as a child of a given node. Basicly, this method act in two
	 * steps. Firstly, it makes the node root to remove all relation with the
	 * ancestors. Secondly, it create all relations from the new ancestors with
	 * the node and its descendants.
	 *
	 * @param  \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsChildOf(NodeInterface $ofNode)
	{
		if ( ! $this->exists || ! $ofNode->exists) {
			throw new MoveNotPossibleException;
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

		\DB::statement($query);

		return $this;
	}

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $nodeA
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $nodeB
	 */
	protected function assertIsDifferentNode(NodeInterface $nodeA, NodeInterface $nodeB)
	{
		if ($nodeA->getId() === $nodeB->getId()) {
			throw new SameNodeException('Node A(id='.$nodeA->getId().') and B(id='.$nodeB->getId().') must be different (they are the same at this point).');
		}
	}

	/**
	 * @param callable $callback_name
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	protected function insertAs($callback_name, NodeInterface $ofNode)
	{
		if ( ! $this->save()) {
			return false;
		}

		$this->$callback_name($ofNode);

		return $this;
	}

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function insertAsChildOf(NodeInterface $ofNode)
	{
		return $this->insertAs('moveAsChildOf', $ofNode);
	}

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsParentOf(NodeInterface $ofNode)
	{
		$this->moveAsSiblingOf($ofNode);
		return $ofNode->moveAsChild($this);
	}

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function insertAsParentOf(NodeInterface $ofNode)
	{
		return $this->insertAs('moveAsParentOf', $ofNode);
	}

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsSiblingOf(NodeInterface $ofNode)
	{
		$parent = $ofNode->getParent();

		if ($parent === null) {
			$this->makeRoot();
		}

		return $this->moveAsChildOf($parent);
	}

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function insertAsSiblingOf(NodeInterface $ofNode)
	{
		return $this->insertAs('moveAsSiblingOf', $ofNode);
	}

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsRootOf(NodeInterface $ofNode)
	{
		$root = $ofNode->getRoot();

		if ($root === null) {
			throw new MoveNotPossibleException();
		}

		return $root->moveAsParentOf($this);
	}

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function insertAsRootOf(NodeInterface $ofNode)
	{
		return $this->insertAs('moveAsRootOf', $ofNode);
	}

	/**
	 * @return $this
	 */
	public function pullUp()
	{
		$parent = $this->getParent();

		if ($parent === null) {
			throw new MoveNotPossibleException();
		}

		return $this->moveAsParentOf($parent);
	}

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofChild
	 * return $this
	 */
	public function pushDown(NodeInterface $ofChild)
	{
		return $ofChild->moveAsParentOf($this);
	}

	/**
	 * @return $this
	 */
	public function extractChildren()
	{
		foreach ($this->getChildren() as $child) {
			$child->moveAsSiblingOf($this);
		}

		return $this;
	}
}
