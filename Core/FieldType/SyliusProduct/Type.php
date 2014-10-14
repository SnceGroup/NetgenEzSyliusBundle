<?php

namespace Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;


class Type extends FieldType
{

    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'syliusproduct';
    }

    /**
     * Returns a human readable string representation from the given $value
     * It will be used to generate content name and url alias if current field
     * is designated to be used in the content name/urlAlias pattern.
     *
     * @param \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     *
     * @return integer
     */
    public function getName( SPIValue $value )
    {
        return $value->name;
    }

    /**
     * Returns the empty value for this field type.
     *
     * @return \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     *
     * @return bool
     */
    protected function getSortInfo( BaseValue $value )
    {
        return $value->price;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param mixed $inputValue
     *
     * @return \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value The potentially converted input value.
     */
    protected function createValueFromInput( $inputValue )
    {
        if (is_array($inputValue))
        {
            if ( empty($inputValue['price']) || empty($inputValue['name']) )
                return $inputValue;

            $inputValue = new Value($inputValue['price'], $inputValue['name'], null);

            if (!empty($inputValue['description']))
            {
                $inputValue->description = $inputValue['description'];
            }

            if (!empty($inputValue['sylius_id']))
            {
                $inputValue->syliusId = $inputValue['sylius_id'];
            }

            if (!empty($inputValue['slug']))
            {
                $inputValue->slug = $inputValue['slug'];
            }

            if (!empty($inputValue['available_on']) && $inputValue['available_on'] instanceof \DateTime )
            {
                $inputValue->available_on = $inputValue['available_on'];
            }
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     */
    protected function checkValueStructure( BaseValue $value )
    {
        if (!is_int($value->price) && $value->price < 0)
        {
            throw new InvalidArgumentType(
                '$value->price',
                'integer',
                $value->price);
        }
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @throws \Exception
     *
     * @return \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value
     */
    public function fromHash( $hash )
    {
        if ( !is_array( $hash ) && !empty($hash['name']) )
        {
            return new Value();
        }

        $value = new Value();

        if (!empty($hash['price']))
            $value->price = $hash['price'];

        if (!empty($hash['name']))
            $value->name = $hash['name'];

        if (!empty($hash['sylius_id']))
            $value->syliusId = $hash['sylius_id'];

        if (!empty($hash['description']))
            $value->description = $hash['description'];

        if (!empty($hash['slug']))
            $value->slug = $hash['slug'];

        if (!empty($hash['available_on']))
            $value->available_on = $hash['available_on'];

        return $value;
    }

    /**
     * Converts the given $value into a plain hash format
     *
     * @param \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     *
     * @return array
     */
    public function toHash( SPIValue $value )
    {
        if (empty($value->price) || empty($value->name) || empty($value->syliusId) )
            return null;

        return array( 'price' => $value->price,
                      'name' => $value->name,
                      'sylius_id' => $value->syliusId,
                      'description' => $value->description,
                      'slug' => $value->slug,
                      'available_on' => $value->available_on);
    }

    /**
     * @param \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( SPIValue $value )
    {
        return new FieldValue(
            array(
                "data" => $this->ezToHash($value),
                "externalData" => $this->syliusToHash($value),
                "sortKey" => $this->getSortInfo( $value ),
            )
        );
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     * @return \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        if ( $fieldValue->data === null )
        {
            return $this->getEmptyValue();
        }
        $value = new Value();
        $value->name = $fieldValue->externalData['name'];
        $value->price = $fieldValue->externalData['price'];
        $value->description = $fieldValue->externalData['description'];
        $value->slug = $fieldValue->externalData['slug'];
        $value->syliusId = $fieldValue->data['sylius_id'];
        $value->available_on = $fieldValue->externalData['available_on'];

        return $value;
    }

    /**
     * Returns hash of values to be stored in eZ database
     *
     * @param \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     * @return array
     */
    private function ezToHash($value)
    {
        return array(
            'sylius_id' => $value->syliusId
        );
    }

    /**
     * Returns hash of values to be stored in sylius database
     *
     * @param \Netgen\EzSyliusBundle\Core\FieldType\SyliusProduct\Value $value
     * @return array
     */
    private function syliusToHash($value)
    {
        return array(
            'name' => $value->name,
            'price' => $value->price,
            'description' => $value->description,
            'slug' => $value->slug,
            'available_on' => $value->available_on
        );
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return true;
    }
}