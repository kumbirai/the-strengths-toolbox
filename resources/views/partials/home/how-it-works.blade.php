<section class="section-padding bg-white">
    <div class="container-custom">
        <div class="max-w-4xl mx-auto text-center mb-12">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                How it Works
            </h2>
            <p class="text-xl text-gray-600">
                A simple, proven process to transform your team and grow your business
            </p>
        </div>

        <div class="max-w-5xl mx-auto">
            <!-- Steps -->
            <div class="space-y-8 lg:space-y-12">
                @php
                    $steps = [
                        [
                            'number' => '01',
                            'title' => 'Book a Consultation',
                            'description' => 'Schedule your complimentary 30-minute breakthrough call. We\'ll discuss your challenges, goals, and how strengths-based development can help your team.',
                            'cta' => 'Book Your Free Consultation',
                            'cta_link' => route('contact')
                        ],
                        [
                            'number' => '02',
                            'title' => 'Power of Strengths Training',
                            'description' => 'Engage in our comprehensive strengths-based programs tailored to your needs. Whether for individuals, teams, managers, or salespeople, we have the right solution.',
                            'cta' => 'Explore Our Programs',
                            'cta_link' => route('strengths-programme')
                        ],
                        [
                            'number' => '03',
                            'title' => 'Watch Your Profits Grow',
                            'description' => 'Experience measurable results as your team becomes more engaged, productive, and aligned. See improvements in sales, retention, and overall business performance.',
                            'cta' => 'See Success Stories',
                            'cta_link' => '/testimonials'
                        ]
                    ];
                @endphp

                @foreach($steps as $index => $step)
                    <div class="flex flex-col lg:flex-row gap-8 items-center {{ $index % 2 === 1 ? 'lg:flex-row-reverse' : '' }}">
                        <!-- Step Number and Content -->
                        <div class="flex-1">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-16 h-16 bg-primary-600 text-white rounded-full flex items-center justify-center text-2xl font-bold flex-shrink-0">
                                    {{ $step['number'] }}
                                </div>
                                <h3 class="text-2xl md:text-3xl font-bold text-gray-900">
                                    {{ $step['title'] }}
                                </h3>
                            </div>
                            <p class="text-lg text-gray-600 mb-6">
                                {{ $step['description'] }}
                            </p>
                            <a href="{{ $step['cta_link'] }}" class="btn btn-primary">
                                {{ $step['cta'] }}
                            </a>
                        </div>

                        <!-- Visual/Icon -->
                        <div class="flex-1 flex justify-center">
                            <div class="w-64 h-64 bg-gradient-to-br from-primary-100 to-accent-100 rounded-2xl flex items-center justify-center">
                                @if($step['number'] === '01')
                                    <svg class="w-32 h-32 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                @elseif($step['number'] === '02')
                                    <svg class="w-32 h-32 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                @elseif($step['number'] === '03')
                                    <svg class="w-32 h-32 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Connector Line (except for last step) -->
                    @if($index < count($steps) - 1)
                        <div class="flex justify-center">
                            <div class="w-1 h-12 bg-primary-200 rounded"></div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
