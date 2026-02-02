<section class="section-padding bg-gray-50">
    <div class="container-custom">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Image/Visual -->
            <div class="order-2 lg:order-1">
                <div class="bg-gradient-to-br from-primary-100 to-accent-100 rounded-2xl p-8 lg:p-12">
                    @if(isset($experienceImage) && $experienceImage)
                        <img 
                            src="{{ $experienceImage->url }}" 
                            alt="{{ $experienceImage->alt_text ?? 'Professional development and training experience' }}"
                            class="w-full aspect-square object-cover rounded-lg shadow-lg"
                            loading="lazy"
                        >
                    @else
                        <!-- Placeholder for image -->
                        <div class="aspect-square bg-white rounded-lg shadow-lg flex items-center justify-center">
                            <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Content -->
            <div class="order-1 lg:order-2">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    What You'll Experience
                </h2>
                
                <p class="text-lg text-gray-600 mb-8">
                    When you work with The Strengths Toolbox, you'll discover a transformative 
                    approach that goes beyond traditional training:
                </p>

                <ul class="space-y-4 mb-8">
                    @php
                        $experiences = [
                            [
                                'title' => 'Personalized Assessment',
                                'description' => 'Discover your unique strengths profile and understand how to leverage it for maximum impact.'
                            ],
                            [
                                'title' => 'Practical Training',
                                'description' => 'Engage in hands-on workshops and programs designed to build real-world skills.'
                            ],
                            [
                                'title' => 'Ongoing Support',
                                'description' => 'Receive continuous guidance and resources to maintain momentum and achieve lasting results.'
                            ],
                            [
                                'title' => 'Measurable Outcomes',
                                'description' => 'Track your progress with clear metrics and see tangible improvements in performance.'
                            ]
                        ];
                    @endphp

                    @foreach($experiences as $experience)
                        <li class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-primary-600 text-white rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">{{ $experience['title'] }}</h4>
                                <p class="text-gray-600">{{ $experience['description'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('pages.show', 'sales-courses') }}" class="btn btn-primary text-lg">
                        Explore Sales Courses
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-outline text-lg">
                        Schedule a Consultation
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
