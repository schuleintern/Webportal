/**
 * @file versionModule.js
 * Module for downloading doxygen versions
 */

"use strict";

module.exports.downloadVersion = downloadVersion;

var toArray = require("stream-to-array");
var fs = require("fs-extra");
var Promise = require("promise");
var constants = require("./constants");
var helpers = require("./helpers");
var distRoute = __dirname + "/../dist";
helpers.ensureDirectoryExistence(distRoute);

/**
 * @ingroup Doxygen
 * Downloads a doxygen version
 * @param {String} version - The version to download
 * @param {String} protocol - The protocol to be used to download the file
 * @param {String} hostName - The host from which to download the doxygen file
 * @param {String} fileRoute - The route from which to download the doxygen file
 */
function downloadVersion(version, protocol, hostName, fileRoute) {
    version = version ? version : constants.default.version;
    protocol = protocol ? protocol : constants.default.downloadProtocol;
    hostName = hostName ? hostName : constants.default.downloadHostName;
    fileRoute = fileRoute ? fileRoute : getFileRoute(version);

    var versionRoute = distRoute + "/" + version;
    helpers.ensureDirectoryExistence(versionRoute);

    var dataPromise = getDataPromise(protocol, hostName, fileRoute);

    return new Promise(function (resolve, reject) {
        return dataPromise
            .then(toArray)
            .then(arrayToBuffer)
            .then(function (buffer) {
                if (fileRoute.endsWith(".dmg")) {
                    return decompressDmg(buffer, versionRoute);
                } else {
                    return decompressNonDmg(buffer, versionRoute);
                }
            })
            .then(function () {
                resolve(true);
            })
            .catch(function (error) {
                reject(error);
            });
    });
}

/**
 * @ingroup Doxygen
 * Downloads a doxygen version
 * @param {String} protocol - The protocol to be used to download the file
 * @param {String} hostName - The host from which to download the doxygen file
 * @param {String} fileRoute - The route from which to download the doxygen file
 */
function getDataPromise(protocol, hostName, fileRoute) {
    switch (protocol){
        case "http":
        case "https":
            var downloadModule = require(protocol);
            return httpDownload(protocol + "://" + hostName + "/" + fileRoute, downloadModule);
        case "ftp":
            return ftpDownload(hostName, fileRoute);
        default:
            throw constants.error.invalidProtocol;
    }
}

/**
 * Returns the file route to obtain the appropiate doxygen file from the official site
 * @returns {String} The file route
 */
function getFileRoute(version) {
    var osInfo;

    switch (process.platform){
        case constants.platform.macOS.identifier:
            osInfo = constants.platform.macOS;
            break;
        case constants.platform.linux.identifier:
            osInfo = constants.platform.linux;
            break;
        case constants.platform.solaris.identifier:
            osInfo = constants.platform.solaris;
            break;
        case constants.platform.windows.identifier:
            osInfo = constants.platform.windows;
            break;
        default:
            throw constants.error.invalidPlatform;
    }

    return constants.default.downloadFileRoute
        .replace(/%doxygenName%/g, osInfo.doxygenName)
        .replace(/%version%/g, version)
        .replace(/%osPrefix%/g, osInfo.osPrefix)
        .replace(/%x64Prefix%/g, process.arch == "x64" ? osInfo.x64Prefix : "")
        .replace(/%extension%/g, osInfo.extension);
}

/**
 * Downloads a doxygen version with ftp protocol
 * @param {String} hostName - The host from which to download the doxygen file
 * @param {String} fileRoute - The route from which to download the doxygen file
 */
function ftpDownload(hostName, fileRoute) {
    var ftp = require("ftp");

    var ftpConfig = {
        host: hostName
    };

    return new Promise(function (resolve, reject) {
        var client = new ftp();

        function afterGet(err, stream) {
            if (err) {
                reject(err);
            } else {
                stream.once("close", function () {
                    client.end();
                });
                resolve(stream);
            }
        }

        client.on("ready", function () {
            client.get(fileRoute, afterGet);
        });

        client.on("error", function (error) {
            reject(error);
        });

        client.connect(ftpConfig);
    });
}

/**
 * Downloads a doxygen version with ftp protocol
 * @param {String} fileRoute - The url from which to download the doxygen file
 * @param {String} downloadModule - The download module to use(http or https)
 */
function httpDownload(url, downloadModule) {
    return new Promise(function (resolve, reject) {
        downloadModule.get(url, function (response) {
            //handle redirect
            if (response.statusCode === 302) {
                httpDownload(response.headers.location, downloadModule)
                    .then(function(response) {
                        resolve(response);
                    }, function(error){
                        reject(error)
                    });
            } else {
                resolve(response);
            }
        }).on("error", function (e) {
            reject("Request error: " + e.message);
        });
    });
}


/**
 * Creates a buffer with all the segments of a download
 * @param {Object} parts - The array containing all the segments of the download
 * @returns {Object} A buffer
 */
function arrayToBuffer(parts) {
    var buffers = [];
    for (var i = 0, l = parts.length; i < l; ++i) {
        var part = parts[i];
        buffers.push((part instanceof Buffer) ? part : new Buffer(part));
    }
    return Buffer.concat(buffers);
}

/**
 * Uncompresses the file contained in the buffer, and copies the results to the route specified
 * @param {Object} buffer - The file downloaded
 * @param {String} destinationPath - The route on which the files must be copied
 */
function decompressNonDmg(buffer, destinationPath) {
    var decompress = require("decompress");
    return decompress(buffer, destinationPath, {
        filter: function (file) {
            return file.path.endsWith("doxygen") ||
                file.path.endsWith("doxygen.exe") ||
                file.path.endsWith("doxyindexer") ||
                file.path.endsWith("doxyindexer.exe") ||
                file.path.endsWith("doxysearch.cgi.exe") ||
                file.path.endsWith("doxysearch.cgi") ||
                file.path.endsWith("libclang.dll");
        },
        map: function (file) {
            file.path = file.path.substring(file.path.lastIndexOf("/") + 1);
            return file;
        }
    });
}

/**
 * Takes a dmg file from the buffer and copies the files contained to the route specified
 * @param {Object} buffer - The file downloaded
 * @param {String} filePath - The route on which the files must be copied
 */
function decompressDmg(buffer, filePath) {
    return new Promise(function (resolve, reject) {
        fs.writeFile(filePath + "/doxygen.dmg", buffer, "binary", function (error) {
            if (error) {
                reject(error);
            } else {
                copyFileFromDmg(filePath + "/doxygen.dmg", filePath + "/doxygen.app")
                    .then(function () {
                        resolve(true);
                    }, function(error){
                        reject(error);
                    });
            }
        });
    });
}

/**
 * Uncompresses a dmg file, and copies the results to the route specified
 * @param {Object} dmgFilePath - The route of the dmg file
 * @param {String} destinationPath - The route on which the files must be copied
 */
function copyFileFromDmg(dmgFilePath, destinationPath) {
    return new Promise(function (resolve, reject) {
        var dmg = require("dmg");
        dmg.mount(dmgFilePath, function (error, path) {
            if (error) {
                reject(error);
            } else {
                try {
                    fs.copySync(path + "/Doxygen.app", destinationPath);
                    dmg.unmount(path, function () {
                        resolve(true);
                    });
                } catch (error) {
                    rejectCleanup(error, path);
                }

            }
        });

        function rejectCleanup(error, dmgMounted) {
            dmg.unmount(dmgMounted, function () {
                reject(error);
            });
        }
    });
}