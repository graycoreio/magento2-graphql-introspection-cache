<?php

namespace Graycore\GraphQlIntrospectionCache\Test\Unit;

use Graycore\GraphQlIntrospectionCache\Model\Cache\Identity\Introspection\ProductAttributeFilterInput;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProductAttributeFilterInputTest extends TestCase
{
    private function getAttributeMock(int $attributeId, string $attributeCode): AttributeInterface
    {
        return $this->createConfiguredMock(AttributeInterface::class, [
            'getAttributeId' => $attributeId,
            'getAttributeCode' => $attributeCode
        ]);
    }

    public function testCorrectIdentitiesAreReturned()
    {
        $attributes = [
            'name' => 10,
            'description' => 20,
            'sku' => 30
        ];

        $attributeMocks = [];
        foreach ($attributes as $attributeCode => $attributeId) {
            $attributeMocks[] = $this->getAttributeMock($attributeId, $attributeCode);
        }

        $attributeRepository = $this->createConfiguredMock(AttributeRepositoryInterface::class, [
            'getList' => $this->createConfiguredMock(AttributeSearchResultsInterface::class, [
                'getItems' => $attributeMocks
            ])
        ]);

        $searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->method('addFilter')->willReturnSelf();
        $searchCriteriaBuilder->method('create')->willReturn($this->createMock(SearchCriteria::class));

        $objectManagerHelper = new ObjectManager($this);
        /** @var ProductAttributeFilterInput $productAttributeFilterInput */
        $productAttributeFilterInput = $objectManagerHelper->getObject(ProductAttributeFilterInput::class, [
            'attributeRepository' => $attributeRepository,
            'searchCriteriaBuilder' => $searchCriteriaBuilder
        ]);

        $identities = $productAttributeFilterInput->getIdentities([
            '__type' => [
                'inputFields' => [
                    ['name' => 'name'],
                    ['name' => 'description'],
                    ['name' => 'sku'],
                ]
            ]
        ]);

        foreach ($attributes as $attributeId) {
            $this->assertContains(Attribute::CACHE_TAG . '_' . $attributeId, $identities);
        }
    }
}
