<?php

namespace App\Repositories;

use App\Step;

class StepRepository
{
    public function create(array $data)
    {
        // cycle_id is a foreign key excluded from mass-assignment ($fillable).
        // Set it explicitly so it is persisted from the trusted server-side value.
        $cycleId = $data['cycle_id'] ?? null;
        unset($data['cycle_id']);

        $step = new Step($data);
        if ($cycleId !== null) {
            $step->cycle_id = $cycleId;
        }
        $step->save();

        return $step;
    }

    public function update(Step $step, array $data)
   {
      // cycle_id is a foreign key excluded from mass-assignment ($fillable).
      $cycleId = $data['cycle_id'] ?? null;
      unset($data['cycle_id']);

      $step->fill($data);
      if ($cycleId !== null) {
          $step->cycle_id = $cycleId;
      }
      $step->save();

      return $step;
   }

    public function delete(Step $step)
    {
        return $step->delete();
    }

    public function find($id)
    {
        return Step::find($id);
    }

    public function findInProgram($programId, $stepId)
   {
     return Step::where('id', $stepId)->where('cycle_id', $programId)->first();
   }

   public function getByProgram($programId)
{
    return \App\Step::where('cycle_id', $programId)->get();
}
}