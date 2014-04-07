live-reload-service-provider
=========================

A Silex Service Provider for use in conjunction with Grunt-Contrib-Watch.

## Registering the ServiceProvider

```php
$app = new Silex\Application();
$app['ten24.livereload.options'] = array(
  'port' => 35599,
  'host' => 'localhost',
  'enabled' => true,
  'check_server_presence' => true);
$app->register(new Ten24\Silex\Provider\LiveReloadServiceProvider());
```

Once registered, the provider will inject a livereload.js script before the ending body tag. When used in conjunction with grunt-contrib-watch, your JS, SCSS, LESS, or other tasks you've configured in your Gruntfile.js will run and trigger a page/asset refresh when Grunt tasks have finished successfully.

**Note** This injection is dependant on the presence of an X-DEBUG-TOKEN response header. The simplest way to achieve this is to use Silex's Web Profiler (https://github.com/silexphp/Silex-WebProfiler). Plus it gives you a buinch of other great tools while you're in dev.

```php
if ($app['env'] == 'dev')
{
    $app->register(new Silex\Provider\WebProfilerServiceProvider(), array(
            'profiler.cache_dir' => $app['cache.path'].'/profiler',
            'profiler.mount_prefix' => '/_profiler',
    ));
}
```

##Options

The following options are available, the provider looks in $app['ten24.livereload.options'] for its configuration.

- host (optional, default: 'localhost')
- port (optional, default: 35729)
- enabled (optional, default: true)
- check_server_presence (optional, default: true)

##Example

Gruntfile.js

```javascript
module.exports = function (grunt) {
    "use strict";

    var MyProject;

    var resourcesPath = 'src/MyProject/Resources/';
    
    MyProject = {
        'destination':  'web/frontend/',
        'js':           [resourcesPath+'public/**/*.js', '!'+ resourcesPath+'public/vendor/**/*.js', 'Gruntfile.js'],
        'all_scss':     [resourcesPath+'public/scss/**/*.scss', bundlesPath+'public/scss/**/*.scss'],
        'scss':         [resourcesPath+'public/scss/style.scss'],
    };

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        watch: {
            MyProjectScss: {
                files: MyProject.all_scss,
                tasks: ['sass', 'cmq', 'cssmin']
            },
            MyProjectJs: {
                files: MyProject.js,
                tasks: ['uglify', 'concat']
            },
            MyProjectImages: {
                files: MyProject.img,
                tasks: ['imagemin:Ten24MemFaultBundle'],
                options: {
                    event: ['added', 'changed']
                }
            },
            livereload: {
                files: ['web/frontend/css/style.min.css', 'web/frontend/js/script.min.js'],
                options: {
                    livereload: true
                }
            }
        },

        sass: {
            MyProject: {
                options: {
                    style: 'compressed'
                },
                files: {
                    'web/frontend/.temp/css/style.css': [ resourcesPath+'public/scss/style.scss' ],
                }
            }
        },

        cmq: {
            MyProject: {
                files: {
                    'web/frontend/.temp/css/': 'web/frontend/.temp/css/style.css'
                }
            }
        },

        cssmin: {
            MyProject: {
                files: {
                    'web/frontend/css/style.min.css': [
                        'web/frontend/.temp/css/style.css'
                    ]
                }
            }
        },

        jshint: {
            options: {
                camelcase: true,
                curly: true,
                eqeqeq: true,
                eqnull: true,
                forin: true,
                indent: 4,
                trailing: true,
                undef: true,
                browser: true,
                devel: true,
                node: true,
                globals: {
                    jQuery: true,
                    $: true
                }
            },
            MyProject: {
                files: {
                    src: MyProject.js
                }
            }
        },

        uglify: {
            vendors: {
                options: {
                    mangle: {
                        except: ['jQuery']
                    }
                },
                files: {
                    'web/frontend/.temp/js/vendors.min.js': [
                        'web/vendor/jquery/jquery.js',
                        'web/vendor/bootstrap-sass/js/collapse.js',
                        'web/vendor/bootstrap-sass/js/dropdown.js',
                        'web/vendor/fancybox/source/jquery.fancybox.js',
                    ]
                }
            },
            MyProject: {
                files: {
                    'web/frontend/.temp/js/app.min.js': [resourcesPath+'public/js/**/*.js']
                }
            }
        },

        concat: {
            js: {
                src: [
                    'web/frontend/js/modernizr-custom.js',
                    'web/frontend/.temp/js/vendors.min.js',
                    'web/frontend/.temp/js/app.min.js'
                ],
                dest: 'web/frontend/js/footer.min.js'
            }
        },

        modernizr: {
            MyProject: {
                devFile: 'remote',
                parseFiles: true,
                files: {
                    src: [
                        MyProject.js,
                        MyProject.all_scss,
                        MyProject.twig
                    ]
                },
                outputFile: MyProject.destination + 'js/modernizr-custom.js',

                extra: {
                    'shiv' : false,
                    'printshiv' : false,
                    'load' : true,
                    'mq' : false,
                    'cssclasses' : true
                },
                extensibility: {
                    'addtest' : false,
                    'prefixed' : false,
                    'teststyles' : false,
                    'testprops' : false,
                    'testallprops' : false,
                    'hasevents' : false,
                    'prefixes' : false,
                    'domprefixes' : false
                }
            }
        }

    });

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks("grunt-modernizr");
    grunt.loadNpmTasks('grunt-notify');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-combine-media-queries');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    grunt.registerTask('default', ['watch']);
    grunt.registerTask('build', ['sass', 'cmq', 'cssmin', 'modernizr', 'uglify', 'concat']);
};

```

Reference the compiled file in your template

```html
<link rel="stylesheet" href="/frontend/css/style.min.css" type="text/css" />
```

Run 'grunt-watch' in a shell, alter your source files, and try to stop that nasty Control-R habit, OK? :D
