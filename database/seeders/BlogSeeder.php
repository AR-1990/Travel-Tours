<?php

namespace Database\Seeders;

use App\Models\Content\Blog;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $blogs = [
            [
                'title' => 'Top Northern Pakistan Destinations For A Scenic Summer Escape',
                'slug' => 'top-northern-pakistan-destinations-for-a-scenic-summer-escape',
                'image' => 'assets/img/blog/01.jpg',
                'meta_title' => 'Top Northern Pakistan Destinations For A Scenic Summer Escape',
                'meta_description' => 'Discover the best summer destinations in Northern Pakistan with practical travel tips, ideal stay durations, and scenic route ideas.',
                'description' => <<<HTML
<p>From Hunza to Skardu, Northern Pakistan offers dramatic mountains, clear lakes, and road trips that feel unforgettable from the very first mile. Travelers looking for a complete summer itinerary can combine valleys, cultural stops, and scenic overnight stays into one smooth route.</p>
<p>Start with destinations that balance accessibility and beauty. Hunza is ideal for travelers who want comfort, viewpoints, and local food, while Skardu works well for longer scenic escapes and adventure-based plans. If your audience prefers softer experiences, include families, honeymooners, and corporate retreats in the planning recommendations.</p>
<blockquote class="blockqoute">The best itineraries are not the busiest ones. They are the ones that leave enough time to actually experience the landscape.<h6 class="blockqoute-author">Travel Tours Editorial Team</h6></blockquote>
<p>For stronger conversions, present each destination with stay duration, best season, and what kind of traveler it suits. That keeps the blog useful, readable, and aligned with the travel-booking theme of the site.</p>
HTML,
            ],
            [
                'title' => 'How To Plan A Smooth Umrah And Leisure Combo Trip',
                'slug' => 'how-to-plan-a-smooth-umrah-and-leisure-combo-trip',
                'image' => 'assets/img/blog/02.jpg',
                'meta_title' => 'How To Plan A Smooth Umrah And Leisure Combo Trip',
                'meta_description' => 'Learn how to combine Umrah with a comfortable leisure itinerary, including flights, hotels, transport, and family travel planning.',
                'description' => <<<HTML
<p>Many travelers now prefer a package that combines religious travel with a short family leisure stay. A well-structured Umrah and leisure blog should explain how to balance spiritual commitments with comfort, transport convenience, and family-friendly accommodation.</p>
<p>Focus on practical guidance: choosing the right hotel zone, booking transfer windows around prayer schedules, and allowing recovery time after arrival. Readers respond better when the advice reflects real travel flow instead of generic promotion.</p>
<p>Include short sections for couples, elderly travelers, and families with children. These audience-specific suggestions make the article feel intentional and increase trust in your service offering.</p>
HTML,
            ],
            [
                'title' => 'Five Signs Your Corporate Travel Program Needs Better Planning',
                'slug' => 'five-signs-your-corporate-travel-program-needs-better-planning',
                'image' => 'assets/img/blog/03.jpg',
                'meta_title' => 'Five Signs Your Corporate Travel Program Needs Better Planning',
                'meta_description' => 'Identify weak points in your corporate travel workflow and improve booking control, reporting, traveler support, and vendor coordination.',
                'description' => <<<HTML
<p>Corporate travel often becomes expensive not because rates are high, but because approvals, hotel choices, and flight timings are inconsistent. A useful business-travel article should highlight the signs of an unstructured process and show how expert planning improves cost control.</p>
<p>Talk about duplicated bookings, poor traveler visibility, last-minute changes, and missing reporting. These are common pain points that decision-makers instantly recognize.</p>
<blockquote class="blockqoute">Corporate travel works best when policy, service, and traveler experience move together instead of competing with one another.<h6 class="blockqoute-author">Travel Operations Desk</h6></blockquote>
<p>End with a checklist that helps companies review approval flows, preferred suppliers, and emergency support availability. That keeps the article informative while still leading naturally toward managed service inquiries.</p>
HTML,
            ],
            [
                'title' => 'A Practical Guide To Booking Family Holidays Without The Stress',
                'slug' => 'a-practical-guide-to-booking-family-holidays-without-the-stress',
                'image' => 'assets/img/blog/01.jpg',
                'meta_title' => 'A Practical Guide To Booking Family Holidays Without The Stress',
                'meta_description' => 'Make family holiday planning easier with destination selection tips, child-friendly stay ideas, and smarter itinerary pacing.',
                'description' => <<<HTML
<p>Family holidays succeed when the itinerary respects energy, rest, and convenience. The best travel advice is not just about where to go, but how to sequence flights, hotel check-ins, transfers, and activity time so that the whole family enjoys the experience.</p>
<p>Recommend destinations that offer short internal transfers, child-friendly meal options, and flexible sightseeing. Parents usually look for reassurance that the plan is manageable before they look at luxury upgrades.</p>
<p>By framing the trip around comfort, safety, and simple logistics, this kind of blog becomes highly relevant for both website readers and direct booking inquiries.</p>
HTML,
            ],
            [
                'title' => 'Why Hotel And Transfer Bundles Convert Better Than Standalone Deals',
                'slug' => 'why-hotel-and-transfer-bundles-convert-better-than-standalone-deals',
                'image' => 'assets/img/blog/02.jpg',
                'meta_title' => 'Why Hotel And Transfer Bundles Convert Better Than Standalone Deals',
                'meta_description' => 'See why bundled hotel and transfer packages improve traveler confidence, reduce booking friction, and increase overall value.',
                'description' => <<<HTML
<p>Travelers are more likely to book when the journey feels complete. A hotel on its own still leaves open questions about airport pickup, local transport, and arrival stress. Bundled offers remove those gaps and create stronger decision confidence.</p>
<p>Explain how bundles reduce hidden costs and make package comparison easier. Readers should understand that value is not only about a lower headline price, but also about smoother execution on the ground.</p>
<p>This kind of article works especially well for airport cities, pilgrimage traffic, and premium leisure markets where the first and last transfer strongly shapes the overall experience.</p>
HTML,
            ],
            [
                'title' => 'Travel Trends In 2026 That Agencies Should Not Ignore',
                'slug' => 'travel-trends-in-2026-that-agencies-should-not-ignore',
                'image' => 'assets/img/blog/03.jpg',
                'meta_title' => 'Travel Trends In 2026 That Agencies Should Not Ignore',
                'meta_description' => 'Explore the travel trends shaping 2026, from blended itineraries to trust-driven content and flexible service packaging.',
                'description' => <<<HTML
<p>Travel demand in 2026 is increasingly shaped by personalization, convenience, and trust. Customers expect agencies to provide more than availability. They want clear recommendations, bundled planning, and content that helps them choose with confidence.</p>
<p>Key trends include blended leisure and business travel, shorter but more frequent holidays, flexible support policies, and destination content that answers practical booking questions. Agencies that present these trends clearly in their content strategy usually convert faster.</p>
<blockquote class="blockqoute">The agencies that win in 2026 are the ones that make booking feel simple, guided, and reliable from the first click.<h6 class="blockqoute-author">Travel Tours Strategy Team</h6></blockquote>
<p>Use this blog to position your business as a planning partner, not just a seller. That tone aligns well with premium travel services and theme-based frontends that already feel polished and informative.</p>
HTML,
            ],
        ];

        foreach ($blogs as $blog) {
            Blog::updateOrCreate(
                ['slug' => $blog['slug']],
                $blog
            );
        }
    }
}
