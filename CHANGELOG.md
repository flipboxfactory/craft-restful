Changelog
=========

## 3.0.0 - 2023-07-26
### Added
- Support for Craft 4

## 2.2.0 - 2023-05-23
### Added
- Response logging can be marked as 'audit'

## 2.1.0 - 2019-11-22
### Added
- Response log filter which allows specific responses to be logged
- When transforming a model, if errors are present and the response has a successful status code, automatically apply a 400 status code to the response.

## 2.0.2 - 2019-06-26
### Updated
- CORS settings do not recursively merge; this allows the config to completely override settings rather than add to.

## 2.0.1 - 2019-06-26
### Fixed
- Access should only be checked if an access checker is available.

## 2.0.0 - 2019-01-23
### Removed
- Flux and RBAC dependencies

## 1.0.0-rc.3
### Fixed
- Incorrect component name

## 1.0.0-rc.2
### Changed
- Updated to Transform package v3

## 1.0.0-rc.1 - 2018-05-16
### Added
- CORS configuration can be overwritten via settings
- Pagination default page size can be set via settings

## 1.0.0-rc - 2018-03-22
Initial release.