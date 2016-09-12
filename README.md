# ![dePHPend logo](http://mike-on-a-bike.com/dephpend-logo.png) 

> Detect flaws in your architecture, before they drag you down into the depths of dependency hell ...

[![Travis](https://img.shields.io/travis/mihaeu/php-dependencies.svg?maxAge=2592000)]() [![Coveralls](https://img.shields.io/coveralls/mihaeu/php-dependencies.svg?maxAge=2592000)]() ![License MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=flat) [![Gitter](https://img.shields.io/gitter/room/mihaeu/php-dependencies.svg?maxAge=2592000&style=flat)]()

> **DISCLAIMER**
>
> This project is in an early stage and many things aren't working as expected
> and lots of information is not being picked up yet. Star the project and check
> in every now and then, as development is very active. New major releases will
> be released at the end of every month.

## What it does

dePHPend helps with bringing your PHP projects back in shape. Over the course
of a project, we usually keep adding more and more dependencies. Often hidden 
behind singletons or service locators, these dependencies can quickly become 
a maintenance (and testing!) nightmare.

dePHPend analyses your app and attempts to find everything you depend on.

With this information you can:

 - get a quick overview of how an application is structured
 - start refactoring where it's needed the most
 - track architecture violations (maybe your view shouldn't be telling the model what to do?)
 - find out why your changes are breaking tests

## Installation

### Phar (recommended)

When this is more mature, I'm going to sign the phar and set it up with [Phive](https://phar.io/).

Until then just download the phar file by clicking [here](http://phar.dephpend.com/dephpend.phar) or use

```bash
wget http://phar.dephpend.com/dephpend.phar
```

### Others

You could `git clone` or `composer require` this, but it's best to not mix tools and software dependencies (because those have dependencies on their own).

## Usage

You should almost always run QA tools without XDebug (unless you need code coverage of course). You could use a separate `php.ini` where XDebug is not loaded and pass that to php or you just use the `-n` option (this will however not load any extensions). 

```
# or bin/dephpend depending on how you installed this
$ php -n dephpend.phar                                                                                                 
      _      _____  _    _ _____               _ 
     | |    |  __ \| |  | |  __ \             | |
   __| | ___| |__) | |__| | |__) |__ _ __   __| |
  / _` |/ _ \  ___/|  __  |  ___/ _ \ '_ \ / _` |
 | (_| |  __/ |    | |  | | |  |  __/ | | | (_| |
  \__,_|\___|_|    |_|  |_|_|   \___|_| |_|\__,_| version 0.1

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  dsm            Generate a Dependency Structure Matrix of your dependencies
  help           Displays help for a command
  list           Lists commands
  metrics        Generate dependency metrics
  test-features  Test support for dependency detection
  text           Generate a Dependency Structure Matrix of your dependencies
  uml            Generate a UML Class diagram of your dependencies
```

### Filters

Without filters the output for large apps is too bloated which is why I implemented a couple of filters to help you get the output you want:

```bash
      --internals                          Check for dependencies from internal PHP Classes like SplFileInfo.
      --underscore-namespaces              Parse underscores in Class names as namespaces.
      --filter-namespace=FILTER-NAMESPACE  Analyse only classes from this namespace.
      --no-classes                         Remove all classes and analyse only namespaces.
```

For more info just run `php dephpend.phar help text`.

### Text

For quick debugging use the `text` command. Say you want to find out which classes depend on XYZ and what XYZ depends on, you'd run: 

```bash
php -n dephpend.phar text src | grep XYZ
```

### UML

Generates UML class or package diagrams of your source code. Requires [PlantUML](http://plantuml.com/) to be installed.

You can either run 

```bash
php -n dephpend.phar uml --output=uml.png src
``` 

but most likely what you want to do is to use the `--depth[=DEPTH]` option. If your app has more than 20 classes, the UML will become messy if you don't use namespace instead of class level. Experiment with different depth values, but usually a depth of 2 or 3 is what you want.

### Dependency Structure Matrix

If you've tried decrypting massive UML diagrams before, you know that they become very hard to interpret for large applications. DSMs allow you to get a quick overview of your application and where dependency hotspots are.

This feature is still under rework and right now it's not really fun to use. If you still want to try run 

```bash
php -n dephpend.phar dsm src > dependencies.html
``` 
or pipe it to something like [bcat](https://rtomayko.github.io/bcat/).

### Metrics

The most common package metrics have already been implemented, but there are more to come. Check them out by running the following command:

```bash
php -n dephpend.phar metrics src
```

## How it all works

Basically the process can be broken down into four steps (the actual work is a bit more complicated and for those interested, I'll publish a paper about it, later this year):

 - find all relevant PHP files
 - generate an abstract syntax tree using [php-parser]() by the awesome Nikita Popov
 - traverse the tree, gathering dependencies along the way
 - pass the information to a formatter

## Supported Features

Check out `tests/features` for examples of supported features or run `bin/dephpend test-features` for a list of supported detection features:

```
[✓]  creating objects
[✓]  using traits
[✓]  extending other classes
[✓]  type hints in method arguments
[✓]  implementing interfaces
[✗]  return value of known method
[✗]  method arguments and return value from doc
[✗]  singleton
[✗]  return values of methods
[✗]  known variable passed into method without type hints
[✗]  creating objects from strings
```

## License

See [LICENSE](LICENSE) file
