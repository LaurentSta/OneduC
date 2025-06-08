@extends('admin.admin_dashboard')
@section('admin')

<h1>Modifier le Groupe : {{ $group->name }}</h1>

    <form action="{{ route('update.groupe', $group->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nom du groupe</label>
            <input type="text" name="name" class="form-control" value="{{ $group->name }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ $group->description }}</textarea>
        </div>sub

        <div class="mb-3">
            <label for="instructor_id" class="form-label">Instructeur</label>
            <select name="instructor_id" class="form-control" required>
                @foreach ($instructors as $instructor)
                    <option value="{{ $instructor->id }}" {{ $group->instructor_id == $instructor->id ? 'selected' : '' }}>
                        {{ $instructor->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="students" class="form-label">Étudiants</label>
            <select name="students[]" class="form-control" multiple>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" {{ $group->students->contains($student->id) ? 'selected' : '' }}>
                        {{ $student->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour le groupe</button>
    </form>

@endsection
