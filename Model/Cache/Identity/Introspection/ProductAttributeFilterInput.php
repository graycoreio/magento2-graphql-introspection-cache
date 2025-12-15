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
    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get cache identities for product attribute filter input introspection.
     *
     * @param array $resolvedData
     * @return array
     */
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
