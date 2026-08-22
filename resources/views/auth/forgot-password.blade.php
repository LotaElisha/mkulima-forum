@extends('layouts.auth')

@section('title', 'Umesahau nenosiri? — MkulimaForum')

@section('content')
  <h1>Umesahau nenosiri?</h1>
  <p class="lead">Weka barua pepe uliyotumia kujisajili. Tutakutumia kiungo cha kuweka nenosiri jipya.</p>

  <div id="alert" class="alert" role="alert" aria-live="polite"></div>

  <form id="forgot-form" novalidate>
    <div class="field">
      <label for="email">Barua pepe</label>
      <div class="control">
        <input id="email" name="email" type="email" inputmode="email"
               autocomplete="email" autocapitalize="none" spellcheck="false"
               placeholder="mfano@barua.com" required>
      </div>
      <p class="field-error" id="email-error"></p>
    </div>

    <button class="btn btn-primary" id="submit" type="submit">
      <span class="label">Tuma kiungo</span>
    </button>
  </form>
@endsection

@section('foot')
  <a class="back" href="/login">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    Rudi kuingia
  </a>
@endsection

@section('head_extra')
<script nonce="{{ $cspNonce ?? '' }}" defer>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('forgot-form');
  var button = document.getElementById('submit');
  var label = button.querySelector('.label');
  var alertBox = document.getElementById('alert');
  var emailInput = document.getElementById('email');
  var emailError = document.getElementById('email-error');

  function showAlert(message, kind) {
    alertBox.textContent = message;
    alertBox.className = 'alert show ' + (kind === 'ok' ? 'alert-ok' : 'alert-error');
  }

  function fieldError(message) {
    emailError.textContent = message || '';
    emailError.classList.toggle('show', Boolean(message));
    emailInput.setAttribute('aria-invalid', message ? 'true' : 'false');
  }

  function busy(state) {
    button.disabled = state;
    label.textContent = state ? 'Inatuma…' : 'Tuma kiungo';
  }

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    fieldError('');
    alertBox.className = 'alert';

    var email = emailInput.value.trim();
    if (!email || email.indexOf('@') < 1) {
      fieldError('Weka barua pepe sahihi.');
      emailInput.focus();
      return;
    }

    busy(true);
    try {
      var response = await fetch('/api/auth/password/forgot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ email: email })
      });
      var data = await response.json().catch(function () { return {}; });

      if (response.status === 429) {
        showAlert('Umeomba mara nyingi mfululizo. Subiri dakika chache kisha jaribu tena.');
        return;
      }
      // The API answers identically for known and unknown addresses on
      // purpose, so this screen must not hint at which one it was either.
      showAlert(data.message || 'Kama barua pepe hiyo ina akaunti, kiungo kimetumwa.', 'ok');
      form.reset();
    } catch (error) {
      showAlert('Hakuna mtandao. Angalia muunganisho wako kisha jaribu tena.');
    } finally {
      busy(false);
    }
  });
});
</script>
@endsection
