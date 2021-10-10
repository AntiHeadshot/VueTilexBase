/*jslint browser */
/*global process */

"use strict";

let parameters = {
    "Version": "?v=0.2." + Math.floor((((new Date().getTime()) - (new Date().setHours(0, 0, 0, 0))) / 2000)),
    "ThemeColor": "#ffffff",
    "TileColor": "#ff0050",
    "AppName": "Inkment",
    "AppTitle": "Inkment - Terminvergabe"
};

const FileHound = require('filehound');
const fs = require('fs');
const path = require('path');

const files = FileHound.create()
    .paths(__dirname + '/dist/client')
    .discard('node_modules')
    .ext('js', 'css', 'html', 'xml', 'webmanifest', 'json')
    .find();

console.log('setting Parameters');

files.then((filePaths) => {
    filePaths.forEach((filepath) => {
        fs.readFile(filepath, 'utf8', (err, data) => {

            let newData = data;
            let replaced = false;
            for (let parameter in parameters) {
                if (newData.indexOf(`!#${parameter}#!`) < 0) {
                    continue;
                }
                let regex = new RegExp(`!#${parameter}#!`, 'g');
                newData = newData.replace(regex, parameters[parameter]);
                replaced = true;
            }
            if (!replaced)
                return;

            if (err) throw err;

            console.log(`writing to ${filepath}`)
            fs.writeFile(filepath, newData, function (err) {
                if (err) throw err;
            });

        })
    })
});