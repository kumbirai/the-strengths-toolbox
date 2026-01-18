<section id="power-of-strengths" class="section-padding section-light">
    <div class="container-custom">
        <div class="section-header">
            <h2 class="section-title">What is the Power of Strengths?</h2>
            <p class="section-subtitle">
                Strengths-based development is a proven approach that transforms how teams
                work together, perform, and grow. Instead of focusing on weaknesses,
                we help you identify and leverage what your team does best.
            </p>
        </div>

        <!-- Key Benefits Grid -->
        <div class="grid-3 grid-constrained mt-12">
            @php
                $benefits = [
                    [
                        'icon' => 'users',
                        'title' => 'Enhanced Team Performance',
                        'description' => 'Teams that understand their strengths work more effectively together, leading to improved productivity and results.'
                    ],
                    [
                        'icon' => 'chart-line',
                        'title' => 'Increased Profitability',
                        'description' => 'When people work in their strengths zone, they perform better, leading to higher sales and business growth.'
                    ],
                    [
                        'icon' => 'heart',
                        'title' => 'Higher Engagement',
                        'description' => 'Employees who use their strengths daily are more engaged, motivated, and satisfied with their work.'
                    ],
                    [
                        'icon' => 'shield-check',
                        'title' => 'Reduced Turnover',
                        'description' => 'Teams that leverage strengths have lower turnover rates, saving time and money on recruitment.'
                    ],
                    [
                        'icon' => 'lightbulb',
                        'title' => 'Innovation & Creativity',
                        'description' => 'Strengths-based teams are more creative and innovative, driving competitive advantage.'
                    ],
                    [
                        'icon' => 'trophy',
                        'title' => 'Sustainable Growth',
                        'description' => 'Build a business growth system that scales with your team\'s natural talents and capabilities.'
                    ]
                ];
            @endphp

            @foreach($benefits as $benefit)
                <div class="feature-card">
                    <div class="icon-badge icon-badge-primary mb-4">
                        @if($benefit['icon'] === 'users')
                            <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        @elseif($benefit['icon'] === 'chart-line')
                            <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        @elseif($benefit['icon'] === 'heart')
                            <svg class="icon-md" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($benefit['icon'] === 'shield-check')
                            <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        @elseif($benefit['icon'] === 'lightbulb')
                            <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        @elseif($benefit['icon'] === 'trophy')
                            <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        @endif
                    </div>
                    <h3 class="feature-title">{{ $benefit['title'] }}</h3>
                    <p class="feature-description">{{ $benefit['description'] }}</p>
                </div>
            @endforeach
        </div>

        <!-- CTA -->
        <div class="text-center mt-12">
            <a href="{{ route('strengths-programme') }}" class="btn btn-primary text-lg">
                Discover Our Strengths Programme
            </a>
        </div>
    </div>
</section>
