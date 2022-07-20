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
    private CacheableQuery $cacheableQuery;
    private SyncPromiseAdapter $syncPromiseAdapter;
    private array $introspectionHandlers;

    public function __construct(
        CacheableQuery $cacheableQuery,
        SyncPromiseAdapter $syncPromiseAdapter,
        array $introspectionHandlers = []
    ) {
        $this->cacheableQuery = $cacheableQuery;
        $this->syncPromiseAdapter = $syncPromiseAdapter;
        $this->introspectionHandlers = $introspectionHandlers;
    }

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
                if ($argument->name->value !== 'name' || !isset($this->introspectionHandlers[$argument->value->value])) {
                    continue;
                }

                $cacheIdentity = $this->introspectionHandlers[$argument->value->value];
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
