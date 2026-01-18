<section class="section-padding bg-gradient-to-br from-primary-600 to-primary-800 text-white">
    <div class="container-custom">
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Content -->
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">
                        Get Your Free eBook
                    </h2>
                    <p class="text-xl text-primary-100 mb-6">
                        Discover the power of strengths-based development with our 
                        comprehensive guide. Learn how to transform your team and 
                        drive sustainable business growth.
                    </p>
                    <ul class="space-y-3 mb-8">
                        @php
                            $benefits = [
                                'Understanding strengths-based development',
                                'Practical strategies for team building',
                                'Tips for improving team performance',
                                'Real-world case studies and examples'
                            ];
                        @endphp
                        @foreach($benefits as $benefit)
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-primary-200 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ $benefit }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Form Container -->
                <div class="bg-white rounded-xl p-8 shadow-2xl" x-data="{ 
                    submitting: false,
                    success: false,
                    downloadUrl: '',
                    errors: {}
                }">
                    <!-- Success Panel -->
                    <div x-show="success" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="text-center">
                        <!-- Success Icon -->
                        <div class="mb-6 flex justify-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Success Message -->
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">
                            Thank You!
                        </h3>
                        <p class="text-gray-600 mb-2">
                            Your free eBook is ready to download.
                        </p>
                        <p class="text-sm text-gray-500 mb-6">
                            We've also sent a download link to your email.
                        </p>

                        <!-- Download Button -->
                        <a 
                            :href="downloadUrl"
                            class="inline-block w-full btn btn-primary text-lg py-4 mb-4"
                        >
                            Download Your Free eBook
                        </a>

                        <p class="text-xs text-gray-500">
                            We respect your privacy. Unsubscribe at any time.
                        </p>
                    </div>

                    <!-- Form -->
                    <form 
                        action="{{ route('ebook.signup') }}" 
                        method="POST"
                        x-show="!success"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        @submit.prevent="
                            submitting = true;
                            fetch('{{ route('ebook.signup') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    first_name: $refs.first_name.value,
                                    last_name: $refs.last_name.value,
                                    email: $refs.email.value
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                submitting = false;
                                if (data.errors) {
                                    errors = data.errors;
                                } else if (data.message || data.success) {
                                    success = true;
                                    downloadUrl = data.download_url || '{{ route('ebook.download') }}';
                                    $refs.first_name.value = '';
                                    $refs.last_name.value = '';
                                    $refs.email.value = '';
                                }
                            })
                            .catch(() => {
                                submitting = false;
                                errors = { error: ['Something went wrong. Please try again.'] };
                            })
                        "
                    >
                        @csrf

                        <!-- Error Messages -->
                        <div x-show="Object.keys(errors).length > 0" x-transition class="mb-4">
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

                        <!-- First Name Field -->
                        <div class="mb-4">
                            <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                First Name
                            </label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name"
                                x-ref="first_name"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900"
                                placeholder="Enter your first name"
                            >
                        </div>

                        <!-- Last Name Field -->
                        <div class="mb-4">
                            <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Last Name
                            </label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name"
                                x-ref="last_name"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900"
                                placeholder="Enter your last name"
                            >
                        </div>

                        <!-- Email Field -->
                        <div class="mb-6">
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email"
                                x-ref="email"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900"
                                placeholder="Enter your email address"
                            >
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit"
                            :disabled="submitting"
                            class="w-full btn btn-primary text-lg py-4"
                            :class="{ 'opacity-50 cursor-not-allowed': submitting }"
                        >
                            <span x-show="!submitting">Get My Free eBook</span>
                            <span x-show="submitting">Sending...</span>
                        </button>

                        <p class="text-xs text-gray-500 mt-4 text-center">
                            We respect your privacy. Unsubscribe at any time.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
