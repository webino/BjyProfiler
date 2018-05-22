<?php

namespace BjyProfiler\Db\Profiler;

/**
 * Class Query
 */
class Query
{
    /**
     * @var string|null
     */
    protected $sql;

    /**
     * @var int|null
     */
    protected $queryType;

    /**
     * @var array
     */
    protected $queryTypes = [
        Profiler::SELECT  => 'SELECT',
        Profiler::INSERT  => 'INSERT',
        Profiler::UPDATE  => 'UPDATE',
        Profiler::DELETE  => 'DELETE',
        Profiler::QUERY   => 'OTHER',
        Profiler::CONNECT => 'CONNECT',
    ];

    /**
     * @var float|null
     */
    protected $startTime;

    /**
     * @var float|null
     */
    protected $endTime;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var array
     */
    protected $callStack = [];

    /**
     * @param string $sql
     * @param int $queryType
     * @param array $parameters
     * @param array $stack
     */
    public function __construct(string $sql, int $queryType, array $parameters = [], array $stack = [])
    {
        $this->sql        = $sql;
        $this->queryType  = $queryType;
        $this->parameters = $parameters;
        $this->callStack  = $stack;
    }

    /**
     * @return $this
     */
    public function start()
    {
        $this->startTime = microtime(true);
        return $this;
    }

    /**
     * @return $this
     */
    public function end()
    {
        $this->endTime = microtime(true);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasEnded() : bool
    {
        return $this->endTime !== null;
    }

    /**
     * @return int|null
     */
    public function getQueryType() : ?int
    {
        return $this->queryType;
    }

    /**
     * @return string|null
     */
    public function getQueryTypeName() : ?string
    {
        return isset($this->queryTypes[$this->queryType]) ? $this->queryTypes[$this->queryType] : null;
    }

    /**
     * @return string|null
     */
    public function getSql() : ?string
    {
        return $this->sql;
    }

    /**
     * @return float|null
     */
    public function getDeltaTime() : ?float
    {
        if (!$this->hasEnded()) {
            return null;
        }
        return $this->endTime - $this->startTime;
    }

    /**
     * @return float|null
     */
    public function getStartTime() : ?float
    {
        return $this->startTime;
    }

    /**
     * @return float|null
     */
    public function getEndTime() : ?float
    {
        return $this->endTime;
    }

    /**
     * @return array
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getCallStack() : array
    {
        return $this->callStack;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'type'       => $this->getQueryTypeName(),
            'sql'        => $this->sql,
            'start'      => $this->startTime,
            'end'        => $this->endTime,
            'delta'      => $this->getDeltaTime(),
            'parameters' => $this->parameters,
            'stack'      => $this->callStack
        ];
    }
}
