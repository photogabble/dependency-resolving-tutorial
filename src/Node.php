<?php

namespace Photogabble\DependencyGraph;

class Node
{
    /**
     * @var string Name of this Node
     */
    public $name;

    /**
     * @var Node[] Array of edge nodes
     */
    public $edges = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addEdge(Node $node)
    {
        array_push($this->edges, $node);
    }
}