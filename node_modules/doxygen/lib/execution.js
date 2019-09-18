/**
 * @file executionModule.js
 * Module for generating automated documentation
 */

"use strict";

var fs = require("fs");

module.exports = {
    run: run,
    isDoxygenExecutableInstalled: isDoxygenExecutableInstalled
};

var constants = require("./constants");

var exec = require("child_process").execSync;
var path = require("path");

/**
 * @ingroup Doxygen
 * Returns a path for doxygen executable given a version.
 * @param {String} [version] - The version of doxygen to run.
 *    If not passed uses default version from constants
 */
function doxygenExecutablePath(version) {
    version = version ? version : constants.default.version;
    var dirName = __dirname;
    var doxygenFolder = "";
    if (process.platform == constants.platform.macOS.identifier) {
        doxygenFolder = constants.path.macOsDoxygenFolder;
    }

    return path.normalize(dirName + "/../dist/" + version + doxygenFolder + "/doxygen");
}

/**
 * @ingroup Doxygen
 * Returns whether a particular version of doxygen is installed.
 * @param {String} [version] - The version of doxygen to run.
 *    If not passed uses default version from constants
 */
function isDoxygenExecutableInstalled(version) {
    var execPath = doxygenExecutablePath(version);
    return fs.existsSync(execPath);
}

/**
 * @ingroup Doxygen
 * Runs doxygen from node
 * @param {String} configPath - The path of the config file
 * @param {String} version - The version of doxygen to run
 */
function run(configPath, version) {
    configPath = configPath ?
                    configPath : 
                    constants.path.configFile;
    var doxygenPath = doxygenExecutablePath(version);
    exec("\"" + doxygenPath + "\" \"" + path.resolve(configPath) + "\"", 
         { stdio: ["pipe", process.stdout, "pipe"] });
}
