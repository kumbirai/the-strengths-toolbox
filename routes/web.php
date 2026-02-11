<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\EbookController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\SearchController;
use App\Services\SEOService;
use Illuminate\Support\Facades\Route;

// Health check endpoints
Route::get('/health', [HealthController::class, 'index'])->name('health');
Route::get('/health/detailed', [HealthController::class, 'detailed'])->name('health.detailed');

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/ebook-signup', [HomeController::class, 'submitEbookForm'])
    ->middleware('rate.limit.forms')
    ->name('ebook.signup');

// eBook download
Route::get('/ebook/download', [EbookController::class, 'download'])->name('ebook.download');

// Blog routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/search', [BlogController::class, 'search'])->name('blog.search');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{slug}', [BlogController::class, 'tag'])->name('blog.tag');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Blog comment routes
Route::post('/blog/{slug}/comments', [\App\Http\Controllers\Web\BlogCommentController::class, 'store'])
    ->middleware('rate.limit.forms')
    ->name('blog.comments.store');
Route::post('/blog/{slug}/comments/{comment}/reply', [\App\Http\Controllers\Web\BlogCommentController::class, 'storeReply'])
    ->middleware('rate.limit.forms')
    ->name('blog.comments.reply');

// Contact routes
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])
    ->middleware('rate.limit.forms')
    ->name('contact.submit');

// Dynamic form submission route
Route::post('/forms/{slug}/submit', [\App\Http\Controllers\Web\FormController::class, 'submit'])
    ->middleware('rate.limit.forms')
    ->name('forms.submit');

// Booking route
Route::get('/booking', function (SEOService $seoService) {
    $seo = $seoService->getDefaultMeta();
    $seo['title'] = 'Book a Consultation - The Strengths Toolbox';
    $seo['description'] = 'Book your complimentary 30-minute consultation with The Strengths Toolbox. Schedule a time that works for you.';
    $seo['keywords'] = 'book consultation, schedule meeting, free consultation, strengths assessment';
    $seo['canonical'] = route('booking');
    $seo['og_title'] = $seo['title'];
    $seo['og_description'] = $seo['description'];
    $seo['og_url'] = route('booking');

    return view('pages.booking', compact('seo'));
})->name('booking');

// Static pages
Route::get('/about-us', [PageController::class, 'show'])->name('about-us');
Route::get('/strengths-programme', [PageController::class, 'show'])->name('strengths-programme');
Route::get('/keynote-talks', function (SEOService $seoService) {
    $seo = $seoService->getDefaultMeta();
    $seo['title'] = 'Keynote Talks - The Strengths Toolbox';
    $seo['description'] = 'Book Eberhard Niklaus for your next event. Engaging keynote talks on strengths-based development, team building, and business growth.';
    $seo['canonical'] = route('keynote-talks');
    $seo['og_url'] = route('keynote-talks');

    return view('pages.keynote-talks', compact('seo'));
})->name('keynote-talks');
Route::get('/books', function (SEOService $seoService) {
    $seo = $seoService->getDefaultMeta();
    $seo['title'] = 'Books - The Strengths Toolbox';
    $seo['description'] = 'Explore books and resources on strengths-based development, team building, and business growth from The Strengths Toolbox.';
    $seo['canonical'] = route('books');
    $seo['og_url'] = route('books');

    // Get eBook cover image
    $ebookImage = \App\Models\Media::where(function ($query) {
        $query->where('filename', 'like', '%ebook%')
            ->orWhere('filename', 'like', '%free%ebook%')
            ->orWhere('original_filename', 'like', '%Free-Ebook%');
    })->first();

    return view('pages.books', compact('seo', 'ebookImage'));
})->name('books');
Route::get('/testimonials', function (SEOService $seoService) {
    $seo = $seoService->getDefaultMeta();
    $seo['title'] = 'Testimonials - The Strengths Toolbox';
    $seo['description'] = 'Read testimonials from businesses that have transformed their teams with The Strengths Toolbox.';
    $seo['canonical'] = route('testimonials');
    $seo['og_url'] = route('testimonials');

    return view('pages.testimonials', compact('seo'));
})->name('testimonials');
Route::get('/privacy-statement', function (SEOService $seoService) {
    $seo = $seoService->getDefaultMeta();
    $seo['title'] = 'Privacy Statement - The Strengths Toolbox';
    $seo['description'] = 'Privacy statement and data protection policy for The Strengths Toolbox.';
    $seo['canonical'] = route('privacy');
    $seo['og_url'] = route('privacy');
    $seo['robots'] = 'noindex, follow'; // Don't index privacy statement

    return view('pages.privacy-statement', compact('seo'));
})->name('privacy');

// Search route (before catch-all)
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Robots.txt (before catch-all routes)
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

// Sitemap (before catch-all routes)
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Dynamic pages (catch-all for CMS pages - must be last)
// Allow slashes in slug for nested paths like 'strengths-based-development/teams'
// Exclude admin routes from catch-all
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '^(?!admin).*')
    ->name('pages.show');
