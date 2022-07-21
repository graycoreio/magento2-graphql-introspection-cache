<?php

namespace Graycore\GraphQlIntrospectionCache\Plugin;

use GraphQL\Executor\Executor;
use Graycore\GraphQlIntrospectionCache\Executor\ReferenceExecutor;
use Magento\Framework\GraphQl\Query\QueryProcessor;

class ExecutorPlugin
{
    /**
     * Before processing a GraphQL query, replace the factory with a wrapper that uses the object manager.
     * This allows us to create plugins on the ReferenceExecutor.
     */
    public function beforeProcess(
        QueryProcessor $subject,
        ...$args
    ): array {
        Executor::setImplementationFactory([ReferenceExecutor::class, 'create']);
        return $args;
    }
}
