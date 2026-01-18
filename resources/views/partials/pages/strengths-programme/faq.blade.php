<section class="section-padding bg-white">
    <div class="container-custom">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Frequently Asked Questions
                </h2>
                <p class="text-xl text-gray-600">
                    Everything you need to know about the Strengths Programme
                </p>
            </div>

            <div 
                x-data="{ 
                    openFaq: null,
                    toggle(index) {
                        this.openFaq = this.openFaq === index ? null : index;
                    }
                }"
                class="space-y-4"
            >
                @php
                    $faqs = [
                        [
                            'question' => 'What is the Strengths Programme and how can it benefit my business?',
                            'answer' => 'The Strengths Programme is a comprehensive strengths-based development system designed to help businesses unlock their team\'s potential. By identifying and leveraging natural talents, businesses can improve team performance, increase engagement, reduce turnover, and drive sustainable growth. The program helps align individual strengths with organizational goals, creating a more productive and satisfied workforce.'
                        ],
                        [
                            'question' => 'Who should participate in the Strengths Programme?',
                            'answer' => 'The Strengths Programme is designed for organizations of all sizes looking to improve team performance. It\'s ideal for businesses experiencing high turnover, struggling with team alignment, missing sales targets, or dealing with employee disengagement. The program offers solutions for individuals, teams, managers, leaders, and salespeople, making it suitable for any level of your organization.'
                        ],
                        [
                            'question' => 'How does the Strengths-Based Team Development component work?',
                            'answer' => 'The team development component focuses on helping team members understand their individual strengths and how they complement each other. Through assessments, workshops, and ongoing support, teams learn to communicate more effectively, collaborate better, and leverage each member\'s unique talents. This creates stronger team cohesion, reduces conflict, and improves overall team performance.'
                        ],
                        [
                            'question' => 'What results can businesses expect from participating?',
                            'answer' => 'Businesses typically see measurable improvements in several key areas: increased employee engagement and satisfaction, reduced turnover rates, improved sales performance, better team collaboration, and enhanced leadership effectiveness. Many clients report seeing results within the first few months, with sustained improvements over time as the strengths-based approach becomes embedded in the organizational culture.'
                        ],
                        [
                            'question' => 'How do I get started with the Strengths Programme?',
                            'answer' => 'Getting started is easy. Simply book a complimentary 30-minute consultation where we\'ll discuss your business challenges, goals, and how the Strengths Programme can help. Based on your needs, we\'ll recommend the most appropriate program (for individuals, teams, managers, or salespeople) and create a customized plan. There\'s no obligation, and the consultation is completely free.'
                        ]
                    ];
                @endphp

                @foreach($faqs as $index => $faq)
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button
                            @click="toggle({{ $index }})"
                            class="w-full px-6 py-4 text-left flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors"
                        >
                            <span class="font-semibold text-gray-900 pr-4">{{ $faq['question'] }}</span>
                            <svg 
                                class="w-5 h-5 text-gray-500 flex-shrink-0 transition-transform"
                                :class="{ 'rotate-180': openFaq === {{ $index }} }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div 
                            x-show="openFaq === {{ $index }}"
                            x-collapse
                            class="px-6 py-4 bg-white"
                        >
                            <p class="text-gray-600 leading-relaxed">{{ $faq['answer'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Still Have Questions CTA -->
            <div class="text-center mt-12">
                <p class="text-lg text-gray-700 mb-4">
                    Still have questions?
                </p>
                <a href="{{ route('contact') }}" class="btn btn-primary text-lg">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</section>
