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
        $nodes['a']->addEdge($nodes['b']); // b depends on a
        $nodes['a']->addEdge($nodes['d']); // d depends on a
        $nodes['b']->addEdge($nodes['c']); // c depends on b
        $nodes['b']->addEdge($nodes['e']); // e depends on b
        $nodes['c']->addEdge($nodes['d']); // d depends on c
        $nodes['c']->addEdge($nodes['e']); // e depends on c

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
        $nodes['a']->addEdge($nodes['b']); // b depends on a
        $nodes['a']->addEdge($nodes['d']); // d depends on a
        $nodes['b']->addEdge($nodes['c']); // c depends on b
        $nodes['b']->addEdge($nodes['e']); // e depends on b
        $nodes['c']->addEdge($nodes['d']); // d depends on c
        $nodes['c']->addEdge($nodes['e']); // e depends on c
        $nodes['d']->addEdge($nodes['b']); // b depends on d - circular

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

        $nodes['a']->addEdge($nodes['b']); // b depends on a
        $nodes['a']->addEdge($nodes['d']); // d depends on a
        $nodes['b']->addEdge($nodes['c']); // c depends on b
        $nodes['b']->addEdge($nodes['e']); // e depends on b
        $nodes['c']->addEdge($nodes['d']); // d depends on c
        $nodes['c']->addEdge($nodes['e']); // e depends on c

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
        foreach (range('a', 'f') as $letter) {
            $nodes[$letter] = new Node($letter);
        }

        $nodes['c']->changed = true;

        $nodes['a']->addEdge($nodes['b']); // b depends on a
        $nodes['a']->addEdge($nodes['d']); // d depends on a
        $nodes['b']->addEdge($nodes['c']); // c depends on b
        $nodes['b']->addEdge($nodes['e']); // e depends on b
        $nodes['c']->addEdge($nodes['d']); // d depends on c
        $nodes['c']->addEdge($nodes['e']); // e depends on c

        $class = new GraphResolver();
        $class->resolve($nodes['a']);
        $reduced = $class->reduce();

        $this->assertCount(3, $reduced);
        $this->assertSame([$nodes['c'],$nodes['d'], $nodes['e']], $reduced);

        $nodes['e']->addEdge($nodes['f']); // f depends on e
        $class = new GraphResolver();
        $class->resolve($nodes['a']);
        $reduced = $class->reduce();

        $this->assertCount(4, $reduced);
        $this->assertSame([$nodes['c'],$nodes['d'], $nodes['f'], $nodes['e']], $reduced);
    }
}