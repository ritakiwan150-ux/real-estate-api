<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\City;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Realistic bilingual demo listings (published, owned by owner@example.com).
 * Safe to run more than once: listings are matched by slug.
 *
 * No photos are seeded on purpose: upload real ones from the owner dashboard
 * ("Upload images") so they live on your Cloudinary account.
 */
class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'owner@example.com')->first();

        if (! $owner) {
            $this->command?->warn('owner@example.com not found - run TestUsersSeeder first.');
            return;
        }

        $types = PropertyType::pluck('id', 'name_en');
        $cities = City::pluck('id', 'name_en');
        $amenities = Amenity::pluck('id', 'name_en');

        $listings = [
            [
                'slug' => 'palm-jumeirah-signature-villa',
                'type' => 'Villa', 'city' => 'Dubai', 'operation' => 'sale',
                'title_en' => 'Signature Villa on the Palm Jumeirah',
                'title_ar' => 'فيلا فاخرة على نخلة جميرا',
                'description_en' => 'A beachfront six-bedroom villa with a private pool, panoramic Gulf views and direct access to the sand.',
                'description_ar' => 'فيلا من ست غرف نوم على الشاطئ مباشرة، مع مسبح خاص وإطلالة بانورامية على الخليج وإمكانية الوصول المباشر إلى الرمال.',
                'price' => 42500000, 'area' => 9800, 'bedrooms' => 6, 'bathrooms' => 7,
                'district' => 'Palm Jumeirah', 'latitude' => 25.1124, 'longitude' => 55.1390,
                'featured' => true, 'amenities' => ['Swimming Pool', 'Parking', '24/7 Security', 'Balcony'],
            ],
            [
                'slug' => 'downtown-sky-penthouse',
                'type' => 'Apartment', 'city' => 'Dubai', 'operation' => 'sale',
                'title_en' => 'Sky Penthouse with Burj Khalifa Views',
                'title_ar' => 'بنتهاوس سكاي بإطلالة على برج خليفة',
                'description_en' => 'A full-floor penthouse in the heart of Downtown Dubai with floor-to-ceiling glass and a wraparound terrace.',
                'description_ar' => 'بنتهاوس بمساحة طابق كامل في قلب وسط مدينة دبي، بواجهات زجاجية من الأرض إلى السقف وشرفة تحيط بالوحدة.',
                'price' => 28900000, 'area' => 6200, 'bedrooms' => 4, 'bathrooms' => 5,
                'district' => 'Downtown Dubai', 'latitude' => 25.1972, 'longitude' => 55.2744,
                'featured' => true, 'amenities' => ['Gym', 'Parking', '24/7 Security', 'Balcony'],
            ],
            [
                'slug' => 'dubai-marina-waterfront-apartment',
                'type' => 'Apartment', 'city' => 'Dubai', 'operation' => 'rent',
                'title_en' => 'Waterfront Apartment in Dubai Marina',
                'title_ar' => 'شقة على الواجهة المائية في دبي مارينا',
                'description_en' => 'A bright two-bedroom apartment overlooking the marina promenade, steps from the tram and the beach.',
                'description_ar' => 'شقة مشرقة من غرفتي نوم تطل على ممشى المارينا، على بعد خطوات من الترام والشاطئ.',
                'price' => 165000, 'area' => 1450, 'bedrooms' => 2, 'bathrooms' => 3,
                'district' => 'Dubai Marina', 'latitude' => 25.0805, 'longitude' => 55.1403,
                'featured' => false, 'amenities' => ['Swimming Pool', 'Gym', 'Parking', 'Balcony'],
            ],
            [
                'slug' => 'emirates-hills-contemporary-mansion',
                'type' => 'Villa', 'city' => 'Dubai', 'operation' => 'sale',
                'title_en' => 'Contemporary Mansion in Emirates Hills',
                'title_ar' => 'قصر عصري في تلال الإمارات',
                'description_en' => 'A seven-bedroom mansion on a golf-course plot with a cinema room, landscaped gardens and a staff wing.',
                'description_ar' => 'قصر من سبع غرف نوم على قطعة أرض مطلة على ملعب الغولف، مع غرفة سينما وحدائق منسقة وجناح للموظفين.',
                'price' => 68000000, 'area' => 16500, 'bedrooms' => 7, 'bathrooms' => 9,
                'district' => 'Emirates Hills', 'latitude' => 25.0657, 'longitude' => 55.1713,
                'featured' => true, 'amenities' => ['Swimming Pool', 'Gym', 'Parking', '24/7 Security'],
            ],
            [
                'slug' => 'business-bay-grade-a-office',
                'type' => 'Office', 'city' => 'Dubai', 'operation' => 'rent',
                'title_en' => 'Grade-A Office in Business Bay',
                'title_ar' => 'مكتب من الفئة الأولى في الخليج التجاري',
                'description_en' => 'A fitted open-plan office floor with canal views, meeting rooms and reserved parking.',
                'description_ar' => 'طابق مكتبي مفتوح مجهّز بإطلالة على القناة، مع قاعات اجتماعات ومواقف سيارات مخصصة.',
                'price' => 480000, 'area' => 3800, 'bedrooms' => null, 'bathrooms' => 4,
                'district' => 'Business Bay', 'latitude' => 25.1850, 'longitude' => 55.2610,
                'featured' => false, 'amenities' => ['Parking', '24/7 Security'],
            ],
            [
                'slug' => 'saadiyat-island-beach-villa',
                'type' => 'Villa', 'city' => 'Abu Dhabi', 'operation' => 'sale',
                'title_en' => 'Beach Villa on Saadiyat Island',
                'title_ar' => 'فيلا على الشاطئ في جزيرة السعديات',
                'description_en' => 'A five-bedroom villa moments from the Louvre, with a private beach club membership and a landscaped courtyard.',
                'description_ar' => 'فيلا من خمس غرف نوم على مقربة من متحف اللوفر، مع عضوية في نادٍ شاطئي خاص وفناء داخلي منسق.',
                'price' => 19500000, 'area' => 7400, 'bedrooms' => 5, 'bathrooms' => 6,
                'district' => 'Saadiyat Island', 'latitude' => 24.5340, 'longitude' => 54.4340,
                'featured' => false, 'amenities' => ['Swimming Pool', 'Parking', '24/7 Security'],
            ],
            [
                'slug' => 'sharjah-freehold-land-plot',
                'type' => 'Land', 'city' => 'Sharjah', 'operation' => 'sale',
                'title_en' => 'Freehold Residential Land in Sharjah',
                'title_ar' => 'أرض سكنية تملّك حر في الشارقة',
                'description_en' => 'A corner residential plot in a growing master community, ready for villa development.',
                'description_ar' => 'قطعة أرض سكنية على الزاوية ضمن مجتمع متكامل متنامٍ، جاهزة لبناء فيلا.',
                'price' => 3200000, 'area' => 10800, 'bedrooms' => null, 'bathrooms' => null,
                'district' => 'Al Zubair', 'latitude' => 25.3463, 'longitude' => 55.4209,
                'featured' => false, 'amenities' => [],
            ],
            [
                'slug' => 'al-marjan-island-resort-apartment',
                'type' => 'Apartment', 'city' => 'Ras Al Khaimah', 'operation' => 'sale',
                'title_en' => 'Resort Apartment on Al Marjan Island',
                'title_ar' => 'شقة منتجعية في جزيرة المرجان',
                'description_en' => 'A one-bedroom sea-view apartment inside a branded resort, with access to the pools and private beach.',
                'description_ar' => 'شقة من غرفة نوم واحدة بإطلالة على البحر داخل منتجع بعلامة عالمية، مع إمكانية استخدام المسابح والشاطئ الخاص.',
                'price' => 1450000, 'area' => 780, 'bedrooms' => 1, 'bathrooms' => 2,
                'district' => 'Al Marjan Island', 'latitude' => 25.6700, 'longitude' => 55.7500,
                'featured' => false, 'amenities' => ['Swimming Pool', 'Gym', 'Balcony'],
            ],
        ];

        Property::unguarded(function () use ($listings, $owner, $types, $cities, $amenities) {
            foreach ($listings as $data) {
                if (! isset($types[$data['type']], $cities[$data['city']])) {
                    $this->command?->warn("Skipping {$data['slug']}: run MasterDataSeeder first.");
                    continue;
                }

                $property = Property::updateOrCreate(
                    ['slug' => $data['slug']],
                    [
                        'owner_id' => $owner->id,
                        'type_id' => $types[$data['type']],
                        'city_id' => $cities[$data['city']],
                        'title_en' => $data['title_en'],
                        'title_ar' => $data['title_ar'],
                        'description_en' => $data['description_en'],
                        'description_ar' => $data['description_ar'],
                        'original_language' => 'en',
                        'operation' => $data['operation'],
                        'price' => $data['price'],
                        'area' => $data['area'],
                        'bedrooms' => $data['bedrooms'],
                        'bathrooms' => $data['bathrooms'],
                        'district' => $data['district'],
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude'],
                        'status' => 'published',
                        'is_featured' => $data['featured'],
                    ]
                );

                $property->amenities()->sync(
                    collect($data['amenities'])->map(fn ($name) => $amenities[$name] ?? null)->filter()->values()->all()
                );
            }
        });
    }
}
