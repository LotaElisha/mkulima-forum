import { useState } from 'react'
import { Save, Bell, Shield, Globe, CreditCard } from 'lucide-react'

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

  const handleSave = () => {
    setSaved(true)
    setTimeout(() => setSaved(false), 3000)
  }

  const tabs = [
    { id: 'general', label: 'General', icon: Globe },
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