<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\FormService;
use App\Services\RecaptchaVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FormController extends Controller
{
    protected FormService $formService;

    protected RecaptchaVerifier $recaptchaVerifier;

    public function __construct(FormService $formService, RecaptchaVerifier $recaptchaVerifier)
    {
        $this->formService = $formService;
        $this->recaptchaVerifier = $recaptchaVerifier;
    }

    /**
     * Submit a dynamic form
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request, string $slug)
    {
        $form = $this->formService->getBySlug($slug);

        if (! $form) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form not found.',
                ], 404);
            }
            abort(404);
        }

        if (! $form->is_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This form is not currently active.',
                ], 403);
            }
            abort(403);
        }

        if ($request->filled('fax_number')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification failed. Please try again.',
                ], 422);
            }
            return redirect()->back()
                ->withErrors(['error' => 'Verification failed. Please try again.']);
        }

        if (! $this->recaptchaVerifier->verify($request->input('grecaptcha_response', ''), 'dynamic_form')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification failed. Please try again.',
                ], 422);
            }
            return redirect()->back()
                ->withErrors(['error' => 'Verification failed. Please try again.']);
        }

        $submissionData = $request->except(['fax_number', 'grecaptcha_response', '_token']);

        try {
            $submission = $this->formService->submit($form->id, $submissionData);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $form->success_message ?? 'Thank you! Your submission has been received.',
                ]);
            }

            return redirect()->back()
                ->with('success', $form->success_message ?? 'Thank you! Your submission has been received.');
        } catch (\Exception $e) {
            Log::error('Form submission failed', [
                'form_slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                // Extract field name from error message for proper validation error format
                $errorMessage = $e->getMessage();
                $fieldName = null;
                if (preg_match("/Field '([^']+)'/", $errorMessage, $matches)) {
                    $fieldName = $matches[1];
                }

                $response = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];

                if ($fieldName) {
                    $response['errors'] = [
                        $fieldName => [$e->getMessage()],
                    ];
                }

                return response()->json($response, 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
