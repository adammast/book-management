@extends('components.layout')

@section('title', 'Book List')

@section('content')
    <h1 class="mb-4">Books</h1>
    <table id="booksTable" class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($books as $book)
                <tr>
                    <td>{{ $book->title }}</td>
                    <td>{{ $book->author }}</td>
                    <td>
                        <a href="{{ route('books.edit', $book->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    </td>
                    <td>
                        <form action="{{ route('books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this book?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <button id="exportCsvBtn" class="btn btn-success">Export CSV</button>
    <button id="exportXmlBtn" class="btn btn-primary">Export XML</button>

    <!-- Column Selection Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Select Columns to Export</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="exportForm">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="title" id="columnTitle" checked>
                            <label class="form-check-label" for="columnTitle">Title</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="author" id="columnAuthor" checked>
                            <label class="form-check-label" for="columnAuthor">Author</label>
                        </div>
                        <input type="hidden" id="exportType">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmExport">Export</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#booksTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true, // Show info text (e.g., "Showing 1 to 10 of 50 books")
                "lengthMenu": [5, 10, 25, 50, 100], // Dropdown for number of items per page
                "columnDefs": [
                    { "orderable": false, "targets": [2, 3] } // Disable sorting for Edit & Delete columns
                ]
            });
        });

        // Show modal when export button is clicked
        $('#exportCsvBtn').click(function () {
            $('#exportType').val('csv');
            $('#exportModal').modal('show');
        });

        $('#exportXmlBtn').click(function () {
            $('#exportType').val('xml');
            $('#exportModal').modal('show');
        });

        // Handle Export
        $('#confirmExport').click(function () {
            let columns = [];
            if ($('#columnTitle').is(':checked')) columns.push('title');
            if ($('#columnAuthor').is(':checked')) columns.push('author');

            let exportType = $('#exportType').val();
            let url = exportType === 'csv' ? "{{ route('export.csv') }}" : "{{ route('export.xml') }}";

            // Redirect with selected columns
            window.location.href = url + "?columns[]=" + columns.join('&columns[]=');

            $('#exportModal').modal('hide');
        });
    </script>
@endsection