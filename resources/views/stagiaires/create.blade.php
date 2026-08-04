@extends('layouts.app')
@section('title', 'Ajouter un stagiaire')
@section('page-title', 'Ajouter un stagiaire')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('stagiaires.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>
</div>

@if($errors->any())
<div class="alert alert-error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg>
    {{ $errors->count() }} erreur(s) à corriger.
</div>
@endif

<form method="POST" action="{{ route('stagiaires.store') }}" enctype="multipart/form-data" class="personnel-form">
    @csrf
    @include('stagiaires._form')
    <div class="form-submit-bar">
        <a href="{{ route('stagiaires.index') }}" class="btn-ghost">Annuler</a>
        <button type="submit" class="btn-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Enregistrer le stagiaire
        </button>
    </div>
</form>
@endsection
