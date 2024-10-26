const path = require('path')
const { watch } = require('gulp')
const buildConfig = require('../build-config.json')
const { buildJs, buildJsLibs } = require('./buildScripts')

function watchJs() {
	buildConfig.uglifyList.forEach((entry) => {
		entry.src.forEach((path) => {
			const jsFile = buildConfig.globalOptions.js.srcPath + path
			watch(jsFile, buildJs)
		})
	})
}

function watchJsLibs(done) {
	const srcPath = buildConfig.globalOptions.jsLibs.srcPath
	return watch(path.join(srcPath, '**/*.js'), { ignoreInitial: false })
		.on('change', (filePath) => {
			// Only trigger buildJsLibs if the file wasn't a minified file
			if (!filePath.endsWith('.min.js')) {
				buildJsLibs(done)
			}
		})
}

function watchModuleJsFiles(done) {
	const srcPath = buildConfig.modules.srcPath
	return watch(path.join(srcPath, '**/*.js'), { ignoreInitial: false })
		.on('change', (filePath) => {
			if (!filePath.endsWith('.min.js')) {
				buildJs(done,
					buildConfig.modules.moduleUglifyList,
					buildConfig.modules.srcPath,
					buildConfig.modules.srcPath
				)
			}
		})
}

function watchModuleJsMainFile(done) {
	const srcPath = buildConfig.modulesMainFile.srcPath
	return watch(path.join(srcPath, '**/*.js'), { ignoreInitial: false })
		.on('change', (filePath) => {
			if (!filePath.endsWith('.min.js')) {
				buildJs(
					done,
					buildConfig.modulesMainFile.modulesMainFileUglifyList,
					buildConfig.modulesMainFile.srcPath,
					buildConfig.modulesMainFile.destPath
				)
			}
		})
}

module.exports = {
	watchJs,
	watchJsLibs,
	watchModuleJsFiles,
	watchModuleJsMainFile
}
