<?php

namespace Database\Seeders;

use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Comprehensive content migration seeder
 * Populates CMS with all pages from existing website.
 *
 * Sales Courses images are stored locally at storage/app/public/sales-courses/.
 * Run: php artisan content:download-sales-courses-images
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
        $this->seedSalesCoursesPages();
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
            'meta_title' => 'The Strengths Toolbox | Strong Teams, Strong Profits',
            'meta_description' => 'Transform your business with strengths-based development. The Strengths Toolbox helps teams achieve peak performance and drive sustainable growth. Learn more.',
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
            'meta_title' => 'Strengths Programme | The Strengths Toolbox',
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
                'meta_title' => 'Strengths-Based Team Development | The Strengths Toolbox',
                'meta_description' => 'Transform your team with strengths-based development. Build stronger collaboration, improve team performance, and align roles with natural talents.',
                'meta_keywords' => 'team building, team development, strengths-based teams, team collaboration',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Strengths-Based Development for Managers and Leaders',
                'slug' => 'strengths-based-development/managers-leaders',
                'excerpt' => 'Develop authentic leadership styles and build high-performing teams through strengths-based management.',
                'content' => $this->getManagersLeadersContent(),
                'meta_title' => 'Strengths-Based Leadership | The Strengths Toolbox',
                'meta_description' => 'Develop your leadership skills with strengths-based management. Lead with authenticity, build high-performing teams, and develop talent effectively.',
                'meta_keywords' => 'leadership development, management training, strengths-based leadership, team management',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Strengths-Based Development for Salespeople',
                'slug' => 'strengths-based-development/salespeople',
                'excerpt' => 'Transform sales performance by understanding and leveraging your natural selling strengths.',
                'content' => $this->getSalespeopleContent(),
                'meta_title' => 'Strengths-Based Sales Development | The Strengths Toolbox',
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
                'meta_title' => 'Individual Strengths Development | The Strengths Toolbox',
                'meta_description' => 'Discover your unique strengths and unlock your potential. Personal development through a strengths-based approach with CliftonStrengths.',
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
     * Seed Sales Courses pages (parent + 4 courses from TSA sales-courses content)
     */
    protected function seedSalesCoursesPages(): void
    {
        $this->command->info('Seeding Sales Courses pages...');

        $bookingUrl = '/booking';

        $parentPage = Page::firstOrNew(['slug' => 'sales-courses']);
        $parentPage->fill([
            'title' => 'Sales Courses',
            'slug' => 'sales-courses',
            'excerpt' => 'At The Strengths Toolbox, we\'ve designed 4 powerful sales courses to transform the way you and your team sell. Whether you\'re just starting out or ready to master advanced strategies.',
            'content' => $this->getSalesCoursesContent('parent', $bookingUrl),
            'meta_title' => 'Sales Courses - The Strengths Toolbox',
            'meta_description' => 'Transform the way you and your team sell. Sales courses from fundamentals to advanced techniques, relationship selling, mindset, and selling on the phone.',
            'meta_keywords' => 'sales courses, sales training, sales workshops, relationship selling, South Africa',
            'is_published' => true,
            'published_at' => Carbon::now(),
        ]);
        $parentPage->save();
        $this->command->line('  ✓ Created: Sales Courses (parent page)');

        $pages = [
            [
                'title' => 'Sales Fundamentals & Advanced Techniques',
                'slug' => 'sales-courses/sales-fundamentals-and-advanced-techniques',
                'excerpt' => 'Combine essential sales fundamentals with advanced strategies. Goal setting, prospecting, presenting, handling objections, closing, negotiation, and self-motivation.',
                'content' => $this->getSalesCoursesContent('fundamentals-advanced', $bookingUrl),
                'meta_title' => 'Sales Fundamentals & Advanced | The Strengths Toolbox',
                'meta_description' => 'Master sales from fundamentals to advanced. Essential and advanced sales skills in one comprehensive course. Book your seat today.',
                'meta_keywords' => 'sales fundamentals, advanced sales techniques, sales workshop, prospecting, closing',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Mastering Relationship Selling Workshop',
                'slug' => 'sales-courses/mastering-relationship-selling',
                'excerpt' => 'Develop lasting customer relationships that generate repeat business and referrals. Trust building and authentic connection.',
                'content' => $this->getSalesCoursesContent('relationship-selling', $bookingUrl),
                'meta_title' => 'Mastering Relationship Selling | The Strengths Toolbox',
                'meta_description' => 'Build lasting customer relationships through trust, authentic connection, and long-term value creation. Workshop for sales teams.',
                'meta_keywords' => 'relationship selling, customer relationships, trust building, sales workshop',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'The Mindset of a Super Salesperson',
                'slug' => 'sales-courses/mindset-of-a-super-salesperson',
                'excerpt' => 'Emotional intelligence, positive psychological traits, and self-motivation. Turn setbacks into stepping stones and create a positive mindset.',
                'content' => $this->getSalesCoursesContent('mindset-super-salesperson', $bookingUrl),
                'meta_title' => 'The Mindset of a Super Salesperson - The Strengths Toolbox',
                'meta_description' => 'Develop the mindset of a super salesperson. Emotional intelligence, self-motivation, and positive psychology for sales success.',
                'meta_keywords' => 'sales mindset, emotional intelligence, self-motivation, sales psychology',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Selling On The Phone',
                'slug' => 'sales-courses/selling-on-the-phone',
                'excerpt' => 'Master the soft skills and technical selling skills to improve your effectiveness when selling on the phone.',
                'content' => $this->getSalesCoursesContent('selling-on-phone', $bookingUrl),
                'meta_title' => 'Selling On The Phone - The Strengths Toolbox',
                'meta_description' => 'Improve your phone sales results with soft skills and technical selling skills for telephone selling. Practical workshop for sales teams.',
                'meta_keywords' => 'phone sales, telephone selling, sales calls, closing on the phone',
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
                'meta_description' => 'Enhance customer service skills with our comprehensive workshop. Deliver exceptional customer experiences that build loyalty and drive growth.',
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
                'meta_description' => 'Develop emotional intelligence skills to improve relationships, communication, and leadership effectiveness in the workplace.',
                'meta_keywords' => 'emotional intelligence, EQ training, emotional intelligence workshop, self-awareness',
                'is_published' => true,
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Goal Setting and Getting Things Done',
                'slug' => 'facilitation/goal-setting',
                'excerpt' => 'Master goal setting and execution strategies to achieve your objectives and drive results.',
                'content' => $this->getWorkshopContent('goal-setting'),
                'meta_title' => 'Goal Setting Workshop | The Strengths Toolbox',
                'meta_description' => 'Master goal setting and execution strategies to achieve your objectives and drive results. Learn proven frameworks for getting things done.',
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
                'meta_description' => 'Build and lead high-performance teams that consistently deliver exceptional results. Learn strategies for team excellence and sustained performance.',
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
                'meta_description' => 'Develop strong interpersonal skills to build better relationships and improve workplace communication and collaboration.',
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
                'meta_description' => 'Learn practical financial management skills to take control of your personal finances, build wealth, and plan for the future.',
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
                'meta_description' => 'Master presentation skills to deliver compelling presentations that engage and persuade your audience. Practical training for professionals.',
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
                'meta_description' => 'Develop effective supervision skills to lead, motivate, and manage your team successfully. Practical training for new and experienced supervisors.',
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
                'meta_title' => 'Keynote Talks | Eberhard Niklaus - The Strengths Toolbox',
                'meta_description' => 'Book Eberhard Niklaus for your next event. Engaging keynote talks on strengths-based development, team building, and business growth.',
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
                'meta_description' => 'Explore books and resources on strengths-based development, team building, and business growth. Free eBook and practical guides.',
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
    <h1>Build Strong Teams, Unlock Strong Profits</h1>
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
    <h1>Strengths Programme</h1>
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
    <h1>About Us</h1>
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
    <h1>The Power of Strengths</h1>
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
    <h1>Strengths-Based Development for Teams</h1>
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
    <h1>Strengths-Based Development for Managers and Leaders</h1>
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
    <h1>Strengths-Based Development for Salespeople</h1>
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
    <h1>Individual Strengths Development</h1>
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
     * Get content for Sales Courses pages (from TSA sales-courses; brand replaced)
     */
    protected function getSalesCoursesContent(string $type, string $bookingUrl): string
    {
        $img = '/storage/sales-courses';
        if ($type === 'parent') {
            return '<div class="prose prose-lg max-w-none">
<figure class="my-6"><img src="'.$img.'/stacking-wooden-blocks.jpg" alt="Sales courses – strong teams, strong profits" class="rounded-lg w-full max-w-2xl mx-auto" /></figure>
<h1>Strong Teams. Strong Profits.</h1>
<p>At The Strengths Toolbox, we\'ve designed 4 powerful sales courses to transform the way you and your team sell. Whether you\'re just starting out or ready to master advanced strategies, these courses will help you sell with confidence, close more deals, and grow your profits.</p>
<p><a href="'.$bookingUrl.'" class="btn btn-primary">Book your seat today</a></p>
<h3>Sales Training Details</h3>
<ul>
<li><strong>Pricing:</strong> R1,790.00 (20% discount on groups of 3+)</li>
<li><strong>Format &amp; Duration:</strong> Full-day intensive (08:30 – 16:00)</li>
<li><strong>Interactive Experience:</strong> Includes role-playing, real-world case studies, and personalized feedback.</li>
</ul>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
<a href="/sales-courses/sales-fundamentals-and-advanced-techniques" class="block p-4 border rounded-lg hover:bg-gray-50"><h3 class="font-semibold">Sales Fundamentals &amp; Advanced Techniques</h3><p class="text-sm text-gray-600">Essential and advanced sales skills in one course</p></a>
<a href="/sales-courses/mastering-relationship-selling" class="block p-4 border rounded-lg hover:bg-gray-50"><h3 class="font-semibold">Mastering Relationship Selling Workshop</h3><p class="text-sm text-gray-600">Build lasting customer relationships</p></a>
<a href="/sales-courses/mindset-of-a-super-salesperson" class="block p-4 border rounded-lg hover:bg-gray-50"><h3 class="font-semibold">The Mindset of a Super Salesperson</h3><p class="text-sm text-gray-600">Emotional intelligence and self-motivation</p></a>
<a href="/sales-courses/selling-on-the-phone" class="block p-4 border rounded-lg hover:bg-gray-50"><h3 class="font-semibold">Selling On The Phone</h3><p class="text-sm text-gray-600">Master phone sales techniques</p></a>
</div>
</div>';
        }

        $bookingLink = '<p><a href="'.$bookingUrl.'" class="btn btn-primary">Book your seat today</a></p>';
        $trainingOptions = '<h4>Training Options</h4><ul><li>Virtual sessions (coming soon).</li><li>Custom team training options available.</li><li>One-on-one coaching sessions.</li><li>In-person group workshops (max 16 participants).</li></ul>';

        $content = [
            'fundamentals-advanced' => '<div class="prose prose-lg max-w-none">
<h1>Sales Fundamentals &amp; Advanced Techniques</h1>
<p>This combined course merges <strong>Essential Sales Fundamentals</strong> and <strong>Advanced Sales Strategies and Techniques</strong> into one comprehensive programme.</p>
<figure class="my-6"><img src="'.$img.'/stacking-wooden-blocks.jpg" alt="Sales fundamentals and business growth" class="rounded-lg w-full max-w-2xl mx-auto" /></figure>
<h3>What You\'ll Master</h3>
<ul>
<li><strong>Essential Soft Skills:</strong> Listening skills, questioning skills, emotional intelligence</li>
<li><strong>Essential Sales Skills:</strong> Prospecting, presenting, uncovering prospects\' needs, satisfying prospects\' needs (features and benefits), handling objections, closing the sale</li>
<li><strong>Goal Setting and Self-Motivation:</strong> Create inspiring goals to stay focused</li>
<li><strong>Advanced Sales Skills:</strong> Prospecting and lead generation, negotiation skills, presentation skills, handling objections, closing techniques</li>
</ul>
<h3>Why you need this workshop</h3>
<ul>
<li><strong>Goal Setting:</strong> Learn how to set effective sales goals that will inspire you to success.</li>
<li><strong>Develop Sales Excellence:</strong> Learn the traits and habits of top-performing salespeople.</li>
<li><strong>Understand the Sales Journey:</strong> Know all the essential steps in the sales journey and the importance of "going all the way."</li>
<li><strong>Accelerate Sales Success:</strong> Learn more effective sales techniques and strategies.</li>
<li><strong>Master Sales Skills:</strong> Take your sales skills from "good to great".</li>
</ul>
<h3>Workshop Curriculum</h3>
<ul>
<li>After Sales Service</li>
<li>Close the Sale (Identify "buying signals"; closing techniques)</li>
<li>Handling Objections</li>
<li>Satisfy the Need (Features and Benefits)</li>
<li>Uncover the Need (Questions are the answer)</li>
<li>Prospecting Techniques</li>
<li>Develop Effective Listening Skills</li>
<li>How to have a Positive Attitude</li>
<li>Self-Motivation &amp; Goal Setting: Staying Driven and Focused</li>
<li>Advanced Strategies for: Overcoming Objections; Closing the Sale</li>
<li>Sales Presentations Skills</li>
<li>Negotiations Skills</li>
<li>Prospecting and Lead Generation</li>
</ul>
<figure class="my-6"><img src="'.$img.'/person-plays-chess.jpg" alt="Advanced sales strategies and planning" class="rounded-lg w-full max-w-2xl mx-auto" /></figure>
'.$bookingLink.'
<h4>Who Should Attend?</h4>
<ul>
<li>Sales teams.</li>
<li>Start-up business owners who will also play a role in selling.</li>
<li>Salespersons wanting to brush up on their sales skills.</li>
<li>Salespersons starting off in a sales career.</li>
<li>Sales teams who want to integrate advanced sales strategies and techniques into their team dynamics.</li>
<li>Small business owners who also play a sales role in their organisations.</li>
<li>Sales managers who want to help their sales teams.</li>
<li>Salespersons who want to elevate their basic sales skills and become expert salespersons.</li>
</ul>
'.$trainingOptions.'
</div>',
            'relationship-selling' => '<div class="prose prose-lg max-w-none">
<h1>Mastering Relationship Selling Workshop</h1>
<figure class="my-6"><img src="'.$img.'/people-working-office.jpg" alt="People working in an elegant office space" class="rounded-lg w-full max-w-2xl mx-auto" /></figure>
<h3>What You\'ll Master</h3>
<ul>
<li><strong>Long-term Value Creation:</strong> Develop lasting customer relationships that generate repeat business and referrals.</li>
<li><strong>Trust Building:</strong> Learn how to earn and maintain customer trust.</li>
<li><strong>Authentic Connection:</strong> Build real relationships that naturally lead to sales.</li>
</ul>
<h3>Why you need this workshop</h3>
<ul>
<li><strong>Lifetime Value:</strong> Create loyal customers who advocate for your business long after the sale.</li>
<li><strong>Competitive Advantage:</strong> Products can be replicated, but genuine relationships set you apart.</li>
<li><strong>Changed Buying Behaviour:</strong> Today\'s informed customers choose trust over mere product quality.</li>
<li><strong>The 5–7 Contact Reality:</strong> Modern buyers need multiple meaningful interactions before committing. Building strong relationships is essential.</li>
</ul>
<h3>Workshop Curriculum</h3>
<ul>
<li>Long-term Value: Learn strategies to create ongoing, profitable customer relationships</li>
<li>Customer Connection Mastery: Master the art of engaging with customers on a deeper level</li>
<li>The Trust Formula: Discover proven methods to build lasting trust with customers</li>
<li>Building Your Foundation: Set the groundwork for trust and authenticity</li>
<li>The Evolution of Sales: Understand how sales has shifted to relationship-driven strategies</li>
</ul>
'.$bookingLink.'
<h4>Who Should Attend?</h4>
<ul>
<li>Sales teams transitioning to consultative, relationship-focused selling.</li>
<li>Account managers aiming to strengthen client relationships.</li>
<li>Business owners wanting to boost customer loyalty.</li>
<li>Sales professionals looking to modernize their approach.</li>
</ul>
<h4>Training Options</h4>
<ul><li>Virtual sessions (coming soon).</li><li>Custom team training options available.</li><li>One-on-one coaching sessions.</li><li>In-person group workshops (max 12 participants).</li></ul>
</div>',
            'mindset-super-salesperson' => '<div class="prose prose-lg max-w-none">
<h1>The Mindset of a Super Salesperson</h1>
<figure class="my-6"><img src="'.$img.'/brain-mindset-concept.jpg" alt="Mindset and psychological steps to success" class="rounded-lg w-full max-w-2xl mx-auto" /></figure>
<h3>What You\'ll Master</h3>
<ul>
<li><strong>Emotional Intelligence:</strong> Create self-awareness and self-motivation.</li>
<li><strong>Positive psychological traits:</strong> Nip depression, anxiety and distress in the bud.</li>
<li><strong>Self-Motivation:</strong> Emotions that produce positive action.</li>
</ul>
<h3>Why you need this workshop</h3>
<ul>
<li>Get practical tools to help you turn setbacks into stepping stones, deal with negative thoughts and create a positive mindset.</li>
<li>6 Ways to Invest in your potential</li>
<li>Learn how to be in control</li>
<li>Learn three psychological steps to Success</li>
<li>Master Self-Motivation</li>
</ul>
<h3>Workshop Curriculum</h3>
<ul>
<li>The Role of Emotional Intelligence in Sales Success</li>
<li>Invest in Yourself: Six Steps to Success</li>
<li>The 3 Psychological Steps to Sales Success</li>
<li>Using motivation and emotions to connect with your customers</li>
</ul>
'.$bookingLink.'
<h4>Who Should Attend?</h4>
<ul>
<li>Sales teams who want to support each other by demonstrating Emotional Intelligence</li>
<li>Small business owners who also play a sales role in their organisations</li>
<li>Sales managers who want to help their sales teams</li>
<li>Salespersons who want to use their thoughts and emotions to accelerate success</li>
</ul>
'.$trainingOptions.'
</div>',
            'selling-on-phone' => '<div class="prose prose-lg max-w-none">
<h1>Selling On The Phone</h1>
<figure class="my-6"><img src="'.$img.'/selling-on-phone.jpg" alt="Selling on the phone and customer engagement" class="rounded-lg w-full max-w-2xl mx-auto" /></figure>
<p>We cannot escape the fact that the phone is very often still used as a convenient instrument in selling our products and services, as well as selling appointments. The difference between an average sales person and an excellent one will make a significant difference to your sales results.</p>
<p>Consider: if your closing rate (incoming hot leads) improved from 60% to 80%, it translates into more than 30% improvement in your closing rate! Or if your closing rate increased from 10% to 15% (outgoing calls) it means that your closing rate has improved by 50%, and this in turn results in a huge difference to the total sales you bring in.</p>
<p>For example, if you phoned 250 prospects per week (50 per day; 10 per hour for 5 hours per day), and closed 10% (2 sales per 20 calls) at an average of R500, your sales will amount to R12,500. If you improved your closing rate from 2 to 3 sales per 20 calls (which is 50% growth), your sales will soar to R18,750, just by closing one more sale for every 20 calls.</p>
<h3>Why you need this workshop</h3>
<ul>
<li>Learn how to maintain a positive attitude when selling on the phone.</li>
<li>"Selling on the Phone" will teach you both the soft skills as well as specific technical selling skills to improve your effectiveness with selling on the phone.</li>
</ul>
<h3>Workshop Curriculum</h3>
<ul>
<li>Measuring your Strike Rate (Keeping Records)</li>
<li>Closing the Sale</li>
<li>Buying Signals</li>
<li>Handling a Stall</li>
<li>Overcoming Objections</li>
<li>What do Customers Buy?</li>
<li>Talk Features, not Benefits</li>
<li>Satisfy the Need</li>
<li>Uncover the Need</li>
<li>The Effective use of Questioning Techniques</li>
<li>The Importance of First Impressions</li>
<li>The Art of Effective Listening Skills</li>
<li>3 Important Pre-requisites before Picking up the Phone</li>
</ul>
'.$bookingLink.'
<h4>Who Should Attend?</h4>
<ul>
<li>Sales teams.</li>
<li>Start-up business owners who will also play a role in selling.</li>
<li>Salespersons wanting to brush up on their sales skills.</li>
<li>Salespersons starting off in a sales career.</li>
</ul>
'.$trainingOptions.'
</div>',
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
    <h1>Customer Service Workshop</h1>
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
    <h1>Emotional Intelligence Workshop</h1>
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
    <h1>Goal Setting and Getting Things Done</h1>
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
    <h1>High-Performance Teams Workshop</h1>
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
    <h1>Interpersonal Skills Workshop</h1>
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
    <h1>Managing Personal Finances Workshop</h1>
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
    <h1>Presentation Skills Workshop</h1>
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
    <h1>Supervising Others Workshop</h1>
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
    <h1>Keynote Talks</h1>
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
    <h1>Books and Resources</h1>
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
