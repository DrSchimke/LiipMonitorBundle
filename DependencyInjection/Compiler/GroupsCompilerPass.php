<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class GroupsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('liip_monitor.runner')) {
            return;
        }

        $defaultGroup = $container->getParameter('liip_monitor.default_group');

        $definition = $container->getDefinition('liip_monitor.runner');
        $container->removeDefinition('liip_monitor.runner');
        $container->setDefinition('liip_monitor.runner_' . $defaultGroup, $definition);
        $container->setAlias('liip_monitor.runner', 'liip_monitor.runner_' . $defaultGroup);

        foreach ($container->findTaggedServiceIds('liip_monitor.check') as $id => $tags) {
            foreach ($tags as $attributes) {
                if (isset($attributes['group'])) {
                    $container->setDefinition('liip_monitor.runner_' . $attributes['group'], $definition);
                }
            }
        }

        foreach ($container->findTaggedServiceIds('liip_monitor.check_collection') as $id => $tags) {
            foreach ($tags as $attributes) {
                if (isset($attributes['group'])) {
                    $container->setDefinition('liip_monitor.runner_' . $attributes['group'], $definition);
                }
            }
        }
    }
}
