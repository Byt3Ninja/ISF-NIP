<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TranslateArabicNamesSeeder extends Seeder
{
    /**
     * Translate non-Arabic name_ar values in countries, states, and cities tables.
     */
    public function run(): void
    {
        $this->translateCountries();
        $this->translateStates();
        $this->translateCities();
    }

    private function translateCountries(): void
    {
        $translations = [
            'Egypt' => 'مصر',
            'Saudi Aribya' => 'المملكة العربية السعودية',
            'UAE' => 'الإمارات العربية المتحدة',
            'Saudi Arbia' => 'المملكة العربية السعودية',
            'USA' => 'الولايات المتحدة الأمريكية',
            'Canada' => 'كندا',
        ];

        $this->updateTable('countries', $translations);
    }

    private function translateStates(): void
    {
        $translations = [
            'Alexandria' => 'الإسكندرية',
            'Beheira' => 'البحيرة',
            'Cairo' => 'القاهرة',
            'Qalyubia' => 'القليوبية',
            'Sharqia' => 'الشرقية',
            'Tabuk' => 'تبوك',
            'Makkah' => 'مكة المكرمة',
            'Dubai' => 'دبي',
            'Ryadh' => 'الرياض',
            'California' => 'كاليفورنيا',
            'Ontario' => 'أونتاريو',
        ];

        $this->updateTable('states', $translations);
    }

    private function translateCities(): void
    {
        $translations = [
            // Bahrain
            'Madīnat ḩamad' => 'مدينة حمد',
            'ar rifā \'' => 'الرفاع',
            'Madīnat \'sá' => 'مدينة عيسى',

            // Egypt - Alexandria
            'AR-RAML' => 'الرمل',

            // Egypt - Aswan
            'IDFū' => 'إدفو',

            // Egypt - Asyut
            'asyūţ' => 'أسيوط',
            'Dayrūţ' => 'ديروط',
            'Manfalūţ' => 'منفلوط',

            // Egypt - Beheira
            'Damanhūr' => 'دمنهور',
            'ḩawsh īsá' => 'حوش عيسى',
            'idkū' => 'إدكو',
            'Kafr Ad Dawwār' => 'كفر الدوار',
            'kawm ḩamādah' => 'كوم حمادة',

            // Egypt - Cairo
            'bulq' => 'بولاق',
            'fustat' => 'الفسطاط',

            // Egypt - Dakahlia
            'ţalkhā' => 'طلخا',

            // Egypt - Damietta
            'AZ Zarqā' => 'الزرقاء',
            'Fāraskūr' => 'فارسكور',

            // Egypt - Faiyum
            'Ibshawāy' => 'إبشواي',
            'ţamiyah' => 'طامية',

            // Egypt - Gharbia
            'basyūn' => 'بسيون',
            'Kafr AZ Zayyāt' => 'كفر الزيات',
            'Quţūr' => 'قطور',
            'Zefta' => 'زفتى',

            // Egypt - Giza
            'ḩawāmidīyah' => 'الحوامدية',
            'owsīm' => 'أوسيم',
            'Madīnat sittah uktūbar' => 'مدينة السادس من أكتوبر',

            // Egypt - Kafr el-Sheikh
            'Disūq' => 'دسوق',
            'Fuwwah' => 'فوة',
            'Sīdī Sālim' => 'سيدي سالم',

            // Egypt - Luxor
            'Markaz al uqşur' => 'مركز الأقصر',

            // Egypt - Minya
            'Samālūţ' => 'سمالوط',

            // Egypt - Monufia
            'Quwaysinā' => 'قويسنا',

            // Egypt - Qalyubia
            'Qalyūb' => 'قليوب',

            // Egypt - Qena
            'dishnā' => 'دشنا',
            'isnā' => 'إسنا',

            // Egypt - Red Sea
            'quşayr' => 'القصير',
            'Safaga' => 'سفاجا',

            // Egypt - Sharqia
            'awlad saqr' => 'أولاد صقر',
            'bilbeis' => 'بلبيس',
            'DIYARB NEGM' => 'ديرب نجم',
            'Hihya' => 'ههيا',
            'Kafr Saqr' => 'كفر صقر',

            // Egypt - Sohag
            'ţahţā' => 'طهطا',

            // Egypt - South Sinai
            'TOR' => 'الطور',
            'Nuwaybi\'a' => 'نويبع',

            // Qatar
            'Ar Rayyān' => 'الريان',
            'Musay\'īd' => 'مسيعيد',
            'ar raways' => 'الرويس',
            'Fuwayriţ' => 'فويرط',
            'Madīnat Ash Shamāl' => 'مدينة الشمال',

            // Saudi Arabia - Asir
            'ma`riyah' => 'المعرية',
            'mifa' => 'ميفا',
            'Tabālah' => 'تبالة',

            // Saudi Arabia - Al Jawf
            'Isawiyah' => 'العيساوية',
            'şuwayr' => 'صوير',
            'ţubarjal' => 'طبرجل',

            // Saudi Arabia - Al Bahah
            '`ajmiyah' => 'العجمية',
            '`Ushash' => 'العشاش',
            '`sthayrah' => 'العشيرة',

            // Saudi Arabia - Al Bahah cities
            'Al Biqa \'' => 'البقع',
            'al multasa' => 'الملتسى',
            'Maqshush' => 'مقشوش',
            'Nujayl' => 'نجيل',
            'SUQ Suwayq' => 'سوق صويق',
            'Suqubiya' => 'سقبية',

            // Saudi Arabia - Al Qassim
            'adh dhibiyah' => 'الذيبية',
            'dukhnah' => 'دخنة',
            'qiba' => 'قبا',

            // Saudi Arabia - Eastern Province
            'ABQAIQ' => 'بقيق',
            'a ţaraf' => 'الطرف',
            'raḩīmah' => 'رحيمة',
            'şafwá' => 'صفوى',
            'udhailiyah' => 'العضيلية',
            'uqair' => 'العقير',

            // Saudi Arabia - Jizan
            'AD DARB' => 'الدرب',
            'AD Dur`iyah' => 'الدرعية',
            'adh dhagharir' => 'الذغارير',
            'al `usaylah' => 'العسيلة',
            'Ar Rukubah' => 'الركوبة',
            'Khumsiyah' => 'خمسية',
            'Qitabir' => 'قطابر',
            'Quwayda \'' => 'قويدع',
            'şabyā' => 'صبيا',
            'şāmitah' => 'صامطة',

            // Saudi Arabia - Makkah
            'al Kidwah' => 'الكدوة',
            'qustubah' => 'القسطبة',
            'Ar Rawdah Ash Shamaliyah' => 'الروضة الشمالية',
            'Ar Rudaymah' => 'الرديمة',
            'AZ ZAYMAH' => 'الزيمة',
            'AZ Zilal' => 'الظلال',
            'AZ Zurra`' => 'الزرع',
            'AZ Zuwayb' => 'الزويب',
            'dabyah' => 'ضبية',
            'dughaybjah' => 'دغبجة',
            'Murshidiyah' => 'المرشدية',
            'Nuzlat al faqin' => 'نزلة الفقين',
            'Quwayzah' => 'قويزة',
            'USFAN' => 'عسفان',

            // Saudi Arabia - Riyadh
            'AFIF' => 'عفيف',
            'Az zulfī' => 'الزلفي',
            'Tumayr' => 'تمير',

            // Saudi Arabia - Tabuk
            'Tabuk' => 'تبوك',

            // Saudi Arabia - Misc
            'muzayri \'' => 'مزيرع',

            // UAE - Ajman
            'masfout' => 'مصفوت',

            // UAE - Sharjah
            'ADH Dhayd' => 'الذيد',
            'Murbaḩ' => 'مربح',
        ];

        $this->updateTable('cities', $translations);
    }

    /**
     * Update name_ar for records whose current name_ar matches a key in the translations map.
     */
    private function updateTable(string $table, array $translations): void
    {
        $updated = 0;

        foreach ($translations as $currentNameAr => $arabicTranslation) {
            $affected = DB::table($table)
                ->where('name_ar', $currentNameAr)
                ->update(['name_ar' => $arabicTranslation]);

            $updated += $affected;
        }

        $this->command?->info("Updated {$updated} {$table} records with Arabic translations.");
    }
}
