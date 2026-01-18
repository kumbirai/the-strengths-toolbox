<section class="section-padding bg-white">
    <div class="container-custom">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Content -->
            <div>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    Why Strong Teams Fail<br>
                    <span class="text-primary-600">Without Strategy</span>
                </h2>
                
                <p class="text-lg text-gray-600 mb-6">
                    Having talented individuals doesn't guarantee success. Even the strongest 
                    teams can struggle when they lack:
                </p>

                <ul class="space-y-4 mb-8">
                    @php
                        $challenges = [
                            'Clear understanding of individual strengths',
                            'Alignment between strengths and roles',
                            'Effective collaboration frameworks',
                            'Strategic focus on what matters most',
                            'Systems to leverage collective talent'
                        ];
                    @endphp

                    @foreach($challenges as $challenge)
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="text-gray-700">{{ $challenge }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="bg-primary-50 border-l-4 border-primary-600 p-6 rounded-r-lg mb-8">
                    <p class="text-primary-900 font-semibold mb-2">
                        The Solution:
                    </p>
                    <p class="text-primary-800">
                        A strengths-based approach that transforms individual talent into 
                        collective performance through proven frameworks and strategic alignment.
                    </p>
                </div>

                <a href="{{ route('strengths-programme') }}" class="btn btn-primary text-lg">
                    Learn About Our Approach
                </a>
            </div>

            <!-- Visual/Image -->
            <div class="relative">
                <div class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl p-8 lg:p-12">
                    @if(isset($whyTeamsFailImage) && $whyTeamsFailImage)
                        <img 
                            src="{{ $whyTeamsFailImage->url }}" 
                            alt="{{ $whyTeamsFailImage->alt_text ?? 'Team strategy and collaboration' }}"
                            class="w-full aspect-square object-cover rounded-lg shadow-lg"
                            loading="lazy"
                        >
                    @else
                        <!-- Placeholder for image -->
                        <div class="aspect-square bg-white rounded-lg shadow-lg flex items-center justify-center">
                            <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
