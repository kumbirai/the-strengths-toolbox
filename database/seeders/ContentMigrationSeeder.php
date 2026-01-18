<?php

namespace Database\Seeders;

use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Comprehensive content migration seeder
 * Populates CMS with all pages from existing website
 */
class ContentMigrationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting comprehensive content migration...');
        $this->command->newLine();

        // Import main pages from transformed content
        $this->importHomepageContent();
        $this->importStrengthsProgrammeContent();
        $this->importAboutUsContent();

        // Import existing content pages
        $this->seedStrengthsBasedDevelopmentPages();
        $this->seedSalesTrainingPages();
        $this->seedFacilitationPages();
        $this->seedStandalonePages();

        $this->command->newLine();
        $this->command->info('✓ Content migration completed successfully!');
        $this->command->info('Total pages created: '.Page::count());
    }

    /**
     * Import homepage content
     */
    protected function importHomepageContent(): void
    {
        $this->command->info('Importing homepage content...');

        // Get homepage content from embedded method
        $homepageContent = $this->getHomepageContent();

        // Create or update homepage page
        $homepage = Page::firstOrNew(['slug' => 'home']);
        $homepage->fill([
            'title' => 'Home - The Strengths Toolbox',
            'slug' => 'home',
            'excerpt' => 'Transform your business with strengths-based development. Build strong teams and unlock strong profits.',
            'content' => $homepageContent,
            'meta_title' => 'The Strengths Toolbox - Build Strong Teams, Unlock Strong Profits',
            'meta_description' => 'Transform your business with strengths-based development. Discover how The Strengths Toolbox helps teams achieve peak performance and drive sustainable growth.',
            'meta_keywords' => 'strengths-based development, team building, business growth, CliftonStrengths, team performance',
            'is_published' => true,
            'published_at' => Carbon::now(),
        ]);
        $homepage->save();

        $this->command->line('  ✓ Homepage content imported');
    }

    /**
     * Import Strengths Programme page content
     */
    protected function importStrengthsProgrammeContent(): void
    {
        $this->command->info('Importing Strengths Programme page content...');

        // Get strengths programme content from embedded method
        $content = $this->getStrengthsProgrammeContent();

        // Create or update page
        $page = Page::firstOrNew(['slug' => 'strengths-programme']);
        $page->fill([
            'title' => 'Strengths Programme - The Strengths Toolbox',
            'slug' => 'strengths-programme',
            'excerpt' => 'Unlock growth through the Power of Strengths. Discover proven programs for individuals, managers, salespeople, and teams.',
            'content' => $content,
            'meta_title' => 'Strengths Programme - Unlock Growth Through Strengths - The Strengths Toolbox',
            'meta_description' => 'Discover how strengths-based development transforms teams and drives business growth. Four proven programs for individuals, managers, salespeople, and teams.',
            'meta_keywords' => 'strengths programme, strengths-based development, team development, leadership training, sales training',
            'is_published' => true,
            'published_at' => Carbon::now(),
        ]);
        $page->save();

        $this->command->line('  ✓ Strengths Programme page imported');
    }

    /**
     * Import About Us page content
     */
    protected function importAboutUsContent(): void
    {
        $this->command->info('Importing About Us page content...');

        // Get about us content from embedded method
        $content = $this->getAboutUsContent();

        // Create or update page
        $page = Page::firstOrNew(['slug' => 'about-us']);
        $page->fill([
            'title' => 'About Us - The Strengths Toolbox',
            'slug' => 'about-us',
            'excerpt' => 'Learn about Eberhard Niklaus and The Strengths Toolbox. 30 years of experience helping businesses build strong teams and drive growth.',
            'content' => $content,
            'meta_title' => 'About Us - The Strengths Toolbox - 30 Years of Experience',
            'meta_description' => 'Meet Eberhard Niklaus and learn about The Strengths Toolbox. 30 years of experience, 1560+ happy clients, proven results in strengths-based development.',
            'meta_keywords' => 'about us, Eberhard Niklaus, strengths-based development, team building, business coaching',
            'is_published' => true,
            'published_at' => Carbon::now(),
        ]);
        $page->save();

        $this->command->line('  ✓ About Us page imported');
    }

    /**
     * Seed Strengths-Based Development pages
     */
    protected function seedStrengthsBasedDevelopmentPages(): void
    {
        $this->command->info('Seeding Strengths-Based Development pages...');

        $pages = [
            [
                'title' => 'The Power of Strengths',
                'slug' => 'the-power-of-strengths',
                'excerpt' => 'Discover how identifying and leveraging natural talents transforms individual and team performance.',
                'content' => $this->getPowerOfStrengthsContent(),
                'meta_title' => 'The Power of Strengths - The Strengths Toolbox',
                'meta_description' => 'Learn how strengths-based development can transform your team and drive business growth. Discover the power of leveraging natural talents.',
                'meta_keywords' => 'strengths-based development, CliftonStrengths, team building, talent development',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Strengths-Based Development for Teams',
                'slug' => 'strengths-based-development/teams',
                'excerpt' => 'Build cohesive teams where members understand and complement each other\'s strengths.',
                'content' => $this->getTeamsContent(),
                'meta_title' => 'Team Development - Strengths-Based Approach - The Strengths Toolbox',
                'meta_description' => 'Transform your team with strengths-based development. Build stronger collaboration and improve team performance.',
                'meta_keywords' => 'team building, team development, strengths-based teams, team collaboration',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Strengths-Based Development for Managers and Leaders',
                'slug' => 'strengths-based-development/managers-leaders',
                'excerpt' => 'Develop authentic leadership styles and build high-performing teams through strengths-based management.',
                'content' => $this->getManagersLeadersContent(),
                'meta_title' => 'Leadership Development - Strengths-Based Management - The Strengths Toolbox',
                'meta_description' => 'Develop your leadership skills with strengths-based management. Learn to lead with authenticity and build high-performing teams.',
                'meta_keywords' => 'leadership development, management training, strengths-based leadership, team management',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Strengths-Based Development for Salespeople',
                'slug' => 'strengths-based-development/salespeople',
                'excerpt' => 'Transform sales performance by understanding and leveraging your natural selling strengths.',
                'content' => $this->getSalespeopleContent(),
                'meta_title' => 'Sales Training - Strengths-Based Selling - The Strengths Toolbox',
                'meta_description' => 'Improve your sales performance with strengths-based selling. Discover your natural selling strengths and close more deals.',
                'meta_keywords' => 'sales training, sales development, strengths-based selling, sales performance',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Strengths-Based Development for Individuals',
                'slug' => 'strengths-based-development/individuals',
                'excerpt' => 'Discover your unique strengths and learn how to leverage them for personal and professional growth.',
                'content' => $this->getIndividualsContent(),
                'meta_title' => 'Individual Development - Discover Your Strengths - The Strengths Toolbox',
                'meta_description' => 'Discover your unique strengths and unlock your potential. Personal development through strengths-based approach.',
                'meta_keywords' => 'personal development, strengths assessment, individual growth, career development',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
        ];

        foreach ($pages as $pageData) {
            $page = Page::firstOrNew(['slug' => $pageData['slug']]);
            $page->fill($pageData);
            $page->save();
            $this->command->line("  ✓ Created: {$pageData['title']}");
        }
    }

    /**
     * Seed Sales Training pages
     */
    protected function seedSalesTrainingPages(): void
    {
        $this->command->info('Seeding Sales Training pages...');

        // Create parent page first
        $parentPage = Page::firstOrNew(['slug' => 'sales-training']);
        $parentPage->fill([
            'title' => 'Sales Training',
            'slug' => 'sales-training',
            'excerpt' => 'Transform your sales team with proven training programs. Discover strengths-based sales training, relationship selling, phone sales, and more.',
            'content' => '<div class="prose prose-lg max-w-none"><h2>Sales Training Programs</h2><p>Transform your sales team with proven training programs designed to leverage natural strengths and build lasting client relationships.</p><div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6"><a href="/sales-training/strengths-based-training" class="block p-4 border rounded-lg hover:bg-gray-50"><h3 class="font-semibold">Strengths-Based Training</h3><p class="text-sm text-gray-600">Leverage natural selling talents for better results</p></a><a href="/sales-training/relationship-selling" class="block p-4 border rounded-lg hover:bg-gray-50"><h3 class="font-semibold">Relationship Selling</h3><p class="text-sm text-gray-600">Build lasting client relationships</p></a><a href="/sales-training/selling-on-the-phone" class="block p-4 border rounded-lg hover:bg-gray-50"><h3 class="font-semibold">Selling On The Phone</h3><p class="text-sm text-gray-600">Master phone sales techniques</p></a><a href="/sales-training/sales-fundamentals-workshop" class="block p-4 border rounded-lg hover:bg-gray-50"><h3 class="font-semibold">Sales Fundamentals Workshop</h3><p class="text-sm text-gray-600">Essential skills for sales success</p></a><a href="/sales-training/top-10-sales-secrets" class="block p-4 border rounded-lg hover:bg-gray-50"><h3 class="font-semibold">Top 10 Sales Secrets</h3><p class="text-sm text-gray-600">Proven secrets from successful salespeople</p></a><a href="/sales-training/in-person-sales" class="block p-4 border rounded-lg hover:bg-gray-50"><h3 class="font-semibold">In-Person Sales</h3><p class="text-sm text-gray-600">Excel at face-to-face selling</p></a></div></div>',
            'meta_title' => 'Sales Training - The Strengths Toolbox',
            'meta_description' => 'Transform your sales team with proven training programs. Strengths-based sales training, relationship selling, phone sales, and more.',
            'meta_keywords' => 'sales training, sales coaching, sales development, strengths-based selling',
            'is_published' => true,
            'published_at' => Carbon::now(),
        ]);
        $parentPage->save();
        $this->command->line('  ✓ Created: Sales Training (parent page)');

        $pages = [
            [
                'title' => 'Strengths-Based Sales Training',
                'slug' => 'sales-training/strengths-based-training',
                'excerpt' => 'Transform your sales team with strengths-based training that leverages natural selling talents.',
                'content' => $this->getSalesTrainingContent('strengths-based'),
                'meta_title' => 'Strengths-Based Sales Training - The Strengths Toolbox',
                'meta_description' => 'Transform your sales team with strengths-based training. Leverage natural selling talents for better results.',
                'meta_keywords' => 'sales training, strengths-based selling, sales development, sales coaching',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Relationship Selling',
                'slug' => 'sales-training/relationship-selling',
                'excerpt' => 'Build lasting client relationships through proven relationship selling techniques.',
                'content' => $this->getSalesTrainingContent('relationship'),
                'meta_title' => 'Relationship Selling Training - The Strengths Toolbox',
                'meta_description' => 'Master relationship selling to build lasting client relationships and increase sales success.',
                'meta_keywords' => 'relationship selling, sales techniques, client relationships, sales training',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Selling On The Phone',
                'slug' => 'sales-training/selling-on-the-phone',
                'excerpt' => 'Master phone sales techniques to close more deals and build stronger client connections.',
                'content' => $this->getSalesTrainingContent('phone'),
                'meta_title' => 'Phone Sales Training - Selling On The Phone - The Strengths Toolbox',
                'meta_description' => 'Master phone sales techniques. Learn to close deals and build relationships over the phone.',
                'meta_keywords' => 'phone sales, telemarketing, phone selling, sales calls, telephone sales',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Sales Fundamentals Workshop',
                'slug' => 'sales-training/sales-fundamentals-workshop',
                'excerpt' => 'Master the fundamentals of sales with our comprehensive workshop covering essential selling skills.',
                'content' => $this->getSalesTrainingContent('fundamentals'),
                'meta_title' => 'Sales Fundamentals Workshop - The Strengths Toolbox',
                'meta_description' => 'Master sales fundamentals with our comprehensive workshop. Essential skills for sales success.',
                'meta_keywords' => 'sales fundamentals, sales workshop, sales basics, sales skills',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Top 10 Sales Secrets',
                'slug' => 'sales-training/top-10-sales-secrets',
                'excerpt' => 'Discover the top 10 proven sales secrets that successful salespeople use to close more deals.',
                'content' => $this->getSalesTrainingContent('secrets'),
                'meta_title' => 'Top 10 Sales Secrets - The Strengths Toolbox',
                'meta_description' => 'Discover the top 10 proven sales secrets used by successful salespeople to close more deals.',
                'meta_keywords' => 'sales secrets, sales tips, sales strategies, sales success',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'In-Person Sales Training',
                'slug' => 'sales-training/in-person-sales',
                'excerpt' => 'Excel at face-to-face sales with proven techniques for in-person selling and client meetings.',
                'content' => $this->getSalesTrainingContent('in-person'),
                'meta_title' => 'In-Person Sales Training - The Strengths Toolbox',
                'meta_description' => 'Master in-person sales techniques. Excel at face-to-face selling and client meetings.',
                'meta_keywords' => 'in-person sales, face-to-face selling, sales meetings, client presentations',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
        ];

        foreach ($pages as $pageData) {
            $page = Page::firstOrNew(['slug' => $pageData['slug']]);
            $page->fill($pageData);
            $page->save();
            $this->command->line("  ✓ Created: {$pageData['title']}");
        }
    }

    /**
     * Seed Facilitation/Workshop pages
     */
    protected function seedFacilitationPages(): void
    {
        $this->command->info('Seeding Facilitation/Workshop pages...');

        $pages = [
            [
                'title' => 'Customer Service Workshop',
                'slug' => 'facilitation/customer-service-workshop',
                'excerpt' => 'Enhance customer service skills with our comprehensive workshop focused on delivering exceptional customer experiences.',
                'content' => $this->getWorkshopContent('customer-service'),
                'meta_title' => 'Customer Service Workshop - The Strengths Toolbox',
                'meta_description' => 'Enhance customer service skills with our comprehensive workshop. Deliver exceptional customer experiences.',
                'meta_keywords' => 'customer service training, customer service workshop, customer experience',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Emotional Intelligence Workshop',
                'slug' => 'facilitation/emotional-intelligence-workshop',
                'excerpt' => 'Develop emotional intelligence skills to improve relationships, communication, and leadership effectiveness.',
                'content' => $this->getWorkshopContent('emotional-intelligence'),
                'meta_title' => 'Emotional Intelligence Workshop - The Strengths Toolbox',
                'meta_description' => 'Develop emotional intelligence skills. Improve relationships, communication, and leadership.',
                'meta_keywords' => 'emotional intelligence, EQ training, emotional intelligence workshop, self-awareness',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Goal Setting and Getting Things Done',
                'slug' => 'facilitation/goal-setting',
                'excerpt' => 'Master goal setting and execution strategies to achieve your objectives and drive results.',
                'content' => $this->getWorkshopContent('goal-setting'),
                'meta_title' => 'Goal Setting Workshop - Getting Things Done - The Strengths Toolbox',
                'meta_description' => 'Master goal setting and execution. Learn strategies to achieve your objectives and drive results.',
                'meta_keywords' => 'goal setting, productivity, execution, achievement, time management',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'High-Performance Teams Workshop',
                'slug' => 'facilitation/high-performance-teams',
                'excerpt' => 'Build and lead high-performance teams that consistently deliver exceptional results.',
                'content' => $this->getWorkshopContent('high-performance-teams'),
                'meta_title' => 'High-Performance Teams Workshop - The Strengths Toolbox',
                'meta_description' => 'Build and lead high-performance teams. Learn strategies to consistently deliver exceptional results.',
                'meta_keywords' => 'high-performance teams, team building, team effectiveness, team leadership',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Interpersonal Skills Workshop',
                'slug' => 'facilitation/interpersonal-skills',
                'excerpt' => 'Develop strong interpersonal skills to build better relationships and improve workplace communication.',
                'content' => $this->getWorkshopContent('interpersonal-skills'),
                'meta_title' => 'Interpersonal Skills Workshop - The Strengths Toolbox',
                'meta_description' => 'Develop strong interpersonal skills. Build better relationships and improve workplace communication.',
                'meta_keywords' => 'interpersonal skills, communication skills, relationship building, workplace communication',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Managing Personal Finances Workshop',
                'slug' => 'facilitation/personal-finances',
                'excerpt' => 'Learn practical financial management skills to take control of your personal finances and build wealth.',
                'content' => $this->getWorkshopContent('personal-finances'),
                'meta_title' => 'Personal Finance Management Workshop - The Strengths Toolbox',
                'meta_description' => 'Learn practical financial management skills. Take control of your personal finances and build wealth.',
                'meta_keywords' => 'personal finance, financial management, money management, financial planning',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Presentation Skills Workshop',
                'slug' => 'facilitation/presentation-skills',
                'excerpt' => 'Master presentation skills to deliver compelling presentations that engage and persuade your audience.',
                'content' => $this->getWorkshopContent('presentation-skills'),
                'meta_title' => 'Presentation Skills Workshop - The Strengths Toolbox',
                'meta_description' => 'Master presentation skills. Deliver compelling presentations that engage and persuade your audience.',
                'meta_keywords' => 'presentation skills, public speaking, presentation training, communication skills',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Supervising Others Workshop',
                'slug' => 'facilitation/supervising-others',
                'excerpt' => 'Develop effective supervision skills to lead, motivate, and manage your team successfully.',
                'content' => $this->getWorkshopContent('supervising-others'),
                'meta_title' => 'Supervision Skills Workshop - The Strengths Toolbox',
                'meta_description' => 'Develop effective supervision skills. Learn to lead, motivate, and manage your team successfully.',
                'meta_keywords' => 'supervision, management skills, team supervision, leadership training',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
        ];

        foreach ($pages as $pageData) {
            $page = Page::firstOrNew(['slug' => $pageData['slug']]);
            $page->fill($pageData);
            $page->save();
            $this->command->line("  ✓ Created: {$pageData['title']}");
        }
    }

    /**
     * Seed standalone pages
     */
    protected function seedStandalonePages(): void
    {
        $this->command->info('Seeding standalone pages...');

        // Note: Keynote Talks, Books, Testimonials, Privacy Statement
        // are handled by dedicated views, but we can create CMS versions if needed

        $pages = [
            [
                'title' => 'Keynote Talks',
                'slug' => 'keynote-talks',
                'excerpt' => 'Book Eberhard Niklaus for your next event. Engaging keynote talks on strengths-based development, team building, and business growth.',
                'content' => $this->getKeynoteTalksContent(),
                'meta_title' => 'Keynote Talks - Book Eberhard Niklaus - The Strengths Toolbox',
                'meta_description' => 'Book Eberhard Niklaus for your next event. Engaging keynote talks on strengths-based development and business growth.',
                'meta_keywords' => 'keynote speaker, business speaker, strengths-based development, team building speaker',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Books and Resources',
                'slug' => 'books',
                'excerpt' => 'Explore books and resources on strengths-based development, team building, and business growth.',
                'content' => $this->getBooksContent(),
                'meta_title' => 'Books and Resources - The Strengths Toolbox',
                'meta_description' => 'Explore books and resources on strengths-based development, team building, and business growth.',
                'meta_keywords' => 'business books, leadership books, team building resources, strengths-based development',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
        ];

        foreach ($pages as $pageData) {
            $page = Page::firstOrNew(['slug' => $pageData['slug']]);
            $page->fill($pageData);
            $page->save();
            $this->command->line("  ✓ Created: {$pageData['title']}");
        }
    }

    /**
     * Get homepage content
     */
    protected function getHomepageContent(): string
    {
        // Homepage uses dedicated view, return minimal content for CMS version
        return <<<'HTML'
<div class="prose prose-lg max-w-none">
    <p>Welcome to The Strengths Toolbox. Transform your business with strengths-based development. Build strong teams and unlock strong profits.</p>
    <p>Our proven approach helps teams achieve peak performance and drive sustainable growth through identifying and leveraging natural talents.</p>
</div>
HTML;
    }

    /**
     * Get Strengths Programme content
     */
    protected function getStrengthsProgrammeContent(): string
    {
        // Strengths Programme uses dedicated view, return minimal content for CMS version
        return <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Strengths Programme</h2>
    <p>Unlock growth through the Power of Strengths. Discover proven programs for individuals, managers, salespeople, and teams.</p>
    <p>Our comprehensive strengths-based development programs help you identify natural talents and leverage them for exceptional performance and sustainable business growth.</p>
</div>
HTML;
    }

    /**
     * Get About Us content
     */
    protected function getAboutUsContent(): string
    {
        // About Us uses dedicated view with partials, return minimal content for CMS version
        return <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>About Us</h2>
    <p>Eberhard Niklaus brings 30 years of experience in strengths-based development, team building, and business growth. With a passion for helping individuals and teams unlock their potential, Eberhard has worked with over 1560 clients across various industries to achieve exceptional results.</p>
    <p>At The Strengths Toolbox, we believe that everyone has unique natural talents that, when identified and developed, can lead to exceptional performance. Our approach focuses on identifying and understanding individual strengths, building on natural talents rather than fixing weaknesses, and creating strategies for effective application of strengths.</p>
    <p>Our mission is to help individuals and teams discover their strengths and learn how to leverage them for personal and professional success.</p>
</div>
HTML;
    }

    /**
     * Get content for "The Power of Strengths" page
     */
    protected function getPowerOfStrengthsContent(): string
    {
        return <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>The Power of Strengths</h2>
    <p>Strengths-based development is a transformative approach that focuses on identifying and leveraging natural talents rather than trying to fix weaknesses. When individuals and teams understand their unique strengths, they can achieve exceptional performance and drive sustainable business growth.</p>
    
    <p>Research from Gallup shows that individuals who focus on their strengths are <strong>three times more likely to report an excellent quality of life</strong> and <strong>six times more likely to be engaged in their jobs</strong>. Teams that concentrate on strengths daily experience a <strong>12.5% increase in productivity</strong>. These compelling statistics demonstrate the real power of a strengths-based approach.</p>
    
    <h3>What Are Strengths?</h3>
    <p>Strengths are natural patterns of thinking, feeling, and behaving that can be productively applied. They represent your innate talents—the things you do naturally and effortlessly. When you work in your strengths zone, you experience:</p>
    <ul>
        <li>Higher levels of engagement and satisfaction</li>
        <li>Increased productivity and performance</li>
        <li>Greater confidence and self-awareness</li>
        <li>Improved relationships and collaboration</li>
        <li>More energy and vitality</li>
        <li>Reduced stress and anxiety</li>
    </ul>
    
    <h3>The CliftonStrengths Assessment</h3>
    <p>Developed by Gallup, the CliftonStrengths assessment helps individuals identify their unique talents. Over <strong>12 million people</strong> have taken this assessment, leading to insights that the key to success lies in understanding and applying one's greatest talents in everyday life.</p>
    
    <p>The assessment identifies your top 34 talent themes, with your top 5 being your signature themes—the talents you use most naturally and frequently. Understanding these themes helps you:</p>
    <ul>
        <li>Recognize your natural patterns of thinking, feeling, and behaving</li>
        <li>Understand how you can contribute most effectively</li>
        <li>Develop strategies to apply your talents productively</li>
        <li>Build on your natural abilities to achieve excellence</li>
    </ul>
    
    <h3>The Strengths-Based Approach</h3>
    <p>Unlike traditional development approaches that focus on weaknesses, strengths-based development helps you:</p>
    <ul>
        <li>Identify your unique combination of strengths through proven assessment tools</li>
        <li>Understand how to apply strengths effectively in your work and life</li>
        <li>Build on natural talents to achieve excellence rather than mediocrity</li>
        <li>Create strategies to manage areas of lesser talent through partnerships and systems</li>
        <li>Develop confidence in your natural abilities</li>
        <li>Align your work and life with your strengths for greater fulfillment</li>
    </ul>
    
    <h3>Benefits for Individuals</h3>
    <p>When you understand and use your strengths:</p>
    <ul>
        <li>You perform better in your role and achieve your goals more effectively</li>
        <li>You experience greater job satisfaction and fulfillment</li>
        <li>You have more energy and engagement in your work</li>
        <li>You build stronger professional relationships</li>
        <li>You experience less stress and greater well-being</li>
        <li>You develop faster in areas where you're already strong</li>
    </ul>
    
    <h3>Benefits for Teams</h3>
    <p>Teams that leverage strengths:</p>
    <ul>
        <li>Collaborate more effectively by understanding each other's strengths</li>
        <li>Communicate better by recognizing different communication styles</li>
        <li>Reduce conflict and misunderstandings through strengths awareness</li>
        <li>Achieve higher performance levels with 12.5% productivity increases</li>
        <li>Build on each other's strengths rather than competing</li>
        <li>Create more innovative solutions through diverse strengths perspectives</li>
    </ul>
    
    <h3>Benefits for Organizations</h3>
    <p>Organizations that embrace strengths-based development:</p>
    <ul>
        <li>See increased employee engagement (employees are 6x more engaged)</li>
        <li>Experience reduced turnover and better retention</li>
        <li>Achieve better business results and profitability</li>
        <li>Build a culture of excellence and continuous improvement</li>
        <li>Attract and retain top talent</li>
        <li>Create competitive advantages through strengths-based strategies</li>
    </ul>
    
    <h3>The Research Behind Strengths</h3>
    <p>Gallup's decades of research have shown:</p>
    <ul>
        <li>People who use their strengths every day are 6 times more likely to be engaged at work</li>
        <li>Teams that focus on strengths daily see 12.5% productivity increases</li>
        <li>Organizations with strengths-based cultures see 19% higher sales and 29% increased profits</li>
        <li>High-turnover organizations implementing strengths see 72% lower turnover</li>
        <li>Individuals focusing on strengths are 3 times more likely to report excellent quality of life</li>
    </ul>
    
    <div class="mt-8 p-6 bg-primary-50 rounded-lg">
        <h3>Ready to Discover Your Strengths?</h3>
        <p>Contact us today to learn how strengths-based development can transform your team and drive business growth. Complete the CliftonStrengths assessment and unlock your potential.</p>
        <p class="mt-4"><strong>Phone:</strong> +27 83 294 8033 | <strong>Email:</strong> welcome@eberhardniklaus.co.za</p>
    </div>
</div>
HTML;
    }

    /**
     * Get content for Teams page
     */
    protected function getTeamsContent(): string
    {
        return <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Strengths-Based Development for Teams</h2>
    <p>Building a high-performing team requires more than just talented individuals. It requires understanding how each team member's unique strengths contribute to the collective success. Our strengths-based team development program helps teams achieve exceptional results through better collaboration, communication, and alignment.</p>
    
    <p>Teams that focus on strengths daily experience a 12.5% increase in productivity. When team members understand their own strengths and those of their colleagues, they can work together more effectively, reduce conflict, and achieve better outcomes.</p>
    
    <h3>How It Works</h3>
    <p>Our comprehensive team development process includes:</p>
    <ol>
        <li><strong>Individual Strengths Assessment:</strong> Each team member completes the CliftonStrengths assessment to identify their unique top talents and strengths.</li>
        <li><strong>Team Strengths Mapping:</strong> We map out the collective strengths of the team to identify patterns, gaps, and opportunities for collaboration.</li>
        <li><strong>Strengths-Based Team Building:</strong> Team members learn about each other's strengths and how to leverage complementary talents for better outcomes.</li>
        <li><strong>Collaboration Strategies:</strong> We develop specific strategies for how team members can work together more effectively by understanding and utilizing each other's strengths.</li>
        <li><strong>Ongoing Support and Coaching:</strong> We provide continuous support to help teams maintain momentum, resolve conflicts through strengths awareness, and achieve lasting results.</li>
    </ol>
    
    <h3>Key Benefits for Teams</h3>
    <ul>
        <li><strong>Improved Collaboration:</strong> Team members understand how to work together by leveraging complementary strengths</li>
        <li><strong>Reduced Conflict:</strong> Understanding different strengths reduces misunderstandings and conflicts</li>
        <li><strong>Increased Productivity:</strong> Teams focusing on strengths daily see 12.5% productivity increases</li>
        <li><strong>Better Communication:</strong> Strengths awareness improves how team members communicate and interact</li>
        <li><strong>Higher Engagement:</strong> Team members are more engaged when they can use their strengths</li>
        <li><strong>Optimized Team Abilities:</strong> Teams learn to rely on each other's strengths, promoting collaboration and better outcomes</li>
        <li><strong>Better Alignment:</strong> Teams align their work with organizational goals through strengths-based planning</li>
    </ul>
    
    <h3>Who Benefits</h3>
    <p>This program is ideal for:</p>
    <ul>
        <li>Teams experiencing communication challenges or conflicts</li>
        <li>New teams forming and needing to establish effective working relationships</li>
        <li>Teams undergoing restructuring or reorganization</li>
        <li>High-performing teams looking to optimize further and reach new levels of excellence</li>
        <li>Cross-functional teams that need to collaborate effectively</li>
        <li>Remote or distributed teams that need stronger connections</li>
    </ul>
    
    <h3>Program Outcomes</h3>
    <p>Teams that complete our strengths-based development program report:</p>
    <ul>
        <li>Significantly improved team collaboration and communication</li>
        <li>Reduced conflict and misunderstandings</li>
        <li>Increased team productivity and performance metrics</li>
        <li>Better alignment with organizational goals and strategies</li>
        <li>Higher levels of team engagement and satisfaction</li>
        <li>Improved ability to leverage team diversity</li>
    </ul>
    
    <div class="mt-8 p-6 bg-primary-50 rounded-lg">
        <h3>Ready to Transform Your Team?</h3>
        <p>Contact us today to learn how strengths-based team development can help your team achieve exceptional results through better collaboration and understanding.</p>
        <p class="mt-4"><strong>Phone:</strong> +27 83 294 8033 | <strong>Email:</strong> welcome@eberhardniklaus.co.za</p>
    </div>
</div>
HTML;
    }

    /**
     * Get content for Managers/Leaders page
     */
    protected function getManagersLeadersContent(): string
    {
        return <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Strengths-Based Development for Managers and Leaders</h2>
    <p>Effective leadership begins with understanding your own strengths and learning how to leverage them to inspire and guide others. Our strengths-based leadership development program helps managers and leaders build authentic leadership styles that drive team performance and organizational success.</p>
    
    <p>Managers who play to their own strengths establish a unique management style, creating an environment conducive to employee growth. When leaders understand and utilize their strengths, they can develop management approaches that feel natural and authentic, leading to better outcomes for their teams and organizations.</p>
    
    <h3>Key Benefits for Managers and Leaders</h3>
    <ul>
        <li><strong>Enhanced Management Abilities:</strong> By understanding and utilizing their own strengths, managers develop a unique management style that fosters an environment where employees can grow and thrive.</li>
        <li><strong>Improved Talent Management:</strong> A strengths-based approach helps employees identify, cultivate, and utilize their strengths at work, enabling them to perform at their best and assisting leaders in effective task delegation.</li>
        <li><strong>Increased Engagement and Performance:</strong> When strengths concepts are consistently communicated, employees are more likely to use their strengths, and leaders can align business strategies with the organization's competitive advantages.</li>
        <li><strong>Optimized Team Abilities:</strong> Strengths-based leadership encourages teams to rely on each other's strengths, promoting collaboration and better outcomes.</li>
        <li><strong>Career Development Support:</strong> This management style allows employees to align their career paths with their abilities and interests. Leaders can work individually with employees to uncover and develop their strengths.</li>
    </ul>
    
    <h3>Leadership Development Focus Areas</h3>
    <ul>
        <li><strong>Authentic Leadership:</strong> Discover your natural leadership style through CliftonStrengths assessment and learn to lead authentically using your unique combination of talents.</li>
        <li><strong>Team Building:</strong> Learn to build and develop high-performing teams by understanding team members' strengths and how to leverage them effectively.</li>
        <li><strong>Communication:</strong> Develop communication strategies that resonate with different team members based on their individual strengths and preferences.</li>
        <li><strong>Decision Making:</strong> Leverage your strengths for better decision-making and learn how different strengths contribute to effective leadership decisions.</li>
        <li><strong>Conflict Resolution:</strong> Use strengths-based approaches to understand and resolve team conflicts by recognizing how different strengths perspectives contribute to disagreements.</li>
        <li><strong>Talent Development:</strong> Learn to identify, develop, and retain talent by focusing on employees' natural strengths rather than trying to fix weaknesses.</li>
    </ul>
    
    <h3>Program Components</h3>
    <p>Our program includes:</p>
    <ul>
        <li><strong>CliftonStrengths for Managers Report:</strong> Personalized insights into how your individual talents can contribute to personal, team, and organizational success.</li>
        <li><strong>CliftonStrengths for Leaders Report:</strong> Advanced insights for senior leaders on leveraging strengths for strategic leadership.</li>
        <li><strong>One-on-One Coaching:</strong> Individual coaching sessions to help you develop your unique leadership style based on your strengths.</li>
        <li><strong>Team Development Workshops:</strong> Hands-on workshops to help you apply strengths-based principles with your team.</li>
        <li><strong>Ongoing Support:</strong> Continuous support and resources to help you maintain and develop your strengths-based leadership approach.</li>
    </ul>
    
    <h3>Program Benefits</h3>
    <ul>
        <li>Develop a leadership style that feels natural and authentic</li>
        <li>Build stronger relationships with team members through strengths awareness</li>
        <li>Make better hiring and team composition decisions</li>
        <li>Create more engaged and motivated teams</li>
        <li>Achieve better business results through effective leadership</li>
        <li>Reduce employee turnover by focusing on strengths development</li>
        <li>Improve team performance and productivity</li>
    </ul>
    
    <h3>Who Should Attend</h3>
    <p>This program is designed for:</p>
    <ul>
        <li>New managers transitioning into leadership roles</li>
        <li>Experienced leaders looking to enhance their effectiveness</li>
        <li>Managers facing team challenges or low engagement</li>
        <li>Leaders preparing for increased responsibility</li>
        <li>Senior executives wanting to build a strengths-based culture</li>
        <li>HR professionals responsible for leadership development</li>
    </ul>
    
    <div class="mt-8 p-6 bg-primary-50 rounded-lg">
        <h3>Ready to Develop Your Leadership Strengths?</h3>
        <p>Contact us today to learn how strengths-based leadership development can help you become a more effective and authentic leader.</p>
        <p class="mt-4"><strong>Phone:</strong> +27 83 294 8033 | <strong>Email:</strong> welcome@eberhardniklaus.co.za</p>
    </div>
</div>
HTML;
    }

    /**
     * Get content for Salespeople page
     */
    protected function getSalespeopleContent(): string
    {
        return <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Strengths-Based Development for Salespeople</h2>
    <p>Every salesperson has unique natural talents that can be leveraged for sales success. Our strengths-based sales development program helps salespeople identify their selling strengths and develop personalized approaches that lead to more closed deals and greater job satisfaction.</p>
    
    <p>Organizations that implement strengths-based development report significant improvements in sales performance. Teams receiving such development have achieved <strong>19% higher sales, 29% increased profits, and 72% lower turnover</strong> in high-turnover organizations. By focusing on their natural strengths, salespeople can enhance their effectiveness throughout the entire sales process.</p>
    
    <h3>Discover Your Selling Strengths</h3>
    <p>Through our comprehensive CliftonStrengths assessment and development process, you'll discover:</p>
    <ul>
        <li>Your natural selling style and approach based on your unique combination of strengths</li>
        <li>How to leverage your strengths at each stage of the sales process</li>
        <li>Strategies for managing areas of lesser talent by partnering with others or developing systems</li>
        <li>Ways to build on your natural selling abilities for even greater success</li>
        <li>How your strengths influence your customer relationships and closing techniques</li>
    </ul>
    
    <h3>Sales Development Focus Areas</h3>
    <ul>
        <li><strong>Prospecting:</strong> Use your strengths to identify and connect with prospects in ways that feel natural and authentic to you</li>
        <li><strong>Relationship Building:</strong> Leverage your natural talents to build strong, lasting client relationships based on trust and understanding</li>
        <li><strong>Needs Assessment:</strong> Apply your strengths to better understand customer needs and challenges</li>
        <li><strong>Presentation:</strong> Develop presentation styles that align with your strengths and resonate with different customer types</li>
        <li><strong>Handling Objections:</strong> Use your strengths to address customer concerns and objections effectively</li>
        <li><strong>Closing:</strong> Leverage your natural talents to close deals more effectively and confidently</li>
        <li><strong>Account Management:</strong> Build long-term client relationships using your strengths for ongoing success</li>
    </ul>
    
    <h3>Key Benefits</h3>
    <ul>
        <li><strong>Increased Sales Performance:</strong> Organizations report up to 19% increase in overall sales when implementing strengths-based development</li>
        <li><strong>Enhanced Engagement:</strong> Salespeople are more engaged when they can use their natural strengths, leading to better performance outcomes</li>
        <li><strong>Improved Retention:</strong> Strengths-based development helps individuals grow, reducing turnover and increasing job satisfaction</li>
        <li><strong>Better Customer Relationships:</strong> Salespeople utilizing their innate talents build better customer relationships and close more deals</li>
        <li><strong>Personalized Selling Approach:</strong> Develop a selling style that feels natural and authentic, leading to greater confidence and success</li>
    </ul>
    
    <h3>Expected Results</h3>
    <p>Salespeople who complete our strengths-based development program typically experience:</p>
    <ul>
        <li>Increased sales conversion rates and closed deals</li>
        <li>Higher average deal values</li>
        <li>Improved client relationships and customer satisfaction</li>
        <li>Greater job satisfaction and engagement</li>
        <li>Reduced sales cycle times</li>
        <li>Increased confidence in selling situations</li>
        <li>Better work-life balance through more efficient selling approaches</li>
    </ul>
    
    <h3>Program Components</h3>
    <ul>
        <li>Individual CliftonStrengths assessment to identify your top selling strengths</li>
        <li>Personalized coaching to help you leverage your strengths in sales situations</li>
        <li>Strengths-based sales techniques tailored to your natural talents</li>
        <li>Workshops on applying strengths throughout the sales process</li>
        <li>Ongoing support and resources for continued development</li>
    </ul>
    
    <div class="mt-8 p-6 bg-primary-50 rounded-lg">
        <h3>Ready to Unlock Your Sales Potential?</h3>
        <p>Contact us today to learn how strengths-based sales development can help you achieve greater sales success by leveraging your natural talents.</p>
        <p class="mt-4"><strong>Phone:</strong> +27 83 294 8033 | <strong>Email:</strong> welcome@eberhardniklaus.co.za</p>
    </div>
</div>
HTML;
    }

    /**
     * Get content for Individuals page
     */
    protected function getIndividualsContent(): string
    {
        return <<<'HTML'
<div class="prose prose-lg max-w-none">
    <p>Everyone has a specific set of natural talents and abilities. When we intentionally apply our talents, they can become our greatest source of fulfillment — we become more confident, happy, energetic, and likely to achieve our goals.</p>
    
    <p>Because our talents come so naturally to us, and because we often take them for granted, many times we are not fully aware of our strengths and what we can contribute.</p>
    
    <p>You are stronger than you know. Discover and maximize your most powerful natural talents with CliftonStrengths. Complete the CliftonStrengths talent assessment and unlock personalized results and resources for you to fully understand your talents and maximize your potential.</p>
    
    <p>But the CliftonStrengths assessment is just the start. The real improvement happens when coaches help people develop — not just discover — their talents.</p>
    
    <h2>STRENGTHS-BASED COACHING HELPS YOU:</h2>
    <ul>
        <li>Understand your talents.</li>
        <li>Use your talents to produce results and reach your goals.</li>
        <li>Use your talents to overcome obstacles, weakness, and vulnerabilities.</li>
        <li>Use your talents to transform relationships.</li>
        <li>Understand your unique strengths in the context of others.</li>
    </ul>
    
    <div class="mt-8 p-6 bg-primary-50 rounded-lg">
        <p class="text-xl font-semibold mb-4">CONTACT US TODAY FOR A FREE 30 MINUTE ONLINE CONSULTATION TO HELP YOU UNDERSTAND HOW TO UNLOCK YOUR PERSONAL POTENTIAL AND POWER</p>
    </div>
    
    <p>A strengths approach is unique and powerful. Gallup's research shows that the key to success is to fully understand how to apply your greatest talents and strengths in your everyday life. When you discover your greatest talents, you'll discover your greatest opportunities for excellence, success, and contribution.</p>
    
    <h2>IMAGINE THESE POSSIBILITIES FOR YOURSELF:</h2>
    
    <ol>
        <li><strong>Have higher levels of <em>energy and vitality.</em></strong><br>
        Decades of research have shown that when people are ready to use their strengths, they have higher levels of psychological vitality. When people use their strengths, they experience positive energy and buzz. The more hours per day adults believe they use their strengths, the more likely they are to report having ample energy, feeling rested, being happy, smiling or laughing a lot, and being treated with respect.</li>
        
        <li><strong>Are more likely to <em>achieve their goals.</em></strong><br>
        People who intentionally use their strengths to accomplish their goals are far more likely to achieve them. When they achieve their goals, they satisfy their psychological needs and are happier and more fulfilled as a result.</li>
        
        <li><strong>Are more <em>confident</em></strong><br>
        Recent research found that people who use their strengths report higher levels of self-efficacy and believe they are capable of achieving the things they want to achieve.</li>
        
        <li><em>Perform</em> <strong>better at work.</strong><br>
        When managers emphasize strengths, performance is significantly higher; conversely, when managers emphasize weaknesses, performance declines.</li>
        
        <li><strong>Experience</strong> <em>less stress.</em><br>
        People who use their strengths are less likely to report experiencing worry, stress, anger, or sadness.</li>
        
        <li><strong>Are</strong> <em>more engaged</em> <strong>at work.</strong><br>
        The opportunity to do what you do best each day – that is, use your strengths – is a core predicator of workplace engagement.</li>
        
        <li>Are more effective at developing themselves and growing as an individual.<br>
        When focussing on self-development, people improve faster in areas where they are already strong, compared with areas where they are weak. The more hours per day adults believe they use their strengths, the more likely they are to report having learned something interesting.</li>
    </ol>
    
    <p>A person's greatest talents – the ways in which he or she most naturally thinks, feels, and behaves – represents the person's innate power and potential.</p>
    
    <div class="mt-8 p-6 bg-gray-50 rounded-lg">
        <p class="font-semibold mb-2">Click Here to Download The Power of Strengths Document</p>
    </div>
    
    <h2>Testimonials</h2>
    
    <div class="mt-6 p-6 bg-white border-l-4 border-primary-500">
        <p class="italic mb-4">"I hereby confirm that I underwent 6 coaching sessions with Eberhard. His coaching style was very informative, relaxing and inspirational."</p>
        <p class="italic mb-4">"His coaching helped to uncover and be aware of my talents and strengths. This has given me very useful guidance on my decision making, including making the team selections."</p>
        <p class="italic mb-4">"I sincerely recommend him for Executive Coaching as his coaching gave me confidence and guidance that is rare to find."</p>
        <p class="font-semibold mt-4">Xolani Matthews</p>
        <p class="text-gray-600">Public Investment Corporation</p>
    </div>
    
    <div class="mt-6 p-6 bg-white border-l-4 border-primary-500">
        <p class="italic mb-4">"I received the Gallup executive coaching in 2017 and it was a worthwhile exercise. My coach was Mr Eberhard Niklaus and he did an excellent job in helping me to understand my strengths and talents."</p>
        <p class="italic mb-4">"I found Eberhard to be passionate and insightful which made the coaching exercise enjoyable. I am as a result able apply the principles learnt in my work and home environment. Eberhard's style of coaching was inspiring, relaxed as well as interactive."</p>
        <p class="italic mb-4">"Eberhard is highly recommended as a coach especially if the desired objective is to have fulfilling growth both at work and at home."</p>
        <p class="font-semibold mt-4">Miss ZA Fakude</p>
        <p class="text-gray-600">Gauteng Growth and Development Agency</p>
    </div>
</div>
HTML;
    }

    /**
     * Get content for sales training pages
     */
    protected function getSalesTrainingContent(string $type): string
    {
        $content = [
            'strengths-based' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Strengths-Based Sales Training</h2>
    <p>Transform your sales team with training that leverages each salesperson's natural selling strengths. Our strengths-based approach helps sales teams achieve better results by working with their natural talents rather than against them.</p>
    <p>Organizations implementing strengths-based sales training report up to 19% increase in sales and 29% increased profits. By focusing on each salesperson's natural strengths, teams can achieve exceptional results.</p>
    <h3>Program Overview</h3>
    <p>This comprehensive program combines CliftonStrengths assessment with proven sales methodologies to create personalized development paths for each salesperson. Each participant discovers their unique selling strengths and learns how to leverage them throughout the sales process.</p>
    <h3>Key Components</h3>
    <ul>
        <li>Individual CliftonStrengths assessment for each salesperson to identify top selling talents</li>
        <li>Personalized sales development plans based on natural strengths</li>
        <li>Strengths-based sales techniques tailored to each person's selling style</li>
        <li>Team collaboration strategies to leverage complementary strengths</li>
        <li>Ongoing coaching and support for continued development</li>
        <li>Workshops on applying strengths at each stage of the sales process</li>
    </ul>
    <h3>Expected Results</h3>
    <ul>
        <li>Increased sales conversion rates and closed deals</li>
        <li>Higher average deal values</li>
        <li>Improved customer relationships</li>
        <li>Greater sales team engagement and retention</li>
        <li>Reduced sales cycle times</li>
    </ul>
</div>
HTML,
            'relationship' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Relationship Selling Training</h2>
    <p>Master the art of relationship selling to build lasting client relationships and increase sales success. This program focuses on building trust, understanding client needs, and creating long-term partnerships.</p>
    <h3>Training Focus</h3>
    <ul>
        <li>Building trust and rapport with clients</li>
        <li>Understanding client needs and challenges</li>
        <li>Creating value-based relationships</li>
        <li>Long-term account management</li>
        <li>Referral generation strategies</li>
    </ul>
</div>
HTML,
            'phone' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Selling On The Phone</h2>
    <p>Master phone sales techniques to close more deals and build stronger client connections, even without face-to-face meetings. This program teaches proven strategies for effective telephone selling.</p>
    <h3>Key Skills Covered</h3>
    <ul>
        <li>Building rapport over the phone</li>
        <li>Effective phone communication techniques</li>
        <li>Handling objections on the phone</li>
        <li>Closing deals via telephone</li>
        <li>Follow-up strategies</li>
    </ul>
</div>
HTML,
            'fundamentals' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Sales Fundamentals Workshop</h2>
    <p>Master the fundamentals of sales with our comprehensive workshop covering essential selling skills. Perfect for new salespeople or those looking to refresh their core sales knowledge.</p>
    <h3>Workshop Topics</h3>
    <ul>
        <li>The sales process and pipeline</li>
        <li>Prospecting and lead generation</li>
        <li>Qualifying prospects</li>
        <li>Needs assessment</li>
        <li>Presentation skills</li>
        <li>Handling objections</li>
        <li>Closing techniques</li>
        <li>Follow-up and account management</li>
    </ul>
</div>
HTML,
            'secrets' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Top 10 Sales Secrets</h2>
    <p>Discover the top 10 proven sales secrets that successful salespeople use to close more deals and build lasting client relationships.</p>
    <h3>The Secrets</h3>
    <ol>
        <li>Listen more than you talk</li>
        <li>Focus on solving problems, not selling products</li>
        <li>Build genuine relationships</li>
        <li>Understand your client's business</li>
        <li>Follow up consistently</li>
        <li>Ask the right questions</li>
        <li>Handle objections with confidence</li>
        <li>Create urgency when appropriate</li>
        <li>Always add value</li>
        <li>Never stop learning</li>
    </ol>
</div>
HTML,
            'in-person' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>In-Person Sales Training</h2>
    <p>Excel at face-to-face sales with proven techniques for in-person selling and client meetings. This program covers everything from initial meetings to closing deals in person.</p>
    <h3>Training Components</h3>
    <ul>
        <li>Making great first impressions</li>
        <li>Reading body language and non-verbal cues</li>
        <li>Effective presentation skills</li>
        <li>Building rapport in person</li>
        <li>Handling in-person objections</li>
        <li>Closing techniques for face-to-face meetings</li>
    </ul>
</div>
HTML,
        ];

        return $content[$type] ?? '<p>Content coming soon...</p>';
    }

    /**
     * Get content for workshop pages
     */
    protected function getWorkshopContent(string $type): string
    {
        $content = [
            'customer-service' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Customer Service Workshop</h2>
    <p>Enhance customer service skills with our comprehensive workshop focused on delivering exceptional customer experiences that build loyalty and drive business growth.</p>
    <h3>Workshop Topics</h3>
    <ul>
        <li>Understanding customer needs and expectations</li>
        <li>Effective communication with customers</li>
        <li>Handling difficult customers and situations</li>
        <li>Building customer relationships</li>
        <li>Creating memorable customer experiences</li>
    </ul>
</div>
HTML,
            'emotional-intelligence' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Emotional Intelligence Workshop</h2>
    <p>Develop emotional intelligence skills to improve relationships, communication, and leadership effectiveness. Learn to understand and manage emotions for better personal and professional outcomes.</p>
    <h3>Key Areas Covered</h3>
    <ul>
        <li>Self-awareness and self-regulation</li>
        <li>Empathy and social awareness</li>
        <li>Relationship management</li>
        <li>Emotional intelligence in leadership</li>
        <li>Practical application strategies</li>
    </ul>
</div>
HTML,
            'goal-setting' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Goal Setting and Getting Things Done</h2>
    <p>Master goal setting and execution strategies to achieve your objectives and drive results. Learn proven frameworks for setting meaningful goals and following through to completion.</p>
    <h3>Workshop Focus</h3>
    <ul>
        <li>Setting SMART and meaningful goals</li>
        <li>Creating action plans</li>
        <li>Overcoming obstacles and staying motivated</li>
        <li>Time management and prioritization</li>
        <li>Accountability and follow-through</li>
    </ul>
</div>
HTML,
            'high-performance-teams' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>High-Performance Teams Workshop</h2>
    <p>Build and lead high-performance teams that consistently deliver exceptional results. Learn the principles and practices that drive team excellence.</p>
    <h3>Key Topics</h3>
    <ul>
        <li>Characteristics of high-performance teams</li>
        <li>Team formation and development</li>
        <li>Effective team communication</li>
        <li>Conflict resolution in teams</li>
        <li>Sustaining high performance</li>
    </ul>
</div>
HTML,
            'interpersonal-skills' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Interpersonal Skills Workshop</h2>
    <p>Develop strong interpersonal skills to build better relationships and improve workplace communication. Enhance your ability to work effectively with others.</p>
    <h3>Skills Developed</h3>
    <ul>
        <li>Active listening</li>
        <li>Effective communication</li>
        <li>Building rapport</li>
        <li>Conflict resolution</li>
        <li>Collaboration and teamwork</li>
    </ul>
</div>
HTML,
            'personal-finances' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Managing Personal Finances Workshop</h2>
    <p>Learn practical financial management skills to take control of your personal finances and build wealth. This workshop provides actionable strategies for financial success.</p>
    <h3>Topics Covered</h3>
    <ul>
        <li>Budgeting and expense management</li>
        <li>Saving and investment strategies</li>
        <li>Debt management</li>
        <li>Financial planning</li>
        <li>Building long-term wealth</li>
    </ul>
</div>
HTML,
            'presentation-skills' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Presentation Skills Workshop</h2>
    <p>Master presentation skills to deliver compelling presentations that engage and persuade your audience. Learn to communicate your message effectively and confidently.</p>
    <h3>Workshop Components</h3>
    <ul>
        <li>Structuring effective presentations</li>
        <li>Engaging your audience</li>
        <li>Using visual aids effectively</li>
        <li>Handling questions and objections</li>
        <li>Building confidence as a presenter</li>
    </ul>
</div>
HTML,
            'supervising-others' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Supervising Others Workshop</h2>
    <p>Develop effective supervision skills to lead, motivate, and manage your team successfully. Learn the fundamentals of effective supervision and team management.</p>
    <h3>Key Areas</h3>
    <ul>
        <li>Supervision fundamentals</li>
        <li>Delegation and task management</li>
        <li>Performance management</li>
        <li>Motivating team members</li>
        <li>Handling difficult situations</li>
    </ul>
</div>
HTML,
        ];

        return $content[$type] ?? '<p>Content coming soon...</p>';
    }

    /**
     * Get content for Keynote Talks page
     */
    protected function getKeynoteTalksContent(): string
    {
        return <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Keynote Talks</h2>
    <p>Eberhard Niklaus brings decades of experience and real-world insights to your events. His keynote talks combine practical wisdom with engaging storytelling to inspire audiences and drive action.</p>
    
    <h3>Popular Keynote Topics</h3>
    <ul>
        <li><strong>The Power of Strengths:</strong> Discover how identifying and leveraging natural talents transforms individual and team performance.</li>
        <li><strong>Building High-Performance Teams:</strong> Learn the frameworks for creating cohesive teams that achieve exceptional results.</li>
        <li><strong>Strengths-Based Leadership:</strong> Explore how authentic leadership emerges when leaders understand and use their strengths.</li>
        <li><strong>Driving Business Growth:</strong> Understand how strengths-based development creates sustainable competitive advantage.</li>
    </ul>
    
    <h3>Customized Presentations</h3>
    <p>All keynote talks can be customized to fit your audience, industry, and event objectives. Contact us to discuss your specific needs and how we can create a presentation that resonates with your audience.</p>
    
    <div class="mt-8">
        <a href="/contact?source=keynote-talks" class="btn btn-primary">Book a Keynote Talk</a>
    </div>
</div>
HTML;
    }

    /**
     * Get content for Books page
     */
    protected function getBooksContent(): string
    {
        return <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Books and Resources</h2>
    <p>Expand your knowledge with our recommended reading and resources on strengths-based development, team building, and business growth.</p>
    
    <h3>Free Resources</h3>
    <p>Download our free eBook on sales strategies and strengths-based selling. Learn practical techniques that you can apply immediately to improve your sales performance.</p>
    
    <div class="mt-8">
        <a href="/#ebook-signup" class="btn btn-primary">Download Free eBook</a>
    </div>
    
    <h3>Recommended Reading</h3>
    <p>Coming soon: A curated list of books on strengths-based development, leadership, team building, and business growth.</p>
</div>
HTML;
    }
}
