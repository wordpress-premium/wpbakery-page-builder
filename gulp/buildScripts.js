const path = require('path')
const { src, dest, series } = require('gulp')
const plumber = require('gulp-plumber')
const sourcemaps = require('gulp-sourcemaps')
const uglify = require('gulp-uglify')
const concat = require('gulp-concat')
const mode = require('gulp-mode')()
const buildConfig = require('../build-config.json')
const rename = require('gulp-rename')
const fs = require('fs')

function errorHandler(err) {
	console.error(err)
	this.emit('end') // Continue the task stream
}

function buildJs(done, files, srcPath, destPath) {
	files = files || buildConfig.uglifyList
	srcPath = srcPath || buildConfig.globalOptions.js.srcPath
	destPath = destPath ? path.join(destPath) : buildConfig.globalOptions.js.destPath
	const tasks = files.map((entry) => {
		return function buildingJsFiles () {
			return src(entry.src.map((src) => {
				return srcPath + src
			}))
				.pipe(plumber(errorHandler))
				.pipe(mode.development(sourcemaps.init()))
				.pipe(( mode.production( uglify() ) ))
				.pipe(concat(entry.dest))
				.pipe(mode.development(sourcemaps.write()))
				.pipe(dest(destPath))
		}
	})
	// Execute tasks in series
	series(tasks)((error) => {
		if (error) {
			done(error)
		} else {
			done()
		}
	})
}

function buildJsPackages() {
	const jsTasks = buildConfig.nodeModules.js.map((file) => {
		return function buildingJsPackages() {
			return src(path.join(buildConfig.nodeModules.srcPath, file.src))
				.pipe(uglify())
				.pipe(rename({ suffix: '.min' }))
				.pipe(dest(path.join(buildConfig.nodeModules.srcPath, file.dest)))
		}
	})

	return series.apply(null, jsTasks)
}

function buildModuleJsFiles(done) {
	buildJs(done,
		buildConfig.modules.moduleUglifyList,
		buildConfig.modules.srcPath,
		buildConfig.modules.srcPath
	)
}

function buildModuleJsMainFile(done) {
	buildJs(
		done,
		buildConfig.modulesMainFile.modulesMainFileUglifyList,
		buildConfig.modulesMainFile.srcPath,
		buildConfig.modulesMainFile.destPath
	)
}

/**
 * Recursively processes all JavaScript files within a given directory and its subdirectories.
 * @param {string} directoryPath - The path of the directory to be processed.
 * @param {function} done - Callback function to be called when processing is complete.
 */
function processJsFilesInDirectory(directoryPath, done) {
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
					processJsFilesInDirectory(filePath, (err) => {
						if (--pending === 0) done(err) // Call done only when all recursive calls are done
					})
				} else if (path.extname(file) === '.js' && !file.endsWith('.min.js')) {
					processJsFile(filePath, () => {
						if (--pending === 0) {
							done()
						}
					})
				} else {
					if (--pending === 0) done() // Call done if no further processing is needed
				}
			})
		})
	})
}

/**
 * Minifies a single JavaScript file and outputs the minified file to the same folder.
 * @param {string} filePath - The path of the JavaScript file to be processed.
 * @param {function} done - Callback function to be called when processing is complete.
 */
function processJsFile(filePath, done) {
	const destPath = path.dirname(filePath)
	const fileNameWithoutExtension = path.basename(filePath, '.js')
	const destFileName = fileNameWithoutExtension + '.min.js'

	src(filePath)
		.pipe(plumber(errorHandler))
		.pipe(mode.development(sourcemaps.init()))
		.pipe(mode.production(uglify()))
		.pipe(mode.development(sourcemaps.write()))
		.pipe(rename(destFileName))
		.pipe(dest(destPath))
		.on('end', done)
}

function buildJsLibs(done) {
	const srcPath = buildConfig.globalOptions.jsLibs.srcPath
	processJsFilesInDirectory(srcPath, done)
}

module.exports = {
	buildJs,
	buildJsPackages,
	buildModuleJsFiles,
	buildModuleJsMainFile,
	buildJsLibs
}
