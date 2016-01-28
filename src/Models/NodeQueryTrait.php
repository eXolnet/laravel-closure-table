<?php namespace Exolnet\ClosureTable\Models;

trait NodeQueryTrait
{
	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWhereIsRoot($query)
	{
		return $query->has('path', '=', 1);
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeIsLeaf($query)
	{
		return $query->has('subtree', '=', 1);
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param mixed $values
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWhereDescendantOf($query, $values)
	{
		if ( ! is_array($values)) {
			$values = [$values];
		}

		return $query->whereHas('path', function ($query) use ($values) {
			$query->whereIn($this->getClosureAncestorColumn(), $values);
		});
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param mixed $values
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWhereNotDescendantOf($query, $values)
	{
		if ( ! is_array($values)) {
			$values = [$values];
		}

		return $query->whereHas('path', function ($query) use ($values) {
			$query->whereIn($this->getClosureAncestorColumn(), $values);
		}, '=', 0);
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param mixed $values
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWhereAncestorOf($query, $values)
	{
		if ( ! is_array($values)) {
			$values = [$values];
		}

		return $query->whereHas('subtree', function ($query) use ($values) {
			$query->whereIn($this->getClosureDescendantColumn(), $values);
		});
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param mixed $values
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWhereNotAncestorOf($query, $values)
	{
		if ( ! is_array($values)) {
			$values = [$values];
		}

		return $query->whereHas('subtree', function ($query) use ($values) {
			$query->whereIn($this->getClosureDescendantColumn(), $values);
		}, '=', 0);
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param mixed $values
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWherePartOf($query, $values)
	{
		if ( ! is_array($values)) {
			$values = [$values];
		}

		return $query->where(function($query) use ($values)  {
			$query->where(function ($query) use ($values) {
				$query->whereDescendantOf($values);
			})->orWhere(function ($query) use ($values) {
				$query->whereAncestorOf($values);
			});
		});
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param mixed $values
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWhereNotPartOf($query, $values)
	{
		if ( ! is_array($values)) {
			$values = [$values];
		}

		return $query->where(function($query) use ($values)  {
			$query->where(function ($query) use ($values) {
				$query->whereNotDescendantOf($values);
			})->where(function ($query) use ($values) {
				$query->whereNotAncestorOf($values);
			});
		});
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWithoutRoot($query)
	{
		return $query->whereHas('path', function($query) {
			return $query->where($this->getClosureDepthColumn(), '!=', 0);
		});
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param $relation
	 * @param $column
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 * @param string $operator
	 * @param int $count
	 */
	public function scopeHasTreeRelationOf($query, $relation, $column,
	                                       NodeInterface $node, $operator = '>=', $count = 1)
	{
		$column_getter = 'getClosure'.ucfirst($column).'Column';
		$column = $this->$column_getter();

		$query->whereHas($relation, function($query) use ($column, $node) {
			$query->where($column, '=', $node->getKey());
		}, $operator, $count);
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 */
	public function scopeWithSubtreeOf($query, NodeInterface $node)
	{
		$this->scopeHasTreeRelationOf($query, 'path', 'ancestor', $node, '>=');
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 */
	public function scopeWithDescendantOf($query, NodeInterface $node)
	{
		$this->scopeHasTreeRelationOf($query, 'ancestors', 'ancestor', $node, '>=');
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 */
	public function scopeWithPathOf($query, NodeInterface $node)
	{
		$this->scopeHasTreeRelationOf($query, 'subtree', 'descendant', $node, '>=');
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 */
	public function scopeWithAncestorOf($query, NodeInterface $node)
	{
		$this->scopeHasTreeRelationOf($query, 'descendants', 'descendant', $node, '>=');
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 */
	public function scopeWithChildrenOf($query, NodeInterface $node)
	{
		$this->scopeHasTreeRelationOf($query, 'parent', 'ancestor', $node, '>=');
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 */
	public function scopeWithoutSubtreeOf($query, NodeInterface $node)
	{
		$this->scopeHasTreeRelationOf($query, 'path', 'ancestor', $node, '<');
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 */
	public function scopeWithoutDescendantOf($query, NodeInterface $node)
	{
		$this->scopeHasTreeRelationOf($query, 'ancestors', 'ancestor', $node, '<');
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 */
	public function scopeWithoutPathOf($query, NodeInterface $node)
	{
		$this->scopeHasTreeRelationOf($query, 'subtree', 'descendant', $node, '<');
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 */
	public function scopeWithoutAncestorOf($query, NodeInterface $node)
	{
		$this->scopeHasTreeRelationOf($query, 'descendants', 'descendant', $node, '<');
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param \Exolnet\ClosureTable\Models\NodeInterface $node
	 */
	public function scopeWithoutChildrenOf($query, NodeInterface $node)
	{
		$this->scopeHasTreeRelationOf($query, 'parent', 'ancestor', $node, '<');
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param string $alias
	 */
	public function scopeSelectBreadcrumb($query, $alias = 'breadcrumb')
	{
		# code...
	}
}
