<?php

namespace App\Repositories;

use App\Step;

class StepRepository
{
    public function create(array $data)
    {
        return Step::create($data);
    }

    public function update(Step $step, array $data)
   {
      $step->update($data);
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