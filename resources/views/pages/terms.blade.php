@extends('layouts.app')

@section('title', $seo['title'] ?? 'Terms of Service - The Strengths Toolbox')

{{-- Meta tags are handled by partials/meta.blade.php via $seo variable --}}

@section('content')
    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto">

                <div class="mb-10">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        Terms of Service
                    </h1>
                    <p class="text-base text-neutral-500">
                        <strong>Effective Date:</strong> 1 November 2023 &nbsp;&middot;&nbsp;
                        <strong>Last Updated:</strong> {{ date('F Y') }}
                    </p>
                </div>

                <div class="prose prose-lg max-w-none">

                    <p>
                        Welcome to The Strengths Toolbox. By accessing or using our website at
                        <a href="{{ route('home') }}">www.thestrengthstoolbox.com</a> (the "Site"),
                        you agree to be bound by these Terms of Service. Please read them carefully
                        before using the Site.
                    </p>

                    <p>
                        We may update these terms from time to time. Continued use of the Site after
                        changes are posted constitutes your acceptance of the revised terms. If a
                        material change affects how we use your personal information, we will notify
                        you by email and give you the opportunity to opt out.
                    </p>

                    {{-- ─── DEFINITIONS ──────────────────────────────────────────── --}}
                    <h2>Definitions</h2>

                    <ul>
                        <li>
                            <strong>Personal Information</strong> — your name, email address,
                            telephone number, account credentials, and similar identifying details
                            you choose to share with us.
                        </li>
                        <li>
                            <strong>Non-Identifying Information</strong> — technical data such as
                            your browser type, ISP, referring URL, pages visited, and timestamps,
                            which cannot on their own identify you.
                        </li>
                        <li>
                            <strong>Business Contact Information</strong> — job title, business
                            address, or departmental email addresses. This is not treated as
                            Personal Information.
                        </li>
                        <li>
                            <strong>Aggregated Information</strong> — anonymised, statistical
                            data about multiple users from which no individual identity can be
                            inferred.
                        </li>
                    </ul>

                    {{-- ─── USE OF THE SITE ──────────────────────────────────────── --}}
                    <h2>Use of the Site</h2>

                    <p>You agree to use this Site only for lawful purposes and in a manner that does not infringe the rights of others. You must not:</p>
                    <ul>
                        <li>Reproduce, distribute, or republish any content without our prior written consent.</li>
                        <li>Attempt to gain unauthorised access to any part of the Site or its infrastructure.</li>
                        <li>Use automated tools to scrape, crawl, or harvest data from the Site.</li>
                        <li>Engage in any conduct that could damage, disable, or impair the Site.</li>
                    </ul>

                    {{-- ─── PRIVACY & DATA COLLECTION ───────────────────────────── --}}
                    <h2>Privacy &amp; Data Collection</h2>

                    <h3>Information you provide</h3>
                    <p>
                        We only collect Personal Information that you voluntarily submit, for example
                        when you complete a contact or enquiry form, sign up to receive resources, or
                        book a consultation. You must provide a unique identifier (username or email)
                        to access any restricted areas of the Site.
                    </p>

                    <h3>Automatically collected information</h3>
                    <p>
                        We collect Non-Identifying Information to improve the Site's performance,
                        analyse usage trends, and deliver a better experience. This may involve
                        cookies and similar tracking technologies (see the Cookies section below).
                    </p>

                    {{-- ─── COOKIES ──────────────────────────────────────────────── --}}
                    <h2>Cookies</h2>

                    <p>
                        Cookies are small text files stored on your device by your browser. We use
                        them — along with partners and analytics providers — to remember your
                        preferences, keep you signed in, and understand how visitors use the Site.
                    </p>
                    <p>
                        You can control cookies through your browser settings at any time. Disabling
                        cookies may limit certain features, but the core content of the Site remains
                        accessible.
                    </p>

                    {{-- ─── HOW WE USE YOUR INFORMATION ─────────────────────────── --}}
                    <h2>How We Use Your Information</h2>

                    <p>We may use the information we collect to:</p>
                    <ul>
                        <li>Respond to enquiries and deliver services you have requested.</li>
                        <li>Send you relevant updates, resources, or promotional content (with your consent, and always with an easy opt-out).</li>
                        <li>Maintain and improve the Site's functionality and security.</li>
                        <li>Fulfil orders and manage billing and account administration.</li>
                        <li>Comply with legal obligations and protect the rights of The Strengths Toolbox and its users.</li>
                    </ul>

                    <p>
                        We only collect and use information for purposes that are reasonable and
                        proportionate. You may withdraw your consent to marketing communications at
                        any time using the unsubscribe link in any email or by
                        <a href="{{ route('contact') }}">contacting us directly</a>.
                    </p>

                    {{-- ─── SHARING OF INFORMATION ───────────────────────────────── --}}
                    <h2>Sharing of Information</h2>

                    <h3>Service providers</h3>
                    <p>
                        We engage trusted third-party providers (for example, email platforms and
                        payment processors) to support site operations. These providers act under
                        confidentiality agreements and are permitted to use your data only for the
                        specific services they perform on our behalf.
                    </p>

                    <h3>Business transfers</h3>
                    <p>
                        In the event of a merger, acquisition, or sale of assets, Personal
                        Information may be transferred as part of that transaction, subject to the
                        promises made in this document.
                    </p>

                    <h3>Legal disclosure</h3>
                    <p>We may disclose information without your consent where required or permitted by law, including when:</p>
                    <ul>
                        <li>Required by a court order, regulation, or government authority.</li>
                        <li>Necessary to protect the safety, rights, or property of users or others.</li>
                        <li>Needed to prevent, investigate, or respond to fraud or a breach of these Terms.</li>
                        <li>The information is already publicly available.</li>
                    </ul>
                    <p>We will never disclose more information than is necessary to fulfil the disclosure purpose.</p>

                    {{-- ─── CHILDREN ─────────────────────────────────────────────── --}}
                    <h2>Children</h2>

                    <p>
                        Our services are directed at business professionals and are not intended for
                        children under 16. We do not knowingly collect Personal Information from
                        anyone under 16. If you believe a child has provided us with their details,
                        please <a href="{{ route('contact') }}">contact us</a> and we will delete the
                        information promptly.
                    </p>

                    {{-- ─── YOUR RIGHTS ──────────────────────────────────────────── --}}
                    <h2>Your Rights</h2>

                    <p>You have the right to:</p>
                    <ul>
                        <li><strong>Access</strong> the Personal Information we hold about you.</li>
                        <li><strong>Correct</strong> any inaccurate or incomplete data.</li>
                        <li><strong>Request deletion</strong> of your data (subject to legal retention requirements).</li>
                        <li><strong>Opt out</strong> of marketing communications at any time.</li>
                        <li><strong>Withdraw consent</strong> to any processing you have previously agreed to.</li>
                    </ul>

                    <p>
                        To exercise any of these rights, please
                        <a href="{{ route('contact') }}">contact us</a>. We will respond within
                        30 days of receiving your request.
                    </p>

                    {{-- ─── DATA RETENTION & SECURITY ───────────────────────────── --}}
                    <h2>Data Retention &amp; Security</h2>

                    <p>
                        We keep Personal Information only for as long as needed to fulfil the
                        purpose for which it was collected or as required by law. When data is no
                        longer required, it is securely deleted or anonymised.
                    </p>
                    <p>
                        We protect your information using SSL encryption for data in transit and
                        appropriate organisational and technical controls for data at rest. Access to
                        Personal Information is restricted to authorised personnel only. Any misuse
                        of data by an employee or third party will result in disciplinary action, up
                        to and including termination.
                    </p>

                    {{-- ─── INTELLECTUAL PROPERTY ────────────────────────────────── --}}
                    <h2>Intellectual Property</h2>

                    <p>
                        All content on this Site — including text, graphics, logos, and images — is
                        the property of The Strengths Toolbox or its content suppliers and is
                        protected by copyright law. You may save copies for your personal,
                        non-commercial use only. Any other reproduction, distribution, or
                        transmission without our prior written consent is strictly prohibited.
                    </p>
                    <p>
                        Images and photographs used on this Site may be subject to third-party
                        copyrights; they remain the property of their respective owners.
                    </p>

                    {{-- ─── LINKS TO THIRD-PARTY SITES ──────────────────────────── --}}
                    <h2>Links to Third-Party Sites</h2>

                    <p>
                        The Site may contain links to external websites for your convenience. These
                        links do not constitute an endorsement. The Strengths Toolbox accepts no
                        responsibility for the content, privacy practices, or accuracy of any
                        third-party site. When you leave our Site, you are subject to the terms and
                        policies of that external site.
                    </p>

                    {{-- ─── DISCLAIMER ───────────────────────────────────────────── --}}
                    <h2>Disclaimer</h2>

                    <p>
                        The content on this Site is provided in good faith for information and
                        guidance purposes only. While every effort is made to ensure accuracy and
                        relevance, the information should not be relied on as professional investment,
                        legal, or tax advice. You are responsible for assessing the suitability of
                        any information for your specific circumstances.
                    </p>
                    <p>
                        Past performance is not a guarantee of future results. The Strengths Toolbox
                        makes no warranty that the information on this Site is free from errors,
                        viruses, or other harmful components. Users access the Site at their own risk.
                    </p>
                    <p>
                        To the fullest extent permitted by law, The Strengths Toolbox is not liable
                        for any loss of income, profit, business, contracts, goodwill, or indirect
                        financial loss arising from the use of, or reliance on, this Site.
                    </p>

                    {{-- ─── REFUND POLICY ────────────────────────────────────────── --}}
                    <h2>Refund Policy</h2>

                    <p>
                        All purchases and programme fees are non-refundable unless there has been an
                        error on our part. If you have enrolled in a subscription or programme and
                        wish to cancel, please contact us with your full name and account details,
                        allowing at least 3 business days before the next billing date. Cancellation
                        requests received after a charge has been processed will not qualify for a
                        refund for that billing period.
                    </p>

                    {{-- ─── CONTACT ───────────────────────────────────────────────── --}}
                    <h2>Contact</h2>

                    <p>
                        If you have any questions about these Terms of Service, or wish to exercise
                        your rights under this document, please contact us:
                    </p>
                    <p>
                        <strong>Eberhard Niklaus</strong><br>
                        <strong>Email:</strong> <a href="mailto:welcome@eberhardniklaus.co.za">welcome@eberhardniklaus.co.za</a><br>
                        <strong>Phone:</strong> <a href="tel:+27832948033">+27 83 294 8033</a><br>
                        <strong>Website:</strong> <a href="{{ route('contact') }}">Contact page</a>
                    </p>

                </div>
            </div>
        </div>
    </section>
@endsection
