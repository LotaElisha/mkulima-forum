import { useState, useEffect, useCallback } from 'react'
import {
  Server, Mail, MessageSquare, Phone, Link2, Save, Send,
  RefreshCw, AlertTriangle, CheckCircle2, XCircle, Lock, Copy, ShieldAlert,
} from 'lucide-react'
import { systemApi } from '../utils/api'

const GROUP_ICONS = {
  general: Server,
  mail: Mail,
  sms: MessageSquare,
  ivr: Phone,
  links: Link2,
}

const STATUS_STYLES = {
  ok: { icon: CheckCircle2, cls: 'text-green-600', bg: 'bg-green-50 border-green-200' },
  warn: { icon: AlertTriangle, cls: 'text-amber-600', bg: 'bg-amber-50 border-amber-200' },
  fail: { icon: XCircle, cls: 'text-red-600', bg: 'bg-red-50 border-red-200' },
}

export default function SystemConfiguration() {
  const [groups, setGroups] = useState([])
  const [canManageSecrets, setCanManageSecrets] = useState(false)
  const [environment, setEnvironment] = useState('')
  const [activeTab, setActiveTab] = useState('readiness')
  const [drafts, setDrafts] = useState({})
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [banner, setBanner] = useState(null)
  const [readiness, setReadiness] = useState(null)
  const [confirming, setConfirming] = useState(null)
  const [revealedSecret, setRevealedSecret] = useState(null)
  const [testEmailTo, setTestEmailTo] = useState('')
  const [testSmsTo, setTestSmsTo] = useState('')
  const [testing, setTesting] = useState(false)

  const notify = (type, text) => {
    setBanner({ type, text })
    if (type === 'success') setTimeout(() => setBanner(null), 6000)
  }

  const loadConfiguration = useCallback(async () => {
    try {
      const { data } = await systemApi.getConfiguration()
      setGroups(data.groups)
      setCanManageSecrets(data.can_manage_secrets)
      setEnvironment(data.environment)
    } catch {
      notify('error', 'Could not load configuration. You may not have permission.')
    } finally {
      setLoading(false)
    }
  }, [])

  const loadReadiness = useCallback(async () => {
    try {
      const { data } = await systemApi.getReadiness()
      setReadiness(data)
    } catch {
      /* the banner from loadConfiguration already covers a permission problem */
    }
  }, [])

  useEffect(() => {
    loadConfiguration()
    loadReadiness()
  }, [loadConfiguration, loadReadiness])

  const setDraft = (key, value) => setDrafts((d) => ({ ...d, [key]: value }))

  const save = async (groupKey, confirmed = false) => {
    const group = groups.find((g) => g.key === groupKey)
    if (!group) return

    const payload = {}
    group.settings.forEach((s) => {
      if (Object.prototype.hasOwnProperty.call(drafts, s.key)) payload[s.key] = drafts[s.key]
    })

    if (Object.keys(payload).length === 0) {
      notify('info', 'Nothing changed.')
      return
    }

    setSaving(true)
    setBanner(null)
    try {
      const { data } = await systemApi.saveConfiguration(payload, confirmed)
      notify('success', data.message)
      setDrafts({})
      setConfirming(null)
      await Promise.all([loadConfiguration(), loadReadiness()])
    } catch (error) {
      const res = error.response
      if (res?.status === 409 && res.data?.requires_confirmation) {
        // High-impact setting: make the operator say so explicitly.
        setConfirming({ group: groupKey, key: res.data.requires_confirmation, message: res.data.message })
      } else if (res?.status === 422 && res.data?.errors) {
        notify('error', Object.values(res.data.errors).flat()[0])
      } else {
        notify('error', res?.data?.message || 'Could not save. Please try again.')
      }
    } finally {
      setSaving(false)
    }
  }

  const sendTestEmail = async () => {
    setTesting(true)
    setBanner(null)
    try {
      const { data } = await systemApi.sendTestEmail(testEmailTo)
      notify('success', data.message)
      loadReadiness()
    } catch (error) {
      const d = error.response?.data
      notify('error', [d?.message, d?.detail].filter(Boolean).join(' — '))
    } finally {
      setTesting(false)
    }
  }

  const sendTestSms = async () => {
    setTesting(true)
    setBanner(null)
    try {
      const { data } = await systemApi.sendTestSms(testSmsTo)
      notify('success', data.message)
    } catch (error) {
      notify('error', error.response?.data?.message || 'Could not send the test message.')
    } finally {
      setTesting(false)
    }
  }

  const rotateSecret = async (channel) => {
    setSaving(true)
    try {
      const { data } = await systemApi.rotateWebhookSecret(channel)
      setRevealedSecret({ channel, secret: data.secret, message: data.message })
      await Promise.all([loadConfiguration(), loadReadiness()])
    } catch (error) {
      notify('error', error.response?.data?.message || 'Could not rotate the secret.')
    } finally {
      setSaving(false)
    }
  }

  if (loading) {
    return <div className="p-8 text-gray-500">Loading configuration…</div>
  }

  const tabs = [{ key: 'readiness', label: 'Readiness' }, ...groups.map((g) => ({ key: g.key, label: g.label }))]

  return (
    <div className="p-6 max-w-5xl">
      <header className="mb-6">
        <div className="flex items-center gap-3">
          <h1 className="text-2xl font-bold text-gray-900">System Configuration</h1>
          {environment && (
            <span className={`px-2 py-0.5 rounded text-xs font-semibold ${
              environment === 'production' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'
            }`}>{environment}</span>
          )}
        </div>
        <p className="text-sm text-gray-500 mt-1">
          Production settings, managed here instead of by editing <code>.env</code> on the server.
          Values saved here override <code>.env</code> and take effect immediately.
        </p>
      </header>

      {banner && (
        <div className={`mb-5 px-4 py-3 rounded-lg border text-sm ${
          banner.type === 'success' ? 'bg-green-50 border-green-200 text-green-800'
            : banner.type === 'info' ? 'bg-gray-50 border-gray-200 text-gray-700'
            : 'bg-red-50 border-red-200 text-red-800'
        }`}>{banner.text}</div>
      )}

      {revealedSecret && (
        <div className="mb-5 p-4 rounded-lg border border-amber-300 bg-amber-50">
          <div className="flex items-start gap-2">
            <ShieldAlert className="w-5 h-5 text-amber-600 flex-none mt-0.5" />
            <div className="min-w-0 flex-1">
              <p className="text-sm text-amber-900 font-medium">{revealedSecret.message}</p>
              <div className="mt-2 flex items-center gap-2">
                <code className="flex-1 px-3 py-2 bg-white border border-amber-200 rounded text-xs break-all">
                  {revealedSecret.secret}
                </code>
                <button
                  onClick={() => navigator.clipboard?.writeText(revealedSecret.secret)}
                  className="px-3 py-2 bg-amber-600 text-white rounded text-xs font-medium hover:bg-amber-700 inline-flex items-center gap-1"
                >
                  <Copy className="w-3.5 h-3.5" /> Copy
                </button>
              </div>
              <button onClick={() => setRevealedSecret(null)} className="mt-3 text-xs text-amber-800 underline">
                I have saved it — hide
              </button>
            </div>
          </div>
        </div>
      )}

      <nav className="flex gap-1 border-b border-gray-200 mb-6 overflow-x-auto">
        {tabs.map((t) => (
          <button
            key={t.key}
            onClick={() => setActiveTab(t.key)}
            className={`px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px ${
              activeTab === t.key
                ? 'border-green-600 text-green-700'
                : 'border-transparent text-gray-500 hover:text-gray-800'
            }`}
          >{t.label}</button>
        ))}
      </nav>

      {activeTab === 'readiness' && <Readiness data={readiness} onRefresh={loadReadiness} />}

      {groups.filter((g) => g.key === activeTab).map((group) => {
        const Icon = GROUP_ICONS[group.key] || Server
        return (
          <section key={group.key}>
            <div className="flex items-center gap-2 mb-4">
              <Icon className="w-5 h-5 text-gray-400" />
              <h2 className="font-semibold text-gray-900">{group.label}</h2>
            </div>

            <div className="space-y-5 bg-white border border-gray-200 rounded-xl p-6">
              {group.settings.map((s) => (
                <Field
                  key={s.key}
                  setting={s}
                  draft={drafts[s.key]}
                  onChange={(v) => setDraft(s.key, v)}
                />
              ))}

              {confirming?.group === group.key && (
                <div className="p-4 rounded-lg border border-red-300 bg-red-50">
                  <p className="text-sm text-red-900">{confirming.message}</p>
                  <div className="mt-3 flex gap-2">
                    <button
                      onClick={() => save(group.key, true)}
                      disabled={saving}
                      className="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50"
                    >Yes, apply it</button>
                    <button
                      onClick={() => setConfirming(null)}
                      className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm"
                    >Cancel</button>
                  </div>
                </div>
              )}

              <div className="pt-2 flex items-center gap-3">
                <button
                  onClick={() => save(group.key)}
                  disabled={saving}
                  className="px-4 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 disabled:opacity-50 inline-flex items-center gap-2"
                >
                  <Save className="w-4 h-4" /> {saving ? 'Saving…' : 'Save changes'}
                </button>
                {!canManageSecrets && group.settings.some((s) => s.secret) && (
                  <span className="text-xs text-gray-500 inline-flex items-center gap-1">
                    <Lock className="w-3.5 h-3.5" /> Secrets on this tab require a superadmin
                  </span>
                )}
              </div>
            </div>

            {group.key === 'mail' && (
              <TestPanel
                title="Send a test email"
                blurb="A saved configuration is not proof of delivery. Send a real message before you rely on password reset."
                placeholder="you@example.com"
                value={testEmailTo}
                onChange={setTestEmailTo}
                onSend={sendTestEmail}
                busy={testing}
              />
            )}

            {group.key === 'sms' && (
              <>
                <TestPanel
                  title="Send a test SMS"
                  blurb="Costs one message. The number must be in 255XXXXXXXXX form."
                  placeholder="255712345678"
                  value={testSmsTo}
                  onChange={setTestSmsTo}
                  onSend={sendTestSms}
                  busy={testing}
                />
                {canManageSecrets && (
                  <RotatePanel channel="sms" onRotate={rotateSecret} busy={saving} />
                )}
              </>
            )}

            {group.key === 'ivr' && canManageSecrets && (
              <RotatePanel channel="ivr" onRotate={rotateSecret} busy={saving} />
            )}
          </section>
        )
      })}
    </div>
  )
}

