@extends('components.layout')

@section('title', 'Add Author')

@section('content')
    <h2>Add a New Author</h2>
    @include('components.author', ['action' => route('authors.store'), 'method' => 'POST', 'author' => null, 'buttonText' => 'Add Author'])
@endsection
