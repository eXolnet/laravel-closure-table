<?php

namespace Exolnet\ClosureTable\Models;

use Exolnet\ClosureTable\Exceptions\DeleteNotPossibleException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\Exolnet\ClosureTable\Models\NodeTrait[] $children
 */
trait NodeTrait
{
    /**
     * @return string
     */
    public function getClosureModel(): string
    {
        return get_class($this);
    }

    /**
     * @return string
     */
    public function getClosureTable(): string
    {
        return $this->closure_table;
    }

    /**
     * @return string
     */
    public function getClosureAncestorColumn(): string
    {
        return $this->closure_ancestor_column;
    }

    /**
     * @return string
     */
    public function getClosureDescendantColumn(): string
    {
        return $this->closure_descendant_column;
    }

    /**
     * @return string
     */
    public function getClosureDepthColumn(): string
    {
        return $this->closure_depth_column;
    }

    //==========================================================================

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth ?: $this->countAncestors();
    }

    /**
     * @return bool
     */
    public function isParent(): bool
    {
        return $this->hasChildren();
    }

    /**
     * @return bool
     */
    public function isChild(): bool
    {
        return $this->getDepth() > 0;
    }

    /**
     * @return bool
     */
    public function isRoot(): bool
    {
        return $this->getDepth() === 0;
    }

    /**
     * @return bool
     */
    public function isLeaf(): bool
    {
        return !$this->hasChildren();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function path(): BelongsToMany
    {
        return $this->belongsToMany(
            $this->getClosureModel(),
            $this->getClosureTable(),
            $this->getClosureDescendantColumn(),
            $this->getClosureAncestorColumn()
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPath(): Collection
    {
        return $this->path;
    }

    /**
     * @return int
     */
    public function countPath(): int
    {
        return $this->getPath()->count();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subtree(): BelongsToMany
    {
        return $this->belongsToMany(
            $this->getClosureModel(),
            $this->getClosureTable(),
            $this->getClosureAncestorColumn(),
            $this->getClosureDescendantColumn()
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSubtree(): Collection
    {
        return $this->subtree;
    }

    /**
     * @return int
     */
    public function countSubtree(): int
    {
        return $this->getSubtree()->count();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ancestors(): BelongsToMany
    {
        return $this->path()->where($this->getClosureDepthColumn(), '>', 0);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestors(): Collection
    {
        return $this->ancestors;
    }

    /**
     * @return int
     */
    public function countAncestors(): int
    {
        return $this->ancestors()->count();
    }

    /**
     * @return bool
     */
    public function hasAncestors(): bool
    {
        return $this->countAncestors() > 0;
    }

    /**
     * @return $this|null
     */
    public function getParent()
    {
        if (! array_key_exists('parent', $this->relations)) {
            $this->load('parent');
        }

        return $this->relations['parent']->first();
    }

    /**
     * @return bool
     */
    public function hasParent(): bool
    {
        return $this->isChild();
    }

    /**
     * @return $this|null
     */
    public function getRoot()
    {
        return $this->ancestors()
            ->orderBy($this->getClosureDepthColumn(), 'desc')
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function descendants(): BelongsToMany
    {
        return $this->subtree()->where($this->getClosureDepthColumn(), '>', 0);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendants(): Collection
    {
        return $this->descendants;
    }

    /**
     * @return int
     */
    public function countDescendants(): int
    {
        return $this->getDescendants()->count();
    }

    /**
     * @return bool
     */
    public function hasDescendants(): bool
    {
        return $this->countDescendants() > 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function parent(): BelongsToMany
    {
        return $this->path()->where($this->getClosureDepthColumn(), '=', 1);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function children(): BelongsToMany
    {
        return $this->subtree()->where($this->getClosureDepthColumn(), '=', 1);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @return int
     */
    public function countChildren(): int
    {
        return $this->getChildren()->count();
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->countChildren() > 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function neighbourhood()
    {
        $parent = $this->getParent();

        if ($parent === null) {
            return $this->newQuery();
        }

        return $parent->descendants();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNeighbourhood(): Collection
    {
        return $this->neighbourhood()->get();
    }

    /**
     * @return int
     */
    public function countNeighbourhood(): int
    {
        return $this->getNeighbourhood()->count();
    }

    /**
     * @return bool
     */
    public function hasNeighbourhood(): bool
    {
        return $this->countNeighbourhood() > 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function siblings()
    {
        $parent = $this->getParent();

        $query = $parent === null ? $this->roots() : $parent->children();

        return $query
            ->where($this->getQualifiedKeyName(), '!=', $this->getKey());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiblings(): Collection
    {
        return $this->siblings()->get();
    }

    /**
     * @return int
     */
    public function countSiblings(): int
    {
        return $this->getSiblings()->count();
    }

    /**
     * @return bool
     */
    public function hasSiblings(): bool
    {
        return $this->countSiblings() > 0;
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        if ($this->hasChildren()) {
            throw new DeleteNotPossibleException();
        }

        return parent::delete();
    }

    /**
     * @return bool
     */
    public function deleteKeepDescendants(): bool
    {
        $this->extractChildren();

        return $this->delete();
    }

    /**
     * @return bool
     */
    public function deleteSubtree(): bool
    {
        $ids = $this->getSubtree()->modelKeys();
        $this->newQuery()->whereIn('id', $ids)->delete();

        return true;
    }

    /**
     * @return bool
     */
    public function deleteDescendants(): bool
    {
        $ids = $this->getDescendants()->modelKeys();
        $this->newQuery()->whereIn('id', $ids)->delete();

        return true;
    }

    /**
     * @return void
     */
    protected function insertNode()
    {
        $id = $this->getKey();

        $this->descendants()->sync([$id]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function roots(): Builder
    {
        $instance = new static();

        return $instance->newQuery()->whereIsRoot();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRoots(): Collection
    {
        return static::roots()->get();
    }

    /**
     * @return int
     */
    public static function countRoots(): int
    {
        return static::roots()->count();
    }

    /**
     * @return bool
     */
    public static function hasRoots(): bool
    {
        return static::countRoots() > 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function leaves(): Builder
    {
        $instance = new static();

        return $instance->newQuery()->isLeaf();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLeaves(): Collection
    {
        return static::leaves()->get();
    }

    /**
     * @return int
     */
    public static function countLeaves(): int
    {
        return static::leaves()->count();
    }

    /**
     * @return bool
     */
    public static function hasLeaves(): bool
    {
        return static::countLeaves() > 0;
    }
}
