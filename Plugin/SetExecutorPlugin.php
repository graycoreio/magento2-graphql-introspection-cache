<?php

namespace Graycore\GraphQlIntrospectionCache\Plugin;

use Closure;
use GraphQL\Executor\Executor;
use GraphQL\Executor\ReferenceExecutor;
use Magento\Framework\GraphQl\Query\QueryProcessor;
use Magento\Framework\ObjectManagerInterface;

class SetExecutorPlugin
{
    private ObjectManagerInterface $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Before processing a GraphQL query, replace the factory with a wrapper that uses the object manager.
     * This allows us to create plugins on the ReferenceExecutor.
     */
    public function beforeProcess(
        QueryProcessor $subject,
        ...$args
    ): array {
        Executor::setImplementationFactory(
            fn(
                $promiseAdapter,
                $schema,
                $documentNode,
                $rootValue,
                $contextValue,
                $variableValues,
                $operationName,
                $fieldResolver
            ) => $this->objectManager->create(
                ReferenceExecutor::class,
                [
                    'context' => Closure::bind(
                        fn() => ReferenceExecutor::buildExecutionContext(
                            $schema,
                            $documentNode,
                            $rootValue,
                            $contextValue,
                            $variableValues,
                            $operationName,
                            $fieldResolver,
                            $promiseAdapter
                        ),
                        null,
                        ReferenceExecutor::class
                    )()
                ]
            )
        );
        return $args;
    }
}
