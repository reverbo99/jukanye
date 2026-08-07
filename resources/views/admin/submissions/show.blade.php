@extends('layouts.admin')
@section('title', 'Submission')
@section('heading', 'Submission #'.$submission->id)
@section('content')
<div class="admin-card">
<p><strong>Form:</strong> {{ $submission->form }}</p>
<p><strong>Email:</strong> {{ $submission->email ?: '—' }}</p>
<p><strong>When:</strong> {{ $submission->created_at }}</p>
<pre style="white-space:pre-wrap;background:#f7f5ef;padding:1rem;border-radius:.4rem;">{{ json_encode($submission->payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
<a class="btn btn-ghost" href="{{ route('admin.submissions.index', ['form' => $submission->form]) }}">Back</a>
</div>
@endsection