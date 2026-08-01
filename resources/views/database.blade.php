@extends('layouts.app')

@section('title', 'Database — SYNRGYPRO')
@section('body-class', 'syn-database-page')

@push('styles')
    @include('database.partials.styles')
@endpush

@section('content')
<div class="db-page" id="databasePage">
    @include('database.partials.sidebar')
    @include('database.partials.header')

    <main class="db-content">
        @include('database.partials.loading')
        @include($contentView)
    </main>

    @include('database.partials.footer')
</div>
@endsection

@push('scripts')
    @include('database.partials.scripts')
@endpush