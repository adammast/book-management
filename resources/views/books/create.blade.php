@extends('components.layout')

@section('title', 'Add a Book')

@section('content')
    <h2>Add a New Book</h2>
    @include('components.book', ['action' => route('books.store'), 'method' => 'POST', 'book' => null, 'buttonText' => 'Add Book'])
@endsection