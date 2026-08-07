<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormSubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $form = $request->string('form')->toString() ?: 'register';
        $submissions = FormSubmission::query()
            ->where('form', $form)
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.submissions.index', compact('submissions', 'form'));
    }

    public function show(FormSubmission $submission): View
    {
        return view('admin.submissions.show', compact('submission'));
    }
}
