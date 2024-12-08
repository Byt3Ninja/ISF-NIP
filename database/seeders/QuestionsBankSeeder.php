<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class QuestionsBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Insert main categories into 'categories' table
        $businessConceptCategoryId = DB::table('categories')->insertGetId([
            'category_name_ar' => 'مفهوم العمل',
            'category_name_en' => 'Business Concept',
            'category_description' => 'يتعلق بتعريف الفكرة الأساسية للعمل، بما في ذلك المهمة والرؤية والخدمات أو المنتجات المقدمة.',
            'category_type' => 'Assesment',
        ]);

        $organisationCategoryId = DB::table('categories')->insertGetId([
            'category_name_ar' => 'الهيكل التنظيمي',
            'category_name_en' => 'Organisation',
            'category_description' => 'يتعلق بالهيكل التنظيمي للمشروع، بما في ذلك الأدوار والمسؤوليات والتنظيم الداخلي.',
            'category_type' => 'Assesment',
        ]);

        $customerRelationsCategoryId = DB::table('categories')->insertGetId([
            'category_name_ar' => 'علاقات العملاء',
            'category_name_en' => 'Customer Relations',
            'category_description' => 'يتعلق بكيفية التعامل مع العملاء، بما في ذلك استراتيجيات خدمة العملاء وبناء العلاقات معهم.',
            'category_type' => 'Assesment',
        ]);

        $operationsCategoryId = DB::table('categories')->insertGetId([
            'category_name_ar' => 'العمليات',
            'category_name_en' => 'Operations',
            'category_description' => 'يتعلق بالعمليات اليومية للمشروع، بما في ذلك إدارة الموارد والجدولة والتكاليف.',
            'category_type' => 'Assesment',
        ]);
        //Sub categories
        $BusinessIdeaSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'فكرة العمل',
            'name_en' => 'Business Idea',
            'description' => 'يتعلق بتوضيح الفكرة الأساسية للمشروع أو المنتج.',
            'category_id' => $businessConceptCategoryId,
            'type' => 'Assesment',
        ]);
        
        $ProductPortfolioSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'محفظة المنتجات',
            'name_en' => 'Product Portfolio',
            'description' => 'يتعلق بتوضيح مجموعة المنتجات أو الخدمات التي يقدمها المشروع.',
            'category_id' => $businessConceptCategoryId,
            'type' => 'Assesment',
        ]);
        
        $BusinessModelSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'نموذج العمل',
            'name_en' => 'Business Model',
            'description' => 'يتعلق بتوضيح كيفية تحقيق الأرباح وتوفير القيمة للعملاء.',
            'category_id' => $businessConceptCategoryId,
            'type' => 'Assesment',
        ]);
        
        $CustomerPortfolioSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'محفظة العملاء',
            'name_en' => 'Customer Portfolio',
            'description' => 'يتعلق بتحديد وتوضيح الفئات المستهدفة من العملاء.',
            'category_id' => $businessConceptCategoryId,
            'type' => 'Assesment',
        ]);
        
        $MarketPositionSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'التموضع في السوق',
            'name_en' => 'Market Position',
            'description' => 'يتعلق بتوضيح موقع المشروع في السوق بالنسبة للمنافسين.',
            'category_id' => $businessConceptCategoryId,
            'type' => 'Assesment',
        ]);
        
        $OwnergroupBoardSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'مجموعة المالكين والمجلس',
            'name_en' => 'Ownergroup & Board',
            'description' => 'يتعلق بتوضيح هيكل الملكية والمجلس الإداري.',
            'category_id' => $organisationCategoryId,
            'type' => 'Assesment',
        ]);
        
        $EmployeesSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'الموظفون',
            'name_en' => 'Employees',
            'description' => 'يتعلق بتحديد الهيكل الوظيفي والمهارات المطلوبة.',
            'category_id' => $organisationCategoryId,
            'type' => 'Assesment',
        ]);
        
        $BusinessProcessesSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'العمليات التجارية',
            'name_en' => 'Business Processes',
            'description' => 'يتعلق بتوضيح العمليات والإجراءات المستخدمة في العمل.',
            'category_id' => $organisationCategoryId,
            'type' => 'Assesment',
        ]);
        
        $PartnershipsSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'الشراكات',
            'name_en' => 'Partnerships',
            'description' => 'يتعلق بتحديد الشراكات الاستراتيجية.',
            'category_id' => $organisationCategoryId,
            'type' => 'Assesment',
        ]);
        
        $LegalIssuesSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'القضايا القانونية',
            'name_en' => 'Legal Issues',
            'description' => 'يتعلق بتوضيح القضايا القانونية المتعلقة بالمشروع.',
            'category_id' => $organisationCategoryId,
            'type' => 'Assesment',
        ]);
        
        $NetworkSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'الشبكة',
            'name_en' => 'Network',
            'description' => 'يتعلق بتوضيح شبكة العلاقات والاتصالات.',
            'category_id' => $customerRelationsCategoryId,
            'type' => 'Assesment',
        ]);
        
        $MarketingSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'التسويق',
            'name_en' => 'Marketing',
            'description' => 'يتعلق بتوضيح استراتيجيات التسويق.',
            'category_id' => $customerRelationsCategoryId,
            'type' => 'Assesment',
        ]);
        
        $SalesSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'المبيعات',
            'name_en' => 'Sales',
            'description' => 'يتعلق بتوضيح استراتيجيات المبيعات والتوزيع.',
            'category_id' => $customerRelationsCategoryId,
            'type' => 'Assesment',
        ]);
        
        $CommunicationsPRSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'الاتصالات والعلاقات العامة',
            'name_en' => 'Communications & PR',
            'description' => 'يتعلق بتوضيح استراتيجيات العلاقات العامة والاتصالات.',
            'category_id' => $customerRelationsCategoryId,
            'type' => 'Assesment',
        ]);
        
        $BrandingSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'العلامة التجارية',
            'name_en' => 'Branding',
            'description' => 'يتعلق بتوضيح استراتيجية بناء العلامة التجارية.',
            'category_id' => $customerRelationsCategoryId,
            'type' => 'Assesment',
        ]);
        
        $ManagementAccountingSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'المحاسبة الإدارية',
            'name_en' => 'Management Accounting',
            'description' => 'يتعلق بتوضيح العمليات المحاسبية الإدارية.',
            'category_id' => $operationsCategoryId,
            'type' => 'Assesment',
        ]);
        
        $FinancingSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'التمويل',
            'name_en' => 'Financing',
            'description' => 'يتعلق بتوضيح استراتيجيات التمويل والتوسعة.',
            'category_id' => $operationsCategoryId,
            'type' => 'Assesment',
        ]);
        
        $ProductionManagementSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'إدارة الإنتاج',
            'name_en' => 'Production Management',
            'description' => 'يتعلق بتوضيح استراتيجيات إدارة الإنتاج.',
            'category_id' => $operationsCategoryId,
            'type' => 'Assesment',
        ]);
        
        $ITSystemsSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'أنظمة تكنولوجيا المعلومات',
            'name_en' => 'IT Systems',
            'description' => 'يتعلق بتوضيح أنظمة تكنولوجيا المعلومات في المشروع.',
            'category_id' => $operationsCategoryId,
            'type' => 'Assesment',
        ]);
        
        $FacilitiesSubCategoryId = DB::table('sub_categories')->insertGetId([
            'name_ar' => 'المرافق',
            'name_en' => 'Facilities',
            'description' => 'يتعلق بتوضيح المرافق والموارد المادية اللازمة.',
            'category_id' => $operationsCategoryId,
            'type' => 'Assesment',
        ]);


        

        $questions = [
            [//1
                'question' => "How unique is your business idea?",
                'explanation_QA_ar' => "ما مدى تميز فكرتك التجارية مقارنة بالأفكار الأخرى؟",
                'explanation_QA_en' => "How distinctive is your business idea compared to others?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $BusinessIdeaSubCategoryId,
                'answers' => [
                ["text" => "Not unique", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat unique", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly unique", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//2
                'question' => "How scalable is your business idea?",
                'explanation_QA_ar' => "ما مدى قابلية فكرتك التجارية للتوسع؟",
                'explanation_QA_en' => "How scalable is your business idea?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $BusinessIdeaSubCategoryId,
                'answers' => [
                ["text" => "Not unique", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat unique", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly unique", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//3
                'question' => "How well-defined is your business idea?",
                'explanation_QA_ar' => "ما مدى وضوح فكرتك التجارية؟",
                'explanation_QA_en' => "How clear and well-defined is your business idea?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $BusinessIdeaSubCategoryId,
                'answers' => [
                ["text" => "Undefined", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately defined", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Well-defined", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//4
                'question' => "How validated is your business idea with customers?",
                'explanation_QA_ar' => "ما مدى التحقق من صحة فكرتك التجارية بناءً على آراء العملاء؟",
                'explanation_QA_en' => "How well has your business idea been validated by customers?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $BusinessIdeaSubCategoryId,
                'answers' => [
                ["text" => "Not validated", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Partially validated", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Fully validated", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//5
                'question' => "How realistic is your business idea in the current market?",
                'explanation_QA_ar' => "ما مدى واقعية فكرتك التجارية في السوق الحالي؟",
                'explanation_QA_en' => "How realistic is your business idea in the current market?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $BusinessIdeaSubCategoryId,
                'answers' => [
                ["text" => "Unrealistic", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately realistic", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly realistic", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//6
                'question' => "How diversified is your product portfolio?",
                'explanation_QA_ar' => "ما مدى تنوع مجموعة منتجاتك؟",
                'explanation_QA_en' => "How diversified is your product portfolio?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $ProductPortfolioSubCategoryId,
                'answers' => [
                ["text" => "Not diversified", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat diversified", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly diversified", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//7
                'question' => "How innovative is your product portfolio?",
                'explanation_QA_ar' => "ما مدى ابتكار مجموعة منتجاتك؟",
                'explanation_QA_en' => "How innovative is your product portfolio?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $ProductPortfolioSubCategoryId,
                'answers' => [
                ["text" => "Not innovative", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat innovative", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly innovative", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//8
                'question' => "How well does your product portfolio meet market needs?",
                'explanation_QA_ar' => "ما مدى تلبية مجموعة منتجاتك لاحتياجات السوق؟",
                'explanation_QA_en' => "How well does your product portfolio meet market needs?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $ProductPortfolioSubCategoryId,
                'answers' => [
                ["text" => "Poor fit", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate fit", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent fit", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//9
                'question' => "How complete is your product portfolio?",
                'explanation_QA_ar' => "ما مدى اكتمال مجموعة منتجاتك؟",
                'explanation_QA_en' => "How complete is your product portfolio?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $ProductPortfolioSubCategoryId,
                'answers' => [
                ["text" => "Incomplete", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately complete", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Fully complete", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//10
                'question' => "How adaptable is your product portfolio to market changes?",
                'explanation_QA_ar' => "ما مدى تكيف مجموعة منتجاتك مع تغيرات السوق؟",
                'explanation_QA_en' => "How adaptable is your product portfolio to market changes?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $ProductPortfolioSubCategoryId,
                'answers' => [
                ["text" => "Not adaptable", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat adaptable", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly adaptable", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//11
                'question' => "How scalable is your business model?",
                'explanation_QA_ar' => "ما مدى قابلية نموذج عملك للتوسع؟",
                'explanation_QA_en' => "How scalable is your business model?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $BusinessModelSubCategoryId,
                'answers' => [
                ["text" => "Not scalable", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately scalable", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly scalable", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//12
                'question' => "How profitable is your business model?",
                'explanation_QA_ar' => "ما مدى ربحية نموذج عملك؟",
                'explanation_QA_en' => "How profitable is your business model?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $BusinessModelSubCategoryId,
                'answers' => [
                ["text" => "Not profitable", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately profitable", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly profitable", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//13
                'question' => "How sustainable is your business model?",
                'explanation_QA_ar' => "ما مدى استدامة نموذج عملك؟",
                'explanation_QA_en' => "How sustainable is your business model?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $BusinessModelSubCategoryId,
                'answers' => [
                ["text" => "Unsustainable", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat sustainable", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly sustainable", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//14
                'question' => "How flexible is your business model for market changes?",
                'explanation_QA_ar' => "ما مدى مرونة نموذج عملك للتكيف مع تغيرات السوق؟",
                'explanation_QA_en' => "How flexible is your business model for market changes?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $BusinessModelSubCategoryId,
                'answers' => [
                ["text" => "Not flexible", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately flexible", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly flexible", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//15
                'question' => "How well does your business model align with your strategy?",
                'explanation_QA_ar' => "ما مدى توافق نموذج عملك مع استراتيجيتك؟",
                'explanation_QA_en' => "How well does your business model align with your strategy?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $BusinessModelSubCategoryId,
                'answers' => [
                ["text" => "Not aligned", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately aligned", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Fully aligned", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//16
                'question' => "How strong is your customer base?",
                'explanation_QA_ar' => "ما مدى قوة قاعدة عملائك؟",
                'explanation_QA_en' => "How strong is your customer base?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $CustomerPortfolioSubCategoryId,
                'answers' => [
                ["text" => "Weak", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Strong", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//17
                'question' => "How diversified is your customer portfolio?",
                'explanation_QA_ar' => "ما مدى تنوع قاعدة عملائك؟",
                'explanation_QA_en' => "How diversified is your customer portfolio?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $CustomerPortfolioSubCategoryId,
                'answers' => [
                ["text" => "Not diversified", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat diversified", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly diversified", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//18
                'question' => "How loyal are your customers?",
                'explanation_QA_ar' => "ما مدى ولاء عملائك؟",
                'explanation_QA_en' => "How loyal are your customers?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $CustomerPortfolioSubCategoryId,
                'answers' => [
                ["text" => "Not loyal", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately loyal", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly loyal", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//19
                'question' => "How well does your customer portfolio align with your target market?",
                'explanation_QA_ar' => "ما مدى توافق قاعدة عملائك مع السوق المستهدف؟",
                'explanation_QA_en' => "How well does your customer portfolio align with your target market?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $CustomerPortfolioSubCategoryId,
                'answers' => [
                ["text" => "Poor alignment", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate alignment", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent alignment", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [//20
                'question' => "How well do you manage customer relationships?",
                'explanation_QA_ar' => "ما مدى إدارتك الجيدة لعلاقات العملاء؟",
                'explanation_QA_en' => "How well do you manage customer relationships?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $CustomerPortfolioSubCategoryId,
                'answers' => [
                ["text" => "Poorly", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Exceptionally", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //21
                'question' => "How well-positioned are you in the market?",
                'explanation_QA_ar' => "ما مدى تمركزك القوي في السوق؟",
                'explanation_QA_en' => "How well-positioned are you in the market?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $MarketPositionSubCategoryId,
                'answers' => [
                ["text" => "Poorly positioned", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately positioned", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Well-positioned", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //22
                'question' => "How competitive is your market position compared to competitors?",
                'explanation_QA_ar' => "ما مدى تنافسية موقعك في السوق مقارنة بالمنافسين؟",
                'explanation_QA_en' => "How competitive is your market position compared to competitors?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $MarketPositionSubCategoryId,
                'answers' => [
                ["text" => "Not competitive", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately competitive", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly competitive", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //23
                'question' => "How well are you perceived by your target market?",
                'explanation_QA_ar' => "كيف يتم إدراكك من قبل السوق المستهدف؟",
                'explanation_QA_en' => "How well are you perceived by your target market?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $MarketPositionSubCategoryId,
                'answers' => [
                ["text" => "Poor perception", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate perception", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent perception", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //24
                'question' => "How visible are you in your market segment?",
                'explanation_QA_ar' => "ما مدى وضوحك في قطاع السوق الخاص بك؟",
                'explanation_QA_en' => "How visible are you in your market segment?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $MarketPositionSubCategoryId,
                'answers' => [
                ["text" => "Not visible", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately visible", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly visible", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //25
                'question' => "How well does your pricing strategy align with your market position?",
                'explanation_QA_ar' => "ما مدى توافق استراتيجيتك التسعيرية مع موقعك في السوق؟",
                'explanation_QA_en' => "How well does your pricing strategy align with your market position?",
                'ascategory_id' => $businessConceptCategoryId,
                'subcategory_id' => $MarketPositionSubCategoryId,
                'answers' => [
                ["text" => "Poor alignment", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate alignment", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent alignment", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //26
                'question' => "How involved is your board in strategic decision-making?",
                'explanation_QA_ar' => "ما مدى مشاركة مجلس إدارتك في اتخاذ القرارات الاستراتيجية؟",
                'explanation_QA_en' => "How involved is your board in strategic decision-making?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $OwnergroupBoardSubCategoryId,
                'answers' => [
                ["text" => "Not involved", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately involved", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly involved", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //27
                'question' => "How experienced is your board with startup growth?",
                'explanation_QA_ar' => "ما مدى خبرة مجلس إدارتك في نمو الشركات الناشئة؟",
                'explanation_QA_en' => "How experienced is your board with startup growth?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $OwnergroupBoardSubCategoryId,
                'answers' => [
                ["text" => "No experience", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Some experience", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Extensive experience", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //28
                'question' => "How well does the board support the management team?",
                'explanation_QA_ar' => "ما مدى دعم مجلس الإدارة لفريق الإدارة؟",
                'explanation_QA_en' => "How well does the board support the management team?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $OwnergroupBoardSubCategoryId,
                'answers' => [
                ["text" => "Poorly", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Exceptionally", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //29
                'question' => "How aligned are the board’s goals with the company’s vision?",
                'explanation_QA_ar' => "ما مدى توافق أهداف مجلس الإدارة مع رؤية الشركة؟",
                'explanation_QA_en' => "How aligned are the board’s goals with the company’s vision?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $OwnergroupBoardSubCategoryId,
                'answers' => [
                ["text" => "Not aligned", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately aligned", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Fully aligned", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //30
                'question' => "How diverse is the board’s skill set?",
                'explanation_QA_ar' => "ما مدى تنوع مهارات مجلس الإدارة؟",
                'explanation_QA_en' => "How diverse is the board’s skill set?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $OwnergroupBoardSubCategoryId,
                'answers' => [
                ["text" => "Not diverse", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately diverse", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly diverse", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //31
                'question' => "How skilled are your employees?",
                'explanation_QA_ar' => "ما مدى مهارة موظفيك؟",
                'explanation_QA_en' => "How skilled are your employees?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $EmployeesSubCategoryId,
                'answers' => [
                ["text" => "Unskilled", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately skilled", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly skilled", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //32
                'question' => "How motivated are your employees?",
                'explanation_QA_ar' => "ما مدى تحفيز موظفيك؟",
                'explanation_QA_en' => "How motivated are your employees?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $EmployeesSubCategoryId,
                'answers' => [
                ["text" => "Not motivated", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately motivated", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly motivated", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //33
                'question' => "Do your employees have opportunities for professional growth?",
                'explanation_QA_ar' => "هل يتمتع موظفوك بفرص للنمو المهني؟",
                'explanation_QA_en' => "Do your employees have opportunities for professional growth?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $EmployeesSubCategoryId,
                'answers' => [
                ["text" => "No opportunities", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Some opportunities", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Significant opportunities", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //34
                'question' => "How well do your employees collaborate and work as a team?",
                'explanation_QA_ar' => "ما مدى تعاون موظفيك وقدرتهم على العمل كفريق؟",
                'explanation_QA_en' => "How well do your employees collaborate and work as a team?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $EmployeesSubCategoryId,
                'answers' => [
                ["text" => "Poor collaboration", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate collaboration", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent collaboration", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //35
                'question' => "How well are your employees aligned with company goals?",
                'explanation_QA_ar' => "ما مدى توافق موظفيك مع أهداف الشركة؟",
                'explanation_QA_en' => "How well are your employees aligned with company goals?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $EmployeesSubCategoryId,
                'answers' => [
                ["text" => "Not aligned", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately aligned", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Fully aligned", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //36
                'question' => "How efficient are your business processes?",
                'explanation_QA_ar' => "ما مدى كفاءة عملياتك التجارية؟",
                'explanation_QA_en' => "How efficient are your business processes?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $BusinessProcessesSubCategoryId,
                'answers' => [
                ["text" => "Inefficient", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately efficient", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly efficient", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //37
                'question' => "How standardized are your business processes?",
                'explanation_QA_ar' => "ما مدى توحيد عملياتك التجارية؟",
                'explanation_QA_en' => "How standardized are your business processes?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $BusinessProcessesSubCategoryId,
                'answers' => [
                ["text" => "Not standardized", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat standardized", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Fully standardized", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //38
                'question' => "How well do your processes handle scale and growth?",
                'explanation_QA_ar' => "ما مدى قدرة عملياتك على التعامل مع التوسع والنمو؟",
                'explanation_QA_en' => "How well do your processes handle scale and growth?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $BusinessProcessesSubCategoryId,
                'answers' => [
                ["text" => "Poor handling", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate handling", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent handling", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //39
                'question' => "How automated are your business processes?",
                'explanation_QA_ar' => "ما مدى أتمتة عملياتك التجارية؟",
                'explanation_QA_en' => "How automated are your business processes?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $BusinessProcessesSubCategoryId,
                'answers' => [
                ["text" => "Not automated", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately automated", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Fully automated", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //40
                'question' => "How effective are your processes in supporting innovation?",
                'explanation_QA_ar' => "ما مدى فعالية عملياتك في دعم الابتكار؟",
                'explanation_QA_en' => "How effective are your processes in supporting innovation?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $BusinessProcessesSubCategoryId,
                'answers' => [
                ["text" => "Not effective", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately effective", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly effective", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //41
                'question' => "How strong are your partnerships with external organizations?",
                'explanation_QA_ar' => "ما مدى قوة شراكاتك مع المنظمات الخارجية؟",
                'explanation_QA_en' => "How strong are your partnerships with external organizations?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $PartnershipsSubCategoryId,
                'answers' => [
                ["text" => "Weak", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Strong", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //42
                'question' => "How well do your partnerships support your business goals?",
                'explanation_QA_ar' => "ما مدى دعم شراكاتك لأهداف عملك؟",
                'explanation_QA_en' => "How well do your partnerships support your business goals?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $PartnershipsSubCategoryId,
                'answers' => [
                ["text" => "Poor support", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate support", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Strong support", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //43
                'question' => "How diversified are your partnerships?",
                'explanation_QA_ar' => "ما مدى تنوع شراكاتك؟",
                'explanation_QA_en' => "How diversified are your partnerships?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $PartnershipsSubCategoryId,
                'answers' => [
                ["text" => "Not diversified", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat diversified", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly diversified", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //44
                'question' => "How much value do your partnerships bring to the company?",
                'explanation_QA_ar' => "ما مدى القيمة التي تضيفها شراكاتك للشركة؟",
                'explanation_QA_en' => "How much value do your partnerships bring to the company?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $PartnershipsSubCategoryId,
                'answers' => [
                ["text" => "No value", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Some value", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "High value", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //45
                'question' => "How well do you manage and nurture your partnerships?",
                'explanation_QA_ar' => "ما مدى إدارتك الجيدة ورعايتك لشراكاتك؟",
                'explanation_QA_en' => "How well do you manage and nurture your partnerships?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $PartnershipsSubCategoryId,
                'answers' => [
                ["text" => "Poor management", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate management", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent management", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //46
                'question' => "How well are you managing your legal obligations?",
                'explanation_QA_ar' => "ما مدى إدارتك لالتزاماتك القانونية؟",
                'explanation_QA_en' => "How well are you managing your legal obligations?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $LegalIssuesSubCategoryId,
                'answers' => [
                ["text" => "Poorly", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Exceptionally", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //47
                'question' => "How comprehensive are your legal contracts and agreements?",
                'explanation_QA_ar' => "ما مدى شمولية عقودك واتفاقياتك القانونية؟",
                'explanation_QA_en' => "How comprehensive are your legal contracts and agreements?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $LegalIssuesSubCategoryId,
                'answers' => [
                ["text" => "Incomplete", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately comprehensive", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Fully comprehensive", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //48
                'question' => "How well do you comply with industry regulations?",
                'explanation_QA_ar' => "ما مدى امتثالك للوائح الصناعة؟",
                'explanation_QA_en' => "How well do you comply with industry regulations?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $LegalIssuesSubCategoryId,
                'answers' => [
                ["text" => "Non-compliant", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately compliant", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Fully compliant", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //49
                'question' => "How prepared is your company for legal risks?",
                'explanation_QA_ar' => "ما مدى استعداد شركتك للمخاطر القانونية؟",
                'explanation_QA_en' => "How prepared is your company for legal risks?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $LegalIssuesSubCategoryId,
                'answers' => [
                ["text" => "Not prepared", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately prepared", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly prepared", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //50
                'question' => "How well do you handle intellectual property protection?",
                'explanation_QA_ar' => "ما مدى إدارتك لحماية الملكية الفكرية؟",
                'explanation_QA_en' => "How well do you handle intellectual property protection?",
                'ascategory_id' => $organisationCategoryId,
                'subcategory_id' => $LegalIssuesSubCategoryId,
                'answers' => [
                ["text" => "Poorly", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Exceptionally", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //51
                'question' => "How extensive is your professional network?",
                'explanation_QA_ar' => "ما مدى اتساع شبكتك المهنية؟",
                'explanation_QA_en' => "How extensive is your professional network?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $NetworkSubCategoryId,
                'answers' => [
                ["text" => "Limited network", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate network", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Extensive network", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //52
                'question' => "How often do you leverage your network for business opportunities?",
                'explanation_QA_ar' => "ما مدى استخدامك لشبكتك للحصول على فرص عمل؟",
                'explanation_QA_en' => "How often do you leverage your network for business opportunities?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $NetworkSubCategoryId,
                'answers' => [
                ["text" => "Rarely", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Occasionally", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Frequently", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //53
                'question' => "How diverse is your network in terms of expertise and industries?",
                'explanation_QA_ar' => "ما مدى تنوع شبكتك من حيث الخبرات والصناعات؟",
                'explanation_QA_en' => "How diverse is your network in terms of expertise and industries?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $NetworkSubCategoryId,
                'answers' => [
                ["text" => "Not diverse", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat diverse", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly diverse", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //54
                'question' => "How well do you maintain relationships within your network?",
                'explanation_QA_ar' => "ما مدى حفاظك على العلاقات داخل شبكتك؟",
                'explanation_QA_en' => "How well do you maintain relationships within your network?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $NetworkSubCategoryId,
                'answers' => [
                ["text" => "Poor maintenance", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate maintenance", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent maintenance", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //55
                'question' => "How well does your network support your business goals?",
                'explanation_QA_ar' => "ما مدى دعم شبكتك لأهداف عملك؟",
                'explanation_QA_en' => "How well does your network support your business goals?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $NetworkSubCategoryId,
                'answers' => [
                ["text" => "Poor support", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate support", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Strong support", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //56
                'question' => "How effective are your marketing strategies?",
                'explanation_QA_ar' => "ما مدى فعالية استراتيجياتك التسويقية؟",
                'explanation_QA_en' => "How effective are your marketing strategies?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $MarketingSubCategoryId,
                'answers' => [
                ["text" => "Ineffective", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately effective", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly effective", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //57
                'question' => "How well does your marketing reach your target audience?",
                'explanation_QA_ar' => "ما مدى وصول تسويقك إلى الجمهور المستهدف؟",
                'explanation_QA_en' => "How well does your marketing reach your target audience?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $MarketingSubCategoryId,
                'answers' => [
                ["text" => "Poor reach", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate reach", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent reach", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //58
                'question' => "How innovative are your marketing campaigns?",
                'explanation_QA_ar' => "ما مدى ابتكار حملاتك التسويقية؟",
                'explanation_QA_en' => "How innovative are your marketing campaigns?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $MarketingSubCategoryId,
                'answers' => [
                ["text" => "Not innovative", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat innovative", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly innovative", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //59
                'question' => "How cost-effective are your marketing efforts?",
                'explanation_QA_ar' => "ما مدى فعالية تسويقك من حيث التكلفة؟",
                'explanation_QA_en' => "How cost-effective are your marketing efforts?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $MarketingSubCategoryId,
                'answers' => [
                ["text" => "Not cost-effective", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately cost-effective", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly cost-effective", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //60
                'question' => "How consistent is your marketing message across channels?",
                'explanation_QA_ar' => "ما مدى اتساق رسالتك التسويقية عبر القنوات؟",
                'explanation_QA_en' => "How consistent is your marketing message across channels?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $MarketingSubCategoryId,
                'answers' => [
                ["text" => "Inconsistent", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately consistent", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly consistent", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //61
                'question' => "How strong is your sales performance?",
                'explanation_QA_ar' => "ما مدى قوة أداء مبيعاتك؟",
                'explanation_QA_en' => "How strong is your sales performance?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $SalesSubCategoryId,
                'answers' => [
                ["text" => "Weak", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Strong", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //62
                'question' => "How efficient is your sales pipeline?",
                'explanation_QA_ar' => "ما مدى كفاءة خط مبيعاتك؟",
                'explanation_QA_en' => "How efficient is your sales pipeline?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $SalesSubCategoryId,
                'answers' => [
                ["text" => "Inefficient", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately efficient", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly efficient", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //63
                'question' => "How well does your sales team handle objections?",
                'explanation_QA_ar' => "ما مدى مهارة فريق المبيعات في التعامل مع الاعتراضات؟",
                'explanation_QA_en' => "How well does your sales team handle objections?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $SalesSubCategoryId,
                'answers' => [
                ["text" => "Poor handling", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate handling", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent handling", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //64
                'question' => "How effective are your follow-up strategies?",
                'explanation_QA_ar' => "ما مدى فعالية استراتيجيات المتابعة لديك؟",
                'explanation_QA_en' => "How effective are your follow-up strategies?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $SalesSubCategoryId,
                'answers' => [
                ["text" => "Ineffective", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately effective", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly effective", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [   //65
                'question' => "How well do you convert leads into sales?",
                'explanation_QA_ar' => "ما مدى نجاحك في تحويل العملاء المحتملين إلى مبيعات؟",
                'explanation_QA_en' => "How well do you convert leads into sales?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $SalesSubCategoryId,
                'answers' => [
                ["text" => "Poor conversion", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate conversion", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "High conversion", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //66
                'question' => "How effective is your communication strategy?",
                'explanation_QA_ar' => "ما مدى فعالية استراتيجية الاتصال لديك؟",
                'explanation_QA_en' => "How effective is your communication strategy?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $CommunicationsPRSubCategoryId,
                'answers' => [
                ["text" => "Ineffective", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately effective", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly effective", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //67
                'question' => "How well do you manage your company's public image?",
                'explanation_QA_ar' => "ما مدى إدارتك لصورة شركتك العامة؟",
                'explanation_QA_en' => "How well do you manage your company's public image?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $CommunicationsPRSubCategoryId,
                'answers' => [
                ["text" => "Poorly managed", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately managed", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Well managed", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //68
                'question' => "How responsive are you to PR crises?",
                'explanation_QA_ar' => "ما مدى استجابتك لأزمات العلاقات العامة؟",
                'explanation_QA_en' => "How responsive are you to PR crises?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $CommunicationsPRSubCategoryId,
                'answers' => [
                ["text" => "Not responsive", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately responsive", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly responsive", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //69
                'question' => "How well do you communicate internally with employees?",
                'explanation_QA_ar' => "ما مدى كفاءة التواصل الداخلي مع الموظفين؟",
                'explanation_QA_en' => "How well do you communicate internally with employees?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $CommunicationsPRSubCategoryId,
                'answers' => [
                ["text" => "Poor communication", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate communication", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent communication", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //70
                'question' => "How often do you engage in public relations activities?",
                'explanation_QA_ar' => "ما مدى تكرار مشاركتك في أنشطة العلاقات العامة؟",
                'explanation_QA_en' => "How often do you engage in public relations activities?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $CommunicationsPRSubCategoryId,
                'answers' => [
                ["text" => "Rarely", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Occasionally", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Frequently", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //71
                'question' => "How strong is your brand identity?",
                'explanation_QA_ar' => "ما مدى قوة هوية علامتك التجارية؟",
                'explanation_QA_en' => "How strong is your brand identity?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $BrandingSubCategoryId,
                'answers' => [
                ["text" => "Weak", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Strong", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //72
                'question' => "How consistent is your branding across all channels?",
                'explanation_QA_ar' => "ما مدى اتساق العلامة التجارية عبر جميع القنوات؟",
                'explanation_QA_en' => "How consistent is your branding across all channels?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $BrandingSubCategoryId,
                'answers' => [
                ["text" => "Not consistent", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately consistent", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly consistent", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //73
                'question' => "How well does your branding reflect your company’s values?",
                'explanation_QA_ar' => "ما مدى تعبير علامتك التجارية عن قيم شركتك؟",
                'explanation_QA_en' => "How well does your branding reflect your company’s values?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $BrandingSubCategoryId,
                'answers' => [
                ["text" => "Poor reflection", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate reflection", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Strong reflection", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //74
                'question' => "How recognizable is your brand in your market?",
                'explanation_QA_ar' => "ما مدى وضوح وتميز علامتك التجارية في السوق؟",
                'explanation_QA_en' => "How recognizable is your brand in your market?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $BrandingSubCategoryId,
                'answers' => [
                ["text" => "Not recognizable", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately recognizable", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly recognizable", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //75
                'question' => "How well does your brand stand out from competitors?",
                'explanation_QA_ar' => "ما مدى تميز علامتك التجارية عن المنافسين؟",
                'explanation_QA_en' => "How well does your brand stand out from competitors?",
                'ascategory_id' => $customerRelationsCategoryId,
                'subcategory_id' => $BrandingSubCategoryId,
                'answers' => [
                ["text" => "Does not stand out", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat stands out", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly stands out", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //76
                'question' => "How accurate and reliable is your financial reporting?",
                'explanation_QA_ar' => "ما مدى دقة وموثوقية تقاريرك المالية؟",
                'explanation_QA_en' => "How accurate and reliable is your financial reporting?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ManagementAccountingSubCategoryId,
                'answers' => [
                ["text" => "Inaccurate", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately accurate", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly accurate", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //77
                'question' => "How well do you track your company's financial performance?",
                'explanation_QA_ar' => "ما مدى تتبعك لأداء شركتك المالي؟",
                'explanation_QA_en' => "How well do you track your company's financial performance?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ManagementAccountingSubCategoryId,
                'answers' => [
                ["text" => "Poor tracking", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate tracking", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent tracking", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //78
                'question' => "How well do you manage your cash flow?",
                'explanation_QA_ar' => "ما مدى إدارتك لتدفقاتك النقدية؟",
                'explanation_QA_en' => "How well do you manage your cash flow?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ManagementAccountingSubCategoryId,
                'answers' => [
                ["text" => "Poorly", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Exceptionally", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //79
                'question' => "How effective is your budgeting process?",
                'explanation_QA_ar' => "ما مدى فعالية عملية إعداد الميزانية لديك؟",
                'explanation_QA_en' => "How effective is your budgeting process?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ManagementAccountingSubCategoryId,
                'answers' => [
                ["text" => "Ineffective", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately effective", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly effective", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //80
                'question' => "How well do you use financial data for decision-making?",
                'explanation_QA_ar' => "ما مدى استخدامك للبيانات المالية لاتخاذ القرارات؟",
                'explanation_QA_en' => "How well do you use financial data for decision-making?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ManagementAccountingSubCategoryId,
                'answers' => [
                ["text" => "Poor usage", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate usage", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent usage", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //81
                'question' => "How secure are your current sources of financing?",
                'explanation_QA_ar' => "ما مدى أمان مصادر التمويل الحالية لديك؟",
                'explanation_QA_en' => "How secure are your current sources of financing?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $FinancingSubCategoryId,
                'answers' => [
                ["text" => "Insecure", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately secure", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly secure", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //82
                'question' => "How well does your financing support business growth?",
                'explanation_QA_ar' => "ما مدى دعم تمويلك لنمو الأعمال؟",
                'explanation_QA_en' => "How well does your financing support business growth?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $FinancingSubCategoryId,
                'answers' => [
                ["text" => "Poor support", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate support", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Strong support", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [   //83
                'question' => "How diversified are your sources of financing?",
                'explanation_QA_ar' => "ما مدى تنوع مصادر التمويل لديك؟",
                'explanation_QA_en' => "How diversified are your sources of financing?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $FinancingSubCategoryId,
                'answers' => [
                ["text" => "Not diversified", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Somewhat diversified", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly diversified", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //84
                'question' => "How well do you manage financial risk?",
                'explanation_QA_ar' => "ما مدى إدارتك للمخاطر المالية؟",
                'explanation_QA_en' => "How well do you manage financial risk?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $FinancingSubCategoryId,
                'answers' => [
                ["text" => "Poorly", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Exceptionally", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //85
                'question' => "How accessible is additional financing if needed?",
                'explanation_QA_ar' => "ما مدى سهولة الوصول إلى تمويل إضافي عند الحاجة؟",
                'explanation_QA_en' => "How accessible is additional financing if needed?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $FinancingSubCategoryId,
                'answers' => [
                ["text" => "Inaccessible", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately accessible", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly accessible", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //86
                'question' => "How efficient is your production management process?",
                'explanation_QA_ar' => "ما مدى كفاءة عملية إدارة الإنتاج لديك؟",
                'explanation_QA_en' => "How efficient is your production management process?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ProductionManagementSubCategoryId,
                'answers' => [
                ["text" => "Inefficient", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately efficient", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly efficient", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //87
                'question' => "How scalable is your production capacity?",
                'explanation_QA_ar' => "ما مدى قابلية سعة الإنتاج للتوسع؟",
                'explanation_QA_en' => "How scalable is your production capacity?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ProductionManagementSubCategoryId,
                'answers' => [
                ["text" => "Not scalable", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately scalable", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly scalable", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //88
                'question' => "How well do you manage production costs?",
                'explanation_QA_ar' => "ما مدى إدارتك لتكاليف الإنتاج؟",
                'explanation_QA_en' => "How well do you manage production costs?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ProductionManagementSubCategoryId,
                'answers' => [
                ["text" => "Poor management", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent management", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //89
                'question' => "How well do you manage production risks?",
                'explanation_QA_ar' => "ما مدى إدارتك لمخاطر الإنتاج؟",
                'explanation_QA_en' => "How well do you manage production risks?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ProductionManagementSubCategoryId,
                'answers' => [
                ["text" => "Poorly", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Exceptionally", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //90
                'question' => "How well do you ensure product quality in your production?",
                'explanation_QA_ar' => "ما مدى ضمان جودة المنتج أثناء الإنتاج؟",
                'explanation_QA_en' => "How well do you ensure product quality in your production?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ProductionManagementSubCategoryId,
                'answers' => [
                ["text" => "Poor quality control", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate quality control", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent quality control", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //91
                'question' => "How advanced are your IT systems?",
                'explanation_QA_ar' => "ما مدى تقدم أنظمة تقنية المعلومات لديك؟",
                'explanation_QA_en' => "How advanced are your IT systems?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ITSystemsSubCategoryId,
                'answers' => [
                ["text" => "Outdated", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately advanced", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly advanced", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //92
                'question' => "How secure are your IT systems?",
                'explanation_QA_ar' => "ما مدى أمان أنظمة تقنية المعلومات لديك؟",
                'explanation_QA_en' => "How secure are your IT systems?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ITSystemsSubCategoryId,
                'answers' => [
                ["text" => "Not secure", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately secure", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly secure", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //93
                'question' => "How well do your IT systems support business scalability?",
                'explanation_QA_ar' => "ما مدى دعم أنظمة تقنية المعلومات للتوسع في الأعمال؟",
                'explanation_QA_en' => "How well do your IT systems support business scalability?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ITSystemsSubCategoryId,
                'answers' => [
                ["text" => "Poor support", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate support", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent support", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //94
                'question' => "How cost-effective are your IT solutions?",
                'explanation_QA_ar' => "ما مدى فعالية حلول تقنية المعلومات من حيث التكلفة؟",
                'explanation_QA_en' => "How cost-effective are your IT solutions?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ITSystemsSubCategoryId,
                'answers' => [
                ["text" => "Not cost-effective", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately cost-effective", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly cost-effective", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //95
                'question' => "How well do your IT systems integrate with other business processes?",
                'explanation_QA_ar' => "ما مدى تكامل أنظمة تقنية المعلومات مع العمليات الأخرى؟",
                'explanation_QA_en' => "How well do your IT systems integrate with other business processes?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $ITSystemsSubCategoryId,
                'answers' => [
                ["text" => "Poor integration", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate integration", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent integration", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //96
                'question' => "How well do your facilities support business operations?",
                'explanation_QA_ar' => "ما مدى دعم منشآتك لعمليات الأعمال؟",
                'explanation_QA_en' => "How well do your facilities support business operations?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $FacilitiesSubCategoryId,
                'answers' => [
                ["text" => "Poor support", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate support", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent support", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //97
                'question' => "How scalable are your current facilities for future growth?",
                'explanation_QA_ar' => "ما مدى قدرة منشآتك الحالية على التوسع في المستقبل؟",
                'explanation_QA_en' => "How scalable are your current facilities for future growth?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $FacilitiesSubCategoryId,
                'answers' => [
                ["text" => "Not scalable", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately scalable", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly scalable", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //98
                'question' => "How well do you manage facility costs?",
                'explanation_QA_ar' => "ما مدى إدارتك لتكاليف المنشآت؟",
                'explanation_QA_en' => "How well do you manage facility costs?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $FacilitiesSubCategoryId,
                'answers' => [
                ["text" => "Poor cost management", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent cost management", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //99
                'question' => "How adequate are your facilities in terms of technology infrastructure?",
                'explanation_QA_ar' => "ما مدى كفاية منشآتك من حيث البنية التحتية التقنية؟",
                'explanation_QA_en' => "How adequate are your facilities in terms of technology infrastructure?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $FacilitiesSubCategoryId,
                'answers' => [
                ["text" => "Inadequate infrastructure", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderately adequate", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Highly adequate", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
            [ //100
                'question' => "How well do your facilities support employee productivity?",
                'explanation_QA_ar' => "ما مدى دعم منشآتك لإنتاجية الموظفين؟",
                'explanation_QA_en' => "How well do your facilities support employee productivity?",
                'ascategory_id' => $operationsCategoryId,
                'subcategory_id' => $FacilitiesSubCategoryId,
                'answers' => [
                ["text" => "Poor support", "suggestion" => "consider re-evaluating the foundations of your approach. Focus on innovation, scalability, or clarity to improve this aspect", "score" => 1],
                ["text" => "Moderate support", "suggestion" => "you're on the right track but might need to refine or further develop your strategy to achieve excellence", "score" => 3],
                ["text" => "Excellent support", "suggestion" => "maintain your current strategies and seek opportunities to reinforce and replicate your success", "score" => 5]
                ]
            ],
        ];

        foreach ($questions as $data) {
            $encodedAnswers = json_encode($data['answers']);
            DB::table('questions_bank')->insert([
                'group' => 'assessment',
                'question' => $data['question'],
                'type' => 'mcq',
                'answers' => $encodedAnswers,
                'explanation_QA_ar' => $data['explanation_QA_ar'],
                'explanation_QA_en' => $data['explanation_QA_en'],
                'ascategory_id' => $data['ascategory_id'],
                'subcategory_id' => $data['subcategory_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            
        }



    }
}
