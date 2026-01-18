<?php

use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFormController;
use App\Http\Controllers\Admin\AdminMediaController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\AdminSEOController;
use App\Http\Controllers\Admin\AdminTagController;
use App\Http\Controllers\Admin\AdminTestimonialController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

// Admin authentication routes (outside admin.auth middleware)
Route::middleware('guest.admin')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Password reset routes
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Admin routes (protected by admin.auth middleware)
Route::middleware('admin.auth')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Page management
    Route::resource('pages', AdminPageController::class);
    Route::get('/pages/{id}/preview', [AdminPageController::class, 'preview'])->name('pages.preview');

    // Blog management
    Route::resource('blog', AdminBlogController::class);
    Route::resource('blog/categories', AdminCategoryController::class)->names([
        'index' => 'blog.categories.index',
        'create' => 'blog.categories.create',
        'store' => 'blog.categories.store',
        'edit' => 'blog.categories.edit',
        'update' => 'blog.categories.update',
        'destroy' => 'blog.categories.destroy',
    ]);
    Route::resource('blog/tags', AdminTagController::class)->names([
        'index' => 'blog.tags.index',
        'create' => 'blog.tags.create',
        'store' => 'blog.tags.store',
        'edit' => 'blog.tags.edit',
        'update' => 'blog.tags.update',
        'destroy' => 'blog.tags.destroy',
    ]);

    // Form management
    Route::get('/forms', [AdminFormController::class, 'index'])->name('forms.index');
    Route::get('/forms/create', [AdminFormController::class, 'create'])->name('forms.create');
    Route::post('/forms', [AdminFormController::class, 'store'])->name('forms.store');
    Route::get('/forms/{formId}/submissions', [AdminFormController::class, 'submissions'])->name('forms.submissions');
    Route::get('/forms/{formId}/submissions/{submissionId}', [AdminFormController::class, 'showSubmission'])->name('forms.submission.show');
    Route::get('/forms/{formId}/export', [AdminFormController::class, 'export'])->name('forms.export');

    // Media management
    Route::get('/media', [AdminMediaController::class, 'index'])->name('media.index');
    Route::post('/media/upload', [AdminMediaController::class, 'upload'])->name('media.upload');
    Route::get('/media/{media}', [AdminMediaController::class, 'show'])->name('media.show');
    Route::put('/media/{media}', [AdminMediaController::class, 'update'])->name('media.update');
    Route::delete('/media/{media}', [AdminMediaController::class, 'destroy'])->name('media.destroy');

    // Testimonial management
    Route::resource('testimonials', AdminTestimonialController::class);

    // SEO management
    Route::get('/seo', [AdminSEOController::class, 'index'])->name('seo.index');
    Route::get('/seo/pages/{page}/edit', [AdminSEOController::class, 'editPage'])->name('seo.edit-page');
    Route::put('/seo/pages/{page}', [AdminSEOController::class, 'updatePage'])->name('seo.update-page');
    Route::get('/seo/posts/{post}/edit', [AdminSEOController::class, 'editPost'])->name('seo.edit-post');
    Route::put('/seo/posts/{post}', [AdminSEOController::class, 'updatePost'])->name('seo.update-post');

    // Chatbot management
    Route::prefix('chatbot')->name('chatbot.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminChatbotController::class, 'index'])->name('index');
        Route::post('/update', [\App\Http\Controllers\Admin\AdminChatbotController::class, 'update'])->name('update');
        Route::post('/test', [\App\Http\Controllers\Admin\AdminChatbotController::class, 'test'])->name('test');

        // Prompt management
        Route::prefix('prompts')->name('prompts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminChatbotPromptController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\AdminChatbotPromptController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\AdminChatbotPromptController::class, 'store'])->name('store');
            Route::get('/{prompt}/edit', [\App\Http\Controllers\Admin\AdminChatbotPromptController::class, 'edit'])->name('edit');
            Route::put('/{prompt}', [\App\Http\Controllers\Admin\AdminChatbotPromptController::class, 'update'])->name('update');
            Route::delete('/{prompt}', [\App\Http\Controllers\Admin\AdminChatbotPromptController::class, 'destroy'])->name('destroy');
            Route::post('/{prompt}/test', [\App\Http\Controllers\Admin\AdminChatbotPromptController::class, 'test'])->name('test');
        });

        // Conversation management
        Route::prefix('conversations')->name('conversations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminChatbotConversationController::class, 'index'])->name('index');
            Route::get('/{conversation}', [\App\Http\Controllers\Admin\AdminChatbotConversationController::class, 'show'])->name('show');
            Route::delete('/{conversation}', [\App\Http\Controllers\Admin\AdminChatbotConversationController::class, 'destroy'])->name('destroy');
            Route::get('/{conversation}/export', [\App\Http\Controllers\Admin\AdminChatbotConversationController::class, 'export'])->name('export');
        });
    });
});
