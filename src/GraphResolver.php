<?php

namespace Photogabble\DependencyGraph;

class GraphResolver
{

    /**
     * @var Node[]
     */
    private $resolved = [];

    /**
     * @var array[]
     */
    private $unresolved = [];

    public function resolve(Node $node): array
    {
        $this->resolved = [];
        $this->unresolved = [];
        $this->resolveNode($node);
        return $this->resolved;
    }

    private function resolveNode(Node $node)
    {
        array_push($this->unresolved, $node);
        foreach ($node->edges as $edge)
        {
            if (! in_array($edge, $this->resolved)){
                if (in_array($edge, $this->unresolved)){
                    throw new \Exception('Circular reference detected: ' . $node->name . ' -> '. $edge->name);
                }
                $this->resolveNode($edge);
            }
        }
        array_push($this->resolved, $node);
        if (($key = array_search($node, $this->unresolved)) !== false) {
            unset($this->unresolved[$key]);
        }
    }
}