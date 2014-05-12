({

	dir: "../_built",
	mainConfigFile: "./amd.config.js",
	preserveLicenseComments: false,


	/**
	 * Modules config
	 *
	 * There should be a module listed here for each built file
	 * See: https://github.com/jrburke/r.js/blob/master/build/example.build.js#L334-L403
	 *
	 * @property {Object[]} modules
	 *  @property {String} modules.name        AMD module name, first file to scan for dependencies
	 *  @property {String[]} [modules.include] AMD module names to include in this file
	 *  @property {String[]} [modules.exclude] AMD module names to exclude from this file (module dependencies
	 *    will be excluded as well)
	 */
	modules: [
		{
			name: 'nav'
		},
	]

})
