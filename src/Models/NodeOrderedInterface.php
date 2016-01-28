<?php namespace Exolnet\ClosureTable\Models;

interface NodeOrderedInterface extends NodeInterface {
	/**
	 * @return $this
	 */
	public function makeFirstRoot();

	/**
	 * @return $this
	 */
	public function makeLastRoot();

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsParent(NodeInterface $ofNode);

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsFirstChild(NodeInterface $ofNode);

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsLastChild(NodeInterface $ofNode);

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 * @return $this
	 */
	public function moveBefore(NodeInterface $node);

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 * @return $this
	 */
	public function moveAfter(NodeInterface $node);

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsFirstSibling(NodeInterface $ofNode);

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsLastSibling(NodeInterface $ofNode);
}
