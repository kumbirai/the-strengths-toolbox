<section class="section-padding bg-gradient-to-br from-primary-50 to-accent-50">
    <div class="container-custom">
        <div class="max-w-4xl mx-auto text-center mb-12">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                Why The Strengths Toolbox?
            </h2>
            <p class="text-xl text-gray-600">
                Decades of experience helping businesses unlock their team's potential
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="text-center">
                <div class="text-5xl md:text-6xl font-bold text-primary-600 mb-2">30+</div>
                <div class="text-lg text-gray-700 font-semibold">Years Experience</div>
                <div class="text-sm text-gray-600 mt-1">Proven track record</div>
            </div>
            <div class="text-center">
                <div class="text-5xl md:text-6xl font-bold text-primary-600 mb-2">1560+</div>
                <div class="text-lg text-gray-700 font-semibold">Happy Clients</div>
                <div class="text-sm text-gray-600 mt-1">Successful transformations</div>
            </div>
            <div class="text-center">
                <div class="text-5xl md:text-6xl font-bold text-primary-600 mb-2">100%</div>
                <div class="text-lg text-gray-700 font-semibold">Strengths-Based</div>
                <div class="text-sm text-gray-600 mt-1">Focused approach</div>
            </div>
        </div>

        <!-- Value Propositions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            @php
                $values = [
                    [
                        'icon' => 'check-circle',
                        'title' => 'Proven Success',
                        'description' => 'Decades of experience delivering measurable results for businesses across industries.'
                    ],
                    [
                        'icon' => 'puzzle',
                        'title' => 'Customized Solutions',
                        'description' => 'Tailored programs that fit your unique business needs and team dynamics.'
                    ],
                    [
                        'icon' => 'hand-raise',
                        'title' => 'Empower Your Team',
                        'description' => 'Build confidence and capability through strengths-based development.'
                    ],
                    [
                        'icon' => 'globe',
                        'title' => 'Holistic Approach',
                        'description' => 'Comprehensive solutions that address individual, team, and organizational needs.'
                    ]
                ];
            @endphp

            @foreach($values as $value)
                <div class="bg-white rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                        @if($value['icon'] === 'check-circle')
                            <svg class="w-6 h-6 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($value['icon'] === 'puzzle')
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                            </svg>
                        @elseif($value['icon'] === 'hand-raise')
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 10-3 0v-6a1.5 1.5 0 113 0m6-10V9m0 0v9m0-9a1.5 1.5 0 013 0m-3 0a1.5 1.5 0 00-3 0"/>
                            </svg>
                        @elseif($value['icon'] === 'globe')
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $value['title'] }}</h3>
                    <p class="text-gray-600 text-sm">{{ $value['description'] }}</p>
                </div>
            @endforeach
        </div>

        <!-- CTA -->
        <div class="text-center">
            <a href="{{ route('about-us') }}" class="btn btn-primary text-lg">
                Learn More About Us
            </a>
        </div>
    </div>
</section>
