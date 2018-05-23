<?php

namespace Photogabble\DependencyGraph;

class Node
{
    private $name;
    private $edges = [];

    function __construct(string $name)
    {
        $this->name = $name;
    }

    function addEdge(Node $node) {
        array_push($this->edges, $node);
    }
}