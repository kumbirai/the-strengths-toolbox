<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Services\PageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seed production-ready content
 */
class ProductionContentSeeder extends Seeder
{
    protected PageService $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    public function run(): void
    {
        $this->command->info('Seeding production content...');

        $this->seedCategories();
        $this->seedTags();
        $this->seedTestimonials();
        $this->seedStaticPages();

        $this->command->newLine();
        $this->command->info('✓ Production content seeded successfully!');
        $this->command->info('Run "php artisan db:seed --class=ContentMigrationSeeder" to migrate all content pages.');
        $this->command->info('Run "php artisan db:seed --class=BlogPostMigrationSeeder" to migrate blog posts.');
    }

    protected function seedCategories(): void
    {
        $this->command->info('Seeding blog categories...');

        $categories = [
            [
                'name' => 'Team Building',
                'slug' => 'team-building',
                'description' => 'Articles about building strong, cohesive teams',
                'is_active' => true,
            ],
            [
                'name' => 'Leadership',
                'slug' => 'leadership',
                'description' => 'Leadership development and management insights',
                'is_active' => true,
            ],
            [
                'name' => 'Sales Courses',
                'slug' => 'sales-courses',
                'description' => 'Sales courses and training techniques',
                'is_active' => true,
            ],
            [
                'name' => 'Strengths-Based Development',
                'slug' => 'strengths-based-development',
                'description' => 'Understanding and leveraging individual strengths',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }

    protected function seedTags(): void
    {
        $this->command->info('Seeding blog tags...');

        $tags = [
            'strengths',
            'team performance',
            'employee engagement',
            'leadership development',
            'sales strategies',
            'business growth',
            'organizational development',
            'coaching',
            'training',
            'management',
        ];

        foreach ($tags as $tagName) {
            Tag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                [
                    'name' => $tagName,
                    'is_active' => true,
                ]
            );
        }
    }

    protected function seedTestimonials(): void
    {
        $this->command->info('Seeding testimonials...');

        // Use testimonials from both live websites
        // All testimonials are embedded directly (no content-* folder dependencies)
        // Sourced from:
        // - https://www.thestrengthstoolbox.com/testimonials/
        // - https://www.tsabusinessschool.co.za/ (homepage)
        $testimonials = [
            [
                'name' => 'Xolani Matthews',
                'company' => 'Public Investment Corporation',
                'testimonial' => 'I hereby confirm that I underwent 6 coaching sessions with Eberhard. His coaching style was very informative, relaxing and inspirational. His coaching helped to uncover and be aware of my talents and strengths. This has given me very useful guidance on my decision making, including making the team selections. I sincerely recommend him for Executive Coaching as his coaching gave me confidence and guidance that is rare to find.',
                'rating' => 5,
                'is_featured' => true,
                'is_published' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Miss ZA Fakude',
                'company' => 'Gauteng Growth and Development Agency',
                'testimonial' => 'I received the Gallup executive coaching in 2017 and it was a worthwhile exercise. My coach was Mr Eberhard Niklaus and he did an excellent job in helping me to understand my strengths and talents. I found Eberhard to be passionate and insightful which made the coaching exercise enjoyable. I am as a result able apply the principles learnt in my work and home environment. Eberhard\'s style of coaching was inspiring, relaxed as well as interactive. Eberhard is highly recommended as a coach especially if the desired objective is to have fulfilling growth both at work and at home.',
                'rating' => 5,
                'is_featured' => true,
                'is_published' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Walter Hoyer',
                'company' => 'SNX Digital Concepts',
                'testimonial' => 'Thank you for our recent journey with you when you took us on your Gallup StrengthsFinder team development programme. Our team experienced a meaningful benefit, as we discovered our strengths and learnt to integrate them in a team environment, leading us to achieving the best performance possible from both individual team members, as well as a sales team. The biggest impact this training has had, is to see what we can achieve when we pair up some of the sales reps that\'s got different strengths and to see how they complement each other, by using their individual strengths. By doing this it makes it easy for me as a sales manager to connect the dots as they would say. It really helps when you work out sales strategies or even budgets. This was one of the best sessions we have had and it\'s really worth it, because what we learnt has really helped us in a practical way. We have also become a more engaged team, working together towards a common goal. Thanks for all the help!',
                'rating' => 5,
                'is_featured' => true,
                'is_published' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'Charl du Toit',
                'company' => 'The Success Academy',
                'testimonial' => 'Thank you so much for spending time with The Success Academy Team during 2016. The concept of Focusing on Individuals Strengths is extremely powerful. Firstly everyone enjoys to be informed that they actually do have Strengths……sometimes to their own amazement. Secondly focusing on these strengths results in amazing results. Our team is now functioning much better being more aware of each others strengths. I would strongly recommend your Course to any Company that wants to go to the next level of performance. Thank you.',
                'rating' => 5,
                'is_featured' => true,
                'is_published' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'Chris Wentzel',
                'company' => 'Gas Mart National Conference',
                'testimonial' => 'On rare occasions fate delivers someone to your door that have a radical and profound influence on your perspectives of business and the people you engage with in different business environments. It was such a day when you walked into the Gas Mart office in Centurion. The confluence of events that followed was what set us on the path to build a phenomenal business model and come to terms with franchising and the short comings within the industry. It is therefore my pleasure to thank you for the key note talk you gave at the Gas Mart National Conference. The Franchisees expressed multiple times how insightful the talk was and how much they now value the insight you gave into the franchisor franchisee relationship. The contribution you made to our group has been great and we are looking forward to working with you further on developing the natural talents of our head office staff and unleashing the trapped values in our franchise network.',
                'rating' => 5,
                'is_featured' => true,
                'is_published' => true,
                'display_order' => 5,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::firstOrCreate(
                [
                    'name' => $testimonial['name'],
                    'company' => $testimonial['company'],
                ],
                $testimonial
            );
        }
    }

    protected function seedStaticPages(): void
    {
        $this->command->info('Seeding static pages...');

        $pages = [
            [
                'title' => 'The Power of Strengths',
                'slug' => 'the-power-of-strengths',
                'excerpt' => 'Discover how identifying and leveraging natural talents transforms individual and team performance.',
                'content' => '<p>Content for The Power of Strengths page...</p>',
                'is_published' => true,
                'meta_title' => 'The Power of Strengths - The Strengths Toolbox',
                'meta_description' => 'Learn how strengths-based development can transform your team and drive business growth.',
            ],
            [
                'title' => 'Strengths-Based Development for Teams',
                'slug' => 'strengths-based-development/teams',
                'excerpt' => 'Build cohesive teams where members understand and complement each other\'s strengths.',
                'content' => '<p>Content for Teams page...</p>',
                'is_published' => true,
                'meta_title' => 'Team Development - The Strengths Toolbox',
                'meta_description' => 'Transform your team with strengths-based development programs.',
            ],
        ];

        foreach ($pages as $pageData) {
            $page = Page::firstOrNew(['slug' => $pageData['slug']]);
            $page->fill($pageData);
            $page->save();
        }
    }
}
