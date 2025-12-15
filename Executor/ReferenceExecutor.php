<?php

namespace Graycore\GraphQlIntrospectionCache\Executor;

use GraphQL\Executor\ExecutionContext;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Executor;
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
    /**
     * Initialize executor with execution context.
     *
     * @param ExecutionContext $context
     */
    public function __construct(ExecutionContext $context)
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct($context);
        } else {
            $this->setExecutorPrivateProp('UNDEFINED', Utils::undefined(), true);
            $this->setExecutorPrivateProp('exeContext', $context);
            $this->setExecutorPrivateProp('subFieldCache', new SplObjectStorage());
        }
    }

    /**
     * Set a private property on the parent executor class via reflection.
     *
     * @param string $property
     * @param mixed $value
     * @param bool $static
     * @return void
     */
    private function setExecutorPrivateProp(string $property, $value, bool $static = false): void
    {
        try {
            $reflectionProperty = new ReflectionProperty(\GraphQL\Executor\ReferenceExecutor::class, $property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($static ? null : $this, $value);
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (\ReflectionException $e) {
            // Property may not exist in all versions of graphql-php, silently ignore
        }
    }

    /**
     * @inheritdoc
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    public static function create(
        PromiseAdapter $promiseAdapter,
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue,
        $contextValue,
        $variableValues,
        ?string $operationName,
        callable $fieldResolver,
        ?callable $argsMapper = null,
    ): ExecutorImplementation {
        $reflectionMethod = new ReflectionMethod(\GraphQL\Executor\ReferenceExecutor::class, 'buildExecutionContext');
        if ($reflectionMethod->isPrivate()) {
            $reflectionMethod->setAccessible(true);
        }

        // Old args, before v15
        $args = [
            null,
            $schema,
            $documentNode,
            $rootValue,
            $contextValue,
            $variableValues,
            $operationName,
            $fieldResolver,
            $promiseAdapter,
        ];

        if ($argsMapper || \method_exists(Executor::class, 'getDefaultArgsMapper')) {
            $args = [
                null,
                $schema,
                $documentNode,
                $rootValue,
                $contextValue,
                $variableValues,
                $operationName,
                $fieldResolver,
                $argsMapper ?? Executor::getDefaultArgsMapper(),
                $promiseAdapter,
            ];
        }

        $exeContext = $reflectionMethod->invoke(
            ...$args
        );

        if (is_array($exeContext)) {
            $promise = $promiseAdapter->createFulfilled(new ExecutionResult(null, $exeContext));
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            return new class($promise) implements ExecutorImplementation
            {
                /**
                 * @var Promise
                 */
                private Promise $result;

                /**
                 * @param Promise $result
                 */
                public function __construct(Promise $result)
                {
                    $this->result = $result;
                }

                /**
                 * Execute and return the promise result.
                 *
                 * @return Promise
                 */
                public function doExecute(): Promise
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
