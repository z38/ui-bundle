# UI Bundle

[![Build Status](https://travis-ci.org/z38/ui-bundle.png?branch=master)](https://travis-ci.org/z38/ui-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/z38/ui-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/z38/ui-bundle/?branch=master)

This bundle provides various helpers to ease development of multi-bundle applications. All helpers are modular and can be enabled separately.


## Installation

Install with [Composer](https://getcomposer.org):

    $ composer require z38/ui-bundle

Then register the bundle in the `AppKernel.php` file:

```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Z38\Bundle\UiBundle(),
        // ...
    );

    return $bundles;
}
```

## Credits

Many thanks go out to [Oro Inc.](http://www.orocrm.com) which built most of the features as part of [OroUIBundle](https://github.com/orocrm/platform/src/Oro/Bundle/UIBundle).
