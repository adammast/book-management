@extends('components.layout')

@section('title', 'Edit Author')

@section('content')
    <h2>Edit Author</h2>
    @include('components.author', ['action' => route('authors.update', $author->id), 'method' => 'PUT', 'author' => $author, 'buttonText' => 'Update Author'])
@endsection
