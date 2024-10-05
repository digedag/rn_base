<?php

namespace Sys25\RnBase\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Sys25\RnBase\Domain\Repository\AbstractRepository;
use Sys25\RnBase\Domain\Repository\RepositoryRegistry;

class DomainRepositoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // always first check if the primary service is defined
        if (!$container->has(RepositoryRegistry::class)) {
            return;
        }

        $definition = $container->findDefinition(RepositoryRegistry::class);

        // find all service IDs with the t3sports.stats.indexer tag
        $taggedServices = $container->findTaggedServiceIds(AbstractRepository::SERVICE_TAG);

        foreach ($taggedServices as $id => $tags) {
            // add the indexer to the IndexerProvider service
            $definition->addMethodCall('addRepository', [new Reference($id)]);
        }
    }
}
