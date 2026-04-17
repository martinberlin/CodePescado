<?php

namespace App\CompilerPass;

use App\Channel\NotificationChannelRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class NotificationChannelPass implements CompilerPassInterface
{
    public const TAG = 'app.notification_channel';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(NotificationChannelRegistry::class)
            && !$container->hasAlias(NotificationChannelRegistry::class)
        ) {
            return;
        }

        $definition = $container->findDefinition(NotificationChannelRegistry::class);

        $channels = [];
        foreach ($container->findTaggedServiceIds(self::TAG) as $id => $tags) {
            $channels[] = new Reference($id);
        }

        // Replace the first constructor argument
        $definition->setArgument(0, $channels);
    }
}
