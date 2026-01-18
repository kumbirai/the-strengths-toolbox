@extends('layouts.app')

@section('title', $seo['title'] ?? 'Privacy Statement - The Strengths Toolbox')

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-8">
                    Privacy Statement
                </h1>

                <div class="prose prose-lg max-w-none">
                    <p class="text-lg text-gray-700 mb-6">
                        <strong>Last Updated:</strong> {{ date('F Y') }}
                    </p>

                    <h2>Introduction</h2>
                    <p>
                        The Strengths Toolbox ("we," "our," or "us") is committed to protecting your privacy. 
                        This Privacy Statement explains how we collect, use, disclose, and safeguard your 
                        information when you visit our website.
                    </p>

                    <h2>Information We Collect</h2>
                    <p>We may collect information about you in various ways:</p>
                    <ul>
                        <li><strong>Personal Information:</strong> Name, email address, phone number, and other contact information you provide</li>
                        <li><strong>Usage Data:</strong> Information about how you access and use our website</li>
                        <li><strong>Cookies:</strong> Data files placed on your device to enhance your browsing experience</li>
                    </ul>

                    <h2>How We Use Your Information</h2>
                    <p>We use the information we collect to:</p>
                    <ul>
                        <li>Provide, maintain, and improve our services</li>
                        <li>Respond to your inquiries and requests</li>
                        <li>Send you marketing communications (with your consent)</li>
                        <li>Analyze website usage and trends</li>
                    </ul>

                    <h2>Data Protection</h2>
                    <p>
                        We implement appropriate technical and organizational measures to protect your 
                        personal information against unauthorized access, alteration, disclosure, or destruction.
                    </p>

                    <h2>Your Rights</h2>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access your personal information</li>
                        <li>Correct inaccurate data</li>
                        <li>Request deletion of your data</li>
                        <li>Opt-out of marketing communications</li>
                    </ul>

                    <h2>Contact Us</h2>
                    <p>
                        If you have questions about this Privacy Statement, please contact us at:
                    </p>
                    <p>
                        <strong>Email:</strong> {{ config('mail.from.address') }}<br>
                        <strong>Website:</strong> <a href="{{ route('contact') }}">Contact Page</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
