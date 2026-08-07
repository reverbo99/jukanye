<?php

namespace Database\Seeders;

use App\Models\AwardCategory;
use App\Models\HomeSection;
use App\Models\MapPlace;
use App\Models\Person;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Sponsor;
use App\Models\TeamMember;
use App\Models\TicketTier;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@jukanye.com'],
            [
                'name' => 'Jukanye Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $categories = [
            ['name_en' => 'Liberation', 'name_sw' => 'Ukombozi', 'slug' => 'liberation', 'sort_order' => 1],
            ['name_en' => 'Mama Africa', 'name_sw' => 'Mama Afrika', 'slug' => 'mama-africa', 'sort_order' => 2],
            ['name_en' => 'Wisdom (Religious)', 'name_sw' => 'Hekima (Dini)', 'slug' => 'wisdom', 'sort_order' => 3],
            ['name_en' => 'Honorary (Patriotic Leaders)', 'name_sw' => 'Heshima (Viongozi wa Kizalendo)', 'slug' => 'honorary', 'sort_order' => 4],
            ['name_en' => 'Global Solidarity', 'name_sw' => 'Umoja wa Kimataifa', 'slug' => 'global-solidarity', 'sort_order' => 5],
            ['name_en' => 'Music Award', 'name_sw' => 'Tuzo ya Muziki', 'slug' => 'music-award', 'sort_order' => 6],
        ];
        foreach ($categories as $category) {
            AwardCategory::query()->updateOrCreate(['slug' => $category['slug']], $category);
        }

        SiteSetting::current()->fill([
            'tagline_en' => "Honoring Africa's Liberation Heroes, Promoting Patriotism",
            'tagline_sw' => 'Kuwaenzi Mashujaa wa Ukombozi wa Afrika, Kukuza Uzalendo',
            'date_label' => '19 JULY – 01 AUGUST 2027',
            'location_label' => 'ARUSHA, TANZANIA',
            'festival_starts_at' => '2027-07-19 10:00:00',
            'countdown_at' => '2027-07-19 10:00:00',
            'donate_body_en' => 'Your donation sustains programmes, artists, and community outreach.',
            'donate_body_sw' => 'Michango yako inaendeleza programu, wasanii, na huduma za jamii.',
            'about_intro_en' => 'Jukanye Festival celebrates Africa’s liberation heritage through arts, dialogue, and culture.',
            'about_intro_sw' => 'Tamasha la Jukanye linasherehekea urithi wa ukombozi wa Afrika kupitia sanaa, majadiliano, na utamaduni.',
            'download_text_en' => 'Upload or download festival materials here.',
            'download_text_sw' => 'Pakia au pakua nyaraka za tamasha hapa.',
            'footer_contact' => [
                'email' => 'jukanyefestival@gmail.com',
                'phone' => '',
                'address' => 'Arusha, Tanzania',
            ],
            'social' => [],
        ])->save();

        Sponsor::query()->updateOrCreate(
            ['name' => 'Sample Sponsor'],
            ['tier' => 'Gold', 'url' => 'https://jukanye.com', 'sort_order' => 1, 'status' => 'published']
        );

        TeamMember::query()->updateOrCreate(
            ['name' => 'Festival Visionary'],
            [
                'role_en' => 'Founder',
                'role_sw' => 'Mwanzilishi',
                'bio_en' => 'Leading the festival vision.',
                'bio_sw' => 'Anaongoza maono ya tamasha.',
                'sort_order' => 1,
                'status' => 'published',
            ]
        );

        foreach ([
            ['Festival T-Shirt', 'Shati la Tamasha', 35000],
            ['Jukanye Cap', 'Kofia ya Jukanye', 18000],
            ['Heritage Scarf', 'Skafu ya Urithi', 22000],
            ['Kitenge Wrap', 'Kitenge', 45000],
        ] as $i => [$en, $sw, $price]) {
            Product::query()->updateOrCreate(
                ['name_en' => $en],
                [
                    'name_sw' => $sw,
                    'price' => $price,
                    'currency' => 'TZS',
                    'sort_order' => $i + 1,
                    'status' => 'published',
                ]
            );
        }

        HomeSection::query()->updateOrCreate(
            ['type' => 'objective', 'title_en' => 'Celebrate liberation'],
            [
                'title_sw' => 'Sherehekea ukombozi',
                'body_en' => 'Honour heroes and heritage.',
                'body_sw' => 'Waheshimu mashujaa na urithi.',
                'sort_order' => 1,
                'status' => 'published',
            ]
        );

        foreach ([
            ['speaker', 'Keynote Speaker'],
            ['artist', 'Featured Artist'],
            ['hero', 'Liberation Hero'],
            ['friend', 'Festival Friend'],
            ['exhibition', 'Heritage Exhibition'],
        ] as $i => [$type, $name]) {
            Person::query()->updateOrCreate(
                ['type' => $type, 'name' => $name],
                [
                    'subtitle_en' => 'Featured',
                    'subtitle_sw' => 'Maalum',
                    'bio_en' => 'Published from CMS seed.',
                    'bio_sw' => 'Imechapishwa kutoka CMS.',
                    'sort_order' => $i + 1,
                    'status' => 'published',
                ]
            );
        }

        foreach ([
            ['Serengeti Safari', 'Safari ya Serengeti', 450000, '3 Days', 'Siku 3'],
            ['Mount Kilimanjaro', 'Mlima Kilimanjaro', 1200000, '5 Days', 'Siku 5'],
            ['Zanzibar Beach Tour', 'Ziara ya Zanzibar', 380000, '4 Days', 'Siku 4'],
            ['Cultural Heritage Tour', 'Ziara ya Urithi', 150000, '1 Day', 'Siku 1'],
        ] as $i => [$en, $sw, $price, $den, $dsw]) {
            Tour::query()->updateOrCreate(
                ['name_en' => $en],
                [
                    'name_sw' => $sw,
                    'from_price' => $price,
                    'currency' => 'TZS',
                    'duration_en' => $den,
                    'duration_sw' => $dsw,
                    'sort_order' => $i + 1,
                    'status' => 'published',
                ]
            );
        }

        $tiers = [
            ['one_day', 'One Day Pass', 'Pasi ya Siku Moja', 20000, ['Single day main stage access', 'Food court entry']],
            ['three_day', 'Three Days Pass', 'Pasi ya Siku Tatu', 50000, ['Any 3 festival days', 'Workshop access']],
            ['full', 'Full Festival Pass', 'Pasi Kamili', 100000, ['All Events Access', 'Exhibition Hall Entry']],
            ['vip', 'VIP Pass', 'Pasi ya VIP', 250000, ['VIP lounge access', 'Reserved seating']],
        ];
        foreach ($tiers as $i => [$slug, $en, $sw, $price, $includes]) {
            TicketTier::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name_en' => $en,
                    'name_sw' => $sw,
                    'price' => $price,
                    'currency' => 'TZS',
                    'description_en' => $en,
                    'description_sw' => $sw,
                    'includes' => $includes,
                    'sort_order' => $i + 1,
                    'status' => 'published',
                ]
            );
        }

        MapPlace::query()->updateOrCreate(
            ['name_en' => 'Main Stage'],
            [
                'name_sw' => 'Jukwaa Kuu',
                'lat' => -3.3869,
                'lng' => 36.6830,
                'description_en' => 'Primary performance stage',
                'description_sw' => 'Jukwaa kuu la maonyesho',
                'sort_order' => 1,
                'status' => 'published',
            ]
        );
    }
}
