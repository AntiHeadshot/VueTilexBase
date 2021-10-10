"use strict";

const FileHound = require('filehound');
const fs = require('fs');
const path = require('path');

const files = FileHound.create()
    .paths(__dirname + '/dist/client')
    .discard('node_modules')
    .ext('js')
    .find();


files.then((filePaths) => {

    filePaths.forEach((filepath) => {
        fs.readFile(filepath, 'utf8', (err, data) => {

            if (!data.match(/import\W.*\Wfrom.*?(?<!\.[jJ][sS])["'];/g)) {
                return
            }
            let newData = data.replace(/(import\W.*\Wfrom\s?['"])(.*?(?<!\.js))(?=(?:\?.*?)?['"])/g, '$1$2.js!#Version#!')
            if (err) throw err;

            console.log(`writing to ${filepath}`)
            fs.writeFile(filepath, newData, function(err) {
                if (err) {
                    throw err;
                }
            });
        })

    })
});