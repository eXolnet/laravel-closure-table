<?php

namespace Exolnet\ClosureTable\Contracts;

interface NodeUnorderedInterface extends NodeInterface
{
    /**
     * @return $this
     */
    public function makeRoot();

    /**
     * @return $this
     */
    public function extractChildren();

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsParentOf(NodeInterface $ofNode);

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsChildOf(NodeInterface $ofNode);

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsSiblingOf(NodeInterface $ofNode);

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofNode
     * @return $this
     */
    public function moveAsRootOf(NodeInterface $ofNode);

    /**
     * @return $this
     */
    public function pullUp();

    /**
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $ofChild
     * @return $this
     */
    public function pushDown(NodeInterface $ofChild);
}
