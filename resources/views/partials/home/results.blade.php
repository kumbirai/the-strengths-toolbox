<section class="section-padding section-light">
    <div class="container-custom">
        <div class="section-header">
            <h2 class="section-title">Results You Can Expect</h2>
            <p class="section-subtitle">
                Real outcomes from our strengths-based approach
            </p>
        </div>

        <div class="grid-3 grid-constrained">
            @php
                $results = [
                    [
                        'title' => 'Stronger Teams',
                        'description' => 'Build cohesive teams where members understand and leverage each other\'s strengths for maximum collaboration and performance.',
                        'icon' => 'users',
                        'metrics' => ['Improved collaboration', 'Better communication', 'Higher engagement']
                    ],
                    [
                        'title' => 'Higher Profits',
                        'description' => 'Drive revenue growth through improved sales performance, increased productivity, and optimized team performance.',
                        'icon' => 'chart-bar',
                        'metrics' => ['Increased sales', 'Better productivity', 'Reduced costs']
                    ],
                    [
                        'title' => 'Confident Leadership',
                        'description' => 'Develop leaders who understand their strengths and can effectively guide their teams toward success.',
                        'icon' => 'shield',
                        'metrics' => ['Better decision-making', 'Stronger vision', 'Effective management']
                    ]
                ];
            @endphp

            @foreach($results as $result)
                <div class="text-center">
                    <!-- Icon -->
                    <div class="icon-badge-xl icon-badge-primary mx-auto mb-6">
                        @if($result['icon'] === 'users')
                            <svg class="icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        @elseif($result['icon'] === 'chart-bar')
                            <svg class="icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        @elseif($result['icon'] === 'shield')
                            <svg class="icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        @endif
                    </div>

                    <!-- Title -->
                    <h3 class="feature-title">{{ $result['title'] }}</h3>

                    <!-- Description -->
                    <p class="feature-description mb-6">{{ $result['description'] }}</p>

                    <!-- Metrics -->
                    <ul class="check-list">
                        @foreach($result['metrics'] as $metric)
                            <li class="flex items-center justify-center gap-2 text-sm text-neutral-700">
                                <svg class="icon-sm text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                {{ $metric }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>
