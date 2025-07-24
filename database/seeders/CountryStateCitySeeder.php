<?php

namespace Database\Seeders;

use App\Country;
use App\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CountryStateCitySeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		ini_set('memory_limit', '-1');
		try {
			DB::beginTransaction();
			$db_countries = collect(DB::select('SELECT * FROM countries'));
			$db_states = collect(DB::select('SELECT * FROM states'));
			$db_cities = collect(DB::select('SELECT * FROM cities'));
			// $json = File::get(base_path('database/dbFiles/countries_states_cities.json'));
			$json = File::get(base_path('database/dbFiles/minified_countries_states_cities.json'));
			$data = collect(json_decode($json));

			$iso3Codes = ['EGY', 'SAU', 'ARE', 'QAT', 'BHR'];
			$countries = $data->whereIn('iso3', $iso3Codes)->toArray();

			$citiesToCreate = [];
			$statesToCreate = [];
			$countriesToCreate = [];
			foreach( $countries as $country )
			{
				$checkCountry = $db_countries->where('iso3', $country->iso3)->first();
				if( empty( $checkCountry ) )
				{
					$countriesToCreate[] = [
						'name_ar' => $country->name_ar,
						'name_en' => $country->name_en,
						'iso3' => $country->iso3,
						'phone_code' => $country->phone_code
					];
				}
			}
			if( count( $countriesToCreate ) )
			{
				Country::insert( $countriesToCreate );
				foreach( $countriesToCreate as $country )
				{
					$name = $country['name_en'];
					$this->command->info("Country created successfully for: {$name}");
				}
				$db_countries = collect(DB::select('SELECT * FROM countries'));
			}

			foreach( $db_countries as $country )
			{
				$countryStates = $data->where('iso3', $country->iso3)->first()->states ?? [];
				$countryStates = collect( $countryStates );
				foreach( $countryStates as $state )
				{
					$checkState = $db_states->where('country_id', $country->id)->where('name_en', $state->name_en)->first();
					if( empty( $checkState ) )
					{
						$statesToCreate[] = [
							'country_id' => $country->id,
							'name_ar' => $state->name_ar,
							'name_en' => $state->name_en
						];
					}
				}
			}
			if( count( $statesToCreate ) )
			{
				State::insert( $statesToCreate );
				$db_states = collect(DB::select('SELECT * FROM states'));
				$countries_ids = $db_states->pluck('country_id')->unique()->toArray();
				foreach( $countries_ids as $country_id )
				{
					$country = $db_countries->where('id', $country_id)->first();
					if( !empty( $country ) )
					{
						$name = $country->name_en;
						$this->command->info("States created successfully for: {$name}");
					}
				}
			}
			foreach( $db_countries as $country )
			{
				$countryStates = $data->where('iso3', $country->iso3)->first()->states ?? [];
				$countryStates = collect( $countryStates );
				foreach( $countryStates as $state )
				{
					$stateName = $state->name_en;
					$checkState = $db_states->where('country_id', $country->id)->where('name_en', $stateName)->first();
					if( !empty( $checkState ) )
					{
						$cities = $state->cities;
						foreach( $cities as $city )
						{
							$checkCity = $db_cities->where('country_id', $country->id)->where('state_id', $checkState->id)->where('name_en', $city->name_en)->first();
							if( empty( $checkCity ) )
							{
								$citiesToCreate[] = [
									'country_id' => $country->id,
									'state_id' => $checkState->id,
									'name_ar' => $city->name_ar ?? '',
									'name_en' => $city->name_en ?? ''
								];
							}
						}
					}
				}
			}
			if( count( $citiesToCreate ) )
			{
				DB::table('cities')->insert( $citiesToCreate );
				$db_cities = collect(DB::select('SELECT * FROM cities'));
				$countries_ids = $db_cities->pluck('country_id')->unique()->toArray();
				foreach( $countries_ids as $country_id )
				{
					$country = $db_countries->where('id', $country_id)->first();
					if( !empty( $country ) )
					{
						$name = $country->name_en;
						$this->command->info("cities created successfully for: {$name}");
					}
				}
			}
			/* foreach ($counties as $value) {
				$country =  Country::create(['name_ar' => $value->native, 'name_en' => $value->name, 'iso3' => $value->iso3, 'phone_code' => $value->phone_code]);
				foreach ($value->states as $key) {
					$state = $country->states()->create([
						'name_ar' => GoogleTranslate::trans($key->name, 'ar', 'en'),
						'name_en' => $key->name
					]);
					foreach ($key->cities as $value) {
						$state->cities()->create([
							'country_id' => $country->id,
							'name_ar' => GoogleTranslate::trans($value->name, 'ar', 'en'),
							'name_en' => $value->name
						]);
					}
				}
			} */
			DB::commit();
		} catch (\Throwable $th) {
			DB::rollBack();
			$this->command->error('Something went wrong while deploying country states and cities!');
			$this->command->error($th->getMessage());
			// Log the error message
			Log::error('Error deploying country states and cities: ' . $th->getMessage());
		}
		catch (\Exception $e) {
			DB::rollBack();
			$this->command->error('Something went wrong while deploying country states and cities!');
			$this->command->error($e->getMessage());
			// Log the error message
			Log::error('Error deploying country states and cities: ' . $e->getMessage());
		}
	}
}
