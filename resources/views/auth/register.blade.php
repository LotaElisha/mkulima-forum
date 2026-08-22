@extends('layouts.auth')

@section('title', 'Jisajili — MkulimaForum')

@section('content')
  <h1>Fungua akaunti</h1>
  <p class="lead">Barua pepe ndiyo njia kuu ya kujisajili. Inachukua dakika moja.</p>

  <div id="alert" class="alert" role="alert" aria-live="polite"></div>

  <form id="register-form" novalidate>
    <div class="field">
      <label for="name">Jina kamili</label>
      <div class="control">
        <input id="name" type="text" autocomplete="name" autocapitalize="words"
               placeholder="Jina lako kamili" required>
      </div>
      <p class="field-error" id="name-error"></p>
    </div>

    <div class="field">
      <label for="email">Barua pepe</label>
      <div class="control">
        <input id="email" type="email" inputmode="email" autocomplete="email"
               autocapitalize="none" spellcheck="false"
               placeholder="mfano@barua.com" required>
      </div>
      {{-- Says why, not just what. Registration now sends a verification link
           here, and this is the address a forgotten password is recovered
           through, so a typo costs the account. --}}
      <p class="hint">Tutakutumia kiungo cha kuthibitisha. Hii ndiyo njia ya kurejesha nenosiri ukilisahau.</p>
      <p class="field-error" id="email-error"></p>
    </div>

    <div class="field">
      <label for="password">Nenosiri</label>
      <div class="control has-toggle">
        <input id="password" type="password" autocomplete="new-password"
               minlength="12" placeholder="Angalau herufi 12" required>
        <button class="toggle-visibility" type="button" data-target="password"
                aria-label="Onyesha nenosiri" aria-pressed="false">
          <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.87 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.87 0"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      {{-- Live feedback rather than a rejection after submit. Twelve characters
           is a lot to type on a phone keyboard; showing progress as they go
           beats a 422 telling them to start over. --}}
      <div class="strength" id="strength" aria-live="polite">
        <div class="strength-track"><div class="strength-fill" id="strength-fill"></div></div>
        <span class="strength-text" id="strength-text">Angalau herufi 12</span>
      </div>
      <p class="field-error" id="password-error"></p>
    </div>

    <div class="field">
      <label for="password_confirmation">Rudia nenosiri</label>
      <div class="control has-toggle">
        <input id="password_confirmation" type="password" autocomplete="new-password"
               minlength="12" placeholder="Andika tena nenosiri" required>
        <button class="toggle-visibility" type="button" data-target="password_confirmation"
                aria-label="Onyesha nenosiri" aria-pressed="false">
          <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.87 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.87 0"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <p class="field-error" id="password_confirmation-error"></p>
    </div>

    <div class="field">
      <label for="role">Wewe ni nani?</label>
      <div class="control">
        <select id="role">
          <option value="farmer">Mkulima</option>
          <option value="buyer">Mnunuzi wa mazao</option>
          <option value="agrodealer">Muuzaji wa pembejeo</option>
          <option value="seller">Muuzaji</option>
          <option value="agronomist">Mtaalamu wa kilimo</option>
          <option value="veterinary">Mtaalamu wa mifugo</option>
          <option value="logistics">Msafirishaji</option>
        </select>
      </div>
    </div>

    <div class="field">
      <label for="country">Nchi</label>
      <div class="control">
        <select id="country">
          <option value="tz">Tanzania</option>
          <option value="ke">Kenya</option>
          <option value="ug">Uganda</option>
          <option value="rw">Rwanda</option>
        </select>
      </div>
    </div>

    {{-- Consent was absent entirely. A platform holding farm records, phone
         numbers and marketplace history needs an explicit, recorded agreement. --}}
    <label class="consent" for="terms">
      <input id="terms" type="checkbox" required>
      <span>Nimesoma na ninakubali <a href="/terms" target="_blank" rel="noopener">Masharti ya Matumizi</a> na <a href="/privacy" target="_blank" rel="noopener">Sera ya Faragha</a>.</span>
    </label>
    <p class="field-error" id="terms-error"></p>

    <button class="btn btn-primary" id="submit" type="submit">
      <span class="label">Jisajili</span>
    </button>
  </form>

  @if(config('services.social.google_client_ids.0') || config('services.social.apple_client_ids.0'))
    <div class="divider"><span>AU ENDELEA NA</span></div>
    <div class="socials">
      <button class="btn btn-quiet" id="google" type="button"
              {{ config('services.social.google_client_ids.0') ? '' : 'disabled' }}>
        <svg width="19" height="19" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.76h3.57c2.08-1.92 3.28-4.74 3.28-8.09Z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.76c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23Z"/><path fill="#FBBC05" d="M5.84 14.11a6.6 6.6 0 0 1 0-4.22V7.05H2.18a11 11 0 0 0 0 9.9l3.66-2.84Z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1a11 11 0 0 0-9.82 6.05l3.66 2.84C6.71 7.29 9.14 5.38 12 5.38Z"/></svg>
        Google
      </button>
      <button class="btn btn-quiet" id="apple" type="button"
              {{ config('services.social.apple_client_ids.0') ? '' : 'disabled' }}>
        <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.36 12.72c.02 2.6 2.28 3.47 2.3 3.48-.02.06-.36 1.24-1.19 2.46-.72 1.05-1.47 2.1-2.65 2.12-1.16.02-1.53-.69-2.85-.69s-1.73.67-2.83.71c-1.14.04-2-1.13-2.73-2.18-1.48-2.14-2.62-6.06-1.1-8.7A4.24 4.24 0 0 1 8.9 7.75c1.11-.02 2.16.75 2.85.75.68 0 1.96-.93 3.3-.79.56.02 2.14.23 3.15 1.71-.08.05-1.88 1.1-1.86 3.29M14.2 5.38c.61-.74 1.02-1.77.9-2.79-.88.04-1.94.59-2.57 1.32-.56.65-1.05 1.7-.92 2.7.98.08 1.98-.5 2.59-1.23"/></svg>
        Apple
      </button>
    </div>
  @endif
