@extends('layouts.auth')

@section('title', 'Thibitisha barua pepe — MkulimaForum')

@section('content')
  <livewire:auth.verify-notice />
@endsection

@section('foot')
  <a class="back" href="{{ route('login') }}">Rudi kuingia</a>
@endsection
