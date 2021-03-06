# eZ Publish & Sylius field type and datatype implementation

## About

This repo contains integration point of eZ Publish 5 with Sylius E-Commerce, which is a continuation of the [eZ Publish Sylius integration](https://github.com/netgen/ezpublish-community-sylius).

This includes eZ Publish field type and legacy datatype which provide the ability to create and edit Sylius products via eZ Publish interface (either legacy or public API).

## Installation

For installation instructions, see [installation instructions of the main integration repo](https://github.com/netgen/ezpublish-community-sylius/blob/sylius_integration/INSTALL.md).

## Usage

For usage, see [USAGE.md](https://github.com/netgen/NetgenEzSyliusBundle/blob/master/USAGE.md).

## Features

* Both field type and legacy type have been developed and suppported. This means it is possible to create eZ Publish content with the `sylius_product` datatype in the legacy administration or with public API, and by publishing them, products in Sylius database are created/updated.
* Translation of products as the related eZ Publish content is translated.
* Sort clause for sorting by product number (SKU) has been implemented.
* Trash and untrash slots have been implemented in order to handle deleting and recovering products in Sylius database.
* Url generator, which can generate eZ Publish url alias from Sylius product entity (useful for linking to eZ content from Sylius front-end; eg. from cart).

## Known issues

* Due to the fact that Sylius uses lazy loading of translations, there have been issues with eZ Publish properly caching translations in the SPI cache. Therefore, `ORMTranslatableListener` has been overriden to enable eager loading of translations for all entites that are extending `Sylius\Component\Core\Model\Product`.

This could cause performance issues if there is a large number of languages used on a site.

* Currently, Sylius product attributes that are exposed through eZ Publish field type are limited and hard coded. Future releases should expose configuration which would make it possible to determine which fields are to be manipulated through eZ Publish interface.


## Copyright

* Copyright (C) 2015 Locastic. All rights reserved.
* Copyright (C) 2015 Netgen. All rights reserved.

## License

* http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
