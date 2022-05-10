<?php

namespace Exolnet\ClosureTable\Tests\Mocks;

use Exolnet\ClosureTable\Models\NodeUnordered;

class NodeMock extends NodeUnordered
{
    protected string $closure_table = 'node_mock_closures';
}
