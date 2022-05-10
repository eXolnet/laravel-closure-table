<?php

namespace Exolnet\ClosureTable\Contracts;

interface NodeUnorderedInterface extends NodeInterface
{
    /**
     * @return $this
     */
    public function makeRoot(): ?NodeUnorderedInterface;

    /**
     * @return $this
     */
    public function extractChildren(): NodeUnorderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeUnorderedInterface $ofNode
     * @return $this
     */
    public function moveAsParentOf(NodeUnorderedInterface $ofNode): NodeUnorderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeUnorderedInterface $ofNode
     * @return $this
     */
    public function moveAsChildOf(NodeUnorderedInterface $ofNode): NodeUnorderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeUnorderedInterface $ofNode
     * @return $this
     */
    public function moveAsSiblingOf(NodeUnorderedInterface $ofNode): NodeUnorderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsRootOf(NodeInterface $ofNode): NodeUnorderedInterface;

    /**
     * @return $this
     */
    public function pullUp(): NodeUnorderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofChild
     * @return $this
     */
    public function pushDown(NodeInterface $ofChild): NodeUnorderedInterface;
}
