<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Seeder for Phase 4: Existing Content Pages
 *
 * This seeder creates the page structure for all existing content pages
 * that should have been created in Phase 4. Content will be populated
 * in Phase 5 (Content Migration).
 */
class ExistingContentPagesSeeder extends Seeder
{
    public function run(): void
    {
        // Strengths-Based Development Pages
        $this->createStrengthsBasedDevelopmentPages();

        // Sales Training Pages
        $this->createSalesTrainingPages();

        // Facilitation/Workshop Pages
        $this->createFacilitationPages();
    }

    /**
     * Create Strengths-Based Development pages
     */
    private function createStrengthsBasedDevelopmentPages(): void
    {
        $pages = [
            [
                'title' => 'The Power Of Strengths',
                'slug' => 'the-power-of-strengths',
                'excerpt' => 'Discover how identifying and leveraging natural talents transforms individual and team performance.',
                'meta_title' => 'The Power Of Strengths - The Strengths Toolbox',
                'meta_description' => 'Learn about the power of strengths-based development and how it transforms teams and businesses.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Teams',
                'slug' => 'strengths-based-development/teams',
                'excerpt' => 'Build high-performing teams by understanding and leveraging each member\'s unique strengths.',
                'meta_title' => 'Strengths-Based Team Development - The Strengths Toolbox',
                'meta_description' => 'Transform your team through strengths-based development programs designed for teams.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Managers / Leaders',
                'slug' => 'strengths-based-development/managers-leaders',
                'excerpt' => 'Develop authentic leadership by understanding and leveraging your natural strengths.',
                'meta_title' => 'Strengths-Based Leadership Development - The Strengths Toolbox',
                'meta_description' => 'Leadership development programs based on strengths-based principles for managers and leaders.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Salespeople',
                'slug' => 'strengths-based-development/salespeople',
                'excerpt' => 'Enhance sales performance by identifying and leveraging your natural sales strengths.',
                'meta_title' => 'Strengths-Based Sales Development - The Strengths Toolbox',
                'meta_description' => 'Sales development programs that leverage natural strengths to improve sales performance.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Individuals',
                'slug' => 'strengths-based-development/individuals',
                'excerpt' => 'Discover your unique strengths and unlock your full potential.',
                'meta_title' => 'Individual Strengths Development - The Strengths Toolbox',
                'meta_description' => 'Personal development programs to help individuals discover and leverage their natural strengths.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
        ];

        foreach ($pages as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                array_merge($pageData, [
                    'is_published' => true,
                    'published_at' => now(),
                ])
            );
        }
    }

    /**
     * Create Sales Training pages
     */
    private function createSalesTrainingPages(): void
    {
        $pages = [
            [
                'title' => 'Strengths-Based Training',
                'slug' => 'sales-training/strengths-based-training',
                'excerpt' => 'Sales training programs that leverage your natural strengths for better results.',
                'meta_title' => 'Strengths-Based Sales Training - The Strengths Toolbox',
                'meta_description' => 'Sales training programs designed around your natural strengths and talents.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Relationship Selling',
                'slug' => 'sales-training/relationship-selling',
                'excerpt' => 'Build lasting customer relationships through effective relationship selling strategies.',
                'meta_title' => 'Relationship Selling Training - The Strengths Toolbox',
                'meta_description' => 'Learn relationship selling techniques to build stronger customer connections.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Selling On The Phone',
                'slug' => 'sales-training/selling-on-the-phone',
                'excerpt' => 'Master the art of phone sales with proven techniques and strategies.',
                'meta_title' => 'Phone Sales Training - The Strengths Toolbox',
                'meta_description' => 'Effective phone sales training to improve your telephone selling skills.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Sales Fundamentals Workshop',
                'slug' => 'sales-training/sales-fundamentals-workshop',
                'excerpt' => 'Master the fundamentals of sales with our comprehensive workshop.',
                'meta_title' => 'Sales Fundamentals Workshop - The Strengths Toolbox',
                'meta_description' => 'Comprehensive sales fundamentals workshop covering essential sales skills and techniques.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Top 10 Sales Secrets',
                'slug' => 'sales-training/top-10-sales-secrets',
                'excerpt' => 'Discover the top 10 secrets of successful sales professionals.',
                'meta_title' => 'Top 10 Sales Secrets - The Strengths Toolbox',
                'meta_description' => 'Learn the top 10 proven secrets that successful sales professionals use to close more deals.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'In-Person Sales',
                'slug' => 'sales-training/in-person-sales',
                'excerpt' => 'Excel at face-to-face sales with proven in-person selling techniques.',
                'meta_title' => 'In-Person Sales Training - The Strengths Toolbox',
                'meta_description' => 'Training programs to improve your in-person sales skills and techniques.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
        ];

        foreach ($pages as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                array_merge($pageData, [
                    'is_published' => true,
                    'published_at' => now(),
                ])
            );
        }
    }

