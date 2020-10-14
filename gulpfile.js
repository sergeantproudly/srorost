'use strict';

const gulp = require('gulp');
const del = require('del');
const browserSync = require('browser-sync').create();

const concat = require('gulp-concat');
const autoprefixer = require('gulp-autoprefixer');
const cleanCSS = require('gulp-clean-css');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');   
const uglify = require('gulp-uglify');
const imagemin = require('gulp-imagemin');

const jsFiles = [
	'./src/js/jquery-3.1.1.min.js',
	'./src/js/jquery-ui.min.js',
	'./src/js/jquery.inputmask.js',
	'./src/js/jquery.touchSwipe.min.js',
	'./src/js/jquery.mousewheel.min.js',
	'./src/js/jquery.magnific-popup.js',
	'./src/js/slick.min.js',	
	'./src/js/wow.min.js',
	'./src/js/checks.js',
	'./src/js/messages.js',
	'./src/js/scripts.js'
];

sass.compiler = require('node-sass');

function styles() {
	return gulp.src('./src/css/style.scss')
				.pipe(sourcemaps.init())
				.pipe(concat('style.css'))		
				.pipe(autoprefixer({
		            browsers: ['> 0.1%'],
		            cascade: false
		        }))
		        /*
		        .pipe(cleanCSS({
		        	level: 2
		        }))
		        */
		        .pipe(sass.sync({outputStyle: 'compressed'}).on('error', sass.logError))
		        //.pipe(sourcemaps.write())
				.pipe(gulp.dest('./assets/css'))
				.pipe(browserSync.stream());
}

function scripts() {
	return gulp.src(jsFiles)
				.pipe(concat('scripts.js'))
				.pipe(uglify({
					toplevel: true
				}))
				.pipe(gulp.dest('./assets/js'))
				.pipe(browserSync.stream());
}

function images() {
	return gulp.src('./src/images/*')
        .pipe(imagemin({
        	progressive: true
        }))
        .pipe(gulp.dest('./assets/images'));
}

function fonts() {
	return gulp.src('./src/fonts/**/*')
		.pipe(gulp.dest('./assets/fonts'));
}

function watch() {
	browserSync.init({
        proxy: 'srorost.local'
    });

	gulp.watch('./src/css/**/*.scss', styles);
	gulp.watch('./src/js/**/*.js', scripts);
	gulp.watch('./**/*.htm').on('change', browserSync.reload);
	gulp.watch('./**/*.php').on('change', browserSync.reload);
}

function clean() {
	return del(['assets/*']);
}

//gulp.task('styles', styles);
//gulp.task('scripts', scripts);
gulp.task('images', images);
gulp.task('watch', watch);

gulp.task('build', gulp.series(clean,
						gulp.parallel(styles, scripts, images, fonts)
					));

gulp.task('dev', gulp.series('build', 'watch'));