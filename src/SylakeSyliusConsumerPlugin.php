<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin;

use Sylake\SyliusConsumerPlugin\DependencyInjection\Compiler\ProductPostprocessorPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SylakeSyliusConsumerPlugin extends Bundle
{
    use SyliusPluginTrait;

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ProductPostprocessorPass());
    }
}
