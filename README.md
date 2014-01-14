Phar Replace
============

[![Build Status][]](https://travis-ci.org/phine/lib-phar-replace)
[![Coverage Status][]](https://coveralls.io/r/phine/lib-phar-replace)
[![Latest Stable Version][]](https://packagist.org/packages/phine/phar-replace)
[![Total Downloads][]](https://packagist.org/packages/phine/phar-replace)

Adds search and replace support for the phar library.

Requirement
-----------

- PHP >= 5.3.3
- [Phine Exception][] >= 1.0.0
- [Phine Observer][] >= 2.0
- [Phine Phar][] >= 1.0.0

Installation
------------

Via [Composer][]:

    $ composer require "phine/phar-replace=~1.0"

Usage
-----

The library provides a single subject observer for lib-phar. This observer can
be registered to the following subjects in order to perform a global search and
replace of one or more search strings:

- `Builder::ADD_FILE`
- `Builder::ADD_STRING`

To create an observer, you will need a new instance of `ReplaceObserver`.

```php
use Phine\Phar\Builder;
use Phine\Phar\Replace\ReplaceObserver;

// create the archive builder
$builder = Builder::create('example.phar');

// create the replace observer
$observer = new ReplaceObserver(
    array(
        'search' => 'replace',
        '@search@' => 'replace',
        '{{ search }}' => 'replace',
        // ...etc...
    )
);

// register it with the builder subjects
$builder->observe(Builder::ADD_FILE, $observer);
$builder->observe(Builder::ADD_STRING, $observer);
```

With the observer registered, any file or string added will all of its
`search`, `@search@`, `{{ search }}` string occurrences replaced with the
value `replace`.

> It may be important to note that only scalar values are supported.

You may also set search strings and replacement values after the observer
has been created by calling either `setSearchValue()` or `setSearchValues()`:

```php
// add a value
$observer->setSearchValue('search string', 'replacement value');

// replace a value
$observer->setSearchValue('search string', 'a different value');

// replace all search strings and their values
$observer->setSearchValues(
    array(
        'search' => 'replace',
        '@search@' => 'replace',
        '{{ search }}' => 'replace',
        // ...etc...
    )
);
```

By using `setSearchValues()`, you will be removing all previous search strings
and their replacement values. Only the new search strings and replacement values
provided will be used.

Documentation
-------------

You can find the API [documentation here][].

License
-------

This library is available under the [MIT license](LICENSE).

[Build Status]: https://travis-ci.org/phine/lib-phar-replace.png?branch=master
[Coverage Status]: https://coveralls.io/repos/phine/lib-phar-replace/badge.png
[Latest Stable Version]: https://poser.pugx.org/phine/phar-replace/v/stable.png
[Total Downloads]: https://poser.pugx.org/phine/phar-replace/downloads.png
[Phine Exception]: https://github.com/phine/lib-exception
[Phine Observer]: https://github.com/phine/lib-observer
[Phine Phar]: https://github.com/phine/lib-phar
[Composer]: http://getcomposer.org/
[documentation here]: http://phine.github.io/lib-phar-replace
