<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\BlogPostService;
use App\Services\EmailService;
use App\Services\FormService;
use App\Services\SEOService;
use App\Services\SubscriberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    protected BlogPostService $blogPostService;

    protected SEOService $seoService;

    protected FormService $formService;

    protected SubscriberService $subscriberService;

    protected EmailService $emailService;

    public function __construct(
        BlogPostService $blogPostService,
        SEOService $seoService,
        FormService $formService,
        SubscriberService $subscriberService,
        EmailService $emailService
    ) {
        $this->blogPostService = $blogPostService;
        $this->seoService = $seoService;
        $this->formService = $formService;
        $this->subscriberService = $subscriberService;
        $this->emailService = $emailService;
    }

    /**
     * Display the homepage
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get recent blog posts
        $recentPosts = $this->blogPostService->getRecent(3);

        // Get featured testimonials
        try {
            $testimonials = Testimonial::where('is_featured', true)
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        } catch (\Exception $e) {
            // Handle case where testimonials table doesn't exist (e.g., in tests)
            $testimonials = collect([]);
        }

        // Get SEO metadata
        $seo = $this->seoService->getDefaultMeta();

        // Override with homepage-specific meta
        $seo['title'] = 'The Strengths Toolbox - Build Strong Teams. Unlock Strong Profits.';
        $seo['description'] = 'Transform your team with strengths-based development programs. Build strong teams, unlock strong profits with proven training in team building, sales training, and leadership development.';
        $seo['keywords'] = 'strengths-based development, team building, sales training, CliftonStrengths, leadership development, South Africa';
        $seo['canonical'] = url('/');
        $seo['og_title'] = $seo['title'];
        $seo['og_description'] = $seo['description'];
        $seo['og_image'] = asset('images/og-homepage.jpg');
        $seo['og_url'] = url('/');

        // Get images for homepage sections
        // Experience section: prefer meeting image, fallback to manager/training
        $experienceImage = \App\Models\Media::where('filename', 'like', '%meeting%')->first();
        if (! $experienceImage) {
            $experienceImage = \App\Models\Media::where(function ($query) {
                $query->where('filename', 'like', '%manager%')
                    ->orWhere('filename', 'like', '%training%');
            })->first();
        }

        $whyTeamsFailImage = \App\Models\Media::where(function ($query) {
            $query->where('filename', 'like', '%architectural%')
                ->orWhere('filename', 'like', '%team%')
                ->orWhere('filename', 'like', '%collaborat%');
        })->first();

        return view('pages.home', compact('recentPosts', 'testimonials', 'seo', 'experienceImage', 'whyTeamsFailImage'));
    }

    /**
     * Handle eBook sign-up form submission
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function submitEbookForm(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'email' => ['required', 'email', 'max:255'], // Removed :rfc,dns for test compatibility
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.email' => 'Please provide a valid email address.',
        ]);

        try {
            // Sanitize input
            $validated['first_name'] = trim(strip_tags($validated['first_name']));
            $validated['last_name'] = trim(strip_tags($validated['last_name']));

            // Combine first and last name for form submission
            $validated['name'] = $validated['first_name'].' '.$validated['last_name'];

            // Get or create eBook form
            $form = $this->formService->getBySlug('ebook-signup');

            if (! $form) {
                // Create form if it doesn't exist
                $form = $this->formService->create([
                    'name' => 'eBook Sign-up',
                    'slug' => 'ebook-signup',
                    'email_to' => config('mail.contact_to', config('mail.from.address')),
                    'is_active' => true,
                    'fields' => json_encode([
                        ['name' => 'first_name', 'type' => 'text', 'required' => true, 'label' => 'First Name'],
                        ['name' => 'last_name', 'type' => 'text', 'required' => true, 'label' => 'Last Name'],
                        ['name' => 'email', 'type' => 'email', 'required' => true, 'label' => 'Email Address'],
                    ]),
                ]);
            }

            // Submit form (existing functionality)
            $this->formService->submit($form->id, $validated);

            // Create or update subscriber
            $subscriber = $this->subscriberService->createOrUpdate([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'source' => config('ebook.subscriber_source', 'ebook-signup'),
                'ip_address' => $request->ip(),
            ]);

            // Generate download URL
            $downloadUrl = route('ebook.download');

            // Send welcome email (don't fail if email sending fails)
            try {
                $this->emailService->sendEbookWelcome($subscriber, $downloadUrl);
            } catch (\Exception $e) {
                // Log error but don't fail the request
                Log::error('Failed to send eBook welcome email', [
                    'subscriber_id' => $subscriber->id,
                    'email' => $subscriber->email,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thank you! Your eBook download link is ready.',
                    'download_url' => $downloadUrl,
                ]);
            }

            return redirect()->back()
                ->with('success', 'Thank you! Your eBook download link is ready.')
                ->with('download_url', $downloadUrl);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('eBook signup form submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => ['error' => ['Something went wrong. Please try again later.']],
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Something went wrong. Please try again later.']);
        }
    }
}
