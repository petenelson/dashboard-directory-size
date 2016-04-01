module.exports = function( grunt ) {

	grunt.initConfig( {
		pkg:    grunt.file.readJSON( 'package.json' ),

		makepot: {
			target: {
				options: {
					type:        'wp-plugin',
					mainFile:    'dashboard-directory-size.php'
				}
			}
		},

		clean:  {
			wp: [ "release" ]
		},

		copy:   {

			// create release for WordPress repository
			wp: {
				files: [

					// directories
					{ expand: true, src: ['admin/**'], dest: 'release/' },
					{ expand: true, src: ['includes/**'], dest: 'release/' },
					{ expand: true, src: ['languages/**'], dest: 'release/' },

					// root dir files
					{
						expand: true,
						src: [
							'*.php',
							'readme.txt',
							],
						dest: 'release/'
					}

				]
			} // wp

		}

	} ); // grunt.initConfig


	// Load tasks
	var tasks = [
		'grunt-contrib-clean',
		'grunt-contrib-copy',
		'grunt-wp-i18n'
		];

	for	( var i = 0; i < tasks.length; i++ ) {
		grunt.loadNpmTasks( tasks[ i ] );
	};


	// Register tasks

	// create release for WordPress repository
	grunt.registerTask( 'wp', [ 'clean', 'copy' ] );

	grunt.util.linefeed = '\n';

};
