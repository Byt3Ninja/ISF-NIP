<?php

namespace App\Http\Controllers;

use App\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Validator, DB, Mail};
use App\BuilderForm;
use App\BuilderField;
use App\Field;

class FormController extends Controller
{
    public function index() {
      return view('admin.settings.forms.index');
    }
    
    public function submissions() {
      return view('admin.settings.forms.form-submissions.index');
    }
  
    public function submissionDetails() {
      return view('admin.settings.forms.form-submissions.show');
    }
}
