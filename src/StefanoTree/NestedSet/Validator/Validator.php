<?php
namespace StefanoTree\NestedSet\Validator;

use Exception;
use StefanoTree\Exception\InvalidArgumentException;
use StefanoTree\Exception\TreeIsBrokenException;
use StefanoTree\NestedSet\Adapter\AdapterInterface;
use StefanoTree\NestedSet\NodeInfo;

class Validator
    implements ValidatorInterface
{
    private $adapter = null;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return AdapterInterface
     */
    private function _getAdapter()
    {
        return $this->adapter;
    }

    public function isValid($rootNodeId)
    {
        $adapter = $this->_getAdapter();

        $adapter->beginTransaction();
        try {
            $adapter->lockTree();

            $rootNodeInfo = $this->_getAdapter()->getNodeInfo($rootNodeId);

            $this->_checkIfNodeIsRootNode($rootNodeInfo);
            $this->_rebuild($rootNodeInfo, True);

            $adapter->commitTransaction();
        } catch (TreeIsBrokenException $e) {
            $adapter->rollbackTransaction();
            return False;
        } catch (Exception $e) {
            $adapter->rollbackTransaction();
            throw $e;
        }

        return True;
    }


    public function rebuild($rootNodeId)
    {
        $adapter = $this->_getAdapter();

        $adapter->beginTransaction();
        try {
            $adapter->lockTree();

            $rootNodeInfo = $this->_getAdapter()->getNodeInfo($rootNodeId);

            $this->_checkIfNodeIsRootNode($rootNodeInfo);
            $this->_rebuild($rootNodeInfo);

            $adapter->commitTransaction();
        } catch (Exception $e) {
            $adapter->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * @param NodeInfo $parentNodeInfo
     * @param bool $onlyValidate
     * @param int $left
     * @param int $level
     * @return int|mixed
     * @throws TreeIsBrokenException if tree is broken and $onlyValidate is true
     */
    private function _rebuild(NodeInfo $parentNodeInfo, $onlyValidate = false, $left = 1, $level = 0)
    {
        $adapter = $this->_getAdapter();

        $right = $left + 1;

        $children = $adapter->getChildrenNodeInfo($parentNodeInfo->getId());

        foreach ($children as $childNode) {
            $right = $this->_rebuild($childNode, $onlyValidate, $right, $level + 1);
        }

        if ($parentNodeInfo->getLeft() != $left
            || $parentNodeInfo->getRight() != $right
            || $parentNodeInfo->getLevel() != $level) {
            $parentNodeInfo->setLeft($left);
            $parentNodeInfo->setRight($right);
            $parentNodeInfo->setLevel($level);

            if ($onlyValidate) {
                throw new TreeIsBrokenException();
            } else {
                $adapter->updateNodeMetadata($parentNodeInfo);
            }
        }

        return $right + 1;
    }

    /**
     * @param NodeInfo $node
     * @throws InvalidArgumentException
     */
    private function _checkIfNodeIsRootNode(NodeInfo $node)
    {
        if (null != $node->getParentId()) {
            throw new InvalidArgumentException(
                sprintf('Given node id "%s" is not root id', $node->getId())
            );
        }
    }
}
