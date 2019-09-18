var doxygen = require("../lib/nodeDoxygen");
var rimraf = require("rimraf");
var exec = require("child_process").execSync;

/*describe("Download:", function () {
    beforeEach(function (done) {
        rimraf("dist", function (error) {
            if (error) {
                throw error;
            } else {

                done();
            }
        });
    });

    it("FTP", function (done) {
        doxygen.downloadVersion()
            .then(function () {
                done();
            }, function (error) {
                done();
                done.fail(error);
            });
    }, 360000);

    it("HTTP", function (done) {
        doxygen.downloadVersion(null, "http").then(function () {
            done();
        }, function (error) {
            done();
            done.fail(error);
        });
    }, 360000);
});*/


describe("Generates the config:", function () {

    it("From a task, with the default config location", function () {
        var userOptions = {
            OUTPUT_DIRECTORY: "testResults/Docs",
            INPUT: "./",
            RECURSIVE: "YES",
            FILE_PATTERNS: ["*.js", "*.md"],
            EXTENSION_MAPPING: "js=Javascript",
            GENERATE_LATEX: "NO",
            EXCLUDE_PATTERNS: ["*/node_modules/*", "*/filters/*"],
            PROJECT_NAME: "Node-Doxygen",
            USE_MDFILE_AS_MAINPAGE: "README.md"
        };
        doxygen.createConfig(userOptions);
    });

    it("From a task, with a custom config location", function () {
        var userOptions = {
            OUTPUT_DIRECTORY: "testResults/Docs",
            INPUT: "./",
            RECURSIVE: "YES",
            FILE_PATTERNS: ["*.js", "*.md"],
            EXTENSION_MAPPING: "js=Javascript",
            GENERATE_LATEX: "NO",
            EXCLUDE_PATTERNS: ["*/node_modules/*", "*/filters/*"],
            PROJECT_NAME: "Node-Doxygen",
            USE_MDFILE_AS_MAINPAGE: "README.md"
        };
        doxygen.createConfig(userOptions, "testResults/config");
    });

    it("From CLI, with the default config location", function () {
        var userOptions = {
            OUTPUT_DIRECTORY: "testResults/Docs",
            INPUT: "./",
            RECURSIVE: "YES",
            FILE_PATTERNS: ["*.js", "*.md"],
            EXTENSION_MAPPING: "js=Javascript",
            GENERATE_LATEX: "NO",
            EXCLUDE_PATTERNS: ["*/node_modules/*", "*/filters/*"],
            PROJECT_NAME: "Node-Doxygen",
            USE_MDFILE_AS_MAINPAGE: "README.md"
        };

        exec("node ./bin/nodeDoxygen.js --config --jsonParams="
            + JSON.stringify(JSON.stringify(userOptions)), { stdio: ["pipe", process.stdout, "pipe"] });
    });

    it("From CLI, with a custom config location", function () {
        var userOptions = {
            OUTPUT_DIRECTORY: "testResults/Docs",
            INPUT: "./",
            RECURSIVE: "YES",
            FILE_PATTERNS: ["*.js", "*.md"],
            EXTENSION_MAPPING: "js=Javascript",
            GENERATE_LATEX: "NO",
            EXCLUDE_PATTERNS: ["*/node_modules/*", "*/filters/*"],
            PROJECT_NAME: "Node-Doxygen",
            USE_MDFILE_AS_MAINPAGE: "README.md"
        };
        exec("node ./bin/nodeDoxygen.js --config --configPath=testResults/config --jsonParams="
            + JSON.stringify(JSON.stringify(userOptions)), { stdio: ["pipe", process.stdout, "pipe"] });
    });
});

describe("Generates the docs:", function () {
    beforeAll(function (done) {
        doxygen.downloadVersion("1.8.13")
            .then(function () {
                done();
            }, function (error) {
                throw error;
            });
    }, 360000);

    beforeEach(function (done) {
        rimraf("testResults/Docs", function (error) {
            if (error) {
                throw error;
            } else {
                done();
            }
        });
    });

    it("From a task, with a custom config location", function () {
        doxygen.run("testResults/config", "1.8.13");
    });

    it("From a task, with the default config location", function () {
        doxygen.run(null, "1.8.13");
    });

    it("From CLI, with a custom config location", function () {
        exec("node ./bin/nodeDoxygen.js --docs --version=1.8.13 --configPath=testResults/config", { stdio: ["pipe", process.stdout, "pipe"] });
    });

    it("From CLI, with the default config location", function () {
        exec("node ./bin/nodeDoxygen.js --docs --version=1.8.13", { stdio: ["pipe", process.stdout, "pipe"] });
    });
});