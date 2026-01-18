<section class="section-padding section-muted">
    <div class="container-custom">
        <div class="section-header">
            <h2 class="section-title">Three Pillars of Success</h2>
            <p class="section-subtitle">
                Our proven framework for building strong teams and driving sustainable business growth.
            </p>
        </div>

        <div class="grid-3 grid-constrained">
            @php
                $pillars = [
                    [
                        'number' => '01',
                        'title' => 'Turn Talent into Performance',
                        'description' => 'Identify and develop the unique strengths of each team member. Transform natural talent into exceptional performance that drives results.',
                        'icon' => 'arrow-up',
                        'color' => 'primary'
                    ],
                    [
                        'number' => '02',
                        'title' => 'Build Teams That Stick',
                        'description' => 'Create cohesive teams where members complement each other\'s strengths. Reduce turnover and build lasting professional relationships.',
                        'icon' => 'users',
                        'color' => 'accent'
                    ],
                    [
                        'number' => '03',
                        'title' => 'Drive Growth with Purpose',
                        'description' => 'Align team strengths with business objectives. Create a clear path to growth that energizes your team and accelerates results.',
                        'icon' => 'rocket',
                        'color' => 'primary'
                    ]
                ];
            @endphp

            @foreach($pillars as $index => $pillar)
                <div class="relative feature-card-elevated">
                    <!-- Number Badge -->
                    <div class="absolute -top-4 -left-4 icon-badge-lg {{ $pillar['color'] === 'primary' ? 'icon-badge-primary-solid' : 'icon-badge-accent-solid' }} rounded-full text-2xl font-bold shadow-lg">
                        {{ $pillar['number'] }}
                    </div>

                    <!-- Icon -->
                    <div class="mb-6 mt-4">
                        <div class="icon-badge-lg {{ $pillar['color'] === 'primary' ? 'icon-badge-primary' : 'icon-badge-accent' }}">
                            @if($pillar['icon'] === 'arrow-up')
                                <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            @elseif($pillar['icon'] === 'users')
                                <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            @elseif($pillar['icon'] === 'rocket')
                                <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            @endif
                        </div>
                    </div>

                    <!-- Content -->
                    <h3 class="feature-title">{{ $pillar['title'] }}</h3>
                    <p class="feature-description">{{ $pillar['description'] }}</p>
                </div>
            @endforeach
        </div>

        <!-- Bottom CTA -->
        <div class="text-center mt-12">
            <p class="text-lg text-neutral-700 mb-4">
                Ready to implement these pillars in your organization?
            </p>
            <a href="{{ route('contact') }}" class="btn btn-primary text-lg">
                Get Started Today
            </a>
        </div>
    </div>
</section>
