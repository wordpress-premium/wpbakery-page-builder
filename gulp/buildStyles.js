const fs = require('fs')
const path = require('path')
const autoprefixer = require('autoprefixer')
const { src, dest, series } = require('gulp')
const plumber = require('gulp-plumber')
const sourcemaps = require('gulp-sourcemaps')
const less = require('gulp-less')
const util = require('gulp-util')
const postcss = require('gulp-postcss')
const cssmin = require('gulp-cssmin')
const rename = require('gulp-rename')
const concat = require('gulp-concat')
const mode = require('gulp-mode')()
const buildConfig = require('../build-config.json')

function errorHandler(err) {
	console.error(err)
	this.emit('end') // Continue the task stream
}

function buildCss(done) {
	return src(buildConfig.globalOptions.less.srcFiles.map((src) => {
		return buildConfig.globalOptions.less.srcPath + src
	}))
		.pipe(plumber(errorHandler))
		.pipe(mode.development(sourcemaps.init()))
		.pipe(less({
			paths: [
				buildConfig.globalOptions.less.srcPath // to resolve import paths
			],
			strictMath: true // to resolve calculations in less
		}).on('error', util.log))
		.pipe(postcss([autoprefixer({ overrideBrowserslist: buildConfig.globalOptions.browsers })]))
		.pipe(( mode.production(cssmin()) ))
		.pipe(rename({ suffix: '.min' }))
		.pipe(mode.development(sourcemaps.write()))
		.pipe(dest(buildConfig.globalOptions.less.destPath))
		.on('end', done)
}

function buildFontLibs(done) {
	const streams = []
	buildConfig.globalOptions.fontLibs.srcFiles.forEach((path) => {
		const libFolder = path.split('/')[0]
		const destPath = buildConfig.globalOptions.fontLibs.destPath + libFolder + '/'
		streams.push(src(buildConfig.globalOptions.fontLibs.srcPath + path)
			.pipe(plumber(errorHandler))
			.pipe(mode.development(sourcemaps.init()))
			.pipe(postcss([autoprefixer({ overrideBrowserslist: buildConfig.globalOptions.browsers })]))
			.pipe(( mode.production(cssmin()) ))
			.pipe(rename({ suffix: '.min' }))
			.pipe(mode.development(sourcemaps.write()))
			.pipe(dest(destPath)))
	})

	Promise.all(streams)
		.then(() => {
			done()
		})
		.catch((err) => {
			console.error('Error:', err)
			done(err)
		})
}

function buildCssPackages() {
	const cssTasks = buildConfig.nodeModules.css.map((file) => {
		return function buildingCssPackages() {
			return src(path.join(buildConfig.nodeModules.srcPath, file.src))
				.pipe(cssmin())
				.pipe(rename({ suffix: '.min' }))
				.pipe(dest(path.join(buildConfig.nodeModules.srcPath, file.dest)))
		}
	})

	return series.apply(null, cssTasks)
}

/**
 * Recursively processes all the LESS files within a given directory and its subdirectories.
 * @param {string} directoryPath - The path of the directory to be processed.
 * @param {function} done - Callback function to be called when processing is complete.
 * @param {string} destPath - The destination path for the compiled CSS files.
 */
function processLessFilesInDirectory(directoryPath, done, destPath) {
	fs.readdir(directoryPath, (err, files) => {
		if (err) {
			console.error('Error:', err)
			return done(err)
		}

		let pending = files.length

		if (pending === 0) {
			return done() // If no files, signal completion immediately
		}

		files.forEach((file) => {
			const filePath = path.join(directoryPath, file)
			fs.stat(filePath, (err, stats) => {
				if (err) {
					console.error('Error:', err)
					done(err)
					return
				}

				if (stats.isDirectory()) {
					// If the current item is a directory, recursively process it
					processLessFilesInDirectory(filePath, (err) => {
						if (--pending === 0) done(err) // Call done only when all recursive calls are done
					}, destPath)
				} else if (path.extname(file) === '.less') {
					processLessFile(filePath, () => {
						if (--pending === 0) {
							done()
						}
					}, destPath)
				} else {
					if (--pending === 0) done() // Call done if no further processing is needed
				}
			})
		})
	})
}

/**
 * Compiles a single Less file and outputs the compiled CSS file to the same folder.
 * @param {string} filePath - The path of the Less file to be processed.
 * @param {function} done - Callback function to be called when processing is complete.
 * @param {string} destPath - The destination path for the compiled CSS files.
 */
function processLessFile(filePath, done, destPath) {
	const dir = destPath || 'css'
	const lessDir = path.dirname(filePath)
	const cssDir = path.join(lessDir, '..', dir)
	const fileNameWithoutExtension = path.basename(filePath, '.less')
	const destFileName = fileNameWithoutExtension + '.min.css'

	src(filePath)
		.pipe(mode.development(sourcemaps.init()))
		.pipe(less().on('error', function(err) {
			console.error('Error:', err.message)
			this.emit('end')
		}))
		.pipe(postcss([autoprefixer({ overrideBrowserslist: buildConfig.globalOptions.browsers })]))
		.pipe(mode.production(cssmin()))
		.pipe(rename(destFileName))
		.pipe(mode.development(sourcemaps.write()))
		.pipe(dest(cssDir))
		.on('end', done)
}

function buildCssLibs (done) {
	const srcPath = buildConfig.globalOptions.cssLibs.srcPath
	processLessFilesInDirectory(srcPath, done, '')
}

function buildModuleCss(done) {
	const srcPath = buildConfig.modules.srcPath;
	const tasks = buildConfig.modules.moduleCssList.map((file) => {
		return function buildingCssModules() {
			return src(path.join(srcPath, file.src))
				.pipe(plumber({ errorHandler }))
				.pipe(mode.development(sourcemaps.init()))
				.pipe(less({
					paths: [srcPath],
					strictMath: true
				}).on('error', util.log))
				.pipe(postcss([autoprefixer({ overrideBrowserslist: buildConfig.globalOptions.browsers })]))
				.pipe(mode.production(cssmin()))
				.pipe(rename({ suffix: '.min' }))
				.pipe(mode.development(sourcemaps.write()))
				.pipe(dest(path.join(srcPath, file.dest)))
				.on('end', done)
		}
	})
	return series(...tasks)(done);
}

function buildModuleCssMainFile (done) {
	return src(buildConfig.modulesMainFile.modulesMainCssList[0].src.map((src) => {
		return buildConfig.modulesMainFile.srcPath + src
	}))
		.pipe(mode.development(sourcemaps.init()))
		.pipe(less({
			paths: [ buildConfig.modulesMainFile.srcPath ],
			strictMath: true
		}).on('error', util.log))
		.pipe(concat(buildConfig.modulesMainFile.modulesMainCssList[0].dest))
		.pipe(( mode.production(cssmin()) ))
		.pipe(mode.development(sourcemaps.write()))
		.pipe(dest(buildConfig.modulesMainFile.cssDestPath))
		.on('end', done)
}

exports.buildCss = buildCss
exports.buildFontLibs = buildFontLibs
exports.buildCssLibs = buildCssLibs
exports.buildCssPackages = buildCssPackages
exports.buildModuleCss = buildModuleCss
exports.buildModuleCssMainFile = buildModuleCssMainFile
