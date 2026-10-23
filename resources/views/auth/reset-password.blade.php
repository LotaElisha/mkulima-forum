@extends('layouts.auth')

@section('title', 'Nenosiri jipya — MkulimaForum')

@section('content')
  <livewire:auth.reset-password :token="$token" :email="$email" />
@endsection

@section('foot')
  <a class="back" href="{{ route('login') }}">Rudi kuingia</a>
@endsection
