module.exports = function(grunt) {

    var requirejs   = grunt.config('requirejs') || {};
    var clean       = grunt.config('clean') || {};
    var copy        = grunt.config('copy') || {};

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var ext         = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);
    var out         = 'output';
    
    var paths = {
        'taoTestCenter'          : root + '/taoTestCenter/views/js',
        'taoTestCenterCss'       : root + '/taoTestCenter/views/css',
        'taoProctoring'          : root + '/taoProctoring/views/js',
        'taoProctoringCss'       : root + '/taoProctoring/views/css',
        'taoClientDiagnostic'    : root + '/taoClientDiagnostic/views/js',
        'taoClientDiagnosticCss' : root + '/taoClientDiagnostic/views/css',
        'taoQtiItem'             : root + '/taoQtiItem/views/js',
        'taoItems'               : root + '/taoItems/views/js',
        'taoQtiTest'             : root + '/taoQtiTest/views/js'
    };
    
    /**
     * Remove bundled and bundling files
     */
    clean.taotestcenterbundle = [out];

    /**
     * Compile tao files into a bundle
     */
    requirejs.taotestcenterbundle = {
        options: {
            baseUrl : '../js',
            dir : out,
            mainConfigFile : './config/requirejs.build.js',
            paths : paths,
            modules : [{
                name: 'taoTestCenter/controller/routes',
                include : ext.getExtensionsControllers(['taoProctoring']),
                exclude : ['mathJax'].concat(libs)
            }]
        }
    };

    /**
     * copy the bundles to the right place
     */
    copy.taotestcenterbundle = {
        files: [
            { src: [out + '/taoTestCenter/controller/routes.js'],  dest: root + '/taoTestCenter/views/js/controllers.min.js' },
            { src: [out + '/taoTestCenter/controller/routes.js.map'],  dest: root + '/taoTestCenter/views/js/controllers.min.js.map' }
        ]
    };

    grunt.config('clean', clean);
    grunt.config('requirejs', requirejs);
    grunt.config('copy', copy);

    // bundle task
    grunt.registerTask('taotestcenterbundle', ['clean:taotestcenterbundle', 'requirejs:taotestcenterbundle', 'copy:taotestcenterbundle']);
};
