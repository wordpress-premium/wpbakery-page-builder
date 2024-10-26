const fs = require('fs')
const path = require('path')

const pkg = require('../package.json')
const buildConfig = require('../build-config.json')

const banner = `/*!
* ${pkg.nativeName} v${pkg.version} (${pkg.homepage})
* Copyright 2011-${new Date().getFullYear()} ${pkg.author}
* License: Commercial. More details: ${pkg.license}
*/`

const jsBanner = `${banner}
	// jscs:disable
	// jshint ignore: start \n`

function applyBanner(directoryPath, banner, fileExtension, done) {
	fs.readdir(directoryPath, (err, files) => {
		if (err) {
			console.error('Error:', err)
			return done(err)
		}

		let pending = files.length
		if (pending === 0) return done() // No files, complete immediately

		files.forEach((file) => {
			var filePath = path.join(directoryPath, file)
			fs.stat(filePath, (err, stats) => {
				if (err) {
					console.error('Error:', err)
					done(err)
					return
				}

				if (stats.isDirectory()) {
					// Recursively process directories
					applyBanner(filePath, banner, fileExtension, (err) => {
						if (--pending === 0) done(err)
					})
				} else if (path.extname(file) === fileExtension && file.endsWith('.min' + fileExtension)) {
					// Process files with specified extension
					fs.readFile(filePath, 'utf8', (err, data) => {
						if (err) {
							console.error('Error reading file:', err)
							if (--pending === 0) done(err)
							return
						}

						var newData = banner + data
						fs.writeFile(filePath, newData, 'utf8', (err) => {
							if (err) {
								console.error('Error writing file:', err)
								if (--pending === 0) done(err)
								return
							}

							if (--pending === 0) done()
						})
					})
				} else {
					if (--pending === 0) done()
				}
			})
		})
	})
}

function addJsBanner(done) {
	applyBanner(buildConfig.globalOptions.jsLibs.srcPath, jsBanner, '.js', done)
}

function addCssBanner(done) {
	applyBanner(buildConfig.globalOptions.less.destPath, banner, '.css', done)
}

module.exports = {
	addJsBanner,
	addCssBanner
}
