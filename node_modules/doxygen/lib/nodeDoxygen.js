/**
 * @file nodeDoxygen.js
 * Main File
 */

/**
 * @defgroup Doxygen
 * Module for generating automated documentation
 */

"use strict";

var execution = require("./execution");

module.exports = new NodeDoxygen();


function NodeDoxygen() {
    this.run = execution.run;
    this.isDoxygenExecutableInstalled = execution.isDoxygenExecutableInstalled;
    this.downloadVersion = require("./version").downloadVersion;
    this.createConfig = require("./config").createConfig;
}
