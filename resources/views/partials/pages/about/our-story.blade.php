<section class="section-padding bg-white">
    <div class="container-custom">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Our Story
                </h2>
                <p class="text-xl text-gray-600">
                    The journey that led to The Strengths Toolbox
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                <!-- Founder Image -->
                <div class="order-2 lg:order-1">
                    <div class="bg-gradient-to-br from-primary-100 to-accent-100 rounded-2xl p-6">
                        @if(isset($eberhardImage) && $eberhardImage)
                            <img 
                                src="{{ $eberhardImage->url }}" 
                                alt="{{ $eberhardImage->alt_text ?? 'Eberhard Niklaus, Founder of The Strengths Toolbox' }}"
                                class="w-full aspect-square object-cover rounded-lg shadow-lg"
                                loading="lazy"
                            >
                        @else
                            <!-- Placeholder for founder image -->
                            <div class="aspect-square bg-white rounded-lg shadow-lg flex items-center justify-center">
                                <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Founder Story -->
                <div class="order-1 lg:order-2">
                    <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                        Eberhard Niklaus
                    </h3>
                    
                    <div class="prose prose-lg max-w-none text-gray-700 space-y-4">
                        <p>
                            After spending over <strong>17 years in the franchise industry</strong>, where he helped 
                            nearly <strong>70 SME business owners</strong> succeed, Eberhard transitioned into 
                            <strong>coaching and training</strong>.
                        </p>

                        <p>
                            His passion for <strong>sales performance</strong>, combined with his expertise in 
                            <strong>strengths-based development</strong> using Gallup tools, led him to launch 
                            The Strengths Toolbox. Since <strong>2019</strong>, Eberhard has helped 
                            <strong>hundreds of clients</strong> unlock their peak performance, leveraging their 
                            natural talents for greater success.
                        </p>

                        <p>
                            At The Strengths Toolbox, we are passionate about helping individuals and businesses 
                            realize their unique strengths and talents. <strong>Eberhard Niklaus</strong>, founder 
                            and director, has a rich background in sales, business management, and coaching.
                        </p>

                        <p>
                            From his early days as a <strong>Sales Representative</strong> to becoming a 
                            <strong>Franchisee</strong>, <strong>Sales Manager</strong>, and 
                            <strong>Franchise Network Leader</strong>, Eberhard has seen firsthand the challenges 
                            faced by managers and business owners trying to create thriving, high-performing workplaces.
                        </p>

                        <p>
                            This experience, combined with his deep understanding of strengths-based development, 
                            drives The Strengths Toolbox's mission to help businesses build strong teams and unlock 
                            strong profits through proven methodologies.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
