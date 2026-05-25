@extends('components.layout')

@section('title', 'Edit Book')

@section('content')
    <h2>Edit Book</h2>
    @include('components.book', ['action' => route('books.update', $book->id), 'method' => 'PUT', 'book' => $book, 'buttonText' => 'Update Book'])
@endsection