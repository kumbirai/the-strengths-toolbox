@props(['post', 'parentId' => null])

<div class="bg-white rounded-lg shadow-sm border border-neutral-200 p-6">
    <h3 class="text-xl font-semibold text-neutral-900 mb-4">
        {{ $parentId ? 'Leave a Reply' : 'Leave a Comment' }}
    </h3>

    @if(session('comment_error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            {{ session('comment_error') }}
        </div>
    @endif

    <form 
        action="{{ $parentId ? route('blog.comments.reply', ['slug' => $post->slug, 'comment' => $parentId]) : route('blog.comments.store', $post->slug) }}" 
        method="POST"
        class="space-y-4 js-blog-comment-form"
    >
        @csrf

        <input type="hidden" name="grecaptcha_response" class="js-blog-recaptcha-token" value="">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="author_name" class="block text-sm font-medium text-neutral-700 mb-1">
                    Name <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="author_name"
                    name="author_name"
                    value="{{ old('author_name') }}"
                    required
                    class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                @error('author_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="author_email" class="block text-sm font-medium text-neutral-700 mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <input
                    type="email"
                    id="author_email"
                    name="author_email"
                    value="{{ old('author_email') }}"
                    required
                    class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                @error('author_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="author_website" class="block text-sm font-medium text-neutral-700 mb-1">
                Website (optional)
            </label>
            <input
                type="url"
                id="author_website"
                name="author_website"
                value="{{ old('author_website') }}"
                placeholder="https://example.com"
                class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
            @error('author_website')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="content" class="block text-sm font-medium text-neutral-700 mb-1">
                Comment <span class="text-red-500">*</span>
            </label>
            <textarea
                id="content"
                name="content"
                rows="6"
                required
                minlength="10"
                maxlength="5000"
                class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >{{ old('content') }}</textarea>
            @error('content')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-neutral-500">Minimum 10 characters, maximum 5000 characters.</p>
        </div>

        <div class="text-sm text-neutral-600">
            <p>Your email address will not be published. Required fields are marked <span class="text-red-500">*</span></p>
        </div>

        <div>
            <button
                type="submit"
                class="btn btn-primary"
            >
                {{ $parentId ? 'Post Reply' : 'Post Comment' }}
            </button>
        </div>

        <div style="position:absolute;left:-99999px;width:1px;height:1px;overflow:hidden;opacity:0;visibility:hidden;pointer-events:none;clip:rect(0,0,0,0);" aria-hidden="true">
            <label for="blog_fax_number">Fax</label>
            <input type="text" id="blog_fax_number" name="fax_number" tabindex="-1" autocomplete="off">
        </div>
    </form>
    <script>
        document.querySelectorAll('.js-blog-comment-form').forEach(function(form) {
            if (form.dataset.recaptchaBound) return;
            form.dataset.recaptchaBound = '1';
            form.addEventListener('submit', function(e) {
                if (form.dataset.recaptchaSubmitting === '1') return;
                var siteKey = document.querySelector('meta[name=recaptcha-site-key]');
                if (!siteKey || !siteKey.content || typeof grecaptcha === 'undefined') {
                    return;
                }
                e.preventDefault();
                form.dataset.recaptchaSubmitting = '1';
                grecaptcha.execute(siteKey.content, { action: 'blog_comment' }).then(function(token) {
                    var input = form.querySelector('.js-blog-recaptcha-token');
                    if (input) input.value = token;
                    form.removeAttribute('data-recaptcha-submitting');
                    form.submit();
                }).catch(function() {
                    form.removeAttribute('data-recaptcha-submitting');
                    form.submit();
                });
            });
        });
    </script>
</div>
