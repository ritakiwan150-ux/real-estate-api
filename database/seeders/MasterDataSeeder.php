<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\City;
use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $propertyTypes = [
            ['name_ar' => 'شقة', 'name_en' => 'Apartment'],
            ['name_ar' => 'فيلا', 'name_en' => 'Villa'],
            ['name_ar' => 'مكتب', 'name_en' => 'Office'],
            ['name_ar' => 'أرض', 'name_en' => 'Land'],
            ['name_ar' => 'محل تجاري', 'name_en' => 'Retail Shop'],
        ];

        foreach ($propertyTypes as $type) {
            PropertyType::firstOrCreate(['name_en' => $type['name_en']], $type);
        }

        $cities = [
            ['name_ar' => 'دبي', 'name_en' => 'Dubai'],
            ['name_ar' => 'أبوظبي', 'name_en' => 'Abu Dhabi'],
            ['name_ar' => 'الشارقة', 'name_en' => 'Sharjah'],
            ['name_ar' => 'عجمان', 'name_en' => 'Ajman'],
            ['name_ar' => 'رأس الخيمة', 'name_en' => 'Ras Al Khaimah'],
        ];

        foreach ($cities as $city) {
            City::firstOrCreate(['name_en' => $city['name_en']], $city);
        }

        $amenities = [
            ['name_ar' => 'مسبح', 'name_en' => 'Swimming Pool'],
            ['name_ar' => 'صالة رياضية', 'name_en' => 'Gym'],
            ['name_ar' => 'موقف سيارات', 'name_en' => 'Parking'],
            ['name_ar' => 'أمن على مدار الساعة', 'name_en' => '24/7 Security'],
            ['name_ar' => 'شرفة', 'name_en' => 'Balcony'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(['name_en' => $amenity['name_en']], $amenity);
        }
    }
}
