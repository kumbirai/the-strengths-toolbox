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
                            'answer' => 'The Strengths Programme is a strengths-based development initiative designed for individuals, managers, salespeople, and teams. It uses assessments like CliftonStrengths and coaching to help participants understand and leverage their natural talents. By tailoring roles to strengths, it helps reduce misalignment, burnout, disengagement, and turnover—all driving stronger collaboration, engagement, and business outcomes.'
                        ],
                        [
                            'question' => 'Who should participate in the Strengths Programme?',
                            'answer' => 'The Programme includes four tailored tracks: For Teams – Build stronger collaboration and communication through group assessments, coaching, and workshops. For Salespeople – With the CliftonStrengths for Sales report, use natural strengths to deepen trust, close more deals, and improve performance (by up to 19%). For Managers & Leaders – Enhance leadership by aligning team roles with strengths to boost engagement and productivity. For Individuals – Discover your natural talents, gain confidence, and increase your performance.'
                        ],
                        [
                            'question' => 'How does the Strengths-Based Team Development component work?',
                            'answer' => 'This track includes a structured, multi-step process: (1) Each team member completes the CliftonStrengths assessment. (2) One‑on‑one coaching sessions are held with the manager and each team member. (3) Group workshops apply strengths to real business challenges. (4) Ongoing coaching helps sustain results. This collaborative approach enhances communication, trust, goal alignment, and overall team performance.'
                        ],
                        [
                            'question' => 'What results can businesses expect from participating?',
                            'answer' => 'By focusing on strengths rather than weaknesses, businesses can expect: sales improvements (by up to 19%) and happier, more engaged employees; stronger team collaboration, improved communication, and a positive, high-performance culture; and a reduction in misaligned teams, high turnover, missed sales targets, burnout, and disengagement.'
                        ],
                        [
                            'question' => 'How do I get started with the Strengths Programme?',
                            'answer' => 'The first step is to book a 30-minute consultation to assess your business or team\'s specific needs and explore how the Strengths Programme can align with your goals.'
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
