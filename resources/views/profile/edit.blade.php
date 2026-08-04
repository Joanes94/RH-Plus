@extends('layouts.app')
@section('title', 'Modifier mon profil')
@section('page-title', 'Modifier mes informations')

@section('content')

<div class="dash-card" style="max-width: 700px">
    <div class="card-header">
        <h3>Informations personnelles</h3>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
        @csrf
        @method('PUT')

        <div class="form-row-2col">
            <div class="form-group">
                <label for="nom">Nom <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="text" id="nom" name="nom" value="{{ old('nom', $user->nom) }}"
                           class="{{ $errors->has('nom') ? 'is-invalid' : '' }}">
                </div>
                @error('nom') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="prenoms">Prénoms <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="text" id="prenoms" name="prenoms" value="{{ old('prenoms', $user->prenoms) }}"
                           class="{{ $errors->has('prenoms') ? 'is-invalid' : '' }}">
                </div>
                @error('prenoms') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-row-2col">
            <div class="form-group">
                <label for="sexe">Sexe <span class="req">*</span></label>
                <div class="input-wrapper select-wrapper">
                    <select id="sexe" name="sexe">
                        <option value="M" {{ old('sexe', $user->sexe) === 'M' ? 'selected' : '' }}>Masculin</option>
                        <option value="F" {{ old('sexe', $user->sexe) === 'F' ? 'selected' : '' }}>Féminin</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <div class="input-wrapper">
                    <input type="tel" id="telephone" name="telephone" value="{{ old('telephone', $user->telephone) }}"
                           placeholder="+229 97 00 00 00">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Adresse email <span class="req">*</span></label>
            <div class="input-wrapper">
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                       class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
            </div>
            @error('email') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Enregistrer les modifications</button>
            <a href="{{ route('profile.show') }}" class="btn-ghost">Annuler</a>
        </div>
    </form>
</div>

@endsection
