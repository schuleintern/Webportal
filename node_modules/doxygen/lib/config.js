/**
 * @file configModule.js
 * Module for generating the configuration file
 */
"use strict";

module.exports.createConfig = createConfig;

var helpers = require("./helpers");
var constants = require("./constants");
var path = require("path");
var fs = require("fs");

/**
 * Creates the config file
 * @ingroup Doxygen
 * @param {Object} doxygenOptions - The options to include in the config file
 * @param {String} configPath - The route on which the config file should be created
 */
function createConfig(doxygenOptions, configPath) {
    configPath = configPath ? configPath : constants.path.configFile;

    doxygenOptions = convertUrlsToPaths(doxygenOptions);

    var configLines = [];
    for (var property in doxygenOptions) {
        var configLine = property + " = ";
        if (Array.isArray(doxygenOptions[property])) {
            configLine += doxygenOptions[property].join(" \\ \n");
        } else {
            configLine += doxygenOptions[property];
        }

        configLines.push(configLine);
    }

    helpers.ensureDirectoryExistence(configPath, true);
    fs.writeFileSync(configPath, configLines.join("\n"));
}

/**
 * Transforms a url to be stored as a path in the config
 * @param {String} url - The string to be stored as a path
 */
function convertUrlToPath(url){
    return "\"" + path.resolve(url) + "\"";
}

/**
 * Transforms a url to be stored as a path in the config
 * @param {Object} doxygenOptions - The options to include in the config file
 */
function convertUrlsToPaths(doxygenOptions){
    if (doxygenOptions && doxygenOptions["INPUT"]){
        if(Array.isArray(doxygenOptions["INPUT"])) {
            doxygenOptions["INPUT"] = doxygenOptions["INPUT"]
                                            .map(relativePath => convertUrlToPath(relativePath));
        }
        else {
            doxygenOptions["INPUT"] = convertUrlToPath(doxygenOptions["INPUT"]);
        }
    }
    
    if (doxygenOptions && doxygenOptions["OUTPUT_DIRECTORY"]){
        doxygenOptions["OUTPUT_DIRECTORY"] = convertUrlToPath(doxygenOptions["OUTPUT_DIRECTORY"]);
    }

    return doxygenOptions;
}