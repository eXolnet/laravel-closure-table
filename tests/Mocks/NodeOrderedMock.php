<?php

namespace Exolnet\ClosureTable\Tests\Mocks;

use Exolnet\ClosureTable\Models\NodeOrdered;

class NodeOrderedMock extends NodeOrdered
{
    protected $table = 'node_ordered_mocks';
    protected string $closure_table = 'node_ordered_mock_closures';

    protected $fillable = ['name', 'position'];
}
