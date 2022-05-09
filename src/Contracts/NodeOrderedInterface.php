<?php

namespace Exolnet\ClosureTable\Contracts;

interface NodeOrderedInterface extends NodeInterface
{
    /**
     * @return $this
     */
    public function makeFirstRoot();

    /**
     * @return $this
     */
    public function makeLastRoot();

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsParent(NodeInterface $ofNode);

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsFirstChild(NodeInterface $ofNode);

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsLastChild(NodeInterface $ofNode);

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     * @return $this
     */
    public function moveBefore(NodeInterface $node);

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     * @return $this
     */
    public function moveAfter(NodeInterface $node);

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsFirstSibling(NodeInterface $ofNode);

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsLastSibling(NodeInterface $ofNode);
}
