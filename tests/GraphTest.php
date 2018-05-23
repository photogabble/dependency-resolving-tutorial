<?php

namespace Photogabble\Tests;

use Photogabble\DependencyGraph\Node;

class GraphTest extends \PHPUnit\Framework\TestCase {

    function testNodes()
    {
        /** @var Node[] $nodes */
        $nodes = [];
        foreach (range('a', 'e') as $letter){
            $nodes[$letter] = new Node($letter);
        }

        $nodes['a']->addEdge($nodes['b']); // a depends on b
        $nodes['a']->addEdge($nodes['d']); // a depends on d
        $nodes['b']->addEdge($nodes['c']); // b depends on c
        $nodes['b']->addEdge($nodes['e']); // b depends on e
        $nodes['c']->addEdge($nodes['d']); // c depends on d
        $nodes['c']->addEdge($nodes['e']); // c depends on e

        var_dump($nodes);
    }
}