    /**
     * Create Facilitation/Workshop pages
     */
    private function createFacilitationPages(): void
    {
        $pages = [
            [
                'title' => 'Customer Service Workshop',
                'slug' => 'facilitation/customer-service-workshop',
                'excerpt' => 'Enhance customer service skills with our comprehensive workshop.',
                'meta_title' => 'Customer Service Workshop - The Strengths Toolbox',
                'meta_description' => 'Workshop to improve customer service skills and enhance customer satisfaction.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Emotional Intelligence Workshop',
                'slug' => 'facilitation/emotional-intelligence-workshop',
                'excerpt' => 'Develop emotional intelligence skills for better workplace relationships.',
                'meta_title' => 'Emotional Intelligence Workshop - The Strengths Toolbox',
                'meta_description' => 'Workshop to develop emotional intelligence skills for improved workplace performance.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Goal Setting and Getting Things Done',
                'slug' => 'facilitation/goal-setting',
                'excerpt' => 'Learn effective goal setting strategies and execution techniques.',
                'meta_title' => 'Goal Setting Workshop - The Strengths Toolbox',
                'meta_description' => 'Workshop on goal setting and execution strategies for personal and professional success.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'High-Performance Teams Workshop',
                'slug' => 'facilitation/high-performance-teams',
                'excerpt' => 'Build and develop high-performance teams through proven methodologies.',
                'meta_title' => 'High-Performance Teams Workshop - The Strengths Toolbox',
                'meta_description' => 'Workshop to build and develop high-performance teams that achieve exceptional results.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Interpersonal Skills Workshop',
                'slug' => 'facilitation/interpersonal-skills',
                'excerpt' => 'Enhance interpersonal skills for better workplace communication and relationships.',
                'meta_title' => 'Interpersonal Skills Workshop - The Strengths Toolbox',
                'meta_description' => 'Workshop to develop interpersonal skills for improved workplace communication.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Managing Personal Finances Workshop',
                'slug' => 'facilitation/personal-finances',
                'excerpt' => 'Learn effective personal finance management strategies.',
                'meta_title' => 'Personal Finances Workshop - The Strengths Toolbox',
                'meta_description' => 'Workshop on managing personal finances effectively for financial security.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Presentation Skills Workshop',
                'slug' => 'facilitation/presentation-skills',
                'excerpt' => 'Master presentation skills to deliver compelling and effective presentations.',
                'meta_title' => 'Presentation Skills Workshop - The Strengths Toolbox',
                'meta_description' => 'Workshop to develop presentation skills for effective communication.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
            [
                'title' => 'Supervising Others Workshop',
                'slug' => 'facilitation/supervising-others',
                'excerpt' => 'Develop effective supervision skills to lead and manage teams.',
                'meta_title' => 'Supervising Others Workshop - The Strengths Toolbox',
                'meta_description' => 'Workshop to develop supervision skills for effective team management.',
                'content' => '<p>Content will be migrated in Phase 5.</p>',
            ],
        ];

        foreach ($pages as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                array_merge($pageData, [
                    'is_published' => true,
                    'published_at' => now(),
                ])
            );
        }
    }
}
