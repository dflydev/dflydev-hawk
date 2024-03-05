<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths(
        [
            __DIR__ . '/src',
            __DIR__ . '/tests',
        ]
    );

    $rectorConfig->sets(
        [
            PHPUnitSetList::PHPUNIT_100,
            PHPUnitSetList::PHPUNIT_CODE_QUALITY,
            LevelSetList::UP_TO_PHP_81,
            DowngradeLevelSetList::DOWN_TO_PHP_81,
            SetList::CODE_QUALITY,
            SetList::TYPE_DECLARATION,
        ]
    );

    $rectorConfig->importNames();
};
