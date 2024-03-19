<?php

namespace Graycore\GraphQlIntrospectionCache\Test\Integration;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Schema;
use Graycore\GraphQlIntrospectionCache\Executor\ReferenceExecutor;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class UniqueExecutorTest extends TestCase
{
    public function testThatCreatedExecutorsAreUnique()
    {
        $om = ObjectManager::getInstance();

        $promiseAdapter = $om->create(SyncPromiseAdapter::class);
        $schema = $om->create(Schema::class, ['config' => []]);
        /** @var DocumentNode */
        $documentNode = $om->create(DocumentNode::class, ['vars' => []]);
        $documentNode->definitions = new NodeList([]);

        $first = ReferenceExecutor::create(
            $promiseAdapter,
            $schema,
            $documentNode,
            null,
            null,
            [],
            null,
            function() {}
        );

        $second = ReferenceExecutor::create(
            $promiseAdapter,
            $schema,
            $documentNode,
            null,
            null,
            [],
            null,
            function() {}
        );

        $this->assertNotEquals(spl_object_id($first), spl_object_id($second));
    }
}
