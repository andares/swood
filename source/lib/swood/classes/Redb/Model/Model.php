<?php
namespace Redb\Model;
use Swood\Debug as D;

/**
 * 数据Model。
 *
 * @todo entity 数据导入到 model 时各个模型的映射支持两种模式，一种是直接写 build 方法代码处理，另一种是配置映射。后者暂未实现。
 * @todo model 被设计为存在实体数据，而entity中的update fields则不存原值备份，而是仅记录更新了哪些字段
 *
 * @author andares
 */
abstract class Model extends \Redb\Data {
    protected static $_cluster  = '';
    protected static $_name     = '';

    /**
     * 获取集群
     * @return \Redb\Cluster
     */
    public static function getCluster() {
        return \Redb\Cluster::get(static::$_cluster);
    }

    /**
     * 根据id在集群中获取数据库连接
     * @param mixed $id
     * @return \Redb\Driver\Driver
     */
    public static function getConnById($id = 0) {
        $cluster = static::getCluster();
        $conn    = $cluster->getDriver($id);
        /* @var $conn \Redb\Driver\Driver */
        return $conn;
    }

    /**
     * 获取前集群数据连接
     * @param mixed $id
     * @return bool
     */
    protected function _getPreClusterConnById($id = 0) {
        $cluster = static::getCluster()->getPreCluster();
        if (!$cluster) {
            return false;
        }

        $conn    = $cluster->getDriver($id);
        /* @var $conn \Redb\Driver\Driver */
        return $conn;
    }

    /**
     * 通过指定类型与连接名获取连接
     * @param string $driver_type
     * @param string $conn_name
     * @return \Redb\Driver\Driver
     */
    public static function getConnByName($driver_type, $conn_name) {
        return \Redb\Connections::get($driver_type, $conn_name);
    }

    /**
     * 读取单条记录
     * @param type $id
     * @param bool $force 是否强制载入
     * @return \Redb\Model\Model
     */
    public function read($id, $force = false) {
        $conn = static::getConnById($id);
        $row  = static::_read($id, $conn);

        // 非强制载入时不会二次载入
        if (!$force && $this->_data) {
            return $this;
        }

        if (!$row) {

            // 新旧集群自动迁移
            $pre_conn = $this->_getPreClusterConnById($id);
            if (!$pre_conn) {
                return null;
            }

            $row  = static::_read($id, $pre_conn);
            if (!$row) {
                return null;
            }

            // 存到新环中
            // TODO 回写新环时未删除旧环
            static::_create($id, $row, $conn);
        }

        $this->fill($row);
        return $this;
    }

    /**
     * 批量读取记录。id排序方面，这里不进行处理，entity中会对最终抛出的id进行排序。
     *
     * @todo 新旧环自动同步有等测试
     * @param array $ids
     */
    public static function readByIds(array $ids) {
        $cluster = static::getCluster();

        $it = static::_groupingFetchIds($ids, $cluster);
        $temp = [];
        foreach ($it as $model) {
            /* @var $model self */
            $temp[$model->getId()] = 1;
            yield $model;
        }

        // 旧环读取并同步
        if (count($temp) < count($ids)) {
            $pre_cluster = $cluster->getPreCluster();
            if ($pre_cluster) {
                $pre_ring_ids = [];
                foreach ($ids as $id) {
                    !isset($temp[$id]) && $pre_ring_ids[] = $id;
                }
                $it = static::_groupingFetchIds($pre_ring_ids, $pre_cluster);
                foreach ($id as $model) {
                    // 回写入新环
                    // TODO 回写新环时未删除旧环
                    $conn = static::getConnById($id);
                    static::_create($id, $model->toArray(), $conn);

                    yield $model;
                }
            }
        }
    }

    /**
     * 根据 ids 进行一致性哈希分组后，批量获取数据
     * @param array $ids
     * @param \Redb\Cluster $cluster
     */
    protected static function _groupingFetchIds(array $ids, \Redb\Cluster $cluster) {
        $grouped = [];
        foreach ($ids as $id) {
            $conn_name = $cluster->getConnName($id);
            $grouped[$conn_name][] = $id;
        }

        $class   = get_called_class();
        foreach ($grouped as $conn_name => $grouped_ids) {
           $conn = $cluster->getDriverByName($conn_name);
           $it   = static::_readByIds($grouped_ids, $conn);
           foreach ($it as $row) {
                $model = new $class();
                /* @var $model self */
                $model->fill($row);
                yield $model;
           }
        }
    }

    /**
     * 创建 model 至数据库
     * @return bool
     */
    public function create() {
        $id   = $this->getId();
        $conn = static::getConnById($id);
        if (!static::_create($id, $this->toArray(), $conn)) {
            return false;
        }
        return true;
    }

    /**
     * 更新数据库中的数据
     * @param array $data
     * @return bool
     */
    public function update(array $data) {
        // 获得差异数据
        $data   = $this->_getUpdateFields($data);
        if (!$data) {
            return true;
        }

        // 先更新数据库
        $id     = $this->getId();
        $conn   = static::getConnById($id);
        if (!static::_update($id, $data, $conn)) {
            return false;
        }

        // 更新model自身数据
        $this->fill($data);
        return true;
    }

    protected function _getUpdateFields($data) {
        $update = [];
        foreach (static::getSchema() as $field => $conf) {
            $key = isset($conf['key']) ? $conf['key'] : $field;
            if ($this->$field != $data[$key]) {
                $update[$key] = $data[$key];
            }
        }

        return $update;
    }

    /**
     *
     * @return bool
     */
    public function delete() {
        $id   = $this->getId();
        $conn = static::getConnById($id);
        if (!static::_delete($id, $conn)) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param \Redb\Query $query
     * @return boolean|\Redb\Result
     */
    public function query(\Redb\Query $query) {
        $id     = $this->getId();
        $conn   = static::getConnById($id);
        $result = static::_query($query, $conn);
        if (!$result) {
            return false;
        }
        return $result;
    }

    /**
     * 不同DB类型的Model扩展方法
     */
    abstract protected static function _read($id, \Redb\Driver\Driver $conn);
    abstract protected static function _create($id, array $data, \Redb\Driver\Driver $conn);
    abstract protected static function _update($id, array $data, \Redb\Driver\Driver $conn);
    abstract protected static function _delete($id, \Redb\Driver\Driver $conn);
    abstract protected static function _query(\Redb\Query $query, \Redb\Driver\Driver $conn);

}
