module.exports = function(grunt) {
    'use strict';

    var sass    = grunt.config('sass') || {};
    var watch   = grunt.config('watch') || {};
    var notify  = grunt.config('notify') || {};
    var root    = grunt.option('root') + '/taoTestCenter/views/';

    sass.taotestcenter = { };
    sass.taotestcenter.files = { };
    sass.taotestcenter.files[root + 'css/testcenter.css'] = root + 'scss/testcenter.scss';
    sass.taotestcenter.files[root + 'css/eligibilityEditor.css'] = root + 'scss/eligibilityEditor.scss';
    sass.taotestcenter.files[root + 'css/eligibilityTable.css']  = root + 'scss/eligibilityTable.scss';

    watch.taotestcentersass = {
        files : [root + 'scss/*.scss'],
        tasks : ['sass:taotestcenter', 'notify:taotestcentersass'],
        options : {
            debounceDelay : 1000
        }
    };

    notify.taotestcentersass = {
        options: {
            title: 'Grunt SASS',
            message: 'SASS files compiled to CSS'
        }
    };

    grunt.config('sass', sass);
    grunt.config('watch', watch);
    grunt.config('notify', notify);

    //register an alias for main build
    grunt.registerTask('taotestcentersass', ['sass:taotestcenter']);
};
