# Change Log
All notable changes to this project will be documented in this file.

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
