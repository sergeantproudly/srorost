import gulp from 'gulp';
import { deleteAsync } from 'del';
import browserSync from 'browser-sync';
import concat from 'gulp-concat';
import autoprefixer from 'gulp-autoprefixer';
import cleanCSS from 'gulp-clean-css';
import dartSass from 'sass';
import gulpSass from 'gulp-sass';
import sourcemaps from 'gulp-sourcemaps';
import uglify from 'gulp-uglify';
import imagemin from 'gulp-imagemin';
import imageminMozjpeg from 'imagemin-mozjpeg';
import imageminOptipng from 'imagemin-optipng';

const sass = gulpSass(dartSass);

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

function styles() {
	return gulp.src('./src/css/style.scss')
		.pipe(sourcemaps.init())
		.pipe(sass({ outputStyle: 'expanded' }).on('error', sass.logError))
		.pipe(autoprefixer({ overrideBrowserslist: ['> 0.1%'], cascade: false }))
		.pipe(cleanCSS({ level: 2 }))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('./assets/css'))
		.pipe(browserSync.stream());
};

function scripts() {
	return gulp.src(jsFiles)
		.pipe(concat('scripts.js'))
		.pipe(uglify({
			toplevel: true
		}))
		.pipe(gulp.dest('./assets/js'))
		.pipe(browserSync.stream());
};

function images() {
	return gulp.src('./src/images/**/*')
		.pipe(imagemin([
			imageminMozjpeg({ quality: 75, progressive: true }),
			imageminOptipng({ optimizationLevel: 5 })
		]))
		.pipe(gulp.dest('./assets/images'));
};

function fonts() {
	return gulp.src('./src/fonts/**/*')
		.pipe(gulp.dest('./assets/fonts'));
};

function clean() {
	return deleteAsync(['assets/*']);
};

function watchFiles() {
	browserSync.init({
		proxy: 'srorost.local',
		notify: false,
		open: false
	});

	gulp.watch('./src/css/**/*.scss', styles);
	gulp.watch('./src/js/**/*.js', scripts);
	gulp.watch(['./**/*.htm', './**/*.php']).on('change', browserSync.reload);
};

const build = gulp.series(clean,
	gulp.parallel(styles, scripts, images, fonts)
);

const dev = gulp.series(build, watchFiles);


export { styles, scripts, images, fonts, clean, watchFiles, build, dev };


export default dev;