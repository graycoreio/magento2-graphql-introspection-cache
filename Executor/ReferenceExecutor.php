<?php

namespace Graycore\GraphQlIntrospectionCache\Executor;

use GraphQL\Executor\ExecutionContext;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\ExecutorImplementation;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Type\Schema;
use GraphQL\Utils\Utils;
use Magento\Framework\App\ObjectManager;
use ReflectionMethod;
use ReflectionProperty;
use SplObjectStorage;

class ReferenceExecutor extends \GraphQL\Executor\ReferenceExecutor
{
    protected function __construct(ExecutionContext $context)
    {
        if (is_callable('parent::class::__construct')) {
            parent::__construct($context);
        } else {
            $this->setExecutorPrivateProp('UNDEFINED', Utils::undefined(), true);
            $this->setExecutorPrivateProp('exeContext', $context);
            $this->setExecutorPrivateProp('subFieldCache', new SplObjectStorage());
        }
    }

    private function setExecutorPrivateProp(string $property, $value, bool $static = false): void
    {
        try {
            $reflectionProperty = new ReflectionProperty(\GraphQL\Executor\ReferenceExecutor::class, $property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($static ? null : $this, $value);
        } catch (\ReflectionException $e) {}
    }

    public static function create(
        PromiseAdapter $promiseAdapter,
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue,
        $contextValue,
        $variableValues,
        ?string $operationName,
        callable $fieldResolver
    ) : ExecutorImplementation {
        $reflectionMethod = new ReflectionMethod(\GraphQL\Executor\ReferenceExecutor::class, 'buildExecutionContext');
        if ($reflectionMethod->isPrivate()) {
            $reflectionMethod->setAccessible(true);
        }

        $exeContext = $reflectionMethod->invoke(
            null,
            $schema,
            $documentNode,
            $rootValue,
            $contextValue,
            $variableValues,
            $operationName,
            $fieldResolver,
            $promiseAdapter
        );

        if (is_array($exeContext)) {
            $promise = $promiseAdapter->createFulfilled(new ExecutionResult(null, $exeContext));
            return new class($promise) implements ExecutorImplementation
            {
                private Promise $result;

                public function __construct(Promise $result)
                {
                    $this->result = $result;
                }

                public function doExecute() : Promise
                {
                    return $this->result;
                }
            };
        }

        return ObjectManager::getInstance()->create(
            ReferenceExecutor::class,
            ['context' => $exeContext]
        );
    }
}
