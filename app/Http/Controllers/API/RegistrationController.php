<?php

namespace App\Http\Controllers\API;

use URL;
use App\User;
use App\Company;
use App\Notifications\WelcomeUserNotification;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Middleware\SanctumApiAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
	public function register(Request $request)
	{
		if ($request->isMethod('get')) {
			return response()->json(['status' => false, 'code' => 412, 'message' => 'Request method not allowed']); // توجيه المستخدم إلى صفحة أخرى
		}
		try {
			$rules = [
				'personal.first_name' => 'required',
				'personal.last_name' => 'required',
				'personal.email' => 'required|email:rfc,dns|unique:users,email',
				'personal.phone' => 'required|min:9|max:13',
				'personal.age' => 'sometimes|nullable|numeric|min:18|max:60',
				'personal.gender' => 'sometimes|nullable|in:Male,Female,male,female,man,men,Man,Men,woman,women,Woman,Women',
				'personal.city' => 'sometimes|nullable'
			];
			$validator = Validator::make($request->all(), $rules);
			if( $validator->fails()) { return response()->json(['status' => false, 'code' => 412, 'message' => 'Issues with inputs', 'errors' => $validator->errors()], 422); }
			else
			{
				$product_photo = [];
				$product_photos = [];
				$images = $request->input('company.product_photo', []);
				foreach( $images as $image )
				{
					$filename = $this->decodeAndStoreBase64Image($image, 'companies');
					if ($filename === null) {
						continue;
					}
					$product_photo[] = URL::to('/api/uploads/companies/' . $filename);
					$product_photos[] = $filename;
				}
				$business_categories = [];
				$categories = $request->input('company.business_category', Company::static_categories());
				foreach( $categories as $category => $status )
				{
					if( $status == true )
					{
						if( strtolower($category) == 'other' )
						{
							$other = $request->input('company.other_category_specify', null);
							$business_categories[] = $other ? $other : $category;
						} else { $business_categories[] = $category; }
					}
				}
				DB::beginTransaction();
				try {
					$gender = null;
					if( $request->has('personal.gender') )
					{
						$_gender = strtolower( $request->input('personal.gender') );
						if( in_array($_gender, ['male', 'man', 'men']) )
						{
							$gender = 'Male';
						}
						elseif( in_array($_gender, ['female', 'woman', 'women']) )
						{
							$gender = 'Female';
						}
					}
					// Never returned or mailed: the welcome email signs the founder in with a magic link.
					$password = Str::random(40);
					$user = new User();
					$user->forceFill([
						'email' => $request->input('personal.email'),
						'first_name_en' => $request->input('personal.first_name'),
						'first_name_ar' => $request->input('personal.first_name'),
						'last_name_en' => $request->input('personal.last_name'),
						'last_name_ar' => $request->input('personal.last_name'),
						'guardian' => $request->input('personal.guardian', null),
						'phone' => $request->input('personal.phone'),
						'age' => $request->input('personal.age', null),
						'gender' => $gender,
						'country_id' => 1,
						'city_id' => null,
						'state_id' => null,
						'company_id' => null,
						'HaveCompany' => false,
						'password' => Hash::make( $password ),
					]);
					$user->save();
					$sanctumToken = $user->createToken('registration')->plainTextToken;
					$user->allotRole('Registered');
					$company = new Company();
					$company->fill([
						'name_en' => $request->input('company.business_or_product_name', null),
						'name_ar' => $request->input('company.business_or_product_name', null),
						'address_en' => '',
						'address_ar' => '',
						'institutions' => '',
						'email' => $request->input('personal.email'),
						'annual_revenue' => $request->input('company.annual_revenue', null),
						'years_of_operations' => $request->input('company.years_operating', null),
						'team_num' => $request->input('company.number_of_employees', null),
						'product_or_service' => $request->input('company.product_or_service', null),
						'occupation' => $request->input('company.occupation', null),
						'website' => $request->input('company.website', null),
						'business_categories' => $business_categories,
						'business_phase' => $request->input('company.business_phase', null),
						'collaborators' => $request->input('company.collaborators', null),
						'Funding_Target' => $request->input('company.investment_amount', null),
						'equity_offered' => $request->input('company.equity_offered', null),
						'info_company' => $request->input('company.business_description', null),
						'product_photo' => $product_photos,
						'product_video' => $request->input('company.video_link', null),
						'hear_about_us' => $request->input('company.hear_about_us', null),
					]);
					$company->save();
					$user->company_id = $company->id;
					$user->HaveCompany = true;
					$user->save();
					DB::commit();
					$user->notify(new WelcomeUserNotification($user));
					return response()->json([
						'status' => true,
						'code' => 200,
						'message' => 'Company registered successfully',
						'data' => [
							'personal' => [
								'first_name' => $request->input('personal.first_name'),
								'last_name' => $request->input('personal.last_name'),
								'guardian' => $user->guardian,
								'email' => $user->email,
								'phone' => $user->phone,
								'age' => $user->age,
								'gender' => $user->gender,
								'city' => ''
							],
							'company' => [
								'full_name' => $user->name,
								'annual_revenue' => $company->annual_revenue,
								'years_operating' => $company->years_of_operations,
								'number_of_employees' => $company->team_num,
								'product_or_service' => $company->product_or_service,
								'occupation' => $company->occupation,
								'business_or_product_name' => $company->name,
								'website' => $company->website,
								'business_category' => $categories,
								'other_category_specify' => $request->input('company.other_category_specify', null),
								'business_phase' => $company->business_phase,
								'collaborators' => $company->collaborators,
								'investment_amount' => $company->Funding_Target,
								'equity_offered' => $company->equity_offered,
								'business_description' => $company->info_company,
								'product_photo' => $product_photo,
								'video_link' => $company->product_video,
								'hear_about_us' => $company->hear_about_us
							],
							'request_token' => $sanctumToken
						]
					], 200);
				} catch (Exception $e) {
					DB::rollBack();
					return response()->json(['message' => 'Sorry, something went wrong. Please try again later.'], 500);
				}
			}
		} catch (\Exception $e) {
			return response()->json(['status' => false, 'code' => 500, 'message' => $e->getMessage(), 'errors' => $validator->errors()], 500);
		}
	}

	public function getCompany( Request $request )
	{
		$token = $request->input('request_token', null);
		$user = null;
		if (!empty($token)) {
			$accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
			// Same expiry rule as the API middleware: a stale token is revoked, not honoured.
			if ($accessToken && SanctumApiAuth::tokenHasExpired($accessToken)) {
				$accessToken->delete();
				$accessToken = null;
			}
			$user = $accessToken?->tokenable;
		} else {
			$user = auth('api')->user();
		}

		if( empty( $user ) ) { return response()->json(['status' => false, 'code' => 404, 'message' => 'User not found'], 404); }
		else
		{
			$other = null;
			$company = $user->company;
			$product_photo = $company->product_photo;
			$categories = Company::static_categories();
			$cats = $company->business_categories;
			foreach( $categories as $cat => $value )
			{
				if( in_array( $cat, $cats ) )
				{
					$categories[$cat] = true;
					unset($cats[array_search($cat, $cats)]);
				}
			}
			$categories['Other'] = count($cats) ? true : false;
			if( count($cats) ) { $other = array_shift($cats); }

			foreach( $product_photo as $key => $value ) { $product_photo[$key] = URL::to('/api/uploads/companies/' . $value); }
			return response()->json([
				'status' => true,
				'code' => 200,
				'message' => 'Company registered successfully',
				'data' => [
					'personal' => [
						'first_name' => $user->first_name,
						'last_name' => $user->last_name,
						'guardian' => $user->guardian,
						'email' => $user->email,
						'phone' => $user->phone,
						'age' => $user->age,
						'gender' => $user->gender,
						'city' => ''
					],
					'company' => [
						'full_name' => $user->name,
						'annual_revenue' => $company->annual_revenue,
						'years_operating' => $company->years_of_operations,
						'number_of_employees' => $company->team_num,
						'product_or_service' => $company->product_or_service,
						'occupation' => $company->occupation,
						'business_or_product_name' => $company->name,
						'website' => $company->website,
						'business_category' => $categories,
						'other_category_specify' => $other,
						'business_phase' => $company->business_phase,
						'collaborators' => $company->collaborators,
						'investment_amount' => $company->Funding_Target,
						'equity_offered' => $company->equity_offered,
						'business_description' => $company->info_company,
						'product_photo' => $product_photo,
						'video_link' => $company->product_video,
						'hear_about_us' => $company->hear_about_us
					]
				]
			], 200);
		}
	}

	private function decodeAndStoreBase64Image(string $dataUri, string $disk): ?string
	{
		$decoded = base64_decode(preg_replace('/^data:[\w+\/.-]+;base64,/', '', $dataUri), true);
		if ($decoded === false) {
			return null;
		}

		$finfo = new \finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->buffer($decoded);

		$allowed = [
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
			'image/gif'  => 'gif',
			'image/webp' => 'webp',
		];

		if (!isset($allowed[$mime])) {
			return null;
		}

		$filename = Str::random(20) . '.' . $allowed[$mime];
		Storage::disk($disk)->put($filename, $decoded);

		return $filename;
	}

	public function showImage( $path )
	{
		try {
			$instance = Storage::disk('companies');
			if( $instance->directoryExists( $path ) ) { abort( 404 ); }
			else
			{
				$file = $instance->response( $path );
				$mimeType = $instance->mimeType( $path );
				if( $mimeType == 'image/svg' ) { $mimeType = 'image/svg+xml'; }
				$file->headers->set('Content-Type', $mimeType);
				return $file;
			}
		} catch( \Throwable $th ) { return response()->view('errors.404')->setStatusCode( 404 ); }
		catch( \Exception $e ) { return response()->view('errors.404')->setStatusCode( 404 ); }
	}
}
