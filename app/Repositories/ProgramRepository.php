<?php
namespace App\Repositories;

use App\Cycle;

class ProgramRepository
{
    public function findById($id)
    {
        return Cycle::find($id);
    }
}
