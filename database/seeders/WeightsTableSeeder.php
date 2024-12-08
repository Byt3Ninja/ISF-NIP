<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class WeightsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // مجموعة البيانات التي تحتوي على أسماء stages, categories, و subcategories
        $data = [
            [ //1
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Idea',
                'stage_name' => 'Idea/Ideation',
                'weight' => 30,
            ],
            [//2
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Product Portfolio',
                'stage_name' => 'Idea/Ideation',
                'weight' => 25,
            ],
            [ //3
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Model',
                'stage_name' => 'Idea/Ideation',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Customer Portfolio',
                'stage_name' => 'Idea/Ideation',
                'weight' => 15,
            ],
            [ //5
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Market Position',
                'stage_name' => 'Idea/Ideation',
                'weight' => 10,
            ],
            // Stage 2
            [ //1
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Idea',
                'stage_name' => 'Research & Development',
                'weight' => 30,
            ],
            [//2
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Product Portfolio',
                'stage_name' => 'Research & Development',
                'weight' => 25,
            ],
            [ //3
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Model',
                'stage_name' => 'Research & Development',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Customer Portfolio',
                'stage_name' => 'Research & Development',
                'weight' => 15,
            ],
            [ //5
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Market Position',
                'stage_name' => 'Research & Development',
                'weight' => 10,
            ],
            // Stage 3
            [ //1
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Idea',
                'stage_name' => 'Validation',
                'weight' => 30,
            ],
            [//2
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Product Portfolio',
                'stage_name' => 'Validation',
                'weight' => 25,
            ],
            [ //3
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Model',
                'stage_name' => 'Validation',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Customer Portfolio',
                'stage_name' => 'Validation',
                'weight' => 15,
            ],
            [ //5
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Market Position',
                'stage_name' => 'Research & Development',
                'weight' => 10,
            ],
            // Stage 4
            [ //1
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Idea',
                'stage_name' => 'Prototype/MVP',
                'weight' => 15,
            ],
            [//2
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Product Portfolio',
                'stage_name' => 'Prototype/MVP',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Model',
                'stage_name' => 'Prototype/MVP',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Customer Portfolio',
                'stage_name' => 'Prototype/MVP',
                'weight' => 25,
            ],
            [ //5
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Market Position',
                'stage_name' => 'Prototype/MVP',
                'weight' => 20,
            ],
            // Stage 5
            [ //1
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Idea',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 10,
            ],
            [//2
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Product Portfolio',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 15,
            ],
            [ //3
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Model',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Customer Portfolio',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 30,
            ],
            [ //5
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Market Position',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 25,
            ],
            // Stage 6
            [ //1
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Idea',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 10,
            ],
            [//2
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Product Portfolio',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 15,
            ],
            [ //3
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Model',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Customer Portfolio',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 30,
            ],
            [ //5
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Market Position',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 25,
            ],
            // Stage 7
            [ //1
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Idea',
                'stage_name' => 'Scale/Expansion',
                'weight' => 10,
            ],
            [//2
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Product Portfolio',
                'stage_name' => 'Scale/Expansion',
                'weight' => 15,
            ],
            [ //3
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Model',
                'stage_name' => 'Scale/Expansion',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Customer Portfolio',
                'stage_name' => 'Scale/Expansion',
                'weight' => 30,
            ],
            [ //5
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Market Position',
                'stage_name' => 'Scale/Expansion',
                'weight' => 25,
            ],
            // Stage 8
            [ //1
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Idea',
                'stage_name' => 'Maturity',
                'weight' => 10,
            ],
            [//2
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Product Portfolio',
                'stage_name' => 'Maturity',
                'weight' => 15,
            ],
            [ //3
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Business Model',
                'stage_name' => 'Maturity',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Customer Portfolio',
                'stage_name' => 'Maturity',
                'weight' => 30,
            ],
            [ //5
                'category_name' => 'Business Concept',
                'subcategory_name' => 'Market Position',
                'stage_name' => 'Maturity',
                'weight' => 25,
            ],
            // Stage 1 & Category 2
            [ //1
                'category_name' => 'Organisation',
                'subcategory_name' => 'Ownergroup & Board',
                'stage_name' => 'Idea/Ideation',
                'weight' => 30,
            ],
            [//2
                'category_name' => 'Organisation',
                'subcategory_name' => 'Employees',
                'stage_name' => 'Idea/Ideation',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Organisation',
                'subcategory_name' => 'Business Processes',
                'stage_name' => 'Idea/Ideation',
                'weight' => 25,
            ],
            [ //4
                'category_name' => 'Organisation',
                'subcategory_name' => 'Partnerships',
                'stage_name' => 'Idea/Ideation',
                'weight' => 15,
            ],
            [ //5
                'category_name' => 'Organisation',
                'subcategory_name' => 'Legal Issues',
                'stage_name' => 'Idea/Ideation',
                'weight' => 10,
            ],
            // Stage 2
            [ //1
                'category_name' => 'Organisation',
                'subcategory_name' => 'Ownergroup & Board',
                'stage_name' => 'Research & Development',
                'weight' => 30,
            ],
            [//2
                'category_name' => 'Organisation',
                'subcategory_name' => 'Employees',
                'stage_name' => 'Research & Development',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Organisation',
                'subcategory_name' => 'Business Processes',
                'stage_name' => 'Research & Development',
                'weight' => 25,
            ],
            [ //4
                'category_name' => 'Organisation',
                'subcategory_name' => 'Partnerships',
                'stage_name' => 'Research & Development',
                'weight' => 15,
            ],
            [ //5
                'category_name' => 'Organisation',
                'subcategory_name' => 'Legal Issues',
                'stage_name' => 'Research & Development',
                'weight' => 10,
            ],
            // Stage 3
            [ //1
                'category_name' => 'Organisation',
                'subcategory_name' => 'Ownergroup & Board',
                'stage_name' => 'Validation',
                'weight' => 30,
            ],
            [//2
                'category_name' => 'Organisation',
                'subcategory_name' => 'Employees',
                'stage_name' => 'Validation',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Organisation',
                'subcategory_name' => 'Business Processes',
                'stage_name' => 'Validation',
                'weight' => 25,
            ],
            [ //4
                'category_name' => 'Organisation',
                'subcategory_name' => 'Partnerships',
                'stage_name' => 'Validation',
                'weight' => 15,
            ],
            [ //5
                'category_name' => 'Organisation',
                'subcategory_name' => 'Legal Issues',
                'stage_name' => 'Research & Development',
                'weight' => 10,
            ],
            // Stage 4
            [ //1
                'category_name' => 'Organisation',
                'subcategory_name' => 'Ownergroup & Board',
                'stage_name' => 'Prototype/MVP',
                'weight' => 15,
            ],
            [//2
                'category_name' => 'Organisation',
                'subcategory_name' => 'Employees',
                'stage_name' => 'Prototype/MVP',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Organisation',
                'subcategory_name' => 'Business Processes',
                'stage_name' => 'Prototype/MVP',
                'weight' => 35,
            ],
            [ //4
                'category_name' => 'Organisation',
                'subcategory_name' => 'Partnerships',
                'stage_name' => 'Prototype/MVP',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Organisation',
                'subcategory_name' => 'Legal Issues',
                'stage_name' => 'Prototype/MVP',
                'weight' => 10,
            ],
            // Stage 5
            [ //1
                'category_name' => 'Organisation',
                'subcategory_name' => 'Ownergroup & Board',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 10,
            ],
            [//2
                'category_name' => 'Organisation',
                'subcategory_name' => 'Employees',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Organisation',
                'subcategory_name' => 'Business Processes',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 40,
            ],
            [ //4
                'category_name' => 'Organisation',
                'subcategory_name' => 'Partnerships',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Organisation',
                'subcategory_name' => 'Legal Issues',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 10,
            ],
            // Stage 6
            [ //1
                'category_name' => 'Organisation',
                'subcategory_name' => 'Ownergroup & Board',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 10,
            ],
            [//2
                'category_name' => 'Organisation',
                'subcategory_name' => 'Employees',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Organisation',
                'subcategory_name' => 'Business Processes',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 40,
            ],
            [ //4
                'category_name' => 'Organisation',
                'subcategory_name' => 'Partnerships',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Organisation',
                'subcategory_name' => 'Legal Issues',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 10,
            ],
            // Stage 7
            [ //1
                'category_name' => 'Organisation',
                'subcategory_name' => 'Ownergroup & Board',
                'stage_name' => 'Scale/Expansion',
                'weight' => 10,
            ],
            [//2
                'category_name' => 'Organisation',
                'subcategory_name' => 'Employees',
                'stage_name' => 'Scale/Expansion',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Organisation',
                'subcategory_name' => 'Business Processes',
                'stage_name' => 'Scale/Expansion',
                'weight' => 40,
            ],
            [ //4
                'category_name' => 'Organisation',
                'subcategory_name' => 'Partnerships',
                'stage_name' => 'Scale/Expansion',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Organisation',
                'subcategory_name' => 'Legal Issues',
                'stage_name' => 'Scale/Expansion',
                'weight' => 10,
            ],
            // Stage 8
            [ //1
                'category_name' => 'Organisation',
                'subcategory_name' => 'Ownergroup & Board',
                'stage_name' => 'Maturity',
                'weight' => 10,
            ],
            [//2
                'category_name' => 'Organisation',
                'subcategory_name' => 'Employees',
                'stage_name' => 'Maturity',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Organisation',
                'subcategory_name' => 'Business Processes',
                'stage_name' => 'Maturity',
                'weight' => 40,
            ],
            [ //4
                'category_name' => 'Organisation',
                'subcategory_name' => 'Partnerships',
                'stage_name' => 'Maturity',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Organisation',
                'subcategory_name' => 'Legal Issues',
                'stage_name' => 'Maturity',
                'weight' => 10,
            ],

            // Stage 1 & Category 3
            [ //1
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Network',
                'stage_name' => 'Idea/Ideation',
                'weight' => 20,
            ],
            [//2
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Marketing',
                'stage_name' => 'Idea/Ideation',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Sales',
                'stage_name' => 'Idea/Ideation',
                'weight' => 25,
            ],
            [ //4
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Communications & PR',
                'stage_name' => 'Idea/Ideation',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Branding',
                'stage_name' => 'Idea/Ideation',
                'weight' => 15,
            ],
            // Stage 2
            [ //1
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Network',
                'stage_name' => 'Research & Development',
                'weight' => 20,
            ],
            [//2
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Marketing',
                'stage_name' => 'Research & Development',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Sales',
                'stage_name' => 'Research & Development',
                'weight' => 25,
            ],
            [ //4
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Communications & PR',
                'stage_name' => 'Research & Development',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Branding',
                'stage_name' => 'Research & Development',
                'weight' => 15,
            ],
            // Stage 3
            [ //1
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Network',
                'stage_name' => 'Validation',
                'weight' => 20,
            ],
            [//2
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Marketing',
                'stage_name' => 'Validation',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Sales',
                'stage_name' => 'Validation',
                'weight' => 25,
            ],
            [ //4
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Communications & PR',
                'stage_name' => 'Validation',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Branding',
                'stage_name' => 'Research & Development',
                'weight' => 15,
            ],
            // Stage 4
            [ //1
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Network',
                'stage_name' => 'Prototype/MVP',
                'weight' => 30,
            ],
            [//2
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Marketing',
                'stage_name' => 'Prototype/MVP',
                'weight' => 30,
            ],
            [ //3
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Sales',
                'stage_name' => 'Prototype/MVP',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Communications & PR',
                'stage_name' => 'Prototype/MVP',
                'weight' => 15,
            ],
            [ //5
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Branding',
                'stage_name' => 'Prototype/MVP',
                'weight' => 10,
            ],
            // Stage 5
            [ //1
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Network',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 25,
            ],
            [//2
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Marketing',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 30,
            ],
            [ //3
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Sales',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Communications & PR',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 15,
            ],
            [ //5
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Branding',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 10,
            ],
            // Stage 6
            [ //1
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Network',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 25,
            ],
            [//2
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Marketing',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 30,
            ],
            [ //3
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Sales',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Communications & PR',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 15,
            ],
            [ //5
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Branding',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 10,
            ],
            // Stage 7
            [ //1
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Network',
                'stage_name' => 'Scale/Expansion',
                'weight' => 25,
            ],
            [//2
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Marketing',
                'stage_name' => 'Scale/Expansion',
                'weight' => 30,
            ],
            [ //3
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Sales',
                'stage_name' => 'Scale/Expansion',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Communications & PR',
                'stage_name' => 'Scale/Expansion',
                'weight' => 15,
            ],
            [ //5
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Branding',
                'stage_name' => 'Scale/Expansion',
                'weight' => 10,
            ],
            // Stage 8
            [ //1
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Network',
                'stage_name' => 'Maturity',
                'weight' => 25,
            ],
            [//2
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Marketing',
                'stage_name' => 'Maturity',
                'weight' => 30,
            ],
            [ //3
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Sales',
                'stage_name' => 'Maturity',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Communications & PR',
                'stage_name' => 'Maturity',
                'weight' => 15,
            ],
            [ //5
                'category_name' => 'Customer Relations',
                'subcategory_name' => 'Branding',
                'stage_name' => 'Maturity',
                'weight' => 10,
            ],

            // Stage 1 & Category 4
            [ //1
                'category_name' => 'Operations',
                'subcategory_name' => 'Management Accounting',
                'stage_name' => 'Idea/Ideation',
                'weight' => 30,
            ],
            [//2
                'category_name' => 'Operations',
                'subcategory_name' => 'Financing',
                'stage_name' => 'Idea/Ideation',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Operations',
                'subcategory_name' => 'Production Management',
                'stage_name' => 'Idea/Ideation',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Operations',
                'subcategory_name' => 'IT Systems',
                'stage_name' => 'Idea/Ideation',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Operations',
                'subcategory_name' => 'Facilities',
                'stage_name' => 'Idea/Ideation',
                'weight' => 10,
            ],
            // Stage 2
            [ //1
                'category_name' => 'Operations',
                'subcategory_name' => 'Management Accounting',
                'stage_name' => 'Research & Development',
                'weight' => 30,
            ],
            [//2
                'category_name' => 'Operations',
                'subcategory_name' => 'Financing',
                'stage_name' => 'Research & Development',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Operations',
                'subcategory_name' => 'Production Management',
                'stage_name' => 'Research & Development',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Operations',
                'subcategory_name' => 'IT Systems',
                'stage_name' => 'Research & Development',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Operations',
                'subcategory_name' => 'Facilities',
                'stage_name' => 'Research & Development',
                'weight' => 10,
            ],
            // Stage 3
            [ //1
                'category_name' => 'Operations',
                'subcategory_name' => 'Management Accounting',
                'stage_name' => 'Validation',
                'weight' => 30,
            ],
            [//2
                'category_name' => 'Operations',
                'subcategory_name' => 'Financing',
                'stage_name' => 'Validation',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Operations',
                'subcategory_name' => 'Production Management',
                'stage_name' => 'Validation',
                'weight' => 20,
            ],
            [ //4
                'category_name' => 'Operations',
                'subcategory_name' => 'IT Systems',
                'stage_name' => 'Validation',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Operations',
                'subcategory_name' => 'Facilities',
                'stage_name' => 'Research & Development',
                'weight' => 10,
            ],
            // Stage 4
            [ //1
                'category_name' => 'Operations',
                'subcategory_name' => 'Management Accounting',
                'stage_name' => 'Prototype/MVP',
                'weight' => 20,
            ],
            [//2
                'category_name' => 'Operations',
                'subcategory_name' => 'Financing',
                'stage_name' => 'Prototype/MVP',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Operations',
                'subcategory_name' => 'Production Management',
                'stage_name' => 'Prototype/MVP',
                'weight' => 30,
            ],
            [ //4
                'category_name' => 'Operations',
                'subcategory_name' => 'IT Systems',
                'stage_name' => 'Prototype/MVP',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Operations',
                'subcategory_name' => 'Facilities',
                'stage_name' => 'Prototype/MVP',
                'weight' => 10,
            ],
            // Stage 5
            [ //1
                'category_name' => 'Operations',
                'subcategory_name' => 'Management Accounting',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 20,
            ],
            [//2
                'category_name' => 'Operations',
                'subcategory_name' => 'Financing',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Operations',
                'subcategory_name' => 'Production Management',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 30,
            ],
            [ //4
                'category_name' => 'Operations',
                'subcategory_name' => 'IT Systems',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Operations',
                'subcategory_name' => 'Facilities',
                'stage_name' => 'Beta/Soft Launch',
                'weight' => 10,
            ],
            // Stage 6
            [ //1
                'category_name' => 'Operations',
                'subcategory_name' => 'Management Accounting',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 20,
            ],
            [//2
                'category_name' => 'Operations',
                'subcategory_name' => 'Financing',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Operations',
                'subcategory_name' => 'Production Management',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 30,
            ],
            [ //4
                'category_name' => 'Operations',
                'subcategory_name' => 'IT Systems',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Operations',
                'subcategory_name' => 'Facilities',
                'stage_name' => 'Ready Product/Operating',
                'weight' => 10,
            ],
            // Stage 7
            [ //1
                'category_name' => 'Operations',
                'subcategory_name' => 'Management Accounting',
                'stage_name' => 'Scale/Expansion',
                'weight' => 20,
            ],
            [//2
                'category_name' => 'Operations',
                'subcategory_name' => 'Financing',
                'stage_name' => 'Scale/Expansion',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Operations',
                'subcategory_name' => 'Production Management',
                'stage_name' => 'Scale/Expansion',
                'weight' => 30,
            ],
            [ //4
                'category_name' => 'Operations',
                'subcategory_name' => 'IT Systems',
                'stage_name' => 'Scale/Expansion',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Operations',
                'subcategory_name' => 'Facilities',
                'stage_name' => 'Scale/Expansion',
                'weight' => 10,
            ],
            // Stage 8
            [ //1
                'category_name' => 'Operations',
                'subcategory_name' => 'Management Accounting',
                'stage_name' => 'Maturity',
                'weight' => 20,
            ],
            [//2
                'category_name' => 'Operations',
                'subcategory_name' => 'Financing',
                'stage_name' => 'Maturity',
                'weight' => 20,
            ],
            [ //3
                'category_name' => 'Operations',
                'subcategory_name' => 'Production Management',
                'stage_name' => 'Maturity',
                'weight' => 30,
            ],
            [ //4
                'category_name' => 'Operations',
                'subcategory_name' => 'IT Systems',
                'stage_name' => 'Maturity',
                'weight' => 20,
            ],
            [ //5
                'category_name' => 'Operations',
                'subcategory_name' => 'Facilities',
                'stage_name' => 'Maturity',
                'weight' => 10,
            ],

        ];

        foreach ($data as $entry) {
            // جلب الـ IDs بناءً على الأسماء
            $stage = DB::table('stages')->where('name_en', $entry['stage_name'])->first();
            $category = DB::table('categories')->where('category_name_en', $entry['category_name'])->first();
            $subcategory = DB::table('sub_categories')->where('name_en', $entry['subcategory_name'])->first();

            $stageId = $stage ? $stage->id : null;
            $categoryId = $category ? $category->id : null;
            $subcategoryId = $subcategory ? $subcategory->id : null;

            // التحقق من وجود IDs
            if ($stageId && $categoryId && $subcategoryId) {
                // إدخال البيانات في جدول weights
                DB::table('weights')->insert([
                    'stage_id' => $stageId,
                    'category_id' => $categoryId,
                    'subcategory_id' => $subcategoryId,
                    'weight' => $entry['weight'],
                ]);

                $this->command->info("Data inserted successfully for stage: {$entry['stage_name']}, category: {$entry['category_name']}, subcategory: {$entry['subcategory_name']}.");
            } else {
                $this->command->error("Missing IDs for stage: {$entry['stage_name']}, category: {$entry['category_name']}, subcategory: {$entry['subcategory_name']}.");
            }
        }
    }
}
