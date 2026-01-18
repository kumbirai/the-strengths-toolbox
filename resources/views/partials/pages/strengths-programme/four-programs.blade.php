<section id="four-programs" class="section-padding section-muted">
    <div class="container-custom">
        <div class="section-header">
            <h2 class="section-title">
                The Power of Strengths:<br>
                Four Proven Programs
            </h2>
            <p class="section-subtitle">
                Tailored solutions for every level of your organization
            </p>
        </div>

        <div class="grid-2 grid-constrained">
            @php
                $programs = [
                    [
                        'title' => 'For Individuals',
                        'subtitle' => 'Discover Your Potential',
                        'description' => 'Help individuals identify their unique strengths and learn how to leverage them for personal and professional growth.',
                        'benefits' => [
                            'Understand your natural talents',
                            'Develop confidence in your abilities',
                            'Align your work with your strengths',
                            'Increase job satisfaction and engagement'
                        ],
                        'outcomes' => 'Greater self-awareness, improved performance, and enhanced career satisfaction.',
                        'icon' => 'user',
                        'color' => 'primary'
                    ],
                    [
                        'title' => 'For Managers & Leaders',
                        'subtitle' => 'Lead With Strength',
                        'description' => 'Equip leaders with the tools to understand their leadership style and build high-performing teams.',
                        'benefits' => [
                            'Develop authentic leadership style',
                            'Build stronger team relationships',
                            'Make better hiring decisions',
                            'Create engaged, motivated teams'
                        ],
                        'outcomes' => 'More effective leadership, reduced turnover, and improved team performance.',
                        'icon' => 'shield',
                        'color' => 'accent'
                    ],
                    [
                        'title' => 'For Salespeople',
                        'subtitle' => 'Sell With Confidence',
                        'description' => 'Transform sales performance by helping salespeople understand and leverage their natural selling strengths.',
                        'benefits' => [
                            'Identify your selling strengths',
                            'Develop personalized sales approach',
                            'Build stronger client relationships',
                            'Close more deals consistently'
                        ],
                        'outcomes' => 'Higher conversion rates, increased sales revenue, and greater job satisfaction.',
                        'icon' => 'chart-bar',
                        'color' => 'primary'
                    ],
                    [
                        'title' => 'For Teams',
                        'subtitle' => 'Build Collective Power',
                        'description' => 'Create cohesive teams where members understand and complement each other\'s strengths.',
                        'benefits' => [
                            'Improve team collaboration',
                            'Reduce conflict and misunderstandings',
                            'Increase team productivity',
                            'Build lasting team cohesion'
                        ],
                        'outcomes' => 'Stronger team performance, better communication, and sustainable results.',
                        'icon' => 'users',
                        'color' => 'accent'
                    ]
                ];
            @endphp

            @foreach($programs as $program)
                <div class="feature-card-elevated">
                    <!-- Header -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="icon-badge-lg {{ $program['color'] === 'primary' ? 'icon-badge-primary' : 'icon-badge-accent' }}">
                            @if($program['icon'] === 'user')
                                <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            @elseif($program['icon'] === 'shield')
                                <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            @elseif($program['icon'] === 'chart-bar')
                                <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            @elseif($program['icon'] === 'users')
                                <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="feature-title mb-0">{{ $program['title'] }}</h3>
                            <p class="text-lg {{ $program['color'] === 'primary' ? 'text-primary-600' : 'text-accent-600' }} font-semibold">{{ $program['subtitle'] }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <p class="feature-description mb-6">{{ $program['description'] }}</p>

                    <!-- Benefits -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-neutral-900 mb-3">Key Benefits:</h4>
                        <ul class="check-list">
                            @foreach($program['benefits'] as $benefit)
                                <li class="check-list-item">
                                    <svg class="{{ $program['color'] === 'primary' ? 'check-list-icon-primary' : 'check-list-icon-accent' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="check-list-text">{{ $benefit }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Outcomes -->
                    <div class="{{ $program['color'] === 'primary' ? 'callout-primary' : 'callout-accent' }}">
                        <p class="callout-title {{ $program['color'] === 'primary' ? 'text-primary-900' : 'text-accent-900' }}">Expected Outcomes:</p>
                        <p class="callout-text {{ $program['color'] === 'primary' ? 'text-primary-800' : 'text-accent-800' }}">{{ $program['outcomes'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
