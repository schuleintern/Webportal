/**
 * @file helpers.js
 * Helpers in the doxygen module
 */
"use strict";

var fs = require("fs");
var path = require("path");

module.exports.ensureDirectoryExistence = ensureDirectoryExistence;

/**
 * Makes sure that a folder route exists, creating
 * the folders if necesary
 * @param {String} filePath - The path.
 * @param {Boolean} notDir - True if the path does not reference a directory
 */
function ensureDirectoryExistence(filePath, notDir) {
    if (!fs.existsSync(filePath)) {
        var dirname = path.dirname(filePath);
        ensureDirectoryExistence(dirname);
        if (!notDir) {
            fs.mkdirSync(filePath);
        }
    }
}