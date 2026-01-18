<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FormService;
use Illuminate\Http\Request;

class AdminFormController extends Controller
{
    protected FormService $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    /**
     * Display a listing of forms
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $forms = \App\Models\Form::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.forms.index', compact('forms'));
    }

    /**
     * Show the form for creating a new form
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.forms.create');
    }

    /**
     * Store a newly created form
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:forms,slug',
            'fields' => 'required|array|min:1',
            'fields.*.type' => 'required|string|in:text,email,textarea,select,checkbox,radio,file,number,date',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.required' => 'boolean',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.options' => 'nullable|array',
            'email_to' => 'nullable|email',
            'success_message' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        try {
            $form = $this->formService->create($validated);

            return redirect()->route('admin.forms.index')
                ->with('success', 'Form created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create form: '.$e->getMessage()]);
        }
    }

    /**
     * Display form submissions
     *
     * @return \Illuminate\View\View
     */
    public function submissions(int $formId)
    {
        $form = $this->formService->getById($formId);

        if (! $form) {
            abort(404);
        }

        $submissions = $this->formService->getSubmissions($formId, 20);

        return view('admin.forms.submissions', compact('form', 'submissions'));
    }

    /**
     * Show a specific submission
     *
     * @return \Illuminate\View\View
     */
    public function showSubmission(int $formId, int $submissionId)
    {
        $form = $this->formService->getById($formId);
        $submission = \App\Models\FormSubmission::findOrFail($submissionId);

        return view('admin.forms.submission', compact('form', 'submission'));
    }

    /**
     * Export form submissions to CSV
     *
     * @return \Illuminate\Http\Response
     */
    public function export(int $formId)
    {
        $form = $this->formService->getById($formId);

        if (! $form) {
            abort(404);
        }

        $submissions = $form->submissions()->orderBy('created_at', 'desc')->get();

        $filename = 'form_'.$form->slug.'_submissions_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($submissions, $form) {
            $file = fopen('php://output', 'w');

            // Get field names from form
            $fields = is_array($form->fields) ? $form->fields : json_decode($form->fields, true) ?? [];
            $fieldNames = array_column($fields, 'name');

            // Write header
            fputcsv($file, array_merge(['Submitted At', 'IP Address'], $fieldNames));

            // Write data
            foreach ($submissions as $submission) {
                $data = is_array($submission->data) ? $submission->data : json_decode($submission->data, true) ?? [];
                $row = [
                    $submission->created_at->format('Y-m-d H:i:s'),
                    $submission->ip_address,
                ];

                foreach ($fieldNames as $fieldName) {
                    $row[] = $data[$fieldName] ?? '';
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
