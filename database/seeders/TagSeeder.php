<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Core tags
            ['name' => 'Strengths', 'slug' => 'strengths'],
            ['name' => 'Team Building', 'slug' => 'team-building'],
            ['name' => 'Performance', 'slug' => 'performance'],
            ['name' => 'Coaching', 'slug' => 'coaching'],

            // Personal development tags
            ['name' => 'Personal Development', 'slug' => 'personal-development'],
            ['name' => 'Natural Talents', 'slug' => 'natural-talents'],
            ['name' => 'Potential', 'slug' => 'potential'],
            ['name' => 'Motivation', 'slug' => 'motivation'],
            ['name' => 'Inspiration', 'slug' => 'inspiration'],
            ['name' => 'Enthusiasm', 'slug' => 'enthusiasm'],
            ['name' => 'Opportunities', 'slug' => 'opportunities'],
            ['name' => 'Problem Solving', 'slug' => 'problem-solving'],
            ['name' => 'Positive Mindset', 'slug' => 'positive-mindset'],
            ['name' => 'Vision', 'slug' => 'vision'],
            ['name' => 'Future Planning', 'slug' => 'future-planning'],
            ['name' => 'Goal Setting', 'slug' => 'goal-setting'],
            ['name' => 'Strategy', 'slug' => 'strategy'],
            ['name' => 'Planning', 'slug' => 'planning'],
            ['name' => 'Goal Achievement', 'slug' => 'goal-achievement'],
            ['name' => 'Tenacity', 'slug' => 'tenacity'],
            ['name' => 'Persistence', 'slug' => 'persistence'],
            ['name' => 'Resilience', 'slug' => 'resilience'],
            ['name' => 'Decision Making', 'slug' => 'decision-making'],
            ['name' => 'Choices', 'slug' => 'choices'],
            ['name' => 'Empowerment', 'slug' => 'empowerment'],
            ['name' => 'INVEST', 'slug' => 'invest'],

            // Sales tags
            ['name' => 'Sales Goals', 'slug' => 'sales-goals'],
            ['name' => 'Sales Performance', 'slug' => 'sales-performance'],
            ['name' => 'Sales Courses', 'slug' => 'sales-courses'],
            ['name' => 'Strengths-Based Selling', 'slug' => 'strengths-based-selling'],
            ['name' => 'Sales Strategies', 'slug' => 'sales-strategies'],
            ['name' => 'Sales Talent', 'slug' => 'sales-talent'],
            ['name' => 'Sales Recruitment', 'slug' => 'sales-recruitment'],
            ['name' => 'Sales Myths', 'slug' => 'sales-myths'],
            ['name' => 'Relationship Marketing', 'slug' => 'relationship-marketing'],
            ['name' => 'Sales Strategy', 'slug' => 'sales-strategy'],
            ['name' => 'Customer Relationships', 'slug' => 'customer-relationships'],
            ['name' => 'Buyer Confidence', 'slug' => 'buyer-confidence'],
            ['name' => 'Sales Process', 'slug' => 'sales-process'],
            ['name' => 'Customer Trust', 'slug' => 'customer-trust'],

            // Coaching tags
            ['name' => 'Business Coach', 'slug' => 'business-coach'],
            ['name' => 'Team Coaching', 'slug' => 'team-coaching'],
            ['name' => 'Performance Coaching', 'slug' => 'performance-coaching'],

            // Research tags
            ['name' => 'Gallup Research', 'slug' => 'gallup-research'],

            // Life lessons and wellbeing tags
            ['name' => 'Self-Worth', 'slug' => 'self-worth'],
            ['name' => 'Confidence', 'slug' => 'confidence'],
            ['name' => 'Self-Esteem', 'slug' => 'self-esteem'],
            ['name' => 'Gratitude', 'slug' => 'gratitude'],
            ['name' => 'Life Lessons', 'slug' => 'life-lessons'],
            ['name' => 'Fun', 'slug' => 'fun'],
            ['name' => 'Joy', 'slug' => 'joy'],
            ['name' => 'Wellbeing', 'slug' => 'wellbeing'],
            ['name' => 'Work-Life Balance', 'slug' => 'work-life-balance'],
            ['name' => 'Dreams', 'slug' => 'dreams'],
            ['name' => 'Leadership', 'slug' => 'leadership'],
            ['name' => 'Empathy', 'slug' => 'empathy'],
            ['name' => 'Relationships', 'slug' => 'relationships'],
            ['name' => 'Respect', 'slug' => 'respect'],
            ['name' => 'Personal Responsibility', 'slug' => 'personal-responsibility'],
            ['name' => 'Control', 'slug' => 'control'],
            ['name' => 'Giving', 'slug' => 'giving'],
            ['name' => 'Generosity', 'slug' => 'generosity'],
            ['name' => 'Happiness', 'slug' => 'happiness'],
            ['name' => 'Fulfilment', 'slug' => 'fulfilment'],
            ['name' => 'Tolerance', 'slug' => 'tolerance'],
            ['name' => 'Prejudice', 'slug' => 'prejudice'],
            ['name' => 'Diversity', 'slug' => 'diversity'],
            ['name' => 'Mindset', 'slug' => 'mindset'],
            ['name' => 'Thoughts', 'slug' => 'thoughts'],
            ['name' => 'Reality', 'slug' => 'reality'],
            ['name' => 'Positive Thinking', 'slug' => 'positive-thinking'],
            ['name' => 'Weaknesses', 'slug' => 'weaknesses'],
            ['name' => 'Strengths-Based Development', 'slug' => 'strengths-based-development'],
            ['name' => 'Engagement', 'slug' => 'engagement'],
            ['name' => 'Framework', 'slug' => 'framework'],
            ['name' => 'Self-Investment', 'slug' => 'self-investment'],
            ['name' => 'Journey', 'slug' => 'journey'],
            ['name' => 'Uniqueness', 'slug' => 'uniqueness'],
            ['name' => 'Self-Acceptance', 'slug' => 'self-acceptance'],
            ['name' => 'Law of Attraction', 'slug' => 'law-of-attraction'],
            ['name' => 'Focus', 'slug' => 'focus'],
            ['name' => 'Cause and Effect', 'slug' => 'cause-and-effect'],
            ['name' => 'Effort', 'slug' => 'effort'],
            ['name' => 'Responsibility', 'slug' => 'responsibility'],
            ['name' => 'Behaviour', 'slug' => 'behaviour'],
            ['name' => 'Pain', 'slug' => 'pain'],
            ['name' => 'Pleasure', 'slug' => 'pleasure'],
            ['name' => 'Psychology', 'slug' => 'psychology'],
            ['name' => 'Staff Development', 'slug' => 'staff-development'],
            ['name' => 'Management', 'slug' => 'management'],
            ['name' => 'Life Design', 'slug' => 'life-design'],
            ['name' => 'Selling', 'slug' => 'selling'],
            ['name' => 'Growth', 'slug' => 'growth'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                ['slug' => $tag['slug']],
                $tag
            );
        }
    }
}
