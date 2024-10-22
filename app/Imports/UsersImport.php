<?php

namespace App\Imports;

use App\User;
use App\Company;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row)
    {
        // أولاً: إنشاء المستخدم
        $user = User::create([
            'email'    => $row['email'],   // تأكد من وجود العمود
            'first_name_en'    => $row['First_Name'],   // تأكد من وجود العمود
            'last_name_en'    => $row['Second_Name'],   // تأكد من وجود العمود
            'first_name_ar'    => $row['First_Name'],   // تأكد من وجود العمود
            'last_name_ar'    => $row['Second_Name'],   // تأكد من وجود العمود
            'phone'         => $row['CountryCode']."".$row['Mobile_number'],   // تأكد من وجود العمود
            'HaveCompany'    => $row['Have_Company'],   // تأكد من وجود العمود
            'country_id'    => $row['CountryID'],   // تأكد من وجود العمود
            'password' => Hash::make($row['123456789']),

           
        ]);

        // ثانياً: التحقق إذا كان haveCompany = 1 لإنشاء الشركة
        if ($row['haveCompany'] == 1) { 
            Company::create([
                'name_en'    => $row['Your_business_name'],    // اسم الشركة
                'name_ar' => $row['Your_business_name'], // عنوان الشركة
                'address_en' => $row['Your_business_name'], // عنوان الشركة
                'address_ar' => $row['Your_business_name'], // عنوان الشركة
                'country_code' => $row['CountryCode'], // عنوان الشركة
                'phone' => $row['Mobile_number'], // عنوان الشركة
                'email' => $row['email'], // عنوان الشركة
                'institutions' => '0', // عنوان الشركة
                'user_id' => $user->id,               // ربط الشركة بالمستخدم
            ]);
        }

        return $user;
    }
}
