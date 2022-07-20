<?php

namespace Graycore\GraphQlIntrospectionCache\Test\Integration;

use Magento\Framework\App\Response\Http;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQlCache\Model\CacheableQuery;
use Magento\TestFramework\Request;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\ObjectManager;

class CachedQueryTest extends TestCase
{
    private $om;

    protected function setUp(): void
    {
        $this->om = ObjectManager::getInstance();
    }

    private function createProductAttributeIntrospectionRequest(): Request
    {
        return $this->om->create(Request::class)->setParam('query', <<<EOF
query introspectionTest {
    __type (name: "ProductAttributeFilterInput") {
        inputFields {
            name
            type {
                name
            }
        }
    }
}
EOF
);
    }

    public function testProductAttributeIntrospectionRequestHasCacheTags(): void
    {
        /** @var CacheableQuery $cacheableQuery */
        $cacheableQuery = $this->om->get(CacheableQuery::class);
        $this->assertEmpty($cacheableQuery->getCacheTags());

        $this->om->create(GraphQl::class)->dispatch($this->createProductAttributeIntrospectionRequest());

        $this->assertNotEmpty($cacheableQuery->getCacheTags());
        $this->assertTrue($cacheableQuery->isCacheable());
    }
}
