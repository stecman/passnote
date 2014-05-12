/**
 * AMD config
 */
require.config( {

	/**
	 * Shim config
	 *
	 * Setup non-AMD modules
	 * See: http://requirejs.org/docs/api.html#config-shim
	 */
	shim: {},

	/**
	 * Paths config
	 *
	 * Setup shortcuts to AMD locations
	 * See: http://requirejs.org/docs/api.html#config-paths
	 */
	paths: {},

	/**
	 * Packages config
	 *
	 * Setup AMD packages
	 * See: http://requirejs.org/docs/api.html#packages
	 *
	 * @property {Object[]} packages
	 *  @property {String} packages[].name     AMD package name, AMD string of this package
	 *  @property {String} packages[].location Path to the AMD package root
	 *  @property {String} packages[].main     File name for the packages main module
	 */
	packages: [

		// Internal packages
		{ name: 'passnote', location: 'passnote',      main: 'init' },
		{ name: 'common',   location: 'common',        main: 'common' },

		// Bower packages
		{ name: 'requirejs',  location: '../components/requirejs' },
		{ name: 'react',      location: '../components/react',       main: 'react' },
		{ name: 'ace',       location: '../components/ace/lib/ace',     main: 'ace' },
	]

} );
