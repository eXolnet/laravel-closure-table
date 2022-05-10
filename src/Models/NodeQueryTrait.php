<?php

namespace Exolnet\ClosureTable\Models;

use Exolnet\ClosureTable\Contracts\NodeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait NodeQueryTrait
{
    /**
     * @param mixed $values
     * @return array
     */
    protected function buildRelatedIds($values): array
    {
        if (! is_array($values)) {
            $values = [$values];
        }

        return array_map(function ($value) {
            if ($value instanceof Model) {
                return $value->getKey();
            }

            return $value;
        }, $values);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsRoot(Builder $query): Builder
    {
        return $query->has('path', '=', 1);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsLeaf(Builder $query): Builder
    {
        return $query->has('subtree', '=', 1);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereDescendantOf(Builder $query, $values): Builder
    {
        return $query->whereHas('path', function (Builder $query) use ($values) {
            $query->whereIn($this->getClosureAncestorColumn(), $this->buildRelatedIds($values));
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereNotDescendantOf(Builder $query, $values): Builder
    {
        return $query->whereHas('path', function (Builder $query) use ($values) {
            $query->whereIn($this->getClosureAncestorColumn(), $this->buildRelatedIds($values));
        }, '=', 0);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereAncestorOf(Builder $query, $values): Builder
    {
        return $query->whereHas('subtree', function (Builder $query) use ($values) {
            $query->whereIn($this->getClosureDescendantColumn(), $this->buildRelatedIds($values));
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereNotAncestorOf(Builder $query, $values): Builder
    {
        return $query->whereHas('subtree', function (Builder $query) use ($values) {
            $query->whereIn($this->getClosureDescendantColumn(), $this->buildRelatedIds($values));
        }, '=', 0);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWherePartOf(Builder $query, $values): Builder
    {
        $values = $this->buildRelatedIds($values);

        return $query->where(function (Builder $query) use ($values) {
            $query->where(function (Builder $query) use ($values) {
                $query->whereDescendantOf($values);
            })->orWhere(function (Builder $query) use ($values) {
                $query->whereAncestorOf($values);
            });
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereNotPartOf(Builder $query, $values): Builder
    {
        $values = $this->buildRelatedIds($values);

        return $query->where(function (Builder $query) use ($values) {
            $query->where(function (Builder $query) use ($values) {
                $query->whereNotDescendantOf($values);
            })->where(function (Builder $query) use ($values) {
                $query->whereNotAncestorOf($values);
            });
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutRoot(Builder $query): Builder
    {
        return $query->whereHas('path', function (Builder $query) {
            return $query->where($this->getClosureDepthColumn(), '!=', 0);
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $relation
     * @param $column
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     * @param string $operator
     * @param int $count
     */
    public function scopeHasTreeRelationOf(
        Builder $query,
        $relation,
        $column,
        NodeInterface $node,
        string $operator = '>=',
        int $count = 1
    ): void {
        $column_getter = 'getClosure' . ucfirst($column) . 'Column';
        $column = $this->$column_getter();

        $query->whereHas($relation, function (Builder $query) use ($column, $node) {
            $query->where($column, '=', $node->getKey());
        }, $operator, $count);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     */
    public function scopeWithSubtreeOf(Builder $query, NodeInterface $node)
    {
        $this->scopeHasTreeRelationOf($query, 'path', 'ancestor', $node, '>=');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     */
    public function scopeWithDescendantOf(Builder $query, NodeInterface $node)
    {
        $this->scopeHasTreeRelationOf($query, 'ancestors', 'ancestor', $node, '>=');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     */
    public function scopeWithPathOf(Builder $query, NodeInterface $node)
    {
        $this->scopeHasTreeRelationOf($query, 'subtree', 'descendant', $node, '>=');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     */
    public function scopeWithAncestorOf(Builder $query, NodeInterface $node)
    {
        $this->scopeHasTreeRelationOf($query, 'descendants', 'descendant', $node, '>=');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     */
    public function scopeWithChildrenOf(Builder $query, NodeInterface $node)
    {
        $this->scopeHasTreeRelationOf($query, 'parent', 'ancestor', $node, '>=');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     */
    public function scopeWithoutSubtreeOf(Builder $query, NodeInterface $node)
    {
        $this->scopeHasTreeRelationOf($query, 'path', 'ancestor', $node, '<');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     */
    public function scopeWithoutDescendantOf(Builder $query, NodeInterface $node)
    {
        $this->scopeHasTreeRelationOf($query, 'ancestors', 'ancestor', $node, '<');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     */
    public function scopeWithoutPathOf(Builder $query, NodeInterface $node)
    {
        $this->scopeHasTreeRelationOf($query, 'subtree', 'descendant', $node, '<');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     */
    public function scopeWithoutAncestorOf(Builder $query, NodeInterface $node)
    {
        $this->scopeHasTreeRelationOf($query, 'descendants', 'descendant', $node, '<');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Exolnet\ClosureTable\Contracts\NodeInterface $node
     */
    public function scopeWithoutChildrenOf(Builder $query, NodeInterface $node)
    {
        $this->scopeHasTreeRelationOf($query, 'parent', 'ancestor', $node, '<');
    }
}
