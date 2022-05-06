# Inpsyde WooCommerce Multisite Persistent Cart

Hotfix for WooCommerce [#12534](https://github.com/woocommerce/woocommerce/issues/12534)

The plugin stores carts persistent in the user meta `_woocommerce_persistent_cart_<SITE_ID>` to avoid collisions on multiple shops in a multisite network.


## UNMAINTAINED
**Note:** This version of the plugin is not maintained anymore.

## Commands


## Installation

Install this plugin via composer:

```
$ composer require inpsyde/woocommerce-multisite-persistent-cart
```

## Usage

Just install and activate the plugin, nothing more to do.

## Unit tests
Install PHPUnit via [Phive](https://phar.io/):

```
$ phive install
```

and run `$ tests/bin/phpunit`.

Alternatively can also install phpunit via Composer:

```
$ composer global require phpunit/phpunit
```

## Crafted by Inpsyde

The team at [Inpsyde](http://inpsyde.com) is engineering the Web since 2006.

## License

Copyright (c) 2016 David Naber, Inpsyde

Good news, this plugin is free for everyone! Since it's released under the [MIT License](LICENSE) you can use it free of charge on your personal or commercial website.

## Contributing

All feedback / bug reports / pull requests are welcome.
