<?php

namespace Netgen\Bundle\EzSyliusBundle\EventListener;

use Doctrine\ORM\Mapping\ClassMetadata;
use Sylius\Bundle\TranslationBundle\EventListener\ORMTranslatableListener as BaseListener;

class ORMTranslatableListener extends BaseListener
{
    /**
     * Add mapping data to a translatable entity.
     *
     * @param ClassMetadata $metadata
     */
    private function mapTranslatable(ClassMetadata $metadata)
    {
        $className = $metadata->name;

        try {
            $resourceMetadata = $this->registry->getByClass($className);
        } catch (\InvalidArgumentException $exception) {
            return;
        }

        if (!$resourceMetadata->hasParameter('translation')) {
            return;
        }

        $fetchType = ClassMetadata::FETCH_EXTRA_LAZY;
        if ( $metadata->rootEntityName == 'Sylius\Component\Core\Model\Product' )
        {
            $fetchType = ClassMetadata::FETCH_EAGER;
        }

        $translationResourceMetadata = $this->registry->get($resourceMetadata->getAlias().'_translation');

        $metadata->mapOneToMany([
            'fieldName' => 'translations',
            'targetEntity' => $translationResourceMetadata->getClass('model'),
            'mappedBy' => 'translatable',
            'fetch' => $fetchType,
            'indexBy' => 'locale',
            'cascade' => ['persist', 'merge', 'remove'],
            'orphanRemoval' => true,
        ]);
    }

}
