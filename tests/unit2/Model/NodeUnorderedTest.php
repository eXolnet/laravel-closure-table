<?php

use Exolnet\ClosureTable\Models\NodeUnordered;

require __DIR__.'/../../mocks/NodeMock.php';

class NodeUnorderedTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \NodeMock
	 */
	protected $model;

	public function setUp()
	{
		$this->model = new NodeMock();
	}

	public function testItIsInitializable()
	{
		$this->assertInstanceOf(NodeUnordered::class, $this->model);
	}
}
