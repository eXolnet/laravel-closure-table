<?php

namespace Exolnet\ClosureTable\Contracts;

interface NodeOrderedInterface extends NodeInterface
{
    /**
     * @return $this
     */
    public function makeFirstRoot(): NodeOrderedInterface;

    /**
     * @return $this
     */
    public function makeLastRoot(): NodeOrderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsParent(NodeInterface $ofNode): NodeOrderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsFirstChild(NodeInterface $ofNode): NodeOrderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsLastChild(NodeInterface $ofNode): NodeOrderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     * @return $this
     */
    public function moveBefore(NodeInterface $node): NodeOrderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     * @return $this
     */
    public function moveAfter(NodeInterface $node): NodeOrderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsFirstSibling(NodeInterface $ofNode): NodeOrderedInterface;

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsLastSibling(NodeInterface $ofNode): NodeOrderedInterface;
}
