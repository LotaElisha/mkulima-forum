@extends('layouts.auth')

@section('title', 'Umesahau nenosiri? — MkulimaForum')

@section('content')
  <livewire:auth.forgot-password />
@endsection

@section('foot')
  <a class="back" href="{{ route('login') }}">Rudi kuingia</a>
@endsection
