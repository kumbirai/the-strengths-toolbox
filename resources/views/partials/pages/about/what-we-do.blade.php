<section class="section-padding section-light">
    <div class="container-custom">
        <div class="section-header">
            <h2 class="section-title">What We Do</h2>
        </div>

        <div class="grid-3 grid-constrained">
            @php
                $services = [
                    [
                        'title' => 'Sales Courses',
                        'description' => 'Comprehensive sales courses designed to help sales teams and individuals improve their performance, close more deals, and build stronger client relationships through proven sales methodologies.',
                        'icon' => 'chart-bar',
                        'color' => 'primary'
                    ],
                    [
                        'title' => 'Strengths Programmes',
                        'description' => 'Transform your business with strengths-based development programs tailored for individuals, teams, managers, and salespeople. Discover how to leverage natural talents for exceptional performance.',
                        'icon' => 'star',
                        'color' => 'accent'
                    ],
                    [
                        'title' => 'Business Coaching',
                        'description' => 'Executive coaching and business training programs that help managers, business owners, and leaders unlock their full potential and drive sustainable growth in their organizations.',
                        'icon' => 'lightbulb',
                        'color' => 'primary'
                    ]
                ];
            @endphp

            @foreach($services as $service)
                <div class="feature-card-centered">
                    <div class="icon-badge-lg {{ $service['color'] === 'primary' ? 'icon-badge-primary' : 'icon-badge-accent' }} mx-auto mb-6">
                        @if($service['icon'] === 'chart-bar')
                            <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        @elseif($service['icon'] === 'star')
                            <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        @elseif($service['icon'] === 'lightbulb')
                            <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        @endif
                    </div>
                    <h3 class="feature-title">{{ $service['title'] }}</h3>
                    <p class="feature-description">{{ $service['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
