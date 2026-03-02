@extends('layouts.app')

@section('title', $seo['title'] ?? 'Facilitation & Workshops - The Strengths Toolbox')

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')

    {{-- ─── HERO ──────────────────────────────────────────────────── --}}
    <section class="relative bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 text-white overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V4h4V2h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="container-custom section-padding relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <p class="text-primary-200 font-semibold uppercase tracking-widest text-sm mb-4">
                    Strengths-Based Facilitation
                </p>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    Workshops That Create<br>
                    <span class="text-primary-200">Real, Lasting Change</span>
                </h1>

                <p class="text-xl md:text-2xl text-primary-100 mb-8 max-w-2xl mx-auto">
                    Expertly facilitated workshops that help your team discover their strengths,
                    improve collaboration, and build a culture of high performance.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a
                        href="{{ route('contact') }}?source=facilitation"
                        class="btn btn-secondary text-lg px-8 py-4 shadow-lg"
                    >
                        Book a Workshop
                    </a>
                    <a
                        href="#workshop-types"
                        class="btn btn-outline text-lg px-8 py-4 border-white text-white hover:bg-white hover:text-primary-700"
                    >
                        Explore Workshops
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── WHY FACILITATION ────────────────────────────────────────── --}}
    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto">
                <div class="section-header">
                    <h2 class="section-title">Why Facilitated Workshops Work</h2>
                    <p class="section-subtitle">
                        Training alone rarely sticks. Facilitated workshops create shared
                        experiences that shift mindsets and change behaviours.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                    @php
                        $reasons = [
                            [
                                'icon' => 'lightbulb',
                                'title' => 'Insight in Context',
                                'body' => 'People discover their strengths through guided conversation and group reflection — not just a report. Insight lands differently when it is shared out loud.',
                            ],
                            [
                                'icon' => 'users',
                                'title' => 'Shared Language',
                                'body' => 'Teams that go through a workshop together leave with a common vocabulary for strengths, making collaboration and feedback more natural and effective.',
                            ],
                            [
                                'icon' => 'chart',
                                'title' => 'Immediate Application',
                                'body' => 'Every session includes practical activities and action steps, so participants leave with tangible tools they can use the very next day.',
                            ],
                        ];
                    @endphp

                    @foreach($reasons as $reason)
                        <div class="text-center">
                            <div class="icon-badge-lg icon-badge-primary mx-auto mb-4">
                                @if($reason['icon'] === 'lightbulb')
                                    <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m1.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                @elseif($reason['icon'] === 'users')
                                    <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                @else
                                    <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                @endif
                            </div>
                            <h3 class="text-xl font-bold text-neutral-900 mb-2">{{ $reason['title'] }}</h3>
                            <p class="text-neutral-600">{{ $reason['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ─── WORKSHOP TYPES ──────────────────────────────────────────── --}}
    <section id="workshop-types" class="section-padding section-muted">
        <div class="container-custom">
            <div class="section-header">
                <h2 class="section-title">Workshop Formats</h2>
                <p class="section-subtitle">
                    Each workshop is tailored to your team's size, goals, and context.
                    Half-day, full-day, and multi-session formats are available.
                </p>
            </div>

            <div class="grid-2 grid-constrained">
                @php
                    $workshops = [
                        [
                            'title' => 'Strengths Discovery Workshop',
                            'audience' => 'Individuals & Teams',
                            'description' => 'An immersive introduction to CliftonStrengths. Participants complete their assessment and then work through facilitated activities to understand their top themes, how they interact with others, and where they create the most value.',
                            'outcomes' => [
                                'Complete CliftonStrengths assessment',
                                'Understand your top 5 (or all 34) themes',
                                'Build self-awareness and confidence',
                                'Identify strengths-based development opportunities',
                            ],
                            'duration' => 'Half-day or full day',
                            'color' => 'primary',
                        ],
                        [
                            'title' => 'Team Strengths Workshop',
                            'audience' => 'Teams of 4–20',
                            'description' => 'Designed for existing teams, this workshop maps the collective strengths of the group, reveals gaps, and establishes how each person contributes most effectively. Teams leave with stronger relationships and a clearer operating model.',
                            'outcomes' => [
                                'Map your team\'s collective strengths profile',
                                'Understand how individual strengths complement each other',
                                'Improve communication and reduce friction',
                                'Develop a shared strengths language for day-to-day work',
                            ],
                            'duration' => 'Full day',
                            'color' => 'accent',
                        ],
                        [
                            'title' => 'Leadership Strengths Workshop',
                            'audience' => 'Managers & Senior Leaders',
                            'description' => 'Leaders explore how their top strengths shape their leadership style — including their blind spots. Through structured reflection and peer conversation, they learn to lead more authentically and build teams that perform.',
                            'outcomes' => [
                                'Clarify your authentic leadership style',
                                'Recognise strengths-based blind spots',
                                'Apply strengths to coaching and team development',
                                'Create a personal leadership development plan',
                            ],
                            'duration' => 'Full day or two half-days',
                            'color' => 'primary',
                        ],
                        [
                            'title' => 'Sales Strengths Workshop',
                            'audience' => 'Sales Teams',
                            'description' => 'Sales professionals discover the natural strengths that drive their best performance and learn to build on them rather than conforming to a one-size-fits-all sales script. More authentic selling, better results.',
                            'outcomes' => [
                                'Identify your natural selling strengths',
                                'Develop a personalised sales approach',
                                'Build stronger client relationships',
                                'Increase confidence and conversion rates',
                            ],
                            'duration' => 'Half-day or full day',
                            'color' => 'accent',
                        ],
                    ];
                @endphp

                @foreach($workshops as $workshop)
                    <div class="feature-card-elevated">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="feature-title mb-1">{{ $workshop['title'] }}</h3>
                                <span class="inline-block text-sm font-semibold px-3 py-1 rounded-full
                                    {{ $workshop['color'] === 'primary' ? 'bg-primary-100 text-primary-700' : 'bg-accent-100 text-accent-700' }}">
                                    {{ $workshop['audience'] }}
                                </span>
                            </div>
                        </div>

                        <p class="feature-description mb-6">{{ $workshop['description'] }}</p>

                        <div class="mb-6">
                            <h4 class="font-semibold text-neutral-900 mb-3">What you'll leave with:</h4>
                            <ul class="check-list">
                                @foreach($workshop['outcomes'] as $outcome)
                                    <li class="check-list-item">
                                        <svg class="{{ $workshop['color'] === 'primary' ? 'check-list-icon-primary' : 'check-list-icon-accent' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="check-list-text">{{ $outcome }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="{{ $workshop['color'] === 'primary' ? 'callout-primary' : 'callout-accent' }}">
                            <p class="callout-title {{ $workshop['color'] === 'primary' ? 'text-primary-900' : 'text-accent-900' }}">Format</p>
                            <p class="callout-text {{ $workshop['color'] === 'primary' ? 'text-primary-800' : 'text-accent-800' }}">{{ $workshop['duration'] }} &middot; On-site or virtual &middot; Tailored to your goals</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── HOW IT WORKS ────────────────────────────────────────────── --}}
    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto">
                <div class="section-header">
                    <h2 class="section-title">How It Works</h2>
                    <p class="section-subtitle">A straightforward process from first conversation to lasting impact.</p>
                </div>

                <div class="mt-10 space-y-8">
                    @php
                        $steps = [
                            ['num' => '01', 'title' => 'Discovery Call', 'body' => 'We start with a 30-minute conversation to understand your team, your goals, and the outcomes you want. No obligation — just a clear picture of what will serve you best.'],
                            ['num' => '02', 'title' => 'Custom Design', 'body' => "We tailor the workshop content, activities, and timing to your context — whether you're an executive team, a new sales cohort, or a cross-functional group."],
                            ['num' => '03', 'title' => 'Assessments', 'body' => 'Participants complete their CliftonStrengths assessment in advance, so the workshop day is spent on application and insight rather than administration.'],
                            ['num' => '04', 'title' => 'Workshop Day', 'body' => 'Eberhard leads an engaging, practical session that combines structured learning with open conversation. Participants leave energised, not exhausted.'],
                            ['num' => '05', 'title' => 'Follow-Through', 'body' => 'We provide follow-up resources, action plans, and optional coaching to ensure the insights from the workshop translate into lasting change.'],
                        ];
                    @endphp

                    @foreach($steps as $step)
                        <div class="flex gap-6">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold text-sm">
                                {{ $step['num'] }}
                            </div>
                            <div class="pt-2">
                                <h3 class="text-xl font-bold text-neutral-900 mb-1">{{ $step['title'] }}</h3>
                                <p class="text-neutral-600">{{ $step['body'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ─── CTA ─────────────────────────────────────────────────────── --}}
    <section class="section-padding bg-gradient-to-br from-primary-600 to-primary-800 text-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-6">
                    Ready to Build a Stronger Team?
                </h2>

                <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
                    Book a free 30-minute call to discuss which workshop format is the right
                    fit for your team — and walk away with a clear plan.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a
                        href="{{ route('contact') }}?source=facilitation-cta"
                        class="btn btn-secondary text-lg px-8 py-4 shadow-lg"
                    >
                        Book Your Free Consultation
                    </a>
                    <a
                        href="{{ route('strengths-programme') }}"
                        class="btn btn-outline text-lg px-8 py-4 border-white text-white hover:bg-white hover:text-primary-700"
                    >
                        View the Full Programme
                    </a>
                </div>

                <div class="mt-12 flex flex-wrap justify-center items-center gap-8 text-sm text-primary-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>On-site or Virtual</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Fully Tailored</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Free Discovery Call</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
