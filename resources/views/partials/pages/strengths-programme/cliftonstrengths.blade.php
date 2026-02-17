<section class="section-padding section-muted">
    <div class="container-custom">
        <div class="section-header">
            <h2 class="section-title">About CliftonStrengths</h2>
            <p class="section-subtitle">
                Our coaching approach is based on the CliftonStrengths assessment, which helps individuals 
                identify their unique talents and strengths.
            </p>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="grid-2 grid-constrained mt-12">
                {{-- What is CliftonStrengths --}}
                <div class="feature-card">
                    <div class="icon-badge icon-badge-primary mb-4">
                        <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Identify Your Unique Talents</h3>
                    <p class="feature-description">
                        The CliftonStrengths assessment helps you discover your natural talents and strengths. 
                        Instead of focusing on weaknesses, we help you identify what you do best and how to 
                        leverage these strengths for exceptional performance.
                    </p>
                </div>

                {{-- Strengths-Based Development --}}
                <div class="feature-card">
                    <div class="icon-badge icon-badge-primary mb-4">
                        <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Strengths-Based Development Techniques</h3>
                    <p class="feature-description">
                        We offer courses to teach you strengths-based development techniques. Our coaching 
                        curriculum is designed to help you empower people in the areas of drive, confidence, 
                        and influence.
                    </p>
                </div>

                {{-- Creating Strength-Based Teams --}}
                <div class="feature-card">
                    <div class="icon-badge icon-badge-primary mb-4">
                        <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 5.357M7 10a5.002 5.002 0 017.196 2M7 10H5m2 0h2m-2 0v2a5.002 5.002 0 005.196 2M7 12v-2m5 2v2a5.002 5.002 0 01-2.804 1.5M12 12h2m-2 0H9m2 0v2m0-2v-2"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Creating Strength-Based Teams</h3>
                    <p class="feature-description">
                        Helping Managers & Business Owners Create "Strength-Based Teams" To Maximize Performance. 
                        When team members understand their strengths and work in alignment with them, teams 
                        achieve exceptional results.
                    </p>
                </div>

                {{-- Coaching Curriculum --}}
                <div class="feature-card">
                    <div class="icon-badge icon-badge-primary mb-4">
                        <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Comprehensive Coaching Curriculum</h3>
                    <p class="feature-description">
                        Our coaching curriculum is designed to help you empower people in the areas of drive, 
                        confidence, and influence. We believe that everyone has unique talents and strengths 
                        that can be leveraged to achieve success.
                    </p>
                </div>
            </div>

            {{-- Gallup Certification Badge --}}
            <div class="mt-12 text-center">
                <div class="inline-block">
                    <x-gallup-badge size="large" />
                </div>
                <p class="mt-4 text-neutral-600">
                    Eberhard Niklaus is a Gallup Certified Strengths Coach with extensive experience 
                    in helping individuals and teams unlock their potential through strengths-based development.
                </p>
            </div>

            {{-- CTA --}}
            <div class="text-center mt-12">
                <a href="{{ route('contact') }}" class="btn btn-primary text-lg">
                    Book Your Complimentary Consultation
                </a>
            </div>
        </div>
    </div>
</section>
