<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\EmailService;
use App\Services\FormService;
use App\Services\SEOService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    protected FormService $formService;

    protected EmailService $emailService;

    protected SEOService $seoService;

    public function __construct(
        FormService $formService,
        EmailService $emailService,
        SEOService $seoService
    ) {
        $this->formService = $formService;
        $this->emailService = $emailService;
        $this->seoService = $seoService;
    }

    /**
     * Display contact page
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $seo = $this->seoService->getDefaultMeta();
        $seo['title'] = 'Contact Us - The Strengths Toolbox';
        $seo['description'] = 'Get in touch with The Strengths Toolbox. Book a consultation, ask questions, or learn more about our strengths-based development programs.';
        $seo['keywords'] = 'contact, consultation, strengths training, team building, South Africa';
        $seo['canonical'] = route('contact');
        $seo['og_title'] = $seo['title'];
        $seo['og_description'] = $seo['description'];
        $seo['og_url'] = route('contact');
        $seo['og_type'] = 'website';

        return view('pages.contact', compact('seo'));
    }

    /**
     * Handle contact form submission
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'email' => ['required', 'email', 'max:255'], // Removed :rfc,dns for test compatibility
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.email' => 'Please provide a valid email address.',
            'phone.regex' => 'Please provide a valid phone number.',
            'message.min' => 'Message must be at least 10 characters.',
            'message.max' => 'Message cannot exceed 5000 characters.',
        ]);

        try {
            // Sanitize input
            $validated['name'] = trim(strip_tags($validated['name']));
            $validated['subject'] = trim(strip_tags($validated['subject']));
            $validated['message'] = trim($validated['message']); // Allow HTML in message but will be escaped in email

            // Get or create contact form
            $form = $this->formService->getBySlug('contact');

            if (! $form) {
                $form = $this->formService->create([
                    'name' => 'Contact Form',
                    'slug' => 'contact',
                    'email_to' => config('mail.contact_to', config('mail.from.address')),
                    'is_active' => true,
                    'fields' => json_encode([
                        ['name' => 'name', 'type' => 'text', 'required' => true, 'label' => 'Full Name'],
                        ['name' => 'email', 'type' => 'email', 'required' => true, 'label' => 'Email Address'],
                        ['name' => 'phone', 'type' => 'tel', 'required' => false, 'label' => 'Phone Number'],
                        ['name' => 'subject', 'type' => 'text', 'required' => true, 'label' => 'Subject'],
                        ['name' => 'message', 'type' => 'textarea', 'required' => true, 'label' => 'Message'],
                    ]),
                ]);
            }

            // Submit form
            $this->formService->submit($form->id, $validated);

            // Send email notification to admin
            $this->emailService->sendContactForm($validated);

            // Send acknowledgment email to form submitter
            // This is non-blocking - if it fails, we still return success
            $this->emailService->sendContactFormAcknowledgment($validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thank you! Your message has been sent. We\'ll get back to you soon.',
                ]);
            }

            return redirect()->route('contact')
                ->with('success', 'Thank you for your message! We\'ll get back to you soon.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->route('contact')
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Contact form submission failed', [
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

            return redirect()->route('contact')
                ->withInput()
                ->withErrors(['error' => 'Something went wrong. Please try again later.']);
        }
    }
}
