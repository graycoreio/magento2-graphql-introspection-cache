# Magento 2 GraphQL Introspection Cache

<div align="center">

[![Packagist Downloads](https://img.shields.io/packagist/dm/graycore/magento2-graphql-introspection-cache?color=blue)](https://packagist.org/packages/graycore/magento2-graphql-introspection-cache/stats)
[![Packagist Version](https://img.shields.io/packagist/v/graycore/magento2-graphql-introspection-cache?color=blue)](https://packagist.org/packages/graycore/magento2-graphql-introspection-cache)
[![Packagist License](https://img.shields.io/packagist/l/graycore/magento2-graphql-introspection-cache)](https://github.com/graycoreio/magento2-graphql-introspection-cache/blob/main/LICENSE)
[![Unit Test](https://github.com/graycoreio/magento2-graphql-introspection-cache/actions/workflows/unit.yaml/badge.svg)](https://github.com/graycoreio/magento2-graphql-introspection-cache/actions/workflows/unit.yaml)
[![Integration Test](https://github.com/graycoreio/magento2-graphql-introspection-cache/actions/workflows/integration.yaml/badge.svg)](https://github.com/graycoreio/magento2-graphql-introspection-cache/actions/workflows/integration.yaml)

</div>


This module allows you to use the same mechanism that is used for caching regular GraphQL resolvers, for introspection queries.

This helps minimize the number of times Magento is bootstrapped.

The following introspection types are supported out of the box:

- ProductAttributeFilterInput

This module is **experimental**, it contains BC-fixes for older Magento versions (<2.4.2). Class internals will likely change when support for those versions end.

## Getting Started
This module is intended to be installed with [composer](https://getcomposer.org/). From the root of your Magento 2 project:

1. Download the package
```bash
composer require graycore/magento2-graphql-introspection-cache
```
2. Enable the package

```bash
./bin/magento module:enable GraphQlIntrospectionCache
```

## Usage

You can add your own introspection cache identity by adding a bit of di.xml:

```xml
<type name="Graycore\GraphQlIntrospectionCache\Plugin\CachePlugin">
    <arguments>
        <argument xsi:type="array" name="introspectionHandlers">
            <item xsi:type="object" name="NameOfInspectedType">Namespace\Module\Model\Cache\Identity\Introspection\NameOfInspectedType</item>
        </argument>
    </arguments>
</type>
```

Your class must implement `Magento\Framework\GraphQl\Query\Resolver\IdentityInterface`.

For more information on GraphQL Cache Identities, please visit the [official documentation](https://devdocs.magento.com/guides/v2.4/graphql/develop/identity-class.html).

## Internals

- A before plugin is created on `QueryProcessor::process` to set a custom `ReferenceExecutor`. This `ReferenceExecutor` is instantiated using the Object Manager, so that we can use plugins on it in a later stage.
- An after plugin is created on `ReferenceExecutor::doExecute`. This plugin determines if we are dealing with an introspection request and if there is a cache identity registered for it. If there is, it will attempt to generate cache tags and add it to the request.

Check out the unit/integration tests to get a better picture.
