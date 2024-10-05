<?php

namespace Sys25\RnBase;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Sys25\RnBase\Domain\Repository\AbstractRepository;
use Sys25\RnBase\Frontend\Controller\AbstractAction;
use Sys25\RnBase\Frontend\View\AbstractView;

return function (ContainerConfigurator $container, ContainerBuilder $containerBuilder) {
    $containerBuilder->registerForAutoconfiguration(AbstractRepository::class)->addTag(AbstractRepository::SERVICE_TAG);
    $containerBuilder->addCompilerPass(new DependencyInjection\DomainRepositoryPass());

    $containerBuilder->registerForAutoconfiguration(AbstractAction::class)->addTag(AbstractAction::SERVICE_TAG);
    $containerBuilder->registerForAutoconfiguration(AbstractView::class)->addTag(AbstractView::SERVICE_TAG);
    $containerBuilder->addCompilerPass(new DependencyInjection\FrontendServicePass());
};