@endsection

@section('foot')
  Tayari una akaunti? <a href="/login">Ingia</a>
@endsection

@section('head_extra')
<style>
  .strength { margin-top: 8px; }
  .strength-track {
    height: 4px; border-radius: 999px; background: var(--line); overflow: hidden;
  }
  .strength-fill {
    height: 100%; width: 0; border-radius: 999px;
    background: var(--danger); transition: width .2s ease, background .2s ease;
  }
  .strength-text { display: block; font-size: .8125rem; color: var(--ink-muted); margin-top: 5px; }

  .consent {
    display: flex; gap: 11px; align-items: flex-start;
    margin: 4px 0 20px; font-size: .875rem; color: var(--ink-body);
    font-weight: 400; cursor: pointer;
  }
  .consent input {
    width: 26px; height: 26px; flex: none; margin-top: 1px;
    accent-color: var(--green-600); cursor: pointer;
  }
  /* The whole label is the target, not just the box — a 26px checkbox is
     small, but tapping anywhere in this 48px row toggles it. */
  .consent { min-height: 48px; padding: 6px 0; }
  .consent a { min-height: 0; display: inline; }
  .consent a { text-decoration: underline; }

  .divider {
    display: flex; align-items: center; gap: 14px;
    margin: 24px 0 18px; color: var(--ink-muted); font-size: .8125rem; font-weight: 600;
  }
  .divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: var(--line); }

  .socials { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .socials .btn:disabled { opacity: .45; cursor: not-allowed; }
</style>

<script nonce="{{ $cspNonce ?? '' }}" defer>
document.addEventListener('DOMContentLoaded', function () {
  var googleClientId = @json(config('services.social.google_client_ids.0'));
  var appleClientId  = @json(config('services.social.apple_client_ids.0'));
  var alertBox = document.getElementById('alert');
  var form = document.getElementById('register-form');

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
    ['name', 'email', 'password', 'password_confirmation', 'terms'].forEach(function (n) {
      fieldError(n, '');
    });
    alertBox.className = 'alert';
  }

  document.querySelectorAll('.toggle-visibility').forEach(function (toggle) {
    toggle.addEventListener('click', function () {
      var input = document.getElementById(toggle.dataset.target);
      var reveal = input.type === 'password';
      input.type = reveal ? 'text' : 'password';
      toggle.setAttribute('aria-pressed', String(reveal));
      toggle.setAttribute('aria-label', reveal ? 'Ficha nenosiri' : 'Onyesha nenosiri');
    });
  });

  // Password progress toward the 12-character minimum.
  var passwordInput = document.getElementById('password');
  var fill = document.getElementById('strength-fill');
  var text = document.getElementById('strength-text');
  passwordInput.addEventListener('input', function () {
    var length = passwordInput.value.length;
    var pct = Math.min(100, Math.round((length / 12) * 100));
    fill.style.width = pct + '%';
    if (length === 0) {
      fill.style.background = 'var(--danger)';
      text.textContent = 'Angalau herufi 12';
    } else if (length < 12) {
      fill.style.background = 'var(--danger)';
      text.textContent = 'Herufi ' + (12 - length) + ' zaidi zinahitajika';
    } else {
      fill.style.background = 'var(--green-600)';
      text.textContent = 'Nenosiri linatosha';
    }
  });

  function values() {
    return {
      name: document.getElementById('name').value.trim(),
      role: document.getElementById('role').value,
      country_code: document.getElementById('country').value
    };
  }

  async function post(url, body) {
    var response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Auth-Client': 'web'
      },
      body: JSON.stringify(body)
    });
    var data = await response.json().catch(function () { return {}; });
    return { ok: response.ok, status: response.status, data: data };
  }

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    clearErrors();

    var name = document.getElementById('name').value.trim();
    var email = document.getElementById('email').value.trim();
    var password = passwordInput.value;
    var confirmation = document.getElementById('password_confirmation').value;

    if (!name) { fieldError('name', 'Weka jina lako kamili.'); document.getElementById('name').focus(); return; }
    if (!email || email.indexOf('@') < 1) { fieldError('email', 'Weka barua pepe sahihi.'); document.getElementById('email').focus(); return; }
    if (password.length < 12) { fieldError('password', 'Nenosiri lazima liwe na angalau herufi 12.'); passwordInput.focus(); return; }
    if (password !== confirmation) { fieldError('password_confirmation', 'Manenosiri hayafanani.'); document.getElementById('password_confirmation').focus(); return; }
    if (!document.getElementById('terms').checked) { fieldError('terms', 'Lazima ukubali masharti ili kuendelea.'); return; }

    var button = document.getElementById('submit');
    button.disabled = true;
    button.querySelector('.label').textContent = 'Inasajili…';

    try {
      var payload = values();
      payload.email = email;
      payload.password = password;
      payload.password_confirmation = confirmation;

      var result = await post('/api/auth/register/email', payload);

      if (result.ok) {
        // Tells the farmer the next step exists. Registration now sends a
        // verification link, and a screen that just says "success" leaves them
        // wondering why there is mail waiting.
        showAlert('Akaunti imefunguliwa. Tumekutumia kiungo cha kuthibitisha kwenye ' + email + '.', 'ok');
        form.reset();
        fill.style.width = '0';
        setTimeout(function () { window.location.href = '/login?registered=1'; }, 2600);
        return;
      }

      if (result.data.errors) {
        Object.keys(result.data.errors).forEach(function (key) {
          fieldError(key, result.data.errors[key][0]);
        });
        showAlert(result.data.errors[Object.keys(result.data.errors)[0]][0]);
      } else if (result.status === 429) {
        showAlert('Umejaribu mara nyingi mfululizo. Subiri dakika moja kisha jaribu tena.');
      } else {
        showAlert(result.data.message || 'Usajili umeshindikana. Jaribu tena.');
      }
    } catch (error) {
      showAlert('Hakuna mtandao. Angalia muunganisho wako kisha jaribu tena.');
    } finally {
      button.disabled = false;
      button.querySelector('.label').textContent = 'Jisajili';
    }
  });

  async function social(provider, identityToken, name) {
    var payload = values();
    payload.provider = provider;
    payload.identity_token = identityToken;
    payload.name = name || payload.name;

    var result = await post('/api/auth/social', payload);
    if (!result.ok) {
      throw new Error(result.data.message
        || Object.values(result.data.errors || {}).flat()[0]
        || 'Imeshindikana.');
    }
    window.location.href = @json(\App\Support\AppDownload::hasWebBuild() ? '/app/web/' : '/download');
  }

  var googleButton = document.getElementById('google');
  if (googleButton) {
    googleButton.addEventListener('click', function () {
      try {
        google.accounts.id.initialize({
          client_id: googleClientId,
          callback: function (r) { social('google', r.credential).catch(function (e) { showAlert(e.message); }); }
        });
        google.accounts.id.prompt();
      } catch (error) {
        showAlert('Google sign-in haijakamilika kusanidiwa.');
      }
    });
  }

  var appleButton = document.getElementById('apple');
  if (appleButton) {
    appleButton.addEventListener('click', async function () {
      try {
        AppleID.auth.init({
          clientId: appleClientId, scope: 'name email',
          redirectURI: location.origin + '/register', usePopup: true
        });
        var r = await AppleID.auth.signIn();
        await social('apple', r.authorization.id_token, r.user && r.user.name && r.user.name.firstName);
      } catch (error) {
        showAlert('Apple sign-in haikukamilika.');
      }
    });
  }
});
</script>

@if(config('services.social.google_client_ids.0'))
  <script nonce="{{ $cspNonce ?? '' }}" src="https://accounts.google.com/gsi/client" async></script>
@endif
@if(config('services.social.apple_client_ids.0'))
  <script nonce="{{ $cspNonce ?? '' }}" src="https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js"></script>
@endif
@endsection