function Field({ setting, draft, onChange }) {
  const value = draft !== undefined ? draft : (setting.value ?? '')
  const disabled = !setting.can_edit

  return (
    <div>
      <label className="block text-sm font-medium text-gray-800 mb-1">
        {setting.label}
        {setting.secret && <Lock className="w-3 h-3 inline ml-1.5 -mt-0.5 text-gray-400" />}
        {setting.high_impact && (
          <span className="ml-2 px-1.5 py-0.5 rounded bg-red-50 text-red-700 text-[10px] font-semibold align-middle">
            HIGH IMPACT
          </span>
        )}
      </label>

      {setting.type === 'json' ? (
        <HostList value={value} onChange={onChange} disabled={disabled} />
      ) : setting.options?.length ? (
        <select
          value={value}
          disabled={disabled}
          onChange={(e) => onChange(e.target.value)}
          className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm disabled:bg-gray-50 disabled:text-gray-400"
        >
          <option value="">—</option>
          {setting.options.map((o) => <option key={o} value={o}>{o}</option>)}
        </select>
      ) : (
        <input
          type={setting.secret ? 'password' : setting.type === 'integer' ? 'number' : 'text'}
          value={value}
          disabled={disabled}
          autoComplete={setting.secret ? 'new-password' : 'off'}
          placeholder={setting.secret ? (setting.is_set ? '•••••••• (leave blank to keep)' : 'Not set') : ''}
          onChange={(e) => onChange(e.target.value)}
          className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm disabled:bg-gray-50 disabled:text-gray-400"
        />
      )}

      <div className="flex items-start justify-between gap-4 mt-1">
        {setting.help && <p className="text-xs text-gray-500 flex-1">{setting.help}</p>}
        {setting.secret && (
          <span className={`text-xs flex-none ${setting.is_set ? 'text-green-700' : 'text-gray-400'}`}>
            {setting.is_set ? 'Configured' : 'Not set'}
          </span>
        )}
      </div>
    </div>
  )
}

