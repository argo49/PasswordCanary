var gulp    = require('gulp');
var sass    = require('gulp-sass');
var prefix  = require('gulp-autoprefixer');
var please  = require('gulp-pleeease');
var rename  = require('gulp-rename');
var concat  = require('gulp-concat');
var uglify  = require('gulp-uglify');
var minHtml = require('gulp-minify-html');


gulp.task('default', ['sass', 'scripts', 'watch', 'html']);

// Compile scss
gulp.task('sass', function() {
    return gulp.src('src/scss/*.scss')
        .pipe(sass())
        .pipe(prefix())
        .pipe(please())
        .pipe(gulp.dest('public/css'));
});

// Concat and minify js
gulp.task('scripts', function() {
    return gulp.src('src/js/*.js')
        .pipe(concat('scripts.js'))
        .pipe(uglify())
        .pipe(gulp.dest('public/js'));
});

// Minify HTML
gulp.task('html', function () {
    var options = {conditionals: true};
    return gulp.src('src/*.html')
        .pipe(minHtml(options))
        .pipe(gulp.dest('public'))
});

// Watch things
gulp.task('watch', function() {
    gulp.watch('src/scss/**/*.scss', ['sass']);
    gulp.watch('src/js/*.js', ['scripts']);
    gulp.watch('src/*.html', ['html']);
});