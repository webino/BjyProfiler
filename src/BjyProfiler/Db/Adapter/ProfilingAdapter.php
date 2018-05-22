<?php

namespace BjyProfiler\Db\Adapter;

use BjyProfiler\Db\Profiler\Profiler;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver as ZdbDriver;
use Zend\Db\Adapter\Profiler\ProfilerInterface;
use Zend\Db\ResultSet;

/**
 * Class ProfilingAdapter
 */
class ProfilingAdapter extends Adapter
{
    protected $profiler;

    public function setProfiler(ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }

    public function getProfiler()
    {
        return $this->profiler;
    }

    public function query(
        $sql,
        $parametersOrQueryMode = self::QUERY_MODE_PREPARE,
        ResultSet\ResultSetInterface $resultPrototype = null
    ) {
        $this->getProfiler()->startQuery($sql);
        $return = parent::query($sql, $parametersOrQueryMode, $resultPrototype);
        $this->getProfiler()->endQuery();
        return $return;
    }

    public function injectProfilingStatementPrototype(array $options = array())
    {
        $profiler = $this->getProfiler();
        if (!$profiler instanceof Profiler) {
            // TODO exception
            throw new \InvalidArgumentException('No profiler attached!');
        }

        $driver = $this->getDriver();
        if (method_exists($driver, 'registerStatementPrototype')) {
            $driverName = get_class($driver);
            switch ($driverName) {
                case 'Zend\Db\Adapter\Driver\IbmDb2\IbmDb2':
                    $statementPrototype = new ZdbDriver\IbmDb2\Statement();
                    break;
                case ZdbDriver\Mysqli\Mysqli::class:
                    $defaults = array('buffer_results' => false);
                    $options = array_intersect_key(array_merge($defaults, $options), $defaults);

                    $statementPrototype = new ZdbDriver\Mysqli\Statement($options['buffer_results']);
                    break;
                case ZdbDriver\Oci8\Oci8::class:
                    $statementPrototype = new ZdbDriver\Oci8\Statement();
                    break;
                case ZdbDriver\Sqlsrv\Sqlsrv::class:
                    $statementPrototype = new ZdbDriver\Sqlsrv\Statement();
                    break;
                case ZdbDriver\Pgsql\Pgsql::class:
                    $statementPrototype = new ZdbDriver\Pgsql\Statement();
                    break;
                case ZdbDriver\Pdo\Pdo::class:
                default:
                    $statementPrototype = new ZdbDriver\Pdo\Statement();
            }

            $statementPrototype->setProfiler($this->getProfiler());
            $driver->registerStatementPrototype($statementPrototype);
        }
    }
}
