<?php

namespace Sys25\RnBase\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Sys25\RnBase\Frontend\Provider\FrontendServiceProvider;

class FrontendServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // always first check if the primary service is defined
        if (!$container->has(FrontendServiceProvider::class)) {
            return;
        }

        $definition = $container->findDefinition(FrontendServiceProvider::class);

        // find all service IDs with the rnbase.frontend.action tag
        $taggedServices = $container->findTaggedServiceIds('rnbase.frontend.action');

        foreach ($taggedServices as $id => $tags) {
            // add the indexer to the IndexerProvider service
            $definition->addMethodCall('addAction', [new Reference($id)]);
        }
    }
}
