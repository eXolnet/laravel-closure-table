<?php

namespace Exolnet\ClosureTable\Models;

use Exolnet\ClosureTable\Contracts\NodeOrderedInterface;
use Illuminate\Database\Eloquent\Model;

abstract class NodeOrdered extends Model implements NodeOrderedInterface
{
    use NodeTrait;
    use NodeQueryTrait;
    use NodeOrderedTrait;

    /**
     * The table use for the closure relation.
     *
     * @var string
     */
    protected string $closure_table;

    /**
     * The ancestor column use for the closure relation.
     *
     * @var string
     */
    protected string $closure_ancestor_column = 'ancestor_id';

    /**
     * The descendant column use for the closure relation.
     *
     * @var string
     */
    protected string $closure_descendant_column = 'descendant_id';

    /**
     * The depth column use for the closure relation.
     *
     * @var string
     */
    protected string $closure_depth_column = 'depth';

    /**
     * The position column for ordering.
     *
     * @var string
     */
    protected string $position_column = 'position';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();

        // When entity is created, the appropriate
        // data will be put into the closure table.
        static::created(function (NodeOrdered $node) {
            $node->insertNode();
        });
    }
}
