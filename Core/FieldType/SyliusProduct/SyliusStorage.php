<?php

namespace Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct;

use Doctrine\ORM\EntityManager;
use Mapping\Fixture\Xml\Sluggable;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Gedmo\Sluggable\SluggableListener;
use eZ\Publish\SPI\FieldType\FieldStorage as BaseStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\URLAliasService;

class SyliusStorage implements BaseStorage
{
    protected $repository;
    protected $manager;
    protected $sluggable_listener;
    protected $contentService;
    protected $locationService;
    protected $urlAliasService;

    public function __construct(RepositoryInterface $repository,
                                EntityManager $manager,
                                SluggableListener $listener,
                                ContentService $contentService,
                                LocationService $locationService,
                                URLAliasService $urlAliasService)
    {
        $this->repository = $repository;
        $this->manager = $manager;
        $this->sluggable_listener = $listener;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->urlAliasService = $urlAliasService;
    }

    /**
     * Stores value for $field in an external data source.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return mixed null|true
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $data = $field->value->externalData;

        $name = $data['name'];
        $price = $data['price'];
        $desc = $data['description'];
        $slug = $data['slug'];
        $available_on = $data['available_on'];

        //check if sylius product already exists
        $product = $this->repository->find($field->value->data['sylius_id']);
        if(!$product)
            $product = $this->repository->findOneBy( array('slug' => $slug) );

        if (!$product)
        {
            $product = $this->repository->createNew();
        }

        /** @var \Sylius\Component\Core\Model\Product $product */
        $product
            ->setName( $name )
            ->setDescription( $desc )
            ->setPrice( $price )
            ->setSlug( $slug )
            ->setAvailableOn($available_on);

        // custom transliterator
        $this->sluggable_listener->setTransliterator(array('Netgen\EzSyliusBundle\Util\Urlizer', 'transliterate'));
        $this->sluggable_listener->setUrlizer(array('Netgen\EzSyliusBundle\Util\Urlizer', 'urlize'));

        $this->manager->persist($product);
        $this->manager->flush();

        // fetch product again to get id
        $product = $this->repository->findOneBy(array('slug' => $slug ));
        $productId = $product->getId();
        $field->value->data['sylius_id'] = $productId;

        return true;
    }

    /**
     * Populates $field value property based on the external data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return void
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        /** @var \Sylius\Component\Core\Model\Product $product */
        $product = $this->repository->find($field->value->data['sylius_id']);
        if (!empty($product)) {
            $name = $product->getName();
            $price = $product->getPrice();
            $description = $product->getDescription();
            $slug = $product->getSlug();
            $available_on = $product->getAvailableOn();

            $field->value->externalData = array(
                'name' => $name,
                'price' => $price,
                'description' => $description,
                'slug' => $slug,
                'available_on' => $available_on
            );
        }
    }

    /**
     * Deletes field data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds Array of field IDs
     * @param array $context
     *
     * @return boolean
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {
        $fields = $this->contentService->loadContentByVersionInfo($versionInfo)->getFields();

        foreach($fields as $field)
        {
            if (in_array($field->id, $fieldIds))
            {
                /**@var \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value */
                $value = $field->value;
                $syliusId = $value->syliusId;

                if (!empty ($syliusId))
                {
                    $product = $this->repository->find($syliusId);

                    $this->manager->remove($product);
                    $this->manager->flush();
                }
            }
        }
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return boolean
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * Get index data for external data for search backend
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Field[]
     */
    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
        return false;
    }

    /**
     * This method is used exclusively by Legacy Storage to copy external data of existing field in main language to
     * the untranslatable field not passed in create or update struct, but created implicitly in storage layer.
     *
     * By default the method falls back to the {@link \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()}.
     * External storages implement this method as needed.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Field $originalField
     * @param array $context
     *
     * @return null|boolean Same as {@link \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()}.
     */
    public function copyLegacyField( VersionInfo $versionInfo, Field $field, Field $originalField, array $context )
    {
        return $this->storeFieldData( $versionInfo, $field, $context );
    }
}