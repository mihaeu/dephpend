# ![logo](http://mike-on-a-bike.com/dephpend-logo.png) 

[![Build Status](https://travis-ci.com/mihaeu/php-dependencies.svg?token=6E2gXvaZaEh2XxFCPhrX&branch=develop)](https://travis-ci.com/mihaeu/php-dependencies) ![License MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=flat) [![Gitter](https://img.shields.io/gitter/room/mihaeu/php-dependencies.svg?maxAge=2592000&style=flat)]()

## Usage 

```bash
$ bin/php-dependencies                                                                                                 
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

## Roadmap

### Must have

 - detects explicit dependencies (static calls, new, foreign objects, ...)
 - detects implicit dependencies for popular DICs
 - Visualization
 - filters/ignore

### Nice to have

 - CI integration
 - IDE integration
 - fancy visualization (e.g. UML)

## Similar Tools

### PHP

 - [pdepend by Manuel Pichler](https://github.com/pdepend/pdepend) does not support PHP 7
 - [PhpDependencyAnalysis by Marco Muths](https://github.com/mamuz/PhpDependencyAnalysis) does not support PHP7
 - [Deptrac by Sensiolabs DE](https://github.com/sensiolabs-de/deptrac) dependency constraints only, not tested with PHP7

### Other languages

- [jdepend by Clarkware](http://clarkware.com/software/JDepend.html) (Java)
- [NDepend](http://www.ndepend.com/) (C#)

## Other documents

 - [/doc](doc/README.md)
 - [Master Thesis](https://github.com/mihaeu/static-dependency-analysis)

## License

See `LICENSE` file
