<section class="section-padding section-gradient-primary">
    <div class="container-custom">
        <div class="section-header">
            <h2 class="section-title-light">Our Track Record</h2>
            <p class="section-subtitle-light">
                Decades of experience delivering real results
            </p>
        </div>

        <div class="grid-3 grid-constrained mb-12">
            <!-- 30 Years Experience -->
            <div class="stat-card">
                <div class="stat-value">30+</div>
                <div class="stat-label">Years Experience</div>
                <div class="stat-description">
                    Proven expertise in strengths-based development
                </div>
            </div>

            <!-- 1560+ Happy Clients -->
            <div class="stat-card">
                <div class="stat-value">1560+</div>
                <div class="stat-label">Happy Clients</div>
                <div class="stat-description">
                    Successful business transformations
                </div>
            </div>

            <!-- Visual Representation -->
            <div class="stat-card">
                <div class="stat-value">100%</div>
                <div class="stat-label">Strengths-Based</div>
                <div class="stat-description">
                    Focused approach to development
                </div>
            </div>
        </div>

        <!-- Visual Element -->
        <div class="max-w-3xl mx-auto">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 lg:p-12">
                @if(isset($teamworkImage) && $teamworkImage)
                    <img 
                        src="{{ $teamworkImage->url }}" 
                        alt="{{ $teamworkImage->alt_text ?? 'Team collaboration and support' }}"
                        class="w-full aspect-video object-cover rounded-lg shadow-lg"
                        loading="lazy"
                    >
                @else
                    <!-- Placeholder for teamwork imagery -->
                    <div class="aspect-video bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-32 h-32 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
