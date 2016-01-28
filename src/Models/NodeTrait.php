<?php namespace Exolnet\ClosureTable\Models;

use Exolnet\ClosureTable\Exceptions\DeleteNotPossibleException;

trait NodeTrait {
	/**
	 * @return string
	 */
	public function getClosureModel()
	{
		return get_class($this);
	}

	/**
	 * @return string
	 */
	public function getClosureTable()
	{
		return $this->closure_table;
	}

	/**
	 * @return string
	 */
	public function getClosureAncestorColumn()
	{
		return $this->closure_ancestor_column;
	}

	/**
	 * @return string
	 */
	public function getClosureDescendantColumn()
	{
		return $this->closure_descendant_column;
	}

	/**
	 * @return string
	 */
	public function getClosureDepthColumn()
	{
		return $this->closure_depth_column;
	}

	//==========================================================================

	/**
	 * @return int
	 */
	public function getDepth()
	{
		return $this->depth ?: $this->countAncestors();
	}

	/**
	 * @return bool
	 */
	public function isParent()
	{
		return $this->hasChildren();
	}

	/**
	 * @return bool
	 */
	public function isChild()
	{
		return $this->getDepth() > 0;
	}

	/**
	 * @return bool
	 */
	public function isRoot()
	{
		return $this->getDepth() === 0;
	}

	/**
	 * @return bool
	 */
	public function isLeaf()
	{
		return !$this->hasChildren();
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function path()
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
	public function getPath()
	{
		return $this->path()->get();
	}

	/**
	 * @return int
	 */
	public function countPath()
	{
		return $this->path()->count();
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function subtree()
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
	public function getSubtree()
	{
		return $this->subtree()->get();
	}

	/**
	 * @return int
	 */
	public function countSubtree()
	{
		return $this->subtree()->count();
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function ancestors()
	{
		return $this->path()->where($this->getClosureDepthColumn(), '>', 0);
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getAncestors()
	{
		return $this->ancestors()->get();
	}

	/**
	 * @return int
	 */
	public function countAncestors()
	{
		return $this->ancestors()->count();
	}

	/**
	 * @return bool
	 */
	public function hasAncestors()
	{
		return $this->countAncestors() > 0;
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Model|\Exolnet\ClosureTable\Models\NodeInterface
	 */
	public function getParent()
	{
		if ( ! array_key_exists('parent', $this->relations)) {
			$this->load('parent');
		}

		return $this->relations['parent']->first();
	}

	/**
	 * @return bool
	 */
	public function hasParent()
	{
		return $this->isChild();
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Model|\Exolnet\ClosureTable\Models\NodeInterface
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
	public function descendants()
	{
		return $this->subtree()->where($this->getClosureDepthColumn(), '>', 0);
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getDescendants()
	{
		return $this->descendants()->get();
	}

	/**
	 * @return int
	 */
	public function countDescendants()
	{
		return $this->descendants()->count();
	}

	/**
	 * @return bool
	 */
	public function hasDescendants()
	{
		return $this->countDescendants() > 0;
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function parent()
	{
		return $this->path()->where($this->getClosureDepthColumn(), '=', 1);
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function children()
	{
		return $this->subtree()->where($this->getClosureDepthColumn(), '=', 1);
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getChildren()
	{
		return $this->children()->get();
	}

	/**
	 * @return int
	 */
	public function countChildren()
	{
		return $this->children()->count();
	}

	/**
	 * @return bool
	 */
	public function hasChildren()
	{
		return $this->countChildren() > 0;
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
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
	public function getNeighbourhood()
	{
		return $this->neighbourhood()->get();
	}

	/**
	 * @return int
	 */
	public function countNeighbourhood()
	{
		return $this->neighbourhood()->count();
	}

	/**
	 * @return bool
	 */
	public function hasNeighbourhood()
	{
		return $this->countNeighbourhood() > 0;
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
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
	public function getSiblings()
	{
		return $this->siblings()->get();
	}

	/**
	 * @return int
	 */
	public function countSiblings()
	{
		return $this->siblings()->count();
	}

	/**
	 * @return bool
	 */
	public function hasSiblings()
	{
		return $this->countSiblings() > 0;
	}

	/**
	 * @return bool
	 */
	public function delete()
	{
		if ($this->hasChildren()) {
			throw new DeleteNotPossibleException;
		}

		return parent::delete();
	}

	/**
	 * @return bool
	 */
	public function deleteKeepDescendants()
	{
		$this->extractChildren();

		return $this->delete();
	}

	/**
	 * @return bool
	 */
	public function deleteSubtree()
	{
		$ids = $this->getSubtree()->modelKeys();
		$this->newQuery()->whereIn('id', $ids)->delete();

		return true;
	}

	/**
	 * @return bool
	 */
	public function deleteDescendants()
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
	public static function roots()
	{
		$instance = new static;

		return $instance->newQuery()->whereIsRoot();
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public static function getRoots()
	{
		return static::roots()->get();
	}

	/**
	 * @return int
	 */
	public static function countRoots()
	{
		return static::roots()->count();
	}

	/**
	 * @return bool
	 */
	public static function hasRoots()
	{
		return static::countRoots() > 0;
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public static function leaves()
	{
		$instance = new static;

		return $instance->newQuery()->isLeaf();
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public static function getLeaves()
	{
		return static::leaves()->get();
	}

	/**
	 * @return int
	 */
	public static function countLeaves()
	{
		return static::leaves()->count();
	}

	/**
	 * @return bool
	 */
	public static function hasLeaves()
	{
		return static::countLeaves() > 0;
	}
}
