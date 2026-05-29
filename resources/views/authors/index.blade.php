@extends('components.layout')

@section('title', 'Authors')

@section('content')
    <h1 class="mb-4">Authors</h1>
    
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <a href="{{ route('authors.create') }}" class="btn btn-primary mb-3">Add New Author</a>
    
    <table id="authorsTable" class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Books Count</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($authors as $author)
                <tr>
                    <td>{{ $author->first_name }}</td>
                    <td>{{ $author->last_name }}</td>
                    <td>{{ $author->books_count }}</td>
                    <td>
                        <a href="{{ route('authors.edit', $author->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    </td>
                    <td>
                        <form action="{{ route('authors.destroy', $author->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this author?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" @if($author->books_count > 0) disabled @endif>Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#authorsTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "lengthMenu": [5, 10, 25, 50, 100],
                "columnDefs": [
                    { "orderable": false, "targets": [3, 4] }
                ]
            });
        });
    </script>
@endsection
