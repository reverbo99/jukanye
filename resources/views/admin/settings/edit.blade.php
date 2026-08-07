@extends('layouts.admin')
@section('title', 'Site settings')
@section('heading', 'Site settings')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.settings.update') }}" class="form-grid">
@csrf @method('PUT')
@php($fc = $settings->footer_contact ?? []; $soc = $settings->social ?? [];)
<div class="form-grid two">
<div><label>Tagline (EN)</label><input type="text" name="tagline_en" value="{{ old('tagline_en', $settings->tagline_en) }}"></div>
<div><label>Tagline (SW)</label><input type="text" name="tagline_sw" value="{{ old('tagline_sw', $settings->tagline_sw) }}"></div>
</div>
<div class="form-grid two">
<div><label>Date label</label><input type="text" name="date_label" value="{{ old('date_label', $settings->date_label) }}"></div>
<div><label>Location label</label><input type="text" name="location_label" value="{{ old('location_label', $settings->location_label) }}"></div>
</div>
<div class="form-grid two">
<div><label>Festival starts at</label><input type="datetime-local" name="festival_starts_at" value="{{ old('festival_starts_at', optional($settings->festival_starts_at)->format('Y-m-d\\TH:i')) }}"></div>
<div><label>Countdown at</label><input type="datetime-local" name="countdown_at" value="{{ old('countdown_at', optional($settings->countdown_at)->format('Y-m-d\\TH:i')) }}"></div>
</div>
<div><label>Donate embed URL</label><input type="text" name="donate_embed_url" value="{{ old('donate_embed_url', $settings->donate_embed_url) }}"></div>
<div class="form-grid two">
<div><label>Donate body (EN)</label><textarea name="donate_body_en">{{ old('donate_body_en', $settings->donate_body_en) }}</textarea></div>
<div><label>Donate body (SW)</label><textarea name="donate_body_sw">{{ old('donate_body_sw', $settings->donate_body_sw) }}</textarea></div>
</div>
<div class="form-grid two">
<div><label>About intro (EN)</label><textarea name="about_intro_en">{{ old('about_intro_en', $settings->about_intro_en) }}</textarea></div>
<div><label>About intro (SW)</label><textarea name="about_intro_sw">{{ old('about_intro_sw', $settings->about_intro_sw) }}</textarea></div>
</div>
<div class="form-grid two">
<div><label>Download text (EN)</label><textarea name="download_text_en">{{ old('download_text_en', $settings->download_text_en) }}</textarea></div>
<div><label>Download text (SW)</label><textarea name="download_text_sw">{{ old('download_text_sw', $settings->download_text_sw) }}</textarea></div>
</div>
<div class="form-grid two">
<div><label>Footer email</label><input type="email" name="footer_email" value="{{ old('footer_email', $fc['email'] ?? '') }}"></div>
<div><label>Footer phone</label><input type="text" name="footer_phone" value="{{ old('footer_phone', $fc['phone'] ?? '') }}"></div>
</div>
<div><label>Footer address</label><input type="text" name="footer_address" value="{{ old('footer_address', $fc['address'] ?? '') }}"></div>
<div class="form-grid two">
<div><label>Facebook</label><input type="text" name="social_facebook" value="{{ old('social_facebook', $soc['facebook'] ?? '') }}"></div>
<div><label>Instagram</label><input type="text" name="social_instagram" value="{{ old('social_instagram', $soc['instagram'] ?? '') }}"></div>
</div>
<div class="form-grid two">
<div><label>Twitter / X</label><input type="text" name="social_twitter" value="{{ old('social_twitter', $soc['twitter'] ?? '') }}"></div>
<div><label>YouTube</label><input type="text" name="social_youtube" value="{{ old('social_youtube', $soc['youtube'] ?? '') }}"></div>
</div>
<div class="actions"><button class="btn btn-accent" type="submit">Save settings</button></div>
</form>
</div>
@endsection