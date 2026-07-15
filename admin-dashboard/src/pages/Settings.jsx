import { useState, useEffect, useRef } from 'react'
import { Save, Bell, Shield, Globe, CreditCard, Layout, Upload, Trash2, Image as ImageIcon } from 'lucide-react'

export default function Settings() {
  const [activeTab, setActiveTab] = useState('general')
  const [saved, setSaved] = useState(false)

  const [settings, setSettings] = useState({
    siteName: 'MkulimaForum',
    siteDescription: 'Digital Super-App for East African Farmers',
    maintenanceMode: false,
    allowRegistration: true,
    requireKyc: true,
    defaultCurrency: 'TZS',
    commissionRate: 2.5,
    minOrderAmount: 5000,
    smsNotifications: true,
    pushNotifications: true,
    emailNotifications: true,
    mpesaEnabled: true,
    tigoEnabled: true,
    escrowEnabled: true
  })

  // Landing Page Settings State
  const [landingSettings, setLandingSettings] = useState({
    hero_title: '',
    hero_tagline: '',
    hero_lead: '',
    badge_text: '',
    kicker_jinsi: '',
    title_jinsi: '',
    sub_jinsi: '',
    kicker_vipengele: '',
    title_vipengele: '',
    sub_vipengele: '',
  })
  const [landingLoading, setLandingLoading] = useState(false)

  // Logo upload state
  const [logoUrl, setLogoUrl] = useState('')
  const [logoPreview, setLogoPreview] = useState('')
  const [logoFile, setLogoFile] = useState(null)
  const [logoUploading, setLogoUploading] = useState(false)
  const [logoMessage, setLogoMessage] = useState('')
  const logoInputRef = useRef(null)

  useEffect(() => {
    if (activeTab === 'landing') {
      fetchLandingSettings()
    }
  }, [activeTab])

  const fetchLandingSettings = async () => {
    setLandingLoading(true)
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/settings/landing', {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (res.ok) {
        const data = await res.json()
        setLandingSettings({
          hero_title: data.hero_title || '',
          hero_tagline: data.hero_tagline || '',
          hero_lead: data.hero_lead || '',
          badge_text: data.badge_text || '',
          kicker_jinsi: data.kicker_jinsi || '',
          title_jinsi: data.title_jinsi || '',
          sub_jinsi: data.sub_jinsi || '',
          kicker_vipengele: data.kicker_vipengele || '',
          title_vipengele: data.title_vipengele || '',
          sub_vipengele: data.sub_vipengele || '',
        })
        setLogoUrl(data.logo_url || '')
      }
    } catch (err) {
      console.error('Failed to fetch landing settings:', err)
    } finally {
      setLandingLoading(false)
    }
  }

  const handleLogoSelect = (e) => {
    const file = e.target.files?.[0]
    if (!file) return
    setLogoFile(file)
    setLogoPreview(URL.createObjectURL(file))
    setLogoMessage('')
  }

  const handleLogoUpload = async () => {
    if (!logoFile) return
    setLogoUploading(true)
    setLogoMessage('')
    try {
      const token = localStorage.getItem('admin_token')
      const formData = new FormData()
      formData.append('logo', logoFile)
      const res = await fetch('/api/admin/settings/landing/logo', {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` },
        body: formData,
      })
      const data = await res.json()
      if (res.ok) {
        setLogoUrl(data.logo_url)
        setLogoFile(null)
        setLogoPreview('')
        setLogoMessage('Logo updated successfully!')
      } else {
        setLogoMessage(data.message || 'Upload failed')
      }
    } catch (err) {
      setLogoMessage('Network error while uploading logo')
    } finally {
      setLogoUploading(false)
    }
  }

  const handleLogoRemove = async () => {
    if (!confirm('Remove the current logo? The landing page will fall back to the default.')) return
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/settings/landing/logo', {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token}` },
      })
      if (res.ok) {
        setLogoUrl('')
        setLogoMessage('Logo removed.')
      }
    } catch (err) {
      setLogoMessage('Network error while removing logo')
    }
  }

  const handleSaveLanding = async () => {
    try {
      const token = localStorage.getItem('admin_token')
      const res = await fetch('/api/admin/settings/landing', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({ settings: landingSettings })
      })
      if (res.ok) {
        setSaved(true)
        setTimeout(() => setSaved(false), 3000)
      }
    } catch (err) {
      console.error('Failed to save landing settings:', err)
    }
  }

  const handleSave = () => {
    if (activeTab === 'landing') {
      handleSaveLanding()
    } else {
      setSaved(true)
      setTimeout(() => setSaved(false), 3000)
    }
  }

  const tabs = [
    { id: 'general', label: 'General', icon: Globe },
    { id: 'landing', label: 'Landing Page', icon: Layout },
    { id: 'notifications', label: 'Notifications', icon: Bell },
    { id: 'payments', label: 'Payments', icon: CreditCard },
    { id: 'security', label: 'Security', icon: Shield }
  ]

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Settings</h1>
        <p className="text-gray-500 mt-1">Configure platform settings</p>
      </div>

      {/* Tabs */}
      <div className="flex gap-2 border-b border-gray-200">
        {tabs.map(tab => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            className={`flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition-colors ${
              activeTab === tab.id
                ? 'border-green-600 text-green-700'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            }`}
          >
            <tab.icon className="w-4 h-4" />
            {tab.label}
          </button>
        ))}
      </div>

      {/* General Settings */}
      {activeTab === 'general' && (
        <div className="card space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Site Name
              </label>
              <input
                type="text"
                value={settings.siteName}
                onChange={(e) => setSettings({...settings, siteName: e.target.value})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Default Currency
              </label>
              <select
                value={settings.defaultCurrency}
                onChange={(e) => setSettings({...settings, defaultCurrency: e.target.value})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
              >
                <option value="TZS">Tanzanian Shilling (TZS)</option>
                <option value="KES">Kenyan Shilling (KES)</option>
                <option value="UGX">Ugandan Shilling (UGX)</option>
                <option value="RWF">Rwandan Franc (RWF)</option>
              </select>
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Site Description
              </label>
              <textarea
                value={settings.siteDescription}
                onChange={(e) => setSettings({...settings, siteDescription: e.target.value})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                rows="3"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Commission Rate (%)
              </label>
              <input
                type="number"
                value={settings.commissionRate}
                onChange={(e) => setSettings({...settings, commissionRate: parseFloat(e.target.value)})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                step="0.1"
                min="0"
                max="100"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Minimum Order Amount (TSh)
              </label>
              <input
                type="number"
                value={settings.minOrderAmount}
                onChange={(e) => setSettings({...settings, minOrderAmount: parseInt(e.target.value)})}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                min="0"
              />
            </div>
          </div>

          <div className="flex items-center gap-4">
            <label className="flex items-center gap-2">
              <input
                type="checkbox"
                checked={settings.maintenanceMode}
                onChange={(e) => setSettings({...settings, maintenanceMode: e.target.checked})}
                className="w-4 h-4 text-green-600 rounded focus:ring-green-500"
              />
              <span className="text-sm">Maintenance Mode</span>
            </label>
            <label className="flex items-center gap-2">
              <input
                type="checkbox"
                checked={settings.allowRegistration}
                onChange={(e) => setSettings({...settings, allowRegistration: e.target.checked})}
                className="w-4 h-4 text-green-600 rounded focus:ring-green-500"
              />
              <span className="text-sm">Allow New Registrations</span>
            </label>
            <label className="flex items-center gap-2">
              <input
                type="checkbox"
                checked={settings.requireKyc}
                onChange={(e) => setSettings({...settings, requireKyc: e.target.checked})}
                className="w-4 h-4 text-green-600 rounded focus:ring-green-500"
              />
              <span className="text-sm">Require KYC Verification</span>
            </label>
          </div>
        </div>
      )}

      {/* Landing Page Settings */}
      {activeTab === 'landing' && (
        <div className="card space-y-6">
          <h3 className="font-medium text-lg text-gray-900 border-b pb-2">Landing Page Hero & Main Content</h3>
          
          {landingLoading ? (
            <div className="flex items-center justify-center py-12">
              <div className="w-8 h-8 border-4 border-green-600 border-t-transparent rounded-full animate-spin" />
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Site Logo
                </label>
                <div className="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                  <div className="w-16 h-16 rounded-lg bg-white border border-gray-200 flex items-center justify-center overflow-hidden shrink-0">
                    {logoPreview || logoUrl ? (
                      <img src={logoPreview || logoUrl} alt="Logo" className="w-full h-full object-contain" />
                    ) : (
                      <ImageIcon className="w-6 h-6 text-gray-300" />
                    )}
                  </div>
                  <div className="flex-1 min-w-0">
                    <input
                      ref={logoInputRef}
                      type="file"
                      accept="image/png,image/jpeg,image/webp,image/svg+xml"
                      onChange={handleLogoSelect}
                      className="hidden"
                    />
                    <div className="flex flex-wrap items-center gap-2">
                      <button
                        type="button"
                        onClick={() => logoInputRef.current?.click()}
                        className="btn-secondary text-sm"
                      >
                        Choose file
                      </button>
                      {logoFile && (
                        <button
                          type="button"
                          onClick={handleLogoUpload}
                          disabled={logoUploading}
                          className="btn-primary text-sm flex items-center gap-1 disabled:opacity-50"
                        >
                          <Upload className="w-3.5 h-3.5" />
                          {logoUploading ? 'Uploading…' : 'Upload logo'}
                        </button>
                      )}
                      {logoUrl && !logoFile && (
                        <button
                          type="button"
                          onClick={handleLogoRemove}
                          className="text-sm text-red-600 hover:text-red-700 flex items-center gap-1"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                          Remove
                        </button>
                      )}
                    </div>
                    <p className="text-xs text-gray-500 mt-1">
                      PNG, JPG, WEBP or SVG, up to 2MB. Shown in the landing page header and footer.
                    </p>
                    {logoMessage && <p className="text-xs text-green-700 mt-1">{logoMessage}</p>}
                  </div>
                </div>
              </div>

              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Hero Badge Text
                </label>
                <input
                  type="text"
                  value={landingSettings.badge_text}
                  onChange={(e) => setLandingSettings({...landingSettings, badge_text: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                  placeholder="e.g. AI kwa Wakulima wa Tanzania"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Hero Title (HTML supported)
                </label>
                <textarea
                  value={landingSettings.hero_title}
                  onChange={(e) => setLandingSettings({...landingSettings, hero_title: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                  placeholder="e.g. Daktari wa Mimea<br>Mfukoni <span class='accent'>Mwako</span>"
                  rows="3"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Hero Tagline
                </label>
                <textarea
                  value={landingSettings.hero_tagline}
                  onChange={(e) => setLandingSettings({...landingSettings, hero_tagline: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                  placeholder="e.g. SKANI • TAMBUA • TIBU"
                  rows="3"
                />
              </div>

              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Hero Lead Paragraph (HTML supported)
                </label>
                <textarea
                  value={landingSettings.hero_lead}
                  onChange={(e) => setLandingSettings({...landingSettings, hero_lead: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                  placeholder="e.g. Piga picha ya mmea wako — AI Plant Scanner itambue magonjwa..."
                  rows="3"
                />
              </div>

              <h3 className="md:col-span-2 font-medium text-lg text-gray-900 border-b pb-2 pt-4">"Jinsi Inavyofanya Kazi" Section</h3>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Kicker (Small Category Text)
                </label>
                <input
                  type="text"
                  value={landingSettings.kicker_jinsi}
                  onChange={(e) => setLandingSettings({...landingSettings, kicker_jinsi: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                  placeholder="e.g. Jinsi Inavyofanya Kazi"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Section Title
                </label>
                <input
                  type="text"
                  value={landingSettings.title_jinsi}
                  onChange={(e) => setLandingSettings({...landingSettings, title_jinsi: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                  placeholder="e.g. Hatua 3 tu — chini ya dakika moja"
                />
              </div>

              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Section Subtitle
                </label>
                <textarea
                  value={landingSettings.sub_jinsi}
                  onChange={(e) => setLandingSettings({...landingSettings, sub_jinsi: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                  placeholder="e.g. Huhitaji ujuzi wowote wa kiufundi..."
                  rows="2"
                />
              </div>

              <h3 className="md:col-span-2 font-medium text-lg text-gray-900 border-b pb-2 pt-4">"Vipengele" (Features) Section</h3>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Kicker (Small Category Text)
                </label>
                <input
                  type="text"
                  value={landingSettings.kicker_vipengele}
                  onChange={(e) => setLandingSettings({...landingSettings, kicker_vipengele: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                  placeholder="e.g. Vipengele"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Section Title
                </label>
                <input
                  type="text"
                  value={landingSettings.title_vipengele}
                  onChange={(e) => setLandingSettings({...landingSettings, title_vipengele: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                  placeholder="e.g. Zaidi ya scanner — mfumo kamili wa kilimo"
                />
              </div>

              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Section Subtitle
                </label>
                <textarea
                  value={landingSettings.sub_vipengele}
                  onChange={(e) => setLandingSettings({...landingSettings, sub_vipengele: e.target.value})}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"
                  placeholder="e.g. Kila kitu mkulima anachohitaji..."
                  rows="2"
                />
              </div>
            </div>
          )}
        </div>
      )}

      {/* Notifications */}
      {activeTab === 'notifications' && (
        <div className="card space-y-6">
          <h3 className="font-medium">Notification Channels</h3>
          <div className="space-y-4">
            <label className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p className="font-medium">SMS Notifications</p>
                <p className="text-sm text-gray-500">Send order updates via SMS</p>
              </div>
              <input
                type="checkbox"
                checked={settings.smsNotifications}
                onChange={(e) => setSettings({...settings, smsNotifications: e.target.checked})}
                className="w-5 h-5 text-green-600 rounded focus:ring-green-500"
              />
            </label>
            <label className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p className="font-medium">Push Notifications</p>
                <p className="text-sm text-gray-500">Send push notifications to mobile app</p>
              </div>
              <input
                type="checkbox"
                checked={settings.pushNotifications}
                onChange={(e) => setSettings({...settings, pushNotifications: e.target.checked})}
                className="w-5 h-5 text-green-600 rounded focus:ring-green-500"
              />
            </label>
            <label className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p className="font-medium">Email Notifications</p>
                <p className="text-sm text-gray-500">Send order updates via email</p>
              </div>
              <input
                type="checkbox"
                checked={settings.emailNotifications}
                onChange={(e) => setSettings({...settings, emailNotifications: e.target.checked})}
                className="w-5 h-5 text-green-600 rounded focus:ring-green-500"
              />
            </label>
          </div>
        </div>
      )}

      {/* Payments */}
      {activeTab === 'payments' && (
        <div className="card space-y-6">
          <h3 className="font-medium">Payment Methods</h3>
          <div className="space-y-4">
            <label className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p className="font-medium">M-Pesa</p>
                <p className="text-sm text-gray-500">Enable M-Pesa mobile money payments</p>
              </div>
              <input
                type="checkbox"
                checked={settings.mpesaEnabled}
                onChange={(e) => setSettings({...settings, mpesaEnabled: e.target.checked})}
                className="w-5 h-5 text-green-600 rounded focus:ring-green-500"
              />
            </label>
            <label className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p className="font-medium">Tigo Pesa</p>
                <p className="text-sm text-gray-500">Enable Tigo Pesa mobile money payments</p>
              </div>
              <input
                type="checkbox"
                checked={settings.tigoEnabled}
                onChange={(e) => setSettings({...settings, tigoEnabled: e.target.checked})}
                className="w-5 h-5 text-green-600 rounded focus:ring-green-500"
              />
            </label>
            <label className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p className="font-medium">Escrow System</p>
                <p className="text-sm text-gray-500">Enable secure escrow payments</p>
              </div>
              <input
                type="checkbox"
                checked={settings.escrowEnabled}
                onChange={(e) => setSettings({...settings, escrowEnabled: e.target.checked})}
                className="w-5 h-5 text-green-600 rounded focus:ring-green-500"
              />
            </label>
          </div>
        </div>
      )}

      {/* Security */}
      {activeTab === 'security' && (
        <div className="card space-y-6">
          <h3 className="font-medium">Security Settings</h3>
          <div className="space-y-4">
            <div className="p-4 bg-gray-50 rounded-lg">
              <p className="font-medium">Two-Factor Authentication</p>
              <p className="text-sm text-gray-500 mb-3">Require 2FA for admin accounts</p>
              <button className="btn-secondary text-sm">Configure 2FA</button>
            </div>
            <div className="p-4 bg-gray-50 rounded-lg">
              <p className="font-medium">API Keys</p>
              <p className="text-sm text-gray-500 mb-3">Manage API access keys</p>
              <button className="btn-secondary text-sm">Manage Keys</button>
            </div>
            <div className="p-4 bg-gray-50 rounded-lg">
              <p className="font-medium">Session Management</p>
              <p className="text-sm text-gray-500 mb-3">Active sessions: 1</p>
              <button className="btn-secondary text-sm text-red-600">Revoke All Sessions</button>
            </div>
          </div>
        </div>
      )}

      {/* Save Button */}
      <div className="flex items-center gap-4">
        <button
          onClick={handleSave}
          className="btn-primary flex items-center gap-2"
        >
          <Save className="w-4 h-4" />
          Save Changes
        </button>
        {saved && (
          <span className="text-green-600 text-sm">Settings saved successfully!</span>
        )}
      </div>
    </div>
  )
}