/** Add/remove hosts rather than making an operator edit a comma-separated string. */
function HostList({ value, onChange, disabled }) {
  const hosts = Array.isArray(value) ? value : []
  const [entry, setEntry] = useState('')
  const [error, setError] = useState('')

  const add = () => {
    const host = entry.trim().toLowerCase().replace(/^https?:\/\//, '').replace(/\/.*$/, '')
    if (!host) return
    if (!/^[a-z0-9.-]+\.[a-z]{2,}$/.test(host)) {
      setError('That does not look like a hostname.')
      return
    }
    if (hosts.includes(host)) {
      setError('Already in the list.')
      return
    }
    setError('')
    setEntry('')
    onChange([...hosts, host])
  }

  return (
    <div>
      <div className="flex flex-wrap gap-1.5 mb-2">
        {hosts.length === 0 && <span className="text-xs text-gray-400">No hosts allowed yet.</span>}
        {hosts.map((h) => (
          <span key={h} className="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 rounded text-xs">
            {h}
            {!disabled && (
              <button
                onClick={() => onChange(hosts.filter((x) => x !== h))}
                className="text-gray-500 hover:text-red-600"
                aria-label={`Remove ${h}`}
              >×</button>
            )}
          </span>
        ))}
      </div>
      {!disabled && (
        <div className="flex gap-2">
          <input
            value={entry}
            onChange={(e) => setEntry(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), add())}
            placeholder="example.com"
            className="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
          />
          <button onClick={add} className="px-3 py-2 bg-gray-800 text-white rounded-lg text-sm">Add</button>
        </div>
      )}
      {error && <p className="text-xs text-red-600 mt-1">{error}</p>}
    </div>
  )
}

