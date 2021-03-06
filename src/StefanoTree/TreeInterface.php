<?php
namespace StefanoTree;

use StefanoTree\Exception\RootNodeAlreadyExistException;

interface TreeInterface
{
    const PLACEMENT_TOP = 'top';
    const PLACEMENT_BOTTOM = 'bottom';
    const PLACEMENT_CHILD_TOP = 'childTop';
    const PLACEMENT_CHILD_BOTTOM = 'childBottom';

    /**
     * @throws RootNodeAlreadyExistException if root already exist
     * @param array $data
     * @param null|string|int $scope Required if scope is used
     * @return int Id of new created root
     */
    public function createRootNode($data = array(), $scope = null);

    /**
     * @param null|string|int $scope Required if scope is used
     * @return array
     */
    public function getRootNode($scope = null);

    /**
     * @return array
     */
    public function getRoots();

    /**
     * @param int $nodeId
     * @param array $data
     */
    public function updateNode($nodeId, $data);

    /**
     * @param int $targetNodeId
     * @param array $data
     * @return int|false Id of new created node. False if node has not been created
     */
    public function addNodePlacementBottom($targetNodeId, $data = array());

    /**
     * @param int $targetNodeId
     * @param array $data
     * @return int|false Id of new created node. False if node has not been created
     */
    public function addNodePlacementTop($targetNodeId, $data = array());

    /**
     * @param int $targetNodeId
     * @param array $data
     * @return int|false Id of new created node. False if node has not been created
     */
    public function addNodePlacementChildBottom($targetNodeId, $data = array());

    /**
     * @param int $targetNodeId
     * @param array $data
     * @return int|false Id of new created node. False if node has not been created
     */
    public function addNodePlacementChildTop($targetNodeId, $data = array());

    /**
     * @param int $sourceNodeId
     * @param int $targetNodeId
     * @return boolean
     */
    public function moveNodePlacementBottom($sourceNodeId, $targetNodeId);

    /**
     * @param int $sourceNodeId
     * @param int $targetNodeId
     * @return boolean
     */
    public function moveNodePlacementTop($sourceNodeId, $targetNodeId);

    /**
     * @param int $sourceNodeId
     * @param int $targetNodeId
     * @return boolean
     */
    public function moveNodePlacementChildBottom($sourceNodeId, $targetNodeId);

    /**
     * @param int $sourceNodeId
     * @param int $targetNodeId
     * @return boolean
     */
    public function moveNodePlacementChildTop($sourceNodeId, $targetNodeId);

    /**
     * @param int $nodeId
     * @return boolean
     */
    public function deleteBranch($nodeId);

    /**
     * @param int $nodeId
     * @param int $startLevel 0 = including root node
     * @param boolean $excludeLastNode
     * @return array
     */
    public function getPath($nodeId, $startLevel = 0, $excludeLastNode = false);

    /**
     * @param int $nodeId
     * @return null|array
     */
    public function getNode($nodeId);

    /**
     * @param int $nodeId
     * @param int $startLevel Relative level from $nodeId. 1 = exclude $nodeId from result.
     *                        2 = exclude 2 levels from result
     * @param null|int $levels Number of levels in the results relative to $startLevel
     * @param null|int $excludeBranch Exclude defined branch(node id) from result
     * @return array
     */
    public function getDescendants($nodeId = 1, $startLevel = 0, $levels = null, $excludeBranch = null);

    /**
     * @param int $nodeId
     * @return array
     */
    public function getChildren($nodeId);

    /**
     * Check if left index, right index, level is in consistent state
     *
     * @param $rootNodeId int
     * @return bool
     */
    public function isValid($rootNodeId);

    /**
     * Repair broken tree. Works only if [id, parent_id] pair is not broken
     *
     * @param $rootNodeId int
     * @return void
     */
    public function rebuild($rootNodeId);
}
