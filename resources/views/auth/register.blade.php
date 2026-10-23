@extends('layouts.auth')

@section('title', 'Jisajili — MkulimaForum')

@section('content')
  <livewire:auth.register />
@endsection

@section('foot')
  Una akaunti? <a href="{{ route('login') }}">Ingia</a>
@endsection
