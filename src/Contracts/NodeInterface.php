<?php

namespace Exolnet\ClosureTable\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface NodeInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return int
     */
    public function getDepth(): int;

    /**
     * @return bool
     */
    public function isParent(): bool;

    /**
     * @return bool
     */
    public function isChild(): bool;

    /**
     * @return bool
     */
    public function isRoot(): bool;

    /**
     * @return bool
     */
    public function isLeaf(): bool;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function path(): BelongsToMany;

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPath(): Collection;

    /**
     * @return int
     */
    public function countPath(): int;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ancestors(): BelongsToMany;

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestors(): Collection;

    /**
     * @return int
     */
    public function countAncestors(): int;

    /**
     * @return bool
     */
    public function hasAncestors(): bool;

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Exolnet\ClosureTable\Contracts\NodeInterface
     */
    public function getParent();

    /**
     * @return bool
     */
    public function hasParent(): bool;

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Exolnet\ClosureTable\Contracts\NodeInterface
     */
    public function getRoot();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function descendants(): BelongsToMany;

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendants(): Collection;

    /**
     * @return int
     */
    public function countDescendants(): int;

    /**
     * @return bool
     */
    public function hasDescendants(): bool;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function children(): BelongsToMany;

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildren(): Collection;

    /**
     * @return int
     */
    public function countChildren(): int;

    /**
     * @return bool
     */
    public function hasChildren(): bool;

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function siblings();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiblings(): Collection;

    /**
     * @return int
     */
    public function countSiblings(): int;

    /**
     * @return bool
     */
    public function hasSiblings(): bool;

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function neighbourhood();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNeighbourhood(): Collection;

    /**
     * @return int
     */
    public function countNeighbourhood(): int;

    /**
     * @return bool
     */
    public function hasNeighbourhood(): bool;

    /**
     * @return bool
     */
    public function delete(): bool;

    /**
     * @return bool
     */
    public function deleteSubtree(): bool;

    /**
     * @return bool
     */
    public function deleteDescendants(): bool;

    /**
     * @return bool
     */
    public function deleteKeepDescendants(): bool;

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function roots(): Builder;

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRoots(): Collection;

    /**
     * @return int
     */
    public static function countRoots(): int;

    /**
     * @return bool
     */
    public static function hasRoots(): bool;

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function leaves(): Builder;

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLeaves(): Collection;

    /**
     * @return int
     */
    public static function countLeaves(): int;

    /**
     * @return bool
     */
    public static function hasLeaves(): bool;
}
