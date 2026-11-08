<?php

namespace App\Http\Controllers;

use App\EducationEntity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * GV-10436 (ISF): admins manage the universities, institutes / directorates and research
 * centers that founders pick as their Entity. Platform-wide list, like stages or industries.
 */
class EducationEntitiesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // The list only feeds ISF's education fields; other deployments never show it.
        $this->middleware(function ($request, $next) {
            abort_unless(isIsf(), 404);

            return $next($request);
        });
    }

    public function index()
    {
        $entities = EducationEntity::withCount('users')->orderBy('id', 'desc')->get();
        $types = EducationEntity::TYPES;

        return view('admin.educationEntities.index', compact('entities', 'types'));
    }

    public function store(Request $request)
    {
        $entity = EducationEntity::create($this->validated($request));

        return response()->json([
            'success' => true,
            'message' => __('admin.StoredSuccessfully'),
            'data' => $this->present($entity->loadCount('users')),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $entity = EducationEntity::findOrFail($id);
        $entity->update($this->validated($request));

        return response()->json([
            'success' => true,
            'message' => __('admin.UpdatedSuccessfully'),
            'data' => $this->present($entity->loadCount('users')),
        ]);
    }

    public function destroy($id)
    {
        $entity = EducationEntity::withCount('users')->findOrFail($id);

        // A founder's saved Entity must keep pointing at a real row.
        if ($entity->users_count > 0) {
            return response()->json([
                'success' => false,
                'message' => __('admin.education_entity_in_use', ['count' => $entity->users_count]),
            ], 422);
        }

        $entity->delete();

        return response()->json(['success' => true, 'message' => __('admin.DeletedSuccessfully')]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(EducationEntity::TYPES)],
            'name_en' => ['required', 'string', 'max:191'],
            'name_ar' => ['required', 'string', 'max:191'],
        ], [
            'type.required' => __('admin.education_entity_type_required'),
            'type.in' => __('admin.education_entity_type_required'),
            'name_en.required' => __('admin.education_entity_name_en_required'),
            'name_ar.required' => __('admin.education_entity_name_ar_required'),
        ]);
    }

    private function present(EducationEntity $entity): array
    {
        return [
            'id' => $entity->id,
            'type' => $entity->type,
            'type_label' => __('admin.education_entity_type_'.$entity->type),
            'name_en' => $entity->name_en,
            'name_ar' => $entity->name_ar,
            'users_count' => $entity->users_count,
            'updated_at' => $entity->updated_at->format('Y-m-d H:i'),
        ];
    }
}
