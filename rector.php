<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Rector\ValueObject\PhpVersion;
use RectorLaravel\Rector\ClassMethod\MigrateToSimplifiedAttributeRector;
use RectorLaravel\Set\LaravelSetList;
use RectorLaravel\Set\LaravelSetProvider;

return RectorConfig::configure()
    ->withSetProviders(LaravelSetProvider::class)
    ->withSets([
        LaravelSetList::LARAVEL_120,
        LaravelSetList::LARAVEL_110,
        LaravelSetList::LARAVEL_100,
        LaravelSetList::LARAVEL_90,
        LaravelSetList::LARAVEL_80,
        LaravelSetList::LARAVEL_70,
        LaravelSetList::LARAVEL_60,

        LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL,
        LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
        LaravelSetList::LARAVEL_FACTORIES,
        LaravelSetList::LARAVEL_IF_HELPERS,
        LaravelSetList::LARAVEL_LEGACY_FACTORIES_TO_CLASSES,

        LaravelSetList::LARAVEL_TESTING,
        LaravelSetList::LARAVEL_TYPE_DECLARATIONS,
    ])
    ->withImportNames(
        removeUnusedImports: true
    )
    ->withComposerBased(laravel: true)
    ->withCache(
        cacheDirectory: '/tmp/rector',
        cacheClass: FileCacheStorage::class,
    )
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap/app.php',
        __DIR__ . '/bootstrap/providers.php',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/public',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->withTypeCoverageDocblockLevel(1)
    // uncomment to reach your current PHP version
    ->withPhpSets(php84: true)
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withAttributesSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        strictBooleans: false,
        // carbon: true,
        rectorPreset: true,
    )
    ->withSkip([
        AddOverrideAttributeToOverriddenMethodsRector::class,
        EncapsedStringsToSprintfRector::class,
        PostIncDecToPreIncDecRector::class,
        RemoveConcatAutocastRector::class,
        MigrateToSimplifiedAttributeRector::class,
        __DIR__ . '/public',
        // AddClosureVoidReturnTypeWhereNoReturnRector::class,
        // NullToStrictStringFuncCallArgRector::class,
        // ShortenElseIfRector::class,
        // SimplifyEmptyCheckOnEmptyArrayRector::class,
        // DisallowedEmptyRuleFixerRector::class,
        // CombineIfRector::class,
        // FlipTypeControlToUseExclusiveTypeRector::class,
        // ExplicitBoolCompareRector::class,
        // SeparateMultiUseImportsRector::class,
        // NullableCompareToNullRector::class,
        // // DeclareStrictTypesRector::class,
    ]);
