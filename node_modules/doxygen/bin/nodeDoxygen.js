#!/usr/bin/env node

var doxygen = require("../lib/nodeDoxygen");

var doxygenParams = process.argv.slice(2);
var operation = "docs";
var operationSet = false;
var configPath;
var params;
var version;

for (var i = 0; i < doxygenParams.length; i++) {
    var currentParam = doxygenParams[i];
    var currentParamEqualIndex = currentParam.indexOf("=");
    var currentOption;
    if (currentParamEqualIndex  === -1){
        currentOption = currentParam;
    } else {
        currentOption = currentParam.substring(0, currentParamEqualIndex);
    }

    switch(currentOption){
        case "--jsonParams":
            params = JSON.parse(currentParam.substring(currentParamEqualIndex + 1));
            break;
        case "--configPath":
            configPath = currentParam.substring(currentParamEqualIndex + 1);
            break;
        case "--version":
            version = currentParam.substring(currentParamEqualIndex + 1);
            break;
        case "--config":
            selectOperation("config");
            break;
        case "--download":
            selectOperation("download");
            break;
        case "--docs":
            selectOperation("docs");
            break;
    }
}

switch (operation) {
    case "docs":
        doxygen.run(configPath, version);
        break;

    case "config":
        doxygen.createConfig(params, configPath);
        break;

    case "download":
        doxygen.downloadVersion(version);
        break;
}

function selectOperation(operationName){
    if (operationSet) {
        console.warn("Option --" + 
                    operationName + 
                    " ignored: Only one command can be executed at the same time");
    }
    else {
        operationSet = true;
        operation = operationName;
    }
}