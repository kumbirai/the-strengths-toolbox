<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Seed testimonials from both live websites
 * This seeder creates testimonials with embedded content.
 * All testimonials are embedded directly in this seeder.
 *
 * Testimonials sourced from:
 * - https://www.thestrengthstoolbox.com/testimonials/
 * - https://www.tsabusinessschool.co.za/ (homepage)
 *
 * To add new testimonials, add entries to the $testimonials array below.
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding testimonials...');
        $this->command->newLine();

        $this->seedTestimonials();

        $this->command->newLine();
        $this->command->info('✓ Testimonials seeded successfully!');
        $this->command->info('Total testimonials: '.Testimonial::count());
    }

    /**
     * Seed testimonials with embedded content
     *
     * All testimonial content is embedded directly in this seeder.
     * Testimonials are sourced from both:
     * - The Strengths Toolbox website (thestrengthstoolbox.com/testimonials/)
     * - The Strengths Toolbox website homepage (thestrengthstoolbox.com)
     *
     * To add new testimonials, add entries to the $testimonials array below.
     */
    protected function seedTestimonials(): void
    {
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
            [
                'name' => 'Boffin & Fundi Team',
                'company' => 'Boffin & Fundi',
                'testimonial' => 'I would like to extend my heartfelt appreciation for the insightful and engaging CliftonStrengths workshop you facilitated for our team. The experience has had a genuinely meaningful impact on both my personal development as a manager and on the overall functioning of our team. The most refreshing part of the workshop was seeing the team gain a clear understanding of what they are naturally good at, and how each of them can meaningfully contribute within the team. It created an environment of self-awareness and appreciation that we had not fully tapped into before. Watching individuals recognise and celebrate their strengths was uplifting, and it sparked valuable conversations about collaboration and personal effectiveness. For me personally, the workshop has helped me understand my team a whole lot better — who excels in specific areas and how to strategically align them with responsibilities that match those strengths. Since the session, we have begun adjusting responsibilities based on each person\'s strengths, and this shift has already started to show positive results. We are gradually seeing improvements in the centre\'s productivity, as well as noticeable boosts in morale and motivation. I have also adopted the practice of designing my day around my own strengths, and I now feel more empowered to ask for support from team members who are naturally strong in areas where I may not be. This has not only improved efficiency but has also strengthened our teamwork and collaboration. We are currently in the process of formally restructuring our team and roles to maximise effectiveness, using the guidance and insights gained from the workshop. It is clear that we have taken the lessons to heart, and we remain committed to continuously developing and leveraging our strengths to become an even more effective and cohesive unit. Thank you once again for the transformative experience. The impact is already evident, and we look forward to building on this foundation.',
                'rating' => 5,
                'is_featured' => true,
                'is_published' => true,
                'display_order' => 6,
            ],
        ];

        foreach ($testimonials as $testimonialData) {
            $testimonial = Testimonial::firstOrNew([
                'name' => $testimonialData['name'],
                'company' => $testimonialData['company'],
            ]);

            $testimonial->fill($testimonialData);
            $testimonial->save();

            $this->command->line("  ✓ Imported: {$testimonialData['name']}".($testimonialData['company'] ? " ({$testimonialData['company']})" : ''));
        }
    }
}
