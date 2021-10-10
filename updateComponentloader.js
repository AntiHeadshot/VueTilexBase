/*jslint browser */
/*global process */

"use strict";

const FileHound = require('filehound');
const fs = require('fs');
const path = require('path');

const rootPath = __dirname + '/src/site/scripts';

const files = FileHound.create()
    .paths(rootPath)
    .discard('node_modules')
    .ext('vue')
    .find();

files.then((filePaths) => {
    let output = 'let collection={};\r\n';

    filePaths.forEach((filepath) => {
        let fileName = path.parse(filepath).name;
        output += `import ${fileName} from "./${path.relative(rootPath, filepath).replace('\\', '/')}";\r\n` +
            `collection.${fileName} = ${fileName};\r\n` +
            `Vue.component("${fileName}", ${fileName});\r\n\r\n`;
    });

    output += 'export default collection;';

    fs.writeFile(path.join(rootPath, "componentloader.js"), output, function (err) {
        if (err) throw err;
    });
});