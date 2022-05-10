<?php

namespace Exolnet\ClosureTable\Models;

use Exolnet\ClosureTable\Contracts\NodeUnorderedInterface;
use Illuminate\Database\Eloquent\Model;

abstract class NodeUnordered extends Model implements NodeUnorderedInterface
{
    use NodeTrait;
    use NodeQueryTrait;
    use NodeUnorderedTrait;

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
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();

        // When entity is created, the appropriate
        // data will be put into the closure table.
        static::created(function (NodeUnordered $node) {
            $node->insertNode();
        });
    }
}
