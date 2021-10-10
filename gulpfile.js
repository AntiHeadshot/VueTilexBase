const _gulp = require("gulp");
const _sourcemaps = require("gulp-sourcemaps");
const _typescript = require("gulp-typescript");
const _del = require("del");
const _htmlmin = require("gulp-htmlmin");
const _fs = require("fs");
const _run = require("gulp-run");
const _babelify = require('babelify');
const _browserify = require('browserify');
const _buffer = require('vinyl-buffer');
const _source = require('vinyl-source-stream');
const _uglifyify = require('uglifyify');
const _minify = require('gulp-minify');
const _vueify = require('vueify');
const _deleteEmpty = require('delete-empty');
const _cleanCss = require('gulp-clean-css');
const _map = require('vinyl-map');
const _filenames = require('gulp-filenames');
const _path = require("path");
const _extReplace = require("gulp-ext-replace");

const publishpath = "G:/xampp/htdocs/inkment/web";

const usedLibs = ["vue-router/dist/*.js", "vue/dist/*.js"];

const copyFiles = ["*/style.css"];

var debug = true;

_gulp.task("clean-frontend", (done) => {
    return _del(["./dist"], done);
});

_gulp.task("copy-frontend-resources", () => {
    let x = _gulp.src(
        [
            "src/**/*",
            "!**/*.ts",
            "!**/*.vue",
            "!**/*.js",
            "!**/*.map",
            "!**/*.css",
            "!**/*.html",
            "!**/*.ttf",
            "!**/*.woff",
            "!**/.gitignore",
        ], { dot: true }
    );
    return x.pipe(_gulp.dest("./dist/client"));
});

_gulp.task("copy-frontend-js", () => {
    let x = _gulp.src(
        [
            "src/**/*.js",
            "src/**/*.ts",
            "src/**/*.vue",
            "!**/*.prod.js",
            "!**/*.dev.js",
        ], { dot: true }
    );
    return x.pipe(_gulp.dest("./dist/client-tmp"));
});

_gulp.task("copy-frontend-js-specific", () => {
    let x = _gulp.src(
        [
            "src/**/*." + (debug ? 'dev' : 'prod') + ".js",
        ], { dot: true }
    );
    return x.pipe(_extReplace('.js', /\..*\.js/))
        .pipe(_gulp.dest("./dist/client-tmp"));
});

_gulp.task("copy-frontend-libraries", () => {
    let x = _gulp.src(usedLibs, { cwd: "node_modules" });
    return x.pipe(_gulp.dest("./dist/client/site/lib"));
});

_gulp.task("copy-frontend-files", () => {
    let x = _gulp.src(copyFiles, { cwd: "src/site" });
    return x.pipe(_gulp.dest("./dist/client/site"));
});

_gulp.task("build-frontend-ts", () => {
    let typescriptProject = _typescript.createProject(
        JSON.parse(_fs.readFileSync("./tsconfig.json")).compilerOptions
    );
    let x = _gulp.src(["./src/**/*.ts", "!**/*.d.ts"]);
    x = x.pipe(_sourcemaps.init());
    x = x.pipe(typescriptProject());
    x = x.pipe(_sourcemaps.write());
    return x.pipe(_gulp.dest("./dist/client-tmp"));
});

_gulp.task('init-component-loader', () => {
    return _gulp.src("src/site/scripts/**/*.vue")
        .pipe(_filenames("vue-components"));
});

_gulp.task('write-component-loader', () => {
    return _gulp.src("./dist/client-tmp/**/componentloader.js")
        .pipe(_map(() => { return 'let collection={};\n' + _filenames.get('vue-components').map((x) => `import ${_path.basename(x,_path.extname(x))} from "./${x.replace('\\','/')}";
collection.${_path.basename(x,_path.extname(x))} = ${_path.basename(x,_path.extname(x))};
Vue.component("${_path.basename(x,_path.extname(x))}", ${_path.basename(x,_path.extname(x))});`).reduce((a, b) => `${a}

${b}`) + '\n\nexport default collection;' }))
        .pipe(_gulp.dest('./dist/client-tmp/'))
});

_gulp.task('update-component-loader',
    _gulp.series("init-component-loader", "write-component-loader"));

_gulp.task("compress-frontend-css", () => {
    let x = _gulp.src("./src/**/*.css");
    if (!debug) x = x.pipe(_cleanCss());
    return x.pipe(_gulp.dest("./dist/client"));
});

_gulp.task("compress-frontend-html", () => {
    let x = _gulp.src("./src/**/*.html");
    if (!debug)
        x = x.pipe(debug ? (x) => x() : _htmlmin({ collapseWhitespace: true }));
    return x.pipe(_gulp.dest("./dist/client"));
});

_gulp.task("publish", () => {
    let x = _gulp.src(["./dist/client/**/*", "!*-debug.js"], { dot: true });
    return x.pipe(_gulp.dest(publishpath));
});

_gulp.task("debug:off", (done) => {
    debug = false;
    process.env.NODE_ENV = 'production';
    done();
});

_gulp.task('bundle', function() {
    let x = _browserify({
        entries: ['./dist/client-tmp/site/scripts/main.js'],
        debug: debug
    }); // path to your entry file here

    x.transform(_vueify)
        .plugin('vueify/plugins/extract-css', { out: './dist/client/site/style/bundle.css' }) // path to where you want your css
        .transform(_babelify, { "presets": ["es2015"] });
    if (!debug)
        x = x.transform(_uglifyify, { global: true }); // of course if you want to use this transform
    x = x.external('vue'); // remove vue from the bundle, if you omit this line whole vue will be bundled with your code
    x = x.ignore(debug ? 'shared.prod' : 'shared.dev');
    x = x.bundle()
        .pipe(_source('bundle.js'));
    if (!debug) {
        x = x.pipe(_buffer()) // you have to use it if you want to use more pipes
            .pipe(_minify({ noSource: true, ext: { min: '.js' } })); // This is different from uglifyify transform. I am using both
    }
    return x.pipe(_gulp.dest('./dist/client/site/scripts'));
});

_gulp.task(
    "build:noclean",
    _gulp.series(
        _gulp.parallel(
            "copy-frontend-js",
            "copy-frontend-js-specific"
        ),
        _gulp.parallel(
            "build-frontend-ts",
            "update-component-loader"
        ),
        _gulp.parallel(
            "copy-frontend-resources",
            "copy-frontend-libraries",
            "copy-frontend-files",
            "bundle",
        ),
        _gulp.parallel(
            "compress-frontend-css",
            "compress-frontend-html"
        ))
);

// _gulp.task(
//   "build:fiximports",
//   () => {
//     return _run('npm run fixImports').exec();
//   }
// );

_gulp.task(
    "build:versioning",
    () => {
        return _run('npm run versioning').exec();
    }
);

_gulp.task(
    "update:componentloader",
    () => {
        return _run('npm run updateComponentloader').exec();
    }
);

_gulp.task("build:dev", _gulp.series(
    "clean-frontend",
    "update:componentloader",
    "build:noclean",
    //"build:fiximports",
    "build:versioning"));

_gulp.task("build", _gulp.series("debug:off", "build:dev"));

_gulp.task("deploy", _gulp.series("build", "publish"));

_gulp.task("deploy:dev", _gulp.series("build:dev", "publish"));