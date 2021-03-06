<?php

namespace Photogabble\DependencyGraph;

class GraphResolver
{
    /**
     * @var Node[]
     */
    private $resolved = [];

    /**
     * @var Node[]
     */
    private $unresolved = [];

    /**
     * @var array
     */
    private $adjacencyList = [];

    /**
     * @param Node $node
     * @return array
     * @throws \Exception
     */
    public function resolve(Node $node): array
    {
        $this->resolved = [];
        $this->unresolved = [];
        $this->adjacencyList = [];
        $this->resolveNode($node);
        return $this->resolved;
    }

    /**
     * Returns the resolved graph adjacency list.
     *
     * @return array
     */
    public function getAdjacencyList(): array
    {
        return $this->adjacencyList;
    }

    /**
     * Reduces the resolved graph and returns only nodes that have their
     * changed flag set to true or are connected as dependants to
     * a node that has its changed flag set to true.
     *
     * @return array
     */
    public function reduce(): array
    {
        $modified = [];

        foreach ($this->resolved as $node){
            if ($node->changed === true){
                array_push($modified, $node);
                foreach ($this->adjacencyList[$node->name] as $affected) {
                    array_push($modified,$affected);
                }
            }
        }

        return $modified;
    }

    /**
     * @param Node $node
     * @param array $parents
     * @throws \Exception
     */
    private function resolveNode(Node $node, $parents = [])
    {
        if (! isset($this->adjacencyList[$node->name])){
            $this->adjacencyList[$node->name] = [];
        }

        array_push($this->unresolved, $node);
        foreach ($node->edges as $edge)
        {
            if (! in_array($edge, $this->resolved)){
                if (in_array($edge, $this->unresolved)){
                    throw new \Exception('Circular reference detected: ' . $node->name . ' -> '. $edge->name);
                }
                array_push($parents, $node);
                $this->resolveNode($edge, $parents);
            }
        }
        foreach($parents as $p){
            if ($node->name !== $p->name && !in_array($node, $this->adjacencyList[$p->name])) {
                array_push($this->adjacencyList[$p->name], $node);
            }
        }
        array_push($this->resolved, $node);
        if (($key = array_search($node, $this->unresolved)) !== false) {
            unset($this->unresolved[$key]);
        }
    }
}