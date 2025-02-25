<?php

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
                throw new ErrorException($message, 0, $severity, $file, $line);
            }
            return false; // Let PHP handle other errors as usual
        });
        
        /**
         * @var ExecutionContext $context
         */
        $context = $this->getMockBuilder(ExecutionContext::class)->disableOriginalConstructor()->getMock();
        $exec = new ReferenceExecutor($context);
        $this->assertTrue(true);
    }
}
