<?php namespace Exolnet\ClosureTable\Models;

interface NodeInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getDepth();

    /**
     * @return bool
     */
    public function isParent();

    /**
     * @return bool
     */
    public function isChild();

    /**
     * @return bool
     */
    public function isRoot();

    /**
     * @return bool
     */
    public function isLeaf();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function path();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPath();

    /**
     * @return int
     */
    public function countPath();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ancestors();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestors();

    /**
     * @return int
     */
    public function countAncestors();

    /**
     * @return bool
     */
    public function hasAncestors();

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Exolnet\ClosureTable\Models\NodeInterface
     */
    public function getParent();

    /**
     * @return bool
     */
    public function hasParent();

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Exolnet\ClosureTable\Models\NodeInterface
     */
    public function getRoot();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function descendants();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendants();

    /**
     * @return int
     */
    public function countDescendants();

    /**
     * @return bool
     */
    public function hasDescendants();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function children();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildren();

    /**
     * @return int
     */
    public function countChildren();

    /**
     * @return bool
     */
    public function hasChildren();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function siblings();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiblings();

    /**
     * @return int
     */
    public function countSiblings();

    /**
     * @return bool
     */
    public function hasSiblings();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function neighbourhood();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNeighbourhood();

    /**
     * @return int
     */
    public function countNeighbourhood();

    /**
     * @return bool
     */
    public function hasNeighbourhood();

    /**
     * @return bool
     */
    public function delete();

    /**
     * @return bool
     */
    public function deleteSubtree();

    /**
     * @return bool
     */
    public function deleteDescendants();

    /**
     * @return bool
     */
    public function deleteKeepDescendants();

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function roots();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRoots();

    /**
     * @return int
     */
    public static function countRoots();

    /**
     * @return bool
     */
    public static function hasRoots();

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function leaves();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLeaves();

    /**
     * @return int
     */
    public static function countLeaves();

    /**
     * @return bool
     */
    public static function hasLeaves();
}
