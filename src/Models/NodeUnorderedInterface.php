<?php namespace Exolnet\ClosureTable\Models;

interface NodeUnorderedInterface extends NodeInterface {
	/**
	 * @return $this
	 */
	public function makeRoot();

	/**
	 * @return $this
	 */
	public function extractChildren();

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsParentOf(NodeInterface $ofNode);

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsChildOf(NodeInterface $ofNode);

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsSiblingOf(NodeInterface $ofNode);

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofNode
	 * @return $this
	 */
	public function moveAsRootOf(NodeInterface $ofNode);

	/**
	 * @return $this
	 */
	public function pullUp();

	/**
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $ofChild
	 * @return $this
	 */
	public function pushDown(NodeInterface $ofChild);
}
