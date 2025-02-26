# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

## [0.2.0](https://github.com/graycoreio/magento2-graphql-introspection-cache/compare/v0.1.2...v0.2.0) (2025-02-26)


### ⚠ BREAKING CHANGES

* We no longer support php 7.4 as it is end of life.

### Features

* compat with GraphQL\Executor\ReferenceExecutor from webonyx/graphql-php:15.18.1 ([b553e47](https://github.com/graycoreio/magento2-graphql-introspection-cache/commit/b553e478482c268e7d925b2c2c37c65ece63ffa7))
* drop support for php 7.4 ([ff3e180](https://github.com/graycoreio/magento2-graphql-introspection-cache/commit/ff3e1806b04077639b32a0cfffc0e2ac0b93f746))


### Bug Fixes

* 8.2 deprecation of constructor existence check ([db26ebb](https://github.com/graycoreio/magento2-graphql-introspection-cache/commit/db26ebb2197ca40d49fbc104f643135667c9ea5d))
* add check for Executor::getDefaultArgsMapper for older versions ([d460fca](https://github.com/graycoreio/magento2-graphql-introspection-cache/commit/d460fca3c0e488258d7ab130a41c84f5dc6144a9))
* backwards compat with webonyx 14.11.0 for v2.4.5-p11 and v2.4.4-p12 ([1484b89](https://github.com/graycoreio/magento2-graphql-introspection-cache/commit/1484b8920321e89fa8a102484505b14cbc28e934))


### Miscellaneous Chores

* add dealerdirect/phpcodesniffer-composer-installer allowed plugin ([50b990a](https://github.com/graycoreio/magento2-graphql-introspection-cache/commit/50b990aec401e2e44f946d1d36215efe46f0a113))

### [0.1.2](https://github.com/graycoreio/magento2-graphql-introspection-cache/compare/v0.1.1...v0.1.2) (2022-08-25)


### Bug Fixes

* ensure reference executor always returns a new object ([#6](https://github.com/graycoreio/magento2-graphql-introspection-cache/issues/6)) ([9daede3](https://github.com/graycoreio/magento2-graphql-introspection-cache/commit/9daede31b3de7cab58973bdb54ed9a636560bd01))

### 0.1.1 (2022-07-21)


### Features

* add readme/license ([#3](https://github.com/graycoreio/magento2-graphql-introspection-cache/issues/3)) ([f74262a](https://github.com/graycoreio/magento2-graphql-introspection-cache/commit/f74262ae249834f1fed3a82ece2c66a17719c777))
* allow executor plugin to work with older webonyx graphql versions ([#2](https://github.com/graycoreio/magento2-graphql-introspection-cache/issues/2)) ([1501383](https://github.com/graycoreio/magento2-graphql-introspection-cache/commit/1501383fd3adaa34d8a2a29ab94abbfecd1a917c))
