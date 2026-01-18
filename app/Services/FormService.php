<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Repositories\FormRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FormService
{
    protected FormRepository $formRepository;

    protected EmailService $emailService;

    public function __construct(
        FormRepository $formRepository,
        EmailService $emailService
    ) {
        $this->formRepository = $formRepository;
        $this->emailService = $emailService;
    }

    /**
     * Get a form by slug
     */
    public function getBySlug(string $slug): ?Form
    {
        return $this->formRepository->findBySlug($slug);
    }

    /**
     * Get a form by ID
     */
    public function getById(int $id): ?Form
    {
        return $this->formRepository->findById($id);
    }

    /**
     * Get all active forms
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllActive()
    {
        return $this->formRepository->getAllActive();
    }

    /**
     * Create a new form
     */
    public function create(array $data): Form
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Ensure fields is JSON
        if (isset($data['fields']) && is_array($data['fields'])) {
            $data['fields'] = json_encode($data['fields']);
        }

        return $this->formRepository->create($data);
    }

    /**
     * Update an existing form
     */
    public function update(int $id, array $data): Form
    {
        $form = $this->formRepository->findById($id);

        if (! $form) {
            throw new \Exception("Form with ID {$id} not found");
        }

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $form->name) {
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $id);
            }
        }

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Ensure fields is JSON
        if (isset($data['fields']) && is_array($data['fields'])) {
            $data['fields'] = json_encode($data['fields']);
        }

        $this->formRepository->updateById($id, $data);
        $form->refresh();

        return $form;
    }

    /**
     * Delete a form
     */
    public function delete(int $id): bool
    {
        $form = $this->formRepository->findById($id);

        if (! $form) {
            throw new \Exception("Form with ID {$id} not found");
        }

        return $this->formRepository->deleteById($id);
    }

    /**
     * Process form submission
     */
    public function submit(int $formId, array $submissionData): FormSubmission
    {
        $form = $this->formRepository->findById($formId);

        if (! $form) {
            throw new \Exception("Form with ID {$formId} not found");
        }

        if (! $form->is_active) {
            throw new \Exception('Form is not active');
        }

        // Validate submission data against form fields
        $this->validateSubmission($form, $submissionData);

        // Create submission record
        $submission = FormSubmission::create([
            'form_id' => $formId,
            'data' => json_encode($submissionData),
            'ip_address' => request()->ip(),
            'user_id' => auth()->check() ? auth()->id() : null,
        ]);

        // Send email notification if configured
        if ($form->email_to) {
            try {
                $this->emailService->sendFormSubmissionNotification(
                    $form->email_to,
                    $form,
                    $submission
                );
            } catch (\Exception $e) {
                Log::error('Failed to send form submission email', [
                    'form_id' => $formId,
                    'submission_id' => $submission->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $submission;
    }

    /**
     * Validate submission data against form fields
     *
     * @throws \Exception
     */
    protected function validateSubmission(Form $form, array $submissionData): void
    {
        $fields = is_array($form->fields) ? $form->fields : json_decode($form->fields, true) ?? [];

        foreach ($fields as $field) {
            $fieldName = $field['name'] ?? null;
            $required = $field['required'] ?? false;
            $fieldType = $field['type'] ?? 'text';

            if ($required && empty($submissionData[$fieldName])) {
                throw new \Exception("Field '{$fieldName}' is required");
            }

            // Additional validation based on field type
            if (! empty($submissionData[$fieldName])) {
                $this->validateFieldType($fieldType, $fieldName, $submissionData[$fieldName]);
            }
        }
    }

    /**
     * Validate field value based on type
     *
     * @param  mixed  $value
     *
     * @throws \Exception
     */
    protected function validateFieldType(string $type, string $fieldName, $value): void
    {
        switch ($type) {
            case 'email':
                if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception("Field '{$fieldName}' must be a valid email address");
                }
                break;

            case 'url':
                if (! filter_var($value, FILTER_VALIDATE_URL)) {
                    throw new \Exception("Field '{$fieldName}' must be a valid URL");
                }
                break;

            case 'number':
                if (! is_numeric($value)) {
                    throw new \Exception("Field '{$fieldName}' must be a number");
                }
                break;
        }
    }

    /**
     * Get submissions for a form
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSubmissions(int $formId, int $perPage = 20)
    {
        $form = $this->formRepository->findById($formId);

        if (! $form) {
            throw new \Exception("Form with ID {$formId} not found");
        }

        return $form->submissions()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Generate a unique slug from name
     */
    protected function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->formRepository->slugExists($slug, $excludeId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
