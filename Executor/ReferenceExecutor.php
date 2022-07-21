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
    /** @var ReferenceExecutor|null */
    private static ?self $executorInstance = null;

    private function setPrivateProperty(string $property, $value, bool $static = false): void
    {
        try {
            $reflectionProperty = new ReflectionProperty(\GraphQL\Executor\ReferenceExecutor::class, $property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($static ? null : $this, $value);
        } catch (\ReflectionException $e) {}
    }

    protected function __construct(ExecutionContext $context)
    {
        if (is_callable('parent::__construct')) {
            parent::__construct($context);
        } else {
            $this->setPrivateProperty('UNDEFINED', Utils::undefined(), true);
            $this->setPrivateProperty('exeContext', $context);
            $this->setPrivateProperty('subFieldCache', new SplObjectStorage());
        }
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
        if (self::$executorInstance !== null) {
            return self::$executorInstance;
        }

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
            return new class($promiseAdapter->createFulfilled(new ExecutionResult(null, $exeContext))) implements ExecutorImplementation
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

        return self::$executorInstance = ObjectManager::getInstance()->create(
            ReferenceExecutor::class,
            ['context' => $exeContext]
        );
    }
}
