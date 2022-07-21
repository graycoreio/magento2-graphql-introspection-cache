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
use SplObjectStorage;

class ReferenceExecutor extends \GraphQL\Executor\ReferenceExecutor
{
    /** @var object */
    protected static $UNDEFINED;

    /** @var ExecutionContext */
    protected $exeContext;

    /** @var SplObjectStorage */
    protected $subFieldCache;

    /** @var ReferenceExecutor|null */
    private static ?self $executorInstance = null;

    protected function __construct(ExecutionContext $context)
    {
        if (is_callable('parent::__construct')) {
            parent::__construct($context);
        } else {
            if (!static::$UNDEFINED) {
                static::$UNDEFINED = Utils::undefined();
            }
            $this->exeContext = $context;
            $this->subFieldCache = new SplObjectStorage();
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

        $exeContext = static::buildExecutionContext(
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
