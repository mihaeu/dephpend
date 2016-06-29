# ![logo](http://mike-on-a-bike.com/dephpend-logo.png) 

> Detect flaws in your architecture, before they drag you down into the depths of dependency hell ...

[![Build Status](https://travis-ci.com/mihaeu/php-dependencies.svg?token=6E2gXvaZaEh2XxFCPhrX&branch=develop)](https://travis-ci.com/mihaeu/php-dependencies) ![License MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=flat) [![Gitter](https://img.shields.io/gitter/room/mihaeu/php-dependencies.svg?maxAge=2592000&style=flat)]()

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

Until then just download the phar file by clicking [here](http://mike-on-a-bike.com/php-dependencies.phar) or use

```bash
wget http://mike-on-a-bike.com/php-dependencies.phar
```

### Others

You could `git clone` or `composer require` this, but it's best to not mix tools and software dependencies (because those have dependencies on their own).

## Usage 

```bash
# or bin/php-dependencies, depending on how you installed this
$ php php-dependencies.phar                                                                                                 
        _      _____  _    _ _____               _ 
       | |    |  __ \| |  | |  __ \             | |
     __| | ___| |__) | |__| | |__) |__ _ __   __| |
    / _` |/ _ \  ___/|  __  |  ___/ _ \ '_ \ / _` |
   | (_| |  __/ |    | |  | | |  |  __/ | | | (_| |
    \__,_|\___|_|    |_|  |_|_|   \___|_| |_|\__,_|
  
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
    dsm   Generate a Dependency Structure Matrix of your dependencies
    help  Displays help for a command
    list  Lists commands
    text  Generate a Dependency Structure Matrix of your dependencies
    uml   Generate a UML Class diagram of your dependencies
```

## How it all works

Basically the process can be broken down into four steps (the actual work is a bit more complicated and for those interested, I'll publish a paper about it, later this year):

 - find all relevant PHP files
 - generate an abstract syntax tree using [php-parser]() by the awesome Nikita Popov
 - traverse the tree, gathering dependencies along the way
 - pass the information to a formatter

## License

See [LICENSE](LICENSE) file
