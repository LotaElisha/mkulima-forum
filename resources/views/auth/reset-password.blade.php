@extends('layouts.auth')

@section('title', 'Weka nenosiri jipya — MkulimaForum')

@section('content')
  <h1>Weka nenosiri jipya</h1>
  <p class="lead">Chagua nenosiri jipya lenye angalau herufi 12. Vifaa vingine vitatolewa kwenye akaunti.</p>

  <div id="alert" class="alert" role="alert" aria-live="polite"></div>

  <form id="reset-form" novalidate>
    <div class="field">
      <label for="email">Barua pepe</label>
      <div class="control">
        <input id="email" type="email" inputmode="email" autocomplete="email"
               autocapitalize="none" spellcheck="false"
               value="{{ $email }}" required>
      </div>
      <p class="field-error" id="email-error"></p>
    </div>

    <div class="field">
      <label for="password">Nenosiri jipya</label>
      <div class="control has-toggle">
        <input id="password" type="password" autocomplete="new-password"
               minlength="12" placeholder="Angalau herufi 12" required>
        <button class="toggle-visibility" type="button"
                data-target="password" aria-label="Onyesha nenosiri" aria-pressed="false">
          <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.87 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.87 0"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <p class="hint">Herufi 12 au zaidi. Tumia maneno unayokumbuka kwa urahisi.</p>
      <p class="field-error" id="password-error"></p>
    </div>

    <div class="field">
      <label for="password_confirmation">Rudia nenosiri jipya</label>
      <div class="control has-toggle">
        <input id="password_confirmation" type="password" autocomplete="new-password"
               minlength="12" required>
        <button class="toggle-visibility" type="button"
                data-target="password_confirmation" aria-label="Onyesha nenosiri" aria-pressed="false">
          <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.87 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.87 0"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <p class="field-error" id="password_confirmation-error"></p>
    </div>

    <button class="btn btn-primary" id="submit" type="submit">
      <span class="label">Hifadhi nenosiri</span>
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
  var token = @json($token);
  var form = document.getElementById('reset-form');
  var button = document.getElementById('submit');
  var label = button.querySelector('.label');
  var alertBox = document.getElementById('alert');

  // Password visibility. Farmers typing a 12-character password on a small
  // keyboard need to see what they typed; the control is a real 48px button
  // with an aria-pressed state rather than a decorative icon.
  document.querySelectorAll('.toggle-visibility').forEach(function (toggle) {
    toggle.addEventListener('click', function () {
      var input = document.getElementById(toggle.dataset.target);
      var reveal = input.type === 'password';
      input.type = reveal ? 'text' : 'password';
      toggle.setAttribute('aria-pressed', String(reveal));
      toggle.setAttribute('aria-label', reveal ? 'Ficha nenosiri' : 'Onyesha nenosiri');
    });
  });

  function showAlert(message, kind) {
    alertBox.textContent = message;
    alertBox.className = 'alert show ' + (kind === 'ok' ? 'alert-ok' : 'alert-error');
    alertBox.scrollIntoView({ block: 'nearest' });
  }

  function fieldError(name, message) {
    var node = document.getElementById(name + '-error');
    var input = document.getElementById(name);
    if (!node) return;
    node.textContent = message || '';
    node.classList.toggle('show', Boolean(message));
    if (input) input.setAttribute('aria-invalid', message ? 'true' : 'false');
  }

  function clearErrors() {
    ['email', 'password', 'password_confirmation'].forEach(function (n) { fieldError(n, ''); });
    alertBox.className = 'alert';
  }

  function busy(state) {
    button.disabled = state;
    label.textContent = state ? 'Inahifadhi…' : 'Hifadhi nenosiri';
  }

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    clearErrors();

    var email = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value;
    var confirmation = document.getElementById('password_confirmation').value;

    if (password.length < 12) {
      fieldError('password', 'Nenosiri lazima liwe na angalau herufi 12.');
      document.getElementById('password').focus();
      return;
    }
    if (password !== confirmation) {
      fieldError('password_confirmation', 'Manenosiri hayafanani.');
      document.getElementById('password_confirmation').focus();
      return;
    }

    busy(true);
    try {
      var response = await fetch('/api/auth/password/reset', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
          token: token,
          email: email,
          password: password,
          password_confirmation: confirmation
        })
      });
      var data = await response.json().catch(function () { return {}; });

      if (response.ok) {
        showAlert(data.message || 'Nenosiri limebadilishwa. Unaelekezwa…', 'ok');
        setTimeout(function () { window.location.href = '/login?reset=success'; }, 1400);
        return;
      }

      if (data.errors) {
        Object.keys(data.errors).forEach(function (key) {
          fieldError(key, data.errors[key][0]);
        });
        showAlert(data.errors[Object.keys(data.errors)[0]][0]);
      } else {
        showAlert(data.message || 'Imeshindikana. Omba kiungo kipya.');
      }
    } catch (error) {
      showAlert('Hakuna mtandao. Angalia muunganisho wako kisha jaribu tena.');
    } finally {
      busy(false);
    }
  });
});
</script>
@endsection
