<?php

namespace Database\Seeders;

use App\Company;
use App\Cycle;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get existing cycles
        $cycles = Cycle::all();
        if ($cycles->isEmpty()) {
            $this->command->error('No cycles found! Please run CycleSeeder first.');
            return;
        }

        // Fake company data
        $companiesData = [
            // Innovation Accelerator Program companies
            [
                'name_en' => 'TechSolutions Inc',
                'name_ar' => 'تك سوليوشنز',
                'address_en' => '123 Tech Street, Cairo',
                'address_ar' => '123 شارع التكنولوجيا، القاهرة',
                'phone' => '+201234567890',
                'email' => 'contact@techsolutions.com',
                'sector' => 'Technology',
                'stage' => 'Seed',
                'development_stage' => 'MVP',
                'business_model' => 'SaaS',
                'cycle_id' => $cycles->where('title', 'Innovation Accelerator Program 2024')->first()->id,
            ],
            [
                'name_en' => 'GreenEnergy Co',
                'name_ar' => 'غرين إنرجي',
                'address_en' => '456 Green Avenue, Alexandria',
                'address_ar' => '456 شارع الأخضر، الإسكندرية',
                'phone' => '+201234567891',
                'email' => 'info@greenenergy.com',
                'sector' => 'Energy',
                'stage' => 'Seed',
                'development_stage' => 'Prototype',
                'business_model' => 'B2B',
                'cycle_id' => $cycles->where('title', 'Innovation Accelerator Program 2024')->first()->id,
            ],
            [
                'name_en' => 'EduLearn Platform',
                'name_ar' => 'منصة إدو ليرن',
                'address_en' => '789 Education Road, Giza',
                'address_ar' => '789 طريق التعليم، الجيزة',
                'phone' => '+201234567892',
                'email' => 'hello@edulearn.com',
                'sector' => 'Education',
                'stage' => 'Early Stage',
                'development_stage' => 'Beta',
                'business_model' => 'Marketplace',
                'cycle_id' => $cycles->where('title', 'Innovation Accelerator Program 2024')->first()->id,
            ],

            // Digital Transformation Challenge companies
            [
                'name_en' => 'DigitalBoost Agency',
                'name_ar' => 'وكالة ديجيتال بوست',
                'address_en' => '321 Digital Plaza, Cairo',
                'address_ar' => '321 مول الرقمي، القاهرة',
                'phone' => '+201234567893',
                'email' => 'contact@digitalboost.com',
                'sector' => 'Marketing',
                'stage' => 'Growth',
                'development_stage' => 'Product',
                'business_model' => 'Service',
                'cycle_id' => $cycles->where('title', 'Digital Transformation Challenge')->first()->id,
            ],
            [
                'name_en' => 'AI Analytics Pro',
                'name_ar' => 'ذكاء اصطناعي أناليتيكس برو',
                'address_en' => '654 AI Boulevard, Alexandria',
                'address_ar' => '654 شارع الذكاء الاصطناعي، الإسكندرية',
                'phone' => '+201234567894',
                'email' => 'info@aianalytics.com',
                'sector' => 'Technology',
                'stage' => 'Seed',
                'development_stage' => 'MVP',
                'business_model' => 'SaaS',
                'cycle_id' => $cycles->where('title', 'Digital Transformation Challenge')->first()->id,
            ],

            // Sustainable Tech Initiative companies
            [
                'name_en' => 'EcoBuild Materials',
                'name_ar' => 'مواد إيكو بيلد',
                'address_en' => '987 Eco Street, Cairo',
                'address_ar' => '987 شارع البيئة، القاهرة',
                'phone' => '+201234567895',
                'email' => 'sales@ecobuild.com',
                'sector' => 'Construction',
                'stage' => 'Early Stage',
                'development_stage' => 'Prototype',
                'business_model' => 'B2B',
                'cycle_id' => $cycles->where('title', 'Sustainable Tech Initiative')->first()->id,
            ],
            [
                'name_en' => 'SolarFarm Solutions',
                'name_ar' => 'حلول سولار فارم',
                'address_en' => '147 Solar Road, Giza',
                'address_ar' => '147 طريق الشمس، الجيزة',
                'phone' => '+201234567896',
                'email' => 'info@solarfarm.com',
                'sector' => 'Energy',
                'stage' => 'Growth',
                'development_stage' => 'Product',
                'business_model' => 'B2C',
                'cycle_id' => $cycles->where('title', 'Sustainable Tech Initiative')->first()->id,
            ],
            [
                'name_en' => 'WasteWise Systems',
                'name_ar' => 'نظم وايست وايز',
                'address_en' => '258 Waste Management Ave, Alexandria',
                'address_ar' => '258 شارع إدارة النفايات، الإسكندرية',
                'phone' => '+201234567897',
                'email' => 'contact@wastewise.com',
                'sector' => 'Environment',
                'stage' => 'Seed',
                'development_stage' => 'MVP',
                'business_model' => 'B2B',
                'cycle_id' => $cycles->where('title', 'Sustainable Tech Initiative')->first()->id,
            ],

            // Additional companies for Innovation Program
            [
                'name_en' => 'FinTech Innovations',
                'name_ar' => 'فين تك إينوفيشنز',
                'address_en' => '369 Finance District, Cairo',
                'address_ar' => '369 منطقة المال، القاهرة',
                'phone' => '+201234567898',
                'email' => 'hello@fintechinnovations.com',
                'sector' => 'Finance',
                'stage' => 'Seed',
                'development_stage' => 'MVP',
                'business_model' => 'SaaS',
                'cycle_id' => $cycles->where('title', 'Innovation Accelerator Program 2024')->first()->id,
            ],
            [
                'name_en' => 'HealthTrack App',
                'name_ar' => 'تطبيق هيلث تراك',
                'address_en' => '741 Health Tech Park, Giza',
                'address_ar' => '741 حديقة التكنولوجيا الصحية، الجيزة',
                'phone' => '+201234567899',
                'email' => 'support@healthtrack.com',
                'sector' => 'Healthcare',
                'stage' => 'Early Stage',
                'development_stage' => 'Beta',
                'business_model' => 'Mobile App',
                'cycle_id' => $cycles->where('title', 'Innovation Accelerator Program 2024')->first()->id,
            ],
            [
                'name_en' => 'AgriTech Solutions',
                'name_ar' => 'حلول أغري تك',
                'address_en' => '852 Agriculture Valley, Alexandria',
                'address_ar' => '852 وادي الزراعة، الإسكندرية',
                'phone' => '+201234567800',
                'email' => 'info@agritech.com',
                'sector' => 'Agriculture',
                'stage' => 'Growth',
                'development_stage' => 'Product',
                'business_model' => 'B2B',
                'cycle_id' => $cycles->where('title', 'Innovation Accelerator Program 2024')->first()->id,
            ],
            [
                'name_en' => 'LogiChain Systems',
                'name_ar' => 'نظم لوغي تشين',
                'address_en' => '963 Logistics Hub, Cairo',
                'address_ar' => '963 مركز اللوجستيات، القاهرة',
                'phone' => '+201234567801',
                'email' => 'contact@logichain.com',
                'sector' => 'Logistics',
                'stage' => 'Seed',
                'development_stage' => 'Prototype',
                'business_model' => 'SaaS',
                'cycle_id' => $cycles->where('title', 'Innovation Accelerator Program 2024')->first()->id,
            ],
        ];

        foreach ($companiesData as $companyData) {
            Company::create([
                'name_en' => $companyData['name_en'],
                'name_ar' => $companyData['name_ar'],
                'address_en' => $companyData['address_en'],
                'address_ar' => $companyData['address_ar'],
                'phone' => $companyData['phone'],
                'email' => $companyData['email'],
                'sector' => $companyData['sector'],
                'stage' => $companyData['stage'],
                'development_stage' => $companyData['development_stage'],
                'business_model' => $companyData['business_model'],
                'cycle' => $companyData['cycle_id'],
                'approved' => 1,
                'country_id' => 1, // Assuming Egypt or first country
                'state_id' => 1,
                'city_id' => 1,
                'est_date' => now()->subYears(rand(1, 5)),
                'years_of_operations' => rand(1, 5),
                'product_or_service' => 'Product',
                'info_company' => 'A test company for development purposes.',
                'business_categories' => json_encode(['Technology', 'Innovation']),
                'team_num' => rand(3, 15),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Fake companies created and assigned to programs successfully!');
        $this->command->info('Created companies for:');
        foreach ($cycles as $cycle) {
            $count = Company::where('cycle', $cycle->id)->count();
            $this->command->info("- {$cycle->title}: {$count} companies");
        }
    }
}
