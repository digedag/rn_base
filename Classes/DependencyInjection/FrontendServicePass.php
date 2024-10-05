<?php

namespace Sys25\RnBase\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Sys25\RnBase\Frontend\Controller\AbstractAction;
use Sys25\RnBase\Frontend\Service\FrontendServiceProvider;
use Sys25\RnBase\Frontend\View\AbstractView;

class FrontendServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // always first check if the primary service is defined
        if (!$container->has(FrontendServiceProvider::class)) {
            return;
        }

        $definition = $container->findDefinition(FrontendServiceProvider::class);

        // find all tagged service IDs
        $taggedServices = $container->findTaggedServiceIds(AbstractAction::SERVICE_TAG);

        foreach ($taggedServices as $id => $tags) {
            // add the service to the ServiceProvider service
            $definition->addMethodCall('addFrontendAction', [new Reference($id)]);
        }

        // find all tagged service IDs
        $taggedServices = $container->findTaggedServiceIds(AbstractView::SERVICE_TAG);

        foreach ($taggedServices as $id => $tags) {
            // add the service to the ServiceProvider service
            $definition->addMethodCall('addFrontendView', [new Reference($id)]);
        }
    }
}
