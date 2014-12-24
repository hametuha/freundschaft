module.exports = function (grunt) {

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        compass: {

            dist: {
                options: {
                    config: 'config.rb'
                }
            }

        },

        jshint: {

            options: {
                jshintrc: '.jshintrc',
                force: true
            },

            files: [
                'js/**/*.js',
                '!js/**/*.min.js'
            ]

        },

        uglify: {
            build: {
                options: {
                    sourceMap: true,
                    mangle: true,
                    compress: true
                },
                files: [{
                    expand: true,
                    cwd: './js/',
                    src: ['**/*.js', '!**/*.min.js'],
                    dest: './js/',
                    ext: '.min.js'
                }]
            }
        },

        watch: {

            js: {
                files: ['js/**/*.js', '!js/**/*.min.js'],
                tasks: ['jshint', 'uglify']
            },

            compass: {
                files: ['scss/*.scss'],
                tasks: ['compass']
            }
        }
    });

    // Load plugins
    grunt.loadNpmTasks('grunt-contrib-compass');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Register tasks
    grunt.registerTask('default', ['jshint', 'uglify', 'compass']);

};
