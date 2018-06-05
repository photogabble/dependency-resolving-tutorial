<?php

namespace Photogabble\Tests;

use Photogabble\DependencyGraph\GraphResolver;
use Photogabble\DependencyGraph\Node;

class GraphTest extends \PHPUnit\Framework\TestCase {

    public function testGraphResolver()
    {
        /** @var Node[] $nodes */
        $nodes = [];
        foreach (range('a', 'e') as $letter) {
            $nodes[$letter] = new Node($letter);
        }
        $nodes['a']->addEdge($nodes['b']); // a depends on b
        $nodes['a']->addEdge($nodes['d']); // a depends on d
        $nodes['b']->addEdge($nodes['c']); // b depends on c
        $nodes['b']->addEdge($nodes['e']); // b depends on e
        $nodes['c']->addEdge($nodes['d']); // c depends on d
        $nodes['c']->addEdge($nodes['e']); // c depends on e

        $class = new GraphResolver();
        $result = $class->resolve($nodes['a']);

        $this->assertSame(['d', 'e', 'c', 'b', 'a'], array_map(function(Node $v){
            return $v->name;
        }, $result));
    }

    public function testGraphResolverCircularDetection()
    {
        /** @var Node[] $nodes */
        $nodes = [];
        foreach (range('a', 'e') as $letter) {
            $nodes[$letter] = new Node($letter);
        }
        $nodes['a']->addEdge($nodes['b']); // a depends on b
        $nodes['a']->addEdge($nodes['d']); // a depends on d
        $nodes['b']->addEdge($nodes['c']); // b depends on c
        $nodes['b']->addEdge($nodes['e']); // b depends on e
        $nodes['c']->addEdge($nodes['d']); // c depends on d
        $nodes['c']->addEdge($nodes['e']); // c depends on e
        $nodes['d']->addEdge($nodes['b']); // d depends on b - circular

        $class = new GraphResolver();
        $this->expectExceptionMessage('Circular reference detected: d -> b');
        $class->resolve($nodes['a']);
    }

    public function testGraphAdjacencyList()
    {
        /** @var Node[] $nodes */
        $nodes = [];
        foreach (range('a', 'e') as $letter) {
            $nodes[$letter] = new Node($letter);
        }

        $nodes['a']->addEdge($nodes['b']); // a depends on b
        $nodes['a']->addEdge($nodes['d']); // a depends on d
        $nodes['b']->addEdge($nodes['c']); // b depends on c
        $nodes['b']->addEdge($nodes['e']); // b depends on e
        $nodes['c']->addEdge($nodes['d']); // c depends on d
        $nodes['c']->addEdge($nodes['e']); // c depends on e

        $class = new GraphResolver();
        $class->resolve($nodes['a']);

        $this->assertSame([
            'a' => ['d','e','c','b'],
            'b' => ['d','e','c'],
            'c' => ['d','e'],
            'd' => [],
            'e' => [],
        ], array_map(function(array $v){
            return array_map(function(Node $n){
                return $n->name;
            }, $v);
        }, $class->getAdjacencyList()));
    }

    public function testGraphReduction()
    {
        /** @var Node[] $nodes */
        $nodes = [];
        foreach (range('a', 'e') as $letter) {
            $nodes[$letter] = new Node($letter);
        }

        $nodes['c']->changed = true;

        $nodes['a']->addEdge($nodes['b']); // a depends on b
        $nodes['a']->addEdge($nodes['d']); // a depends on d
        $nodes['b']->addEdge($nodes['c']); // b depends on c
        $nodes['b']->addEdge($nodes['e']); // b depends on e
        $nodes['c']->addEdge($nodes['d']); // c depends on d
        $nodes['c']->addEdge($nodes['e']); // c depends on e

        $class = new GraphResolver();
        $class->resolve($nodes['a']);

        $modified = [];
        $adjacencyList = $class->getAdjacencyList();

        foreach ($nodes as $node){
            if ($node->changed === true){
                array_push($modified, $adjacencyList[$node->name]);
            }
        }

        var_dump($modified);

    }
}