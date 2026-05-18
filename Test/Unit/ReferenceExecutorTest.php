<?php

namespace Graycore\GraphQlIntrospectionCache\Test\Unit;

use GraphQL\Executor\ExecutionContext;
use Graycore\GraphQlIntrospectionCache\Executor\ReferenceExecutor;
use PHPUnit\Framework\TestCase;

class ReferenceExecutorTest extends TestCase
{

    public function testItCanConstruct()
    {
        error_reporting(E_ALL | E_DEPRECATED);
        set_error_handler(function ($severity, $message, $file, $line) {
            if ($severity === E_DEPRECATED) {
                throw new \ErrorException($message, 0, $severity, $file, $line);
            }
            return false; // Let PHP handle other errors as usual
        });

        try {
            /**
             * @var ExecutionContext $context
             */
            $context = $this->createStub(ExecutionContext::class);
            $exec = new ReferenceExecutor($context);
            $this->assertTrue(true);
        } finally {
            restore_error_handler();
        }
    }
}
