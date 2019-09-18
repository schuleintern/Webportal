Doxygen
===========

[![npm Package](https://img.shields.io/npm/v/doxygen.svg?style=flat-square)](https://www.npmjs.org/package/doxygen)
[![Build Status](https://travis-ci.org/EruantalonJS/node-doxygen.svg?branch=master)](https://travis-ci.org/EruantalonJS/node-doxygen)
[![Code Climate](https://codeclimate.com/github/EruantalonJS/node-doxygen/badges/gpa.svg)](https://codeclimate.com/github/EruantalonJS/node-doxygen)

Node wrapper for building [Doxygen](https://www.doxygen.org) documentation.

This module is not associated with [Doxygen](https://www.doxygen.org)
## Setup

This module is a wrapper around Doxygen, to automate the installation and generation of doxygen documentation so that it can be easily included in any project build. Supports Linux, Windows, and MacOS. It supports both local and global installation

`npm install doxygen`

or globally

`npm install doxygen -g `

## Invoking from a task

Downloads the latest doxygen version from the default repository

```javascript

var doxygen = require('doxygen');
doxygen.downloadVersion().then(function (data) {
        doSomething();
});

```

Create an empty config file(Takes all defaults):

```javascript

var doxygen = require('doxygen');
var userOptions = {};

doxygen.createConfig(userOptions);

```

Create a config file that includes js files:

```javascript

var doxygen = require('doxygen');
var userOptions = {
    OUTPUT_DIRECTORY: "Docs",
    INPUT: "./",
    RECURSIVE: "YES",
    FILE_PATTERNS: ["*.js", "*.md"],
    EXTENSION_MAPPING: "js=Javascript",
    GENERATE_LATEX: "NO",
    EXCLUDE_PATTERNS: ["*/node_modules/*"]
};

doxygen.createConfig(userOptions);

```

Generate the documentation

```javascript

var doxygen = require('doxygen');
doxygen.run();

```

## Invoking from CLI

Downloads the latest doxygen version from the default repository

`doxygen --download`

Create a config file(Takes all defaults):

`doxygen --config`

Create a config file in a particular location(Takes all defaults):

`doxygen --config --configPath=\path\to\file`

Create a config file in a particular location, passing some parameters:

`doxygen --config --configPath=\path\to\file --jsonParams={\"OUTPUT_DIRECTORY\":\"Docs\",\"INPUT\":\"./\",\"RECURSIVE\":\"YES\",\"FILE_PATTERNS\":[\"*.js\",\"*.md\"],\"EXTENSION_MAPPING\":\"js=Javascript\",\"GENERATE_LATEX\":\"NO\",\"EXCLUDE_PATTERNS\":[\"*/node_modules/*\"]}`

Generate the documentation

`doxygen --docs`

Generate the documentation using a particular config file:

`doxygen --docs --configPath=\path\to\file`
