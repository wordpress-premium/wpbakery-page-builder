const path = require('path')
const { watch } = require('gulp')
const buildConfig = require('../build-config.json')
const {
	buildCss,
	buildFontLibs,
	buildCssLibs,
	buildModuleCss,
	buildModuleCssMainFile
} = require('./buildStyles')

function watchLess() {
	const lessFiles = buildConfig.globalOptions.less.srcPath + '**/*.less'
	watch(lessFiles, buildCss)
}

function watchFontLibs() {
	const cssFiles = buildConfig.globalOptions.fontLibs.srcFiles.map((path) => {
		return buildConfig.globalOptions.fontLibs.srcPath + path
	})
	watch(cssFiles, buildFontLibs)
}

function watchCssLibs(done) {
	const srcPath = buildConfig.globalOptions.cssLibs.srcPath
	return watch(path.join(srcPath, '**/*.less'))
		.on('change', () => {
			buildCssLibs(done)
		})
}

function watchModuleCssFiles(done) {
	const srcPath = buildConfig.modules.srcPath
	return watch(path.join(srcPath, '**/*.less'))
		.on('change', () => {
			buildModuleCss(done)
		})
}

function watchModuleCssMainFile(done) {
	const srcPath = buildConfig.modulesMainFile.srcPath
	return watch(path.join(srcPath, '**/*.less'))
		.on('change', () => {
			buildModuleCssMainFile(done)
		})
}

module.exports = {
	watchLess,
	watchFontLibs,
	watchCssLibs,
	watchModuleCssFiles,
	watchModuleCssMainFile
}