function TestPanel({ title, blurb, placeholder, value, onChange, onSend, busy }) {
  return (
    <div className="mt-5 bg-white border border-gray-200 rounded-xl p-6">
      <h3 className="font-semibold text-gray-900 text-sm">{title}</h3>
      <p className="text-xs text-gray-500 mt-1 mb-3">{blurb}</p>
      <div className="flex gap-2">
        <input
          value={value}
          onChange={(e) => onChange(e.target.value)}
          placeholder={placeholder}
          className="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
        />
        <button
          onClick={onSend}
          disabled={busy || !value}
          className="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium disabled:opacity-40 inline-flex items-center gap-2"
        >
          <Send className="w-4 h-4" /> {busy ? 'Sending…' : 'Send'}
        </button>
      </div>
    </div>
  )
}

function RotatePanel({ channel, onRotate, busy }) {
  return (
    <div className="mt-5 bg-white border border-gray-200 rounded-xl p-6">
      <h3 className="font-semibold text-gray-900 text-sm">Rotate the {channel.toUpperCase()} webhook secret</h3>
      <p className="text-xs text-gray-500 mt-1 mb-3">
        Generates a new secret and shows it once. You must set the same value at the provider,
        sent as the <code>X-Webhook-Signature</code> header, or callbacks will start being refused.
      </p>
      <button
        onClick={() => onRotate(channel)}
        disabled={busy}
        className="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium disabled:opacity-40 inline-flex items-center gap-2"
      >
        <RefreshCw className="w-4 h-4" /> Rotate secret
      </button>
    </div>
  )
}

function Readiness({ data, onRefresh }) {
  if (!data) return <div className="text-gray-500 text-sm">Loading readiness…</div>

  const grouped = data.checks.reduce((acc, c) => {
    (acc[c.group] ||= []).push(c)
    return acc
  }, {})

  return (
    <section>
      <div className={`mb-5 px-4 py-3 rounded-lg border ${
        data.ready ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'
      }`}>
        <div className="flex items-center justify-between gap-4">
          <div>
            <p className={`font-semibold text-sm ${data.ready ? 'text-green-900' : 'text-red-900'}`}>
              {data.ready ? 'Ready to serve production traffic' : `${data.summary.fail} blocking issue(s)`}
            </p>
            <p className="text-xs mt-0.5 text-gray-600">
              {data.summary.ok} passed · {data.summary.warn} warning(s) · {data.summary.fail} blocking
            </p>
          </div>
          <button onClick={onRefresh} className="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs inline-flex items-center gap-1.5">
            <RefreshCw className="w-3.5 h-3.5" /> Re-check
          </button>
        </div>
      </div>

      <div className="space-y-5">
        {Object.entries(grouped).map(([group, checks]) => (
          <div key={group}>
            <h3 className="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">{group}</h3>
            <div className="bg-white border border-gray-200 rounded-xl divide-y divide-gray-100">
              {checks.map((c) => {
                const style = STATUS_STYLES[c.status] || STATUS_STYLES.warn
                const Icon = style.icon
                return (
                  <div key={c.name} className="px-4 py-3 flex items-start gap-3">
                    <Icon className={`w-4 h-4 mt-0.5 flex-none ${style.cls}`} />
                    <div className="min-w-0 flex-1">
                      <div className="flex items-center gap-2 flex-wrap">
                        <span className="text-sm text-gray-900">{c.name}</span>
                        {c.detail && <code className="text-xs text-gray-500">{c.detail}</code>}
                        {c.blocking && (
                          <span className="px-1.5 py-0.5 rounded bg-red-100 text-red-700 text-[10px] font-semibold">
                            BLOCKING
                          </span>
                        )}
                      </div>
                      {c.consequence && <p className="text-xs text-gray-500 mt-0.5">{c.consequence}</p>}
                    </div>
                  </div>
                )
              })}
            </div>
          </div>
        ))}
      </div>
    </section>
  )
}
