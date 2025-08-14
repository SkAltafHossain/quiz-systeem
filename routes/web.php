<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Home Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    // User Login Routes
    Route::prefix('user')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
        
        // Registration Routes
        Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [RegisterController::class, 'register']);
    });
    
    // Admin Login Routes
    Route::prefix('admin')->group(function () {
        Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
        Route::post('login', [AdminLoginController::class, 'login']);
    });
    
    // Forgot Password Routes (for both user and admin)
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.update');
});

// Logout Routes
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

// Admin Logout
Route::post('admin/logout', [AdminLoginController::class, 'logout'])
    ->name('admin.logout');

// Email Verification
Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Authenticated User Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Quiz routes
    Route::controller(QuizController::class)->group(function () {
        Route::get('/quizzes', 'index')->name('quizzes.index');
        Route::get('/quizzes/{quiz:slug}', 'show')->name('quizzes.show');
        
        // Quiz taking routes (will be implemented later)
        Route::middleware(['auth'])->group(function () {
            Route::get('/quizzes/{quiz:slug}/take', 'take')->name('quizzes.take');
            Route::post('/quizzes/{quiz:slug}/submit', 'submit')->name('quizzes.submit');
            Route::get('/quizzes/{quiz:slug}/results/{result}', 'results')->name('quizzes.results');
        });
    });
    
    // Profile Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
        
        // Quiz History & Leaderboard Routes
        Route::prefix('quizzes')->group(function () {
            // Quiz history (user's attempts)
            Route::get('/history', [\App\Http\Controllers\UserQuizController::class, 'index'])
                ->name('quiz.history');
                
            // Quiz leaderboard
            Route::get('/leaderboard', [\App\Http\Controllers\UserQuizController::class, 'leaderboard'])
                ->name('leaderboard');
                
            // Quiz-specific history
            Route::get('/{quiz}/history', [\App\Http\Controllers\UserQuizController::class, 'quizHistory'])
                ->name('quiz.specific.history')
                ->middleware('can:view,quiz');
                
            // Delete quiz attempt
            Route::delete('/attempts/{attempt}', [\App\Http\Controllers\UserQuizController::class, 'destroy'])
                ->name('quiz.attempt.delete')
                ->middleware('can:delete,attempt');
        });
    });
});

// Admin Routes
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'admin'])
    ->group(function () {
        // Admin Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');
        
        // Quiz Management
        Route::prefix('quizzes')
            ->name('quizzes.')
            ->controller(\App\Http\Controllers\Admin\QuizController::class)
            ->group(function () {
                // Quiz listing with DataTables
                Route::get('/', 'index')->name('index');
                
                // Show create quiz form
                Route::get('/create', 'create')->name('create');
                
                // Get quiz data for editing
                Route::get('/{quiz}/edit', 'edit')->name('edit');
                
                // Store new quiz
                Route::post('/', 'store')->name('store');
                
                // Update quiz
                Route::put('/{quiz}', 'update')->name('update');
                
                // Delete quiz
                Route::delete('/{quiz}', 'destroy')->name('destroy');
                
                // Toggle quiz status
                Route::post('/{quiz}/toggle-status', 'toggleStatus')->name('toggle-status');
                
                // Get categories for dropdown
                Route::get('/get/categories', 'getCategories')->name('categories');
            });
            
        // Categories Management (already implemented)
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)
            ->except(['show']);
            
        // Questions & Options Management
        Route::prefix('quizzes/{quiz}')->group(function () {
            // List questions for a quiz
            Route::get('/questions', [\App\Http\Controllers\Admin\QuestionController::class, 'index'])
                ->name('quizzes.questions.index');
                
            // Get question data for editing
            Route::get('/questions/{question}/edit', [\App\Http\Controllers\Admin\QuestionController::class, 'getQuestion'])
                ->name('quizzes.questions.get');
                
            // Store new question
            Route::post('/questions', [\App\Http\Controllers\Admin\QuestionController::class, 'store'])
                ->name('quizzes.questions.store');
                
            // Update question
            Route::put('/questions/{question}', [\App\Http\Controllers\Admin\QuestionController::class, 'update'])
                ->name('quizzes.questions.update');
                
            // Delete question
            Route::delete('/questions/{question}', [\App\Http\Controllers\Admin\QuestionController::class, 'destroy'])
                ->name('quizzes.questions.destroy');
                
            // Toggle question status
            Route::post('/questions/{question}/toggle-status', [\App\Http\Controllers\Admin\QuestionController::class, 'toggleStatus'])
                ->name('quizzes.questions.toggle-status');
        });
            
        // Results Management
        Route::prefix('results')
            ->name('results.')
            ->controller(\App\Http\Controllers\Admin\ResultController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/export', 'export')->name('export');
            });
            
        // User Management
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
            
        Route::prefix('results')
            ->name('results.')
            ->controller(\App\Http\Controllers\Admin\ResultController::class)
            ->group(function () {
                Route::get('/{result}', 'show')->name('show');
                Route::delete('/{result}', 'destroy')->name('destroy');
            });
    });
