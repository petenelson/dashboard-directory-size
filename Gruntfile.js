module.exports = function( grunt ) {

	require('phplint').gruntPlugin(grunt);

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

		dirs: {
			lang: 'languages',
		},

		potomo: {
			dist: {
				options: {
					poDel: false
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.lang %>',
					src: ['*.po'],
					dest: '<%= dirs.lang %>',
					ext: '.mo',
					nonull: true
				}]
			}
		},

		clean:  {
			wp: [ "release" ]
		},

		phplint: {
			options: {
				limit: 10,
				stdout: true,
				stderr: true
			},
			files: [
				'admin/**/*.php',
				'includes/*.php',
				'*.php'
			]
		},

		phpunit: {
			'default': {
				cmd: 'phpunit',
				args: ['-c', 'phpunit.xml.dist']
			},
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
		'grunt-wp-i18n',
		'grunt-potomo'
		];

	for	( var i = 0; i < tasks.length; i++ ) {
		grunt.loadNpmTasks( tasks[ i ] );
	};


	// Register tasks

	grunt.registerTask( 'test', [ 'phplint', 'phpunit' ] );

	// create release for WordPress repository
	grunt.registerTask( 'wp', [ 'clean', 'copy' ] );

	grunt.registerMultiTask('phpunit', 'Runs PHPUnit tests', function() {
		grunt.util.spawn({
			cmd: this.data.cmd,
			args: this.data.args,
			opts: {stdio: 'inherit'}
		}, this.async());
	});

	grunt.util.linefeed = '\n';

};
