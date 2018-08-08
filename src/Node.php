<?php

namespace Photogabble\DependencyGraph;

class Node
{
    /**
     * @var string Name of this Node
     */
    public $name;

    /**
     * @var bool isChanged flag
     */
    public $changed;

    /**
     * @var Node[] Array of edge nodes
     */
    public $edges = [];

    public function __construct(string $name, bool $changed = false)
    {
        $this->name = $name;
        $this->changed = $changed;
    }

    public function addEdge(Node $node)
    {
        array_push($this->edges, $node);
    }
}