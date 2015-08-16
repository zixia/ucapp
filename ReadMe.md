# 17SALSA UCHome App

## Coding style
* JSCS: preset to google
* JSHint
* vim:
 * pathogen
  * syntastic vim-js-indent vim-sensible html5.vim
 * color-theme distinguished

Reference: [https://github.com/showdownjs/code-style](https://github.com/showdownjs/code-style)

## Semantic Versioning

Given a version number MAJOR.MINOR.PATCH, increment the:

* MAJOR version when you make incompatible API changes,
* MINOR version when you add functionality in a backwards-compatible manner, and
* PATCH version when you make backwards-compatible bug fixes.

Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.

Reference: [http://semver.org/](http://semver.org/)

## Init Project After Check Out
1. npm install -g ionic cordova gulp ios-deploy ios-sim
1. cd app
1. npm install
1. bower install
1. ionic resources
1. ionic platform add ios android
1. ionic browser add crosswalk
1. ionic config build
1. ionic serve --lab


[![Join the chat at https://gitter.im/lijiarui/ucapp](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/lijiarui/ucapp?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
