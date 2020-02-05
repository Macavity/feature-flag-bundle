<?php

declare(strict_types=1);

namespace Shopping\FeatureFlagBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('shopping_feature_flag');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('providers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('cookie')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('values')
                                    ->defaultValue([])
                                    ->useAttributeAsKey('featureFlag')
                                    ->arrayPrototype()
                                        ->isRequired()
                                        ->beforeNormalization()->castToArray()->end()
                                        ->scalarPrototype()
                                            ->beforeNormalization()->always(function ($value) { return (string) $value; })->end()
                                            ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('userAgent')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('values')
                                    ->defaultValue([])
                                    ->useAttributeAsKey('featureFlag')
                                    ->arrayPrototype()
                                        ->isRequired()
                                        ->beforeNormalization()->castToArray()->end()
                                        ->scalarPrototype()
                                            ->beforeNormalization()->always(function ($value) { return (string) $value; })->end()
                                            ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
