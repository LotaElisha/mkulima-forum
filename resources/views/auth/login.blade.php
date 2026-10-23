@extends('layouts.auth')

@section('title', 'Ingia — MkulimaForum')

@section('content')
  <livewire:auth.login />
@endsection

@section('foot')
  Huna akaunti? <a href="{{ route('register') }}">Jisajili</a>
@endsection
