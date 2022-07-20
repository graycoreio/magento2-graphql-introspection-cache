<?php

namespace Graycore\GraphQlIntrospectionCache\Model\Cache\Identity\Introspection;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

class ProductAttributeFilterInput implements IdentityInterface
{
    private AttributeRepositoryInterface $attributeRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getIdentities(array $resolvedData): array
    {
        return array_map(
            fn($attribute) => Attribute::CACHE_TAG . '_' . $attribute->getAttributeId(),
            $this->attributeRepository->getList(
                Product::ENTITY,
                $this->searchCriteriaBuilder->addFilter(
                    AttributeInterface::ATTRIBUTE_CODE,
                    array_map(fn($attribute) => $attribute['name'], $resolvedData['__type']['inputFields'] ?? []),
                    'in'
                )->create()
            )->getItems()
        );
    }
}
