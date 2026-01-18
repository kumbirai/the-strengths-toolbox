<section class="section-padding section-light">
    <div class="container-custom">
        <div class="section-header">
            <h2 class="section-title">What Strengths Matter for Your Business?</h2>
            <p class="section-subtitle">
                Identify and address the key challenges holding your business back
            </p>
        </div>

        <div class="grid-2 grid-constrained">
            @php
                $problems = [
                    [
                        'title' => 'Misaligned Teams',
                        'description' => 'Team members working in roles that don\'t match their natural strengths, leading to frustration and underperformance.',
                        'icon' => 'users-x',
                        'color' => 'red'
                    ],
                    [
                        'title' => 'High Turnover',
                        'description' => 'Losing talented employees because they\'re not engaged or don\'t feel their strengths are being utilized.',
                        'icon' => 'arrow-right',
                        'color' => 'orange'
                    ],
                    [
                        'title' => 'Missed Sales Targets',
                        'description' => 'Sales teams struggling to close deals because they\'re not leveraging their natural selling strengths effectively.',
                        'icon' => 'chart-line',
                        'color' => 'yellow'
                    ],
                    [
                        'title' => 'Burnout and Disengagement',
                        'description' => 'Employees feeling overwhelmed and disconnected because they\'re constantly working against their natural strengths.',
                        'icon' => 'exclamation',
                        'color' => 'red'
                    ]
                ];
            @endphp

            @foreach($problems as $problem)
                @php
                    $cardClass = match($problem['color']) {
                        'red' => 'feature-card-bordered-danger',
                        'orange' => 'feature-card-bordered-warning',
                        'yellow' => 'feature-card-bordered-caution',
                        default => 'feature-card-bordered-danger'
                    };
                    $badgeClass = match($problem['color']) {
                        'red' => 'icon-badge-danger',
                        'orange' => 'icon-badge-warning',
                        'yellow' => 'icon-badge-caution',
                        default => 'icon-badge-danger'
                    };
                @endphp
                <div class="{{ $cardClass }}">
                    <div class="flex items-start gap-4">
                        <div class="icon-badge {{ $badgeClass }}">
                            @if($problem['icon'] === 'users-x')
                                <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            @elseif($problem['icon'] === 'arrow-right')
                                <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            @elseif($problem['icon'] === 'chart-line')
                                <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            @elseif($problem['icon'] === 'exclamation')
                                <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="feature-title">{{ $problem['title'] }}</h3>
                            <p class="feature-description">{{ $problem['description'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <p class="text-lg text-neutral-700 mb-4">
                Ready to transform these challenges into opportunities?
            </p>
            <a href="#four-programs" class="btn btn-primary text-lg">
                Discover Our Solutions
            </a>
        </div>
    </div>
</section>
