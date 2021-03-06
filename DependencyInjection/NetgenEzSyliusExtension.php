<?php

namespace Netgen\Bundle\EzSyliusBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NetgenEzSyliusExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load( array $configs, ContainerBuilder $container )
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration( $configuration, $configs );

        $container->setParameter( 'netgen_ez_sylius.routing.generate_url_aliases', $config['routing']['generate_url_aliases'] );

        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
        $loader->load( 'services.yml' );
        $loader->load( 'override.yml' );
        $loader->load( 'fieldtypes.yml' );
        $loader->load( 'storage_engines.yml' );

        if ( $container->hasParameter( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.common.field.class' ) )
        {
            $loader->load( 'search_old.yml' );
        }
        else
        {
            $loader->load( 'search.yml' );
        }
    }
}
