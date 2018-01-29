# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- .gitattributes file limiting the files that will be exported to composer

## [1.0.1] - 2018-01-29
### Added
- Keep a Changelog changelog

### Changed
- Use direct invocations instead of `call_user_func` calls

### Removed
- Dependency on roave/security-advisories

## [1.0.0] - 2018-01-27
### Added
- Dependency on psr/http-server-middleware
- PHP 7.2 support
- Dependency on roave/security-advisories

### Changed
- Implemented final PSR-15 interfaces

### Removed
- Provided package psr/http-middleware package
- HHVM support
- PHP 5.6 support

## [0.1] - 2017-02-01
### Added
- First release based on the PSR-15 draft.

[Unreleased]: https://github.com/procurios/middleware-dispatcher/compare/1.0.1...HEAD
[1.0.1]: https://github.com/procurios/middleware-dispatcher/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/procurios/middleware-dispatcher/compare/0.1...1.0.0
[0.1]: https://github.com/procurios/middleware-dispatcher/commits/0.1
