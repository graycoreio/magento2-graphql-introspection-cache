<?php

namespace Graycore\GraphQlIntrospectionCache\Plugin;

use Closure;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\ReferenceExecutor;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\GraphQlCache\Model\CacheableQuery;

class CachePlugin
{
    /**
     * @var CacheableQuery
     */
    private CacheableQuery $cacheableQuery;

    /**
     * @var SyncPromiseAdapter
     */
    private SyncPromiseAdapter $syncPromiseAdapter;

    /**
     * @var array
     */
    private array $introspectionHandlers;

    /**
     * @param CacheableQuery $cacheableQuery
     * @param SyncPromiseAdapter $syncPromiseAdapter
     * @param array $introspectionHandlers
     */
    public function __construct(
        CacheableQuery $cacheableQuery,
        SyncPromiseAdapter $syncPromiseAdapter,
        array $introspectionHandlers = []
    ) {
        $this->cacheableQuery = $cacheableQuery;
        $this->syncPromiseAdapter = $syncPromiseAdapter;
        $this->introspectionHandlers = $introspectionHandlers;
    }

    /**
     * After executing a GraphQL query, add cache tags for introspection queries.
     *
     * @param ReferenceExecutor $subject
     * @param Promise $result
     * @return Promise
     */
    public function afterDoExecute(ReferenceExecutor $subject, Promise $result): Promise
    {
        $executionContext = (Closure::bind(fn() => $this->exeContext, $subject, ReferenceExecutor::class))();
        if ($executionContext === null) {
            return $result;
        }

        foreach ($executionContext->operation->selectionSet->selections as $selection) {
            if ($selection->name->value !== '__type') {
                continue;
            }

            foreach ($selection->arguments as $argument) {
                $handlerKey = $argument->value->value;
                if ($argument->name->value !== 'name' || !isset($this->introspectionHandlers[$handlerKey])) {
                    continue;
                }

                $cacheIdentity = $this->introspectionHandlers[$handlerKey];
                if ($cacheIdentity instanceof IdentityInterface) {
                    $this->cacheableQuery->addCacheTags(
                        $cacheIdentity->getIdentities($this->syncPromiseAdapter->wait($result)->data)
                    );
                }
            }
        }

        return $result;
    }
}
