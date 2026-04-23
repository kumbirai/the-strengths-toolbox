<?php

namespace App\Services;

use App\Models\Form;

class FormRenderService
{
    /**
     * Render a form as HTML
     */
    public function render(Form $form): string
    {
        if (! $form->is_active) {
            return '<p class="text-neutral-500">This form is currently inactive.</p>';
        }

        $fields = is_array($form->fields) ? $form->fields : json_decode($form->fields, true) ?? [];

        if (empty($fields)) {
            return '<p class="text-neutral-500">This form has no fields.</p>';
        }

        $html = '<form method="POST" action="'.route('forms.submit', $form->slug).'" enctype="multipart/form-data" class="space-y-4 js-dynamic-form" data-form-slug="'.htmlspecialchars($form->slug).'">';
        $html .= csrf_field();
        $html .= '<input type="hidden" name="grecaptcha_response" class="js-dynamic-recaptcha-token" value="">';

        foreach ($fields as $field) {
            $html .= $this->renderField($field);
        }

        $html .= '<div>';
        $html .= '<button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">';
        $html .= 'Submit';
        $html .= '</button>';
        $html .= '</div>';

        $html .= '<div style="position:absolute;left:-99999px;width:1px;height:1px;overflow:hidden;opacity:0;visibility:hidden;pointer-events:none;clip:rect(0,0,0,0);" aria-hidden="true"><label for="dynamic_form_fax_number">Fax</label><input type="text" id="dynamic_form_fax_number" name="fax_number" tabindex="-1" autocomplete="off"></div>';
        $html .= '</form>';

        if (config('recaptcha.enabled') && config('recaptcha.site_key')) {
            $html .= '<script>';
            $html .= 'document.querySelectorAll(".js-dynamic-form").forEach(function(form){if(form.dataset.recaptchaBound)return;form.dataset.recaptchaBound="1";form.addEventListener("submit",function(e){if(form.dataset.recaptchaSubmitting==="1")return;var siteKey=document.querySelector("meta[name=recaptcha-site-key]");if(!siteKey||!siteKey.content||typeof grecaptcha==="undefined"){return;}e.preventDefault();form.dataset.recaptchaSubmitting="1";grecaptcha.execute(siteKey.content,{action:"dynamic_form"}).then(function(token){var input=form.querySelector(".js-dynamic-recaptcha-token");if(input)input.value=token;form.removeAttribute("data-recaptcha-submitting");form.submit();}).catch(function(){form.removeAttribute("data-recaptcha-submitting");form.submit();});});});';
            $html .= '</script>';
        }

        return $html;
    }

    /**
     * Render a single form field
     */
    protected function renderField(array $field): string
    {
        $type = $field['type'] ?? 'text';
        $label = $field['label'] ?? '';
        $name = $field['name'] ?? '';
        $required = $field['required'] ?? false;
        $placeholder = $field['placeholder'] ?? '';
        $options = $field['options'] ?? [];

        $requiredAttr = $required ? 'required' : '';
        $requiredLabel = $required ? ' <span class="text-red-500">*</span>' : '';

        $html = '<div class="form-field">';
        $html .= '<label for="'.htmlspecialchars($name).'" class="block text-sm font-medium text-neutral-700 mb-1">';
        $html .= htmlspecialchars($label).$requiredLabel;
        $html .= '</label>';

        switch ($type) {
            case 'textarea':
                $html .= '<textarea id="'.htmlspecialchars($name).'" ';
                $html .= 'name="'.htmlspecialchars($name).'" ';
                $html .= 'rows="4" ';
                $html .= 'placeholder="'.htmlspecialchars($placeholder).'" ';
                $html .= $requiredAttr.' ';
                $html .= 'class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>';
                break;

            case 'select':
                $html .= '<select id="'.htmlspecialchars($name).'" ';
                $html .= 'name="'.htmlspecialchars($name).'" ';
                $html .= $requiredAttr.' ';
                $html .= 'class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">';
                $html .= '<option value="">Select an option</option>';
                foreach ($options as $option) {
                    $html .= '<option value="'.htmlspecialchars($option).'">'.htmlspecialchars($option).'</option>';
                }
                $html .= '</select>';
                break;

            case 'checkbox':
                foreach ($options as $index => $option) {
                    $optionId = $name.'_'.$index;
                    $html .= '<div class="flex items-center mb-2">';
                    $html .= '<input type="checkbox" ';
                    $html .= 'id="'.htmlspecialchars($optionId).'" ';
                    $html .= 'name="'.htmlspecialchars($name).'[]" ';
                    $html .= 'value="'.htmlspecialchars($option).'" ';
                    $html .= 'class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">';
                    $html .= '<label for="'.htmlspecialchars($optionId).'" class="ml-2 text-sm text-neutral-700">';
                    $html .= htmlspecialchars($option);
                    $html .= '</label>';
                    $html .= '</div>';
                }
                break;

            case 'radio':
                foreach ($options as $index => $option) {
                    $optionId = $name.'_'.$index;
                    $html .= '<div class="flex items-center mb-2">';
                    $html .= '<input type="radio" ';
                    $html .= 'id="'.htmlspecialchars($optionId).'" ';
                    $html .= 'name="'.htmlspecialchars($name).'" ';
                    $html .= 'value="'.htmlspecialchars($option).'" ';
                    $html .= $requiredAttr.' ';
                    $html .= 'class="w-4 h-4 text-primary-600 border-neutral-300 focus:ring-primary-500">';
                    $html .= '<label for="'.htmlspecialchars($optionId).'" class="ml-2 text-sm text-neutral-700">';
                    $html .= htmlspecialchars($option);
                    $html .= '</label>';
                    $html .= '</div>';
                }
                break;

            case 'file':
                $html .= '<input type="file" ';
                $html .= 'id="'.htmlspecialchars($name).'" ';
                $html .= 'name="'.htmlspecialchars($name).'" ';
                $html .= $requiredAttr.' ';
                $html .= 'class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">';
                break;

            default:
                $inputType = in_array($type, ['email', 'number', 'date']) ? $type : 'text';
                $html .= '<input type="'.htmlspecialchars($inputType).'" ';
                $html .= 'id="'.htmlspecialchars($name).'" ';
                $html .= 'name="'.htmlspecialchars($name).'" ';
                $html .= 'placeholder="'.htmlspecialchars($placeholder).'" ';
                $html .= $requiredAttr.' ';
                $html .= 'class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">';
                break;
        }

        $html .= '</div>';

        return $html;
    }
}
