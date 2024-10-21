<?php

use NITSAN\NsT3Ai\Service\NsT3AiContentService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use NITSAN\NsT3AiLocalization\Controller\T3AiLocalizationController;
use NITSAN\NsT3AiLocalization\Domain\Repository\LocalizationLogRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;


return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void {
    global $typo3VersionArray;
    $typo3VersionArray = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionStringToArray(
        \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version()
    );

    $services = $containerConfigurator->services();
    $services->defaults()
        ->private()
        ->autowire()
        ->autoconfigure();

    $services->load('NITSAN\\NsT3AiLocalization\\', __DIR__ . '/../Classes/')
        ->exclude([
            __DIR__ . '/../Classes/Domain/Model',
        ]);

    $services->set(T3AiLocalizationController::class)
        ->arg('$contentService', new ReferenceConfigurator(NsT3AiContentService::class))
        ->arg('$localizationlogRepository', new ReferenceConfigurator(LocalizationLogRepository::class))
        ->public();

};
