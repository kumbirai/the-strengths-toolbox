<section class="section-padding bg-gray-50">
    <div class="container-custom">
        <div class="max-w-4xl mx-auto text-center mb-12">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                Why Choose Us?
            </h2>
            <p class="text-xl text-gray-600">
                What sets The Strengths Toolbox apart
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
            @php
                $values = [
                    [
                        'title' => 'Proven Success',
                        'description' => 'With over 30 years of experience and 1,560+ successful client transformations, we have a track record of delivering measurable results. Our strengths-based approach has been refined through real-world application across diverse industries.',
                        'icon' => 'trophy',
                        'color' => 'primary'
                    ],
                    [
                        'title' => 'Customized Solutions',
                        'description' => 'We understand that every business is unique. Our programs are tailored to fit your specific needs, industry challenges, and organizational culture. No one-size-fits-all approach—just solutions designed for your success.',
                        'icon' => 'puzzle',
                        'color' => 'accent'
                    ],
                    [
                        'title' => 'Empower Your Team',
                        'description' => 'Our approach focuses on building confidence and capability from within. Rather than imposing external frameworks, we help your team discover and leverage their natural strengths, creating lasting change that comes from within.',
                        'icon' => 'hand-raise',
                        'color' => 'primary'
                    ],
                    [
                        'title' => 'A Holistic Approach',
                        'description' => 'We address the complete picture: individual development, team dynamics, leadership effectiveness, and organizational alignment. Our comprehensive solutions ensure that improvements are sustainable and integrated across all levels.',
                        'icon' => 'globe',
                        'color' => 'accent'
                    ]
                ];
            @endphp

            @foreach($values as $value)
                <div class="bg-white rounded-lg p-8 shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 {{ $value['color'] === 'primary' ? 'bg-primary-100' : 'bg-accent-100' }} rounded-lg flex items-center justify-center mb-6">
                        @if($value['icon'] === 'trophy')
                            <svg class="w-8 h-8 {{ $value['color'] === 'primary' ? 'text-primary-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        @elseif($value['icon'] === 'puzzle')
                            <svg class="w-8 h-8 {{ $value['color'] === 'primary' ? 'text-primary-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                            </svg>
                        @elseif($value['icon'] === 'hand-raise')
                            <svg class="w-8 h-8 {{ $value['color'] === 'primary' ? 'text-primary-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 10-3 0v-6a1.5 1.5 0 113 0m6-10V9m0 0v9m0-9a1.5 1.5 0 013 0m-3 0a1.5 1.5 0 00-3 0"/>
                            </svg>
                        @elseif($value['icon'] === 'globe')
                            <svg class="w-8 h-8 {{ $value['color'] === 'primary' ? 'text-primary-600' : 'text-accent-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $value['title'] }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $value['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
