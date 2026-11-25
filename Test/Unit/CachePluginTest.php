<?php

namespace Graycore\GraphQlIntrospectionCache\Test\Unit;

use GraphQL\Executor\ExecutionContext;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\ReferenceExecutor;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\Parser;
use Graycore\GraphQlIntrospectionCache\Plugin\CachePlugin;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\GraphQlCache\Model\CacheableQuery;
use PHPUnit\Framework\TestCase;

class CachePluginTest extends TestCase
{
    private function executorFor(string $query): ReferenceExecutor
    {
        $document = Parser::parse($query);
        $operation = $document->definitions[0];
        $this->assertInstanceOf(OperationDefinitionNode::class, $operation);

        $context = (new \ReflectionClass(ExecutionContext::class))->newInstanceWithoutConstructor();
        $context->operation = $operation;

        $executor = (new \ReflectionClass(ReferenceExecutor::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(ReferenceExecutor::class, 'exeContext'))->setValue($executor, $context);

        return $executor;
    }

    private function runPlugin(string $query, CacheableQuery $cacheableQuery, array $handlers): void
    {
        $adapter = new SyncPromiseAdapter();
        $plugin = new CachePlugin($cacheableQuery, $adapter, $handlers);
        $result = $adapter->createFulfilled((object) ['data' => []]);

        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $plugin->afterDoExecute($this->executorFor($query), $result);
        } finally {
            restore_error_handler();
        }
    }

    private function handler(): IdentityInterface
    {
        $handler = $this->createStub(IdentityInterface::class);
        $handler->method('getIdentities')->willReturn(['tag_1']);

        return $handler;
    }

    public function testLiteralTypeNameAddsCacheTags(): void
    {
        $cacheableQuery = $this->createMock(CacheableQuery::class);
        $cacheableQuery->expects($this->once())->method('addCacheTags')->with(['tag_1']);

        $this->runPlugin('{ __type(name: "Foo") { name } }', $cacheableQuery, ['Foo' => $this->handler()]);
    }

    public function testRootInlineFragmentDoesNotThrow(): void
    {
        $cacheableQuery = $this->createMock(CacheableQuery::class);
        $cacheableQuery->expects($this->never())->method('addCacheTags');

        $this->runPlugin('{ ... on Query { __typename } }', $cacheableQuery, ['Foo' => $this->handler()]);
    }

    public function testVariableTypeNameDoesNotThrow(): void
    {
        $cacheableQuery = $this->createMock(CacheableQuery::class);
        $cacheableQuery->expects($this->never())->method('addCacheTags');

        $this->runPlugin(
            'query ($n: String!) { __type(name: $n) { name } }',
            $cacheableQuery,
            ['Foo' => $this->handler()]
        );
    }
}
