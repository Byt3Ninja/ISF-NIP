<?php

namespace App\Http\Controllers\API;

use App\Expertise;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExpertiseResource;
use App\Http\Resources\SpecializationResource;

class ExpertiseController extends Controller
{
   use ApiResponder;
  /**
 * @OA\Get(
 *     path="/api/expertises",
 *     summary="Get All Expertises",
 *     description="Returns a flat list of expertises with localized titles based on the `lang` header.",
 *     tags={"Expertises"},
 *     @OA\Parameter(
 *         name="lang",
 *         in="header",
 *         required=false,
 *         description="Language preference. Allowed values: en, ar. Defaults to en.",
 *         @OA\Schema(type="string", enum={"en", "ar"})
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful response with localized expertises",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="Product Development")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
    public function index()
    {
        $expertises = Expertise::whereNull('parent_id')
            ->with('children')
            ->orderByDesc('id')
            ->get();

        return $this->success(ExpertiseResource::collection($expertises));
    }
    public function children()
    {
        $specializations = Expertise::whereNotNull('parent_id')
            ->with('parent')
            ->orderByDesc('id')
            ->get();

        return $this->success(SpecializationResource::collection($specializations));
    }

    public function store(Request $request)
    {
      if($request->has('expertise_id') == null){
      $request->validate([
        'name' => 'required|string',
        'description' => 'nullable|string',
        'status' => 'required',
      ]);
      }else{
        $request->validate([
          'name' => 'required|string',
          'description' => 'nullable|string',
          'status' => 'required',
          'expertise_id' => 'required|exists:expertises,id',
        ]);
      }
        $addExpertise = new Expertise();
        $addExpertise->title = $request->input('name');
        $addExpertise->title_ar = $request->input('name');
        $addExpertise->description = $request->input('description');
        if($request->has('expertise_id') != null){
        $addExpertise->parent_id = $request->input('expertise_id');
        }
        $addExpertise->status = $request->input('status');
        $addExpertise->save();

        $doneExpertise = [
            'id' => $addExpertise->id,
            'name' => $addExpertise->title,
            'description' => $addExpertise->description,
            'specializations' => 0,
            'status' => $addExpertise->status
        ];

        return response()->json($doneExpertise);

    }

    public function update(Request $request, $id)
    {
         if($request->has('expertise_id') == null){
            $request->validate([
              'name' => 'required|string',
              'description' => 'nullable|string',
              'status' => 'required',
            ]);
          }else{
            $request->validate([
              'name' => 'required|string',
              'description' => 'nullable|string',
              'status' => 'required',
              'expertise_id' => 'required|exists:expertises,id',
            ]);
          }
        $updateExpertise = Expertise::findOrFail($id);
        $updateExpertise->title = $request->input('name');
        $updateExpertise->title_ar = $request->input('name');
        $updateExpertise->description = $request->input('description');
        if($request->has('expertise_id') != null){
        $updateExpertise->parent_id = $request->input('expertise_id');
        }
        $updateExpertise->status = $request->input('status');
        $updateExpertise->save();

        $doneUpdate = [
            'id'             => $updateExpertise->id,
            'name'           => $updateExpertise->title,
            'expertise_id'   => $updateExpertise->parent_id,
            'expertise_name' => $updateExpertise->parent ? $updateExpertise->parent->title : null,
            'description'    => $updateExpertise->description,
            'status'         => $updateExpertise->status,
        ];

        return response()->json($doneUpdate);
    }
    public function destroy($id)
    {
        $deleteExpertise = Expertise::findOrFail($id);
        if($deleteExpertise->children->count() > 0){
            $deleteExpertise->children()->delete();
        }
        $deleteExpertise->delete();

        return response()->json($deleteExpertise);
    }



}
