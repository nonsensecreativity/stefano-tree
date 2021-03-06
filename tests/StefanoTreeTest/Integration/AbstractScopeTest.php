<?php
namespace StefanoTreeTest\Integration;

use StefanoTree\NestedSet as TreeAdapter;
use StefanoTreeTest\IntegrationTestCase;

abstract class AbstractScopeTest
    extends IntegrationTestCase
{
    /**
     * @var TreeAdapter
     */
    protected $treeAdapter;

    protected function setUp()
    {
        $this->treeAdapter = $this->getTreeAdapter();

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->treeAdapter = null;
        parent::tearDown();
    }

    /**
     * @return TreeAdapter
     */
    abstract protected function getTreeAdapter();

    protected function getDataSet()
    {
        switch ($this->getName()) {
            case 'testValidateTreeRaiseExceptionIfIdParentIdIsBroken':
                return $this->createMySQLXMLDataSet(__DIR__ . '/_files/NestedSet/with_scope/initDataSetBrokenParents.xml');
            case 'testInvalidTree':
            case 'testRebuildTree':
                return $this->createMySQLXMLDataSet(__DIR__ . '/_files/NestedSet/with_scope/initDataSetBrokenTreeIndexes.xml');
            default:
                return $this->createMySQLXMLDataSet(__DIR__ . '/_files/NestedSet/with_scope/initDataSet.xml');
        }
    }

    public function testCreateRoot()
    {
        $this->treeAdapter
             ->createRootNode(array(), 10);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal_with_scope'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__ . '/_files/NestedSet/with_scope/testCreateRoot.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testCreateRootRootWithSomeScopeAlreadyExist()
    {
        $this->expectException('\StefanoTree\Exception\RootNodeAlreadyExistException');
        $this->expectExceptionMessage('Root node for scope "123" already exist');

        $this->treeAdapter
            ->createRootNode(array(), 123);
        $this->treeAdapter
            ->createRootNode(array(), 123);
    }

    public function testGetRoots()
    {
        $expected = array(
            array(
                'tree_traversal_id' => 1,
                'name' => null,
                'lft' => 1,
                'rgt' => 10,
                'parent_id' => 0,
                'level' => 0,
                'scope' => 2,
            ),
            array(
                'tree_traversal_id' => 6,
                'name' => null,
                'lft' => 1,
                'rgt' => 6,
                'parent_id' => 0,
                'level' => 0,
                'scope' => 1,
            ),
        );

        $roots = $this->treeAdapter
                      ->getRoots();

        $this->assertEquals($expected, $roots);
    }

    public function testAddNodePlacementChildTop()
    {
        $lastGeneratedValue = $this->treeAdapter
            ->addNodePlacementChildTop(1);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal_with_scope'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__ . '/_files/NestedSet/with_scope/testAddNodePlacementChildTop.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
        $this->assertEquals(9, $lastGeneratedValue);
    }

    public function testMoveNodePlacementBottom()
    {
        $this->treeAdapter
             ->moveNodePlacementBottom(3, 5);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal_with_scope'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__ . '/_files/NestedSet/with_scope/testMoveNodePlacementBottom.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testCannotMoveNodeBetweenScopes()
    {
        $this->expectException('\StefanoTree\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Cannot move node between scopes');

        $this->treeAdapter
             ->moveNodePlacementChildBottom(4, 8);
    }

    public function testDeleteBranch()
    {
        $this->treeAdapter
            ->deleteBranch(2);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal_with_scope'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__ . '/_files/NestedSet/with_scope/testDeleteBranch.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testGetDescendants()
    {
        $expectedNodeData = array(
            array(
                'tree_traversal_id' => '2',
                'name' => null,
                'lft' => '2',
                'rgt' => '9',
                'parent_id' => '1',
                'level' => '1',
                'scope' => '2',
            ),
            array(
                'tree_traversal_id' => '3',
                'name' => null,
                'lft' => '3',
                'rgt' => '4',
                'parent_id' => '2',
                'level' => '2',
                'scope' => '2',
            ),
            array(
                'tree_traversal_id' => '4',
                'name' => null,
                'lft' => '5',
                'rgt' => '6',
                'parent_id' => '2',
                'level' => '2',
                'scope' => '2',
            ),
            array(
                'tree_traversal_id' => '5',
                'name' => null,
                'lft' => '7',
                'rgt' => '8',
                'parent_id' => '2',
                'level' => '2',
                'scope' => '2',
            ),
        );

        $nodeData = $this->treeAdapter
                       ->getDescendants(2);
        $this->assertEquals($expectedNodeData, $nodeData);
    }

    public function testGetPath()
    {
        $expectedNodeData = array(
            array(
                'tree_traversal_id' => '1',
                'name' => null,
                'lft' => '1',
                'rgt' => '10',
                'parent_id' => NULL,
                'level' => '0',
                'scope' => '2',
            ),
            array(
                'tree_traversal_id' => '2',
                'name' => null,
                'lft' => '2',
                'rgt' => '9',
                'parent_id' => '1',
                'level' => '1',
                'scope' => '2',
            ),
            array(
                'tree_traversal_id' => '5',
                'name' => null,
                'lft' => '7',
                'rgt' => '8',
                'parent_id' => '2',
                'level' => '2',
                'scope' => '2',
            ),
        );

        $nodeData = $this->treeAdapter
            ->getPath(5);
        $this->assertEquals($expectedNodeData, $nodeData);
    }

    public function testUpdateCannotCorruptTreeStructure()
    {
        $excepted = array(
            'tree_traversal_id' => 4,
            'name' => 'updated',
            'lft' => 5,
            'rgt' => 6,
            'parent_id' => 2,
            'level' => 2,
            'scope' => 2,
        );

        $data = array(
            'tree_traversal_id' => 'corrupt data',
            'name' => 'updated',
            'lft' => 'corrupt data',
            'rgt' => 'corrupt data',
            'parent_id' => 'corrupt data',
            'level' => 'corrupt data',
            'scope' => 'corrupt data',
        );
        $this->treeAdapter
             ->updateNode(4, $data);

        $this->assertEquals($excepted, $this->treeAdapter->getNode(4));
    }

    public function testIsTreeValid()
    {
        $this->assertTrue($this->treeAdapter->isValid(1));
    }

    public function testInvalidTree()
    {
        $this->assertFalse($this->treeAdapter->isValid(1));
    }

    public function testValidateTreeGivenNodeIdIsNotRoot()
    {
        $this->expectException('\StefanoTree\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Given node id "2" is not root id');

        $this->treeAdapter->isValid(2);
    }

    public function testRebuildTree()
    {
        $this->treeAdapter
             ->rebuild(1);

        $dataSet = $this->getConnection()->createDataSet(array('tree_traversal_with_scope'));
        $expectedDataSet = $this->createMySQLXMLDataSet(__DIR__ . '/_files/NestedSet/with_scope/testRebuildTree.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testRebuildTreeGivenNodeIdIsNotRoot()
    {
        $this->expectException('\StefanoTree\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Given node id "5" is not root id');

        $this->treeAdapter->isValid(5);
    }
}
