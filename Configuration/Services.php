<?php

namespace Sys25\RnBase;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Sys25\RnBase\Frontend\Controller\AbstractAction;

return function (ContainerConfigurator $container, ContainerBuilder $containerBuilder) {
    $containerBuilder->registerForAutoconfiguration(AbstractAction::class)->addTag('rnbase.frontend.action');
    $containerBuilder->addCompilerPass(new DependencyInjection\FrontendServicesPass());
};
