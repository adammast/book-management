<form action="{{ $action }}" method="POST" class="card p-4 shadow-sm bg-white">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $book->title ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label for="author" class="form-label">Author</label>
        <input type="text" name="author" id="author" class="form-control" value="{{ old('author', $book->author ?? '') }}" required>
    </div>

    <div class="d-flex justify-content-center gap-2">
        <button type="submit" class="btn btn-success px-4">{{ $buttonText }}</button>
        <a href="{{ route('books.index') }}" class="btn btn-secondary px-4">Cancel</a>
    </div>
</form>