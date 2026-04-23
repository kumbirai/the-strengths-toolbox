<form 
    action="{{ route('contact.submit') }}" 
    method="POST"
    x-data="{ 
        submitting: false,
        success: false,
        errors: {}
    }"
    @submit.prevent="(async () => {
        submitting = true;
        errors = {};
        const siteKey = document.querySelector('meta[name=recaptcha-site-key]')?.content;
        let token = '';
        if (siteKey && typeof grecaptcha !== 'undefined') {
            try { token = await grecaptcha.execute(siteKey, { action: 'contact' }); } catch (e) {}
        }
        const body = {
            name: $refs.name.value,
            email: $refs.email.value,
            phone: $refs.phone.value,
            subject: $refs.subject.value,
            message: $refs.message.value,
            fax_number: $refs.fax_number?.value || '',
            grecaptcha_response: token
        };
        try {
            const response = await fetch('{{ route('contact.submit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            });
            const data = await response.json();
            submitting = false;
            if (data.errors) {
                errors = data.errors;
            } else if (data.success || data.message) {
                success = true;
                $refs.name.value = '';
                $refs.email.value = '';
                $refs.phone.value = '';
                $refs.subject.value = '';
                $refs.message.value = '';
                $nextTick(() => {
                    if ($refs.successMessage) {
                        try { $refs.successMessage.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' }); } catch (e) { $refs.successMessage.scrollIntoView(); }
                    }
                });
                setTimeout(() => { $dispatch('contact-form-success'); }, 2500);
            }
        } catch (err) {
            submitting = false;
            errors = { error: ['Something went wrong. Please try again.'] };
        }
    })()"
    class="bg-white rounded-xl p-8 shadow-lg"
>
    @csrf

    <!-- Success Message -->
    <div 
        x-ref="successMessage"
        x-show="success" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0 scale-95" 
        x-transition:enter-end="opacity-100 scale-100" 
        class="mb-6 p-6 bg-green-50 border-2 border-green-300 rounded-lg text-center"
    >
        <div class="mb-4 flex justify-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
        <h3 class="text-xl font-bold text-green-900 mb-2">
            Thank You!
        </h3>
        <p class="text-green-800 font-semibold mb-2">
            Your message has been sent successfully.
        </p>
        <p class="text-green-700 text-sm">
            We've sent a confirmation email to your inbox. We'll get back to you within 24-48 hours.
        </p>
    </div>

    <!-- Error Messages -->
    <div x-show="Object.keys(errors).length > 0" x-transition class="mb-6">
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <ul class="list-disc list-inside text-red-800 text-sm">
                <template x-for="(errorList, field) in errors" :key="field">
                    <template x-for="error in errorList" :key="error">
                        <li x-text="error"></li>
                    </template>
                </template>
            </ul>
        </div>
    </div>

    <!-- Name Field -->
    <div class="mb-6">
        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
            Full Name <span class="text-red-500">*</span>
        </label>
        <input 
            type="text" 
            id="name" 
            name="name"
            x-ref="name"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="Enter your full name"
        >
    </div>

    <!-- Email Field -->
    <div class="mb-6">
        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
            Email Address <span class="text-red-500">*</span>
        </label>
        <input 
            type="email" 
            id="email" 
            name="email"
            x-ref="email"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="Enter your email address"
        >
    </div>

    <!-- Phone Field -->
    <div class="mb-6">
        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
            Phone Number
        </label>
        <input 
            type="tel" 
            id="phone" 
            name="phone"
            x-ref="phone"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="Enter your phone number (optional)"
        >
    </div>

    <!-- Subject Field -->
    <div class="mb-6">
        <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">
            Subject <span class="text-red-500">*</span>
        </label>
        <input 
            type="text" 
            id="subject" 
            name="subject"
            x-ref="subject"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="What is this regarding?"
        >
    </div>

    <!-- Message Field -->
    <div class="mb-6">
        <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">
            Message <span class="text-red-500">*</span>
        </label>
        <textarea 
            id="message" 
            name="message"
            x-ref="message"
            rows="6"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="Tell us how we can help..."
        ></textarea>
        <p class="text-sm text-gray-500 mt-2">Minimum 10 characters</p>
    </div>

    <!-- Submit Button -->
    <button 
        type="submit"
        :disabled="submitting"
        class="w-full btn btn-primary text-lg py-4"
        :class="{ 'opacity-50 cursor-not-allowed': submitting }"
    >
        <span x-show="!submitting">Send Message</span>
        <span x-show="submitting">Sending...</span>
    </button>

    <p class="text-xs text-gray-500 mt-4 text-center">
        By submitting this form, you agree to our privacy policy.
    </p>

    {{-- Honeypot: at end of form, fully hidden so it never shows or affects layout --}}
    <div style="position:absolute;left:-99999px;width:1px;height:1px;overflow:hidden;opacity:0;visibility:hidden;pointer-events:none;clip:rect(0,0,0,0);" aria-hidden="true">
        <label for="contact_fax_number">Fax</label>
        <input type="text" id="contact_fax_number" name="fax_number" tabindex="-1" autocomplete="off" x-ref="fax_number">
    </div>
</form>
