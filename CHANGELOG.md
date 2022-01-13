# Changelog
All notable changes to this project will be documented in this file.

## [1.5.0] - 2022-01-13
- Changed: allow wa72/htmlpagedom v2

## [1.4.0] - 2021-08-31

- Added: support for php 8

## [1.3.0] - 2018-03-08

### Added
- `{{request_get::*}}` and `{{request_post::*}}` insert tag helpers

## [1.2.4] - 2018-02-15

### Fixed
- tidy improvement

## [1.2.3] - 2018-02-07

### Fixed
- escaping

## [1.2.2] - 2018-02-02

### Fixed
- composer.json

## [1.2.1] - 2018-01-25

### Fixed
- added mistakenly $_GET to $_POST data to current instance if not empty

## [1.2.0] - 2017-05-09

### Changed - 2017-12-15
- changed getInstance function, add $_GET and $_POST to current instance if not empty. This adds contao unused $_GET items like autoitem to current request
- adjusted tests
- changed folder structur
- added psr4 loader
- added travis ci, php codestyle fixer and coversall

## [1.1.3] - 2017-05-09

### Fixed
- fixed null $_GET, $_POST error

## [1.1.2] - 2017-04-12

### Fixed
- xss security fixes, and prevent html document wrapper removal from `Request::getPost()`

## [1.1.1] - 2017-04-11

### Fixed
- xss security fixes

## [1.1.0] - 2017-03-31

### Changed
- `Request::getPost()` does now behave like `\Contao\Input::post`, it returns the save, html encoded input value

### Added 
- setPost(), setGet(), clean(), cleanHtml(), cleanRaw(), getPostHtml(), getPostRaw(), xssClean(), tidy() methods

## [1.0.2] - 2016-12-21

### Added
- hasGet(), hasPost() methods added
