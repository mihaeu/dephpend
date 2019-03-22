# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
 - support for Docker

## [0.5.1] - 2018-07-04
### Fixed
 - replaced Symfony Console hacks with injection through Event Dispatcher
 - PHP 7.2 support for projects which require PHP 7.2 and Symfony 4 components

## [0.5.1] - 2018-07-04
### Fixed
 - PHP 7.2 support for projects which require PHP 7.2 and Symfony 4 components

## [0.5.0] - 2018-03-23
### Added
 - PHP 7.2 support

### Fixed
 - better Windows support

## [0.4.0] - 2017-05-12
### Added
 - PHP 7.1 support
 - add detection of instanceof comparison
 - switch DSM column and row (like NDepend)

### Fixed
 - add support for inner classes

## [0.3.1] - 2016-11-16
### Fixed
 - default options like --help not working

## [0.3.0] - 2016-11-15
### Added
 - dot command for layered dependency graphs

### Changed
 - packages in UML diagrams will now be displayed hierarchical

### Fixed
 - removed empty dependencies after applying depth filter

## [0.2.0] - 2016-10-15
### Added
 - filter-from filter (filters only source dependencies, not the target)
 - exclude-regex (excludes any dependency pair which matches the regular expression)
 - dynamic analysis

### Changed
 - metrics are being displayed as a table

## 0.1.0 - 2016-10-01
### Added
 - first tagged release
 - uml, text, dsm and metrics command

[Unreleased]: https://github.com/mihaeu/dephpend/compare/0.5.1...HEAD
[0.5.1]: https://github.com/mihaeu/dephpend/compare/0.5.0...0.5.1
[0.5.0]: https://github.com/mihaeu/dephpend/compare/0.4.0...0.5.0
[0.4.0]: https://github.com/mihaeu/dephpend/compare/0.3.2...0.4.0
[0.3.2]: https://github.com/mihaeu/dephpend/compare/0.3.1...0.3.2
[0.3.1]: https://github.com/mihaeu/dephpend/compare/0.3.0...0.3.1
[0.3.0]: https://github.com/mihaeu/dephpend/compare/0.2.0...0.3.0
[0.2.0]: https://github.com/mihaeu/dephpend/compare/0.1.0...0.2.0
