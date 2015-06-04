var gulp = require('gulp');
var util = require('gulp-util');
var cjsx = require('gulp-cjsx');
var browserify = require('gulp-browserify');
var watch = require('gulp-watch')

gulp.task('transpile', function() {
  gulp.src('src/*.cjsx')
    .pipe(watch('src/*.cjsx'))
    .pipe(cjsx({bare: true}).on('error', util.log))
    .pipe(gulp.dest('build'))
});

gulp.task('browserify', ['transpile'], function() {
  gulp.src('build/app.js')
    .pipe(watch('build/*.js'))
    .pipe(browserify({insertGlobals: true}))
    .pipe(gulp.dest('js'))
});
