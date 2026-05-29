<form action="{{ $action }}" method="POST" class="card p-4 shadow-sm bg-white">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="first_name" class="form-label">First Name</label>
        <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name', $author->first_name ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label for="last_name" class="form-label">Last Name</label>
        <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $author->last_name ?? '') }}" required>
    </div>

    <div class="d-flex justify-content-center gap-2">
        <button type="submit" class="btn btn-success px-4">{{ $buttonText }}</button>
        <a href="{{ route('authors.index') }}" class="btn btn-secondary px-4">Cancel</a>
    </div>
</form>
