var gulp = require('gulp');
var util = require('gulp-util');
var cjsx = require('gulp-cjsx');
var browserify = require('gulp-browserify');
var watch = require('gulp-watch');
var plumber = require('gulp-plumber');

gulp.task('transpile', function() {
  gulp.src('src/*.cjsx')
    .pipe(plumber())
    .pipe(watch('src/*.cjsx'))
    .pipe(cjsx({bare: true}))
    .pipe(gulp.dest('build'))
});

gulp.task('default', ['transpile'], function() {
  gulp.src('build/app.js')
    .pipe(plumber())
    .pipe(watch('build/*.js'))
    .pipe(browserify({insertGlobals: true}))
    .pipe(gulp.dest('js'))
});
