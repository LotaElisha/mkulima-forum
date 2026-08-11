import React, { useState, useEffect } from 'react';
import { 
  ShieldCheck, AlertTriangle, FileText, Activity, Radio, 
  Store, Database, RefreshCw, Upload, Eye, CheckCircle, XCircle 
} from 'lucide-react';

export default function MkulimaVerify() {
  const [activeTab, setActiveTab] = useState('registry');
  const [stats, setStats] = useState(null);
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);

  // Advisory Form state
  const [advTitleSw, setAdvTitleSw] = useState('');
  const [advTitleEn, setAdvTitleEn] = useState('');
  const [advBodySw, setAdvBodySw] = useState('');
  const [advBodyEn, setAdvBodyEn] = useState('');
  const [advType, setAdvType] = useState('counterfeit_alert');

  useEffect(() => {
    fetchVerifyStats();
  }, []);

  const fetchVerifyStats = async () => {
    try {
      const res = await fetch('/api/admin/verify/stats', {
        headers: { }
      });
      if (res.ok) {
        const data = await res.json();
        setStats(data.data);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateAdvisory = async (e) => {
    e.preventDefault();
    try {
      const res = await fetch('/api/admin/verify/advisories', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',

        },
        body: JSON.stringify({
          type: advType,
          title_sw: advTitleSw,
          title_en: advTitleEn,
          body_sw: advBodySw,
          body_en: advBodyEn,
          dispatch_now: true
        })
      });
      if (res.ok) {
        alert('Advisory dispatched successfully across targeted channels!');
        setAdvTitleSw(''); setAdvTitleEn(''); setAdvBodySw(''); setAdvBodyEn('');
      }
    } catch (e) {
      alert('Error creating advisory');
    }
  };

  return (
    <div className="p-6 max-w-7xl mx-auto space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div>
          <div className="flex items-center gap-3">
            <div className="p-3 bg-emerald-50 text-emerald-700 rounded-xl">
              <ShieldCheck className="w-8 h-8" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Mkulima Verify</h1>
              <p className="text-sm text-gray-500">Anti-Counterfeit Infrastructure & Regulatory Trust Engine</p>
            </div>
          </div>
        </div>
        <div className="flex items-center gap-3">
          <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
            Phase 1 Active • Provider Swappable
          </span>
        </div>
      </div>

      {/* Stats Summary Strip */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div className="flex items-center justify-between text-gray-500 text-sm font-medium">
            <span>Total Product Scans</span>
            <Activity className="w-5 h-5 text-emerald-600" />
          </div>
          <p className="text-2xl font-bold text-gray-900 mt-2">{stats?.total_scans ?? 0}</p>
          <span className="text-xs text-gray-400">Scanned via barcode/serial/USSD</span>
        </div>

        <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div className="flex items-center justify-between text-gray-500 text-sm font-medium">
            <span>Regulated Products</span>
            <Database className="w-5 h-5 text-blue-600" />
          </div>
          <p className="text-2xl font-bold text-gray-900 mt-2">{stats?.total_products ?? 0}</p>
          <span className="text-xs text-gray-400">TOSCI, TPHPA, TBS, TFRA records</span>
        </div>

        <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div className="flex items-center justify-between text-gray-500 text-sm font-medium">
            <span>Counterfeit Incidents</span>
            <AlertTriangle className="w-5 h-5 text-amber-600" />
          </div>
          <p className="text-2xl font-bold text-gray-900 mt-2">{stats?.total_reports ?? 0}</p>
          <span className="text-xs text-gray-400">Farmer community reports</span>
        </div>

        <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div className="flex items-center justify-between text-gray-500 text-sm font-medium">
            <span>Verified Agrodealers</span>
            <Store className="w-5 h-5 text-purple-600" />
          </div>
          <p className="text-2xl font-bold text-gray-900 mt-2">{stats?.verified_dealers ?? 0}</p>
          <span className="text-xs text-gray-400">Mkulima Verified & Reg matched</span>
        </div>
      </div>

      {/* Tabs */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div className="flex border-b border-gray-100 overflow-x-auto">
          {[
            { id: 'registry', label: 'Product Registry', icon: Database },
            { id: 'reports', label: 'Counterfeit Incidents', icon: AlertTriangle },
            { id: 'advisories', label: 'Farmer Advisories', icon: Radio },
            { id: 'dealers', label: 'Agrodealer KYC', icon: Store },
            { id: 'sources', label: 'Regulatory Sources', icon: RefreshCw },
          ].map((tab) => {
            const Icon = tab.icon;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`flex items-center gap-2 px-6 py-4 font-semibold text-sm transition-colors border-b-2 ${
                  activeTab === tab.id
                    ? 'border-emerald-600 text-emerald-700 bg-emerald-50/30'
                    : 'border-transparent text-gray-500 hover:text-gray-700'
                }`}
              >
                <Icon className="w-4 h-4" />
                {tab.label}
              </button>
            );
          })}
        </div>

        <div className="p-6">
          {activeTab === 'registry' && (
            <div className="space-y-4">
              <h3 className="text-lg font-bold text-gray-900">Regulated Agricultural Inputs Registry</h3>
              <p className="text-sm text-gray-500">
                Single canonical registry for seeds (TOSCI), pesticides (TPHPA), fertilizers (TFRA), and quality marks (TBS).
              </p>
              <div className="p-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                <Database className="w-12 h-12 text-gray-400 mx-auto mb-3" />
                <p className="font-semibold text-gray-700">Registry Active (0 Products loaded in current DB)</p>
                <p className="text-xs text-gray-500 mt-1">Upload CSV or sync periodic regulatory list from Regulatory Sources tab.</p>
              </div>
            </div>
          )}

          {activeTab === 'reports' && (
            <div className="space-y-4">
              <h3 className="text-lg font-bold text-gray-900">Farmer Counterfeit Incident Reports (A11)</h3>
              <p className="text-sm text-gray-500">
                Farmer incident submissions with SHA-256 evidence hashing and regulator PDF/JSON case file generation.
              </p>
              <div className="p-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                <AlertTriangle className="w-12 h-12 text-amber-500 mx-auto mb-3" />
                <p className="font-semibold text-gray-700">No Counterfeit Incidents Reported</p>
                <p className="text-xs text-gray-500 mt-1">Reports submitted by farmers will appear here with case numbers (MF-CF-2026-XXXXXX).</p>
              </div>
            </div>
          )}

          {activeTab === 'advisories' && (
            <div className="space-y-6">
              <div>
                <h3 className="text-lg font-bold text-gray-900">Farmer Awareness & Advisories (A17 & Part C)</h3>
                <p className="text-sm text-gray-500">
                  Compose and dispatch target advisories to App, Push, SMS, and WhatsApp Alert Channels simultaneously.
                </p>
              </div>

              <form onSubmit={handleCreateAdvisory} className="space-y-4 max-w-2xl bg-gray-50 p-6 rounded-xl border border-gray-200">
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1">Advisory Type</label>
                  <select
                    value={advType}
                    onChange={(e) => setAdvType(e.target.value)}
                    className="w-full p-2.5 bg-white border border-gray-300 rounded-lg text-sm"
                  >
                    <option value="counterfeit_alert">Counterfeit Alert / Tahadhari ya Bidhaa Feki</option>
                    <option value="recall">Recall Notice / Bidhaa Imerudishwa</option>
                    <option value="licence_warning">Agrodealer Warning / Tahadhari ya Duka</option>
                    <option value="unsafe_pesticide">Unsafe Pesticide Warning / Dawa Hatari</option>
                    <option value="seasonal_seed">Seasonal Seed Warning / Mbegu za Msimu</option>
                  </select>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-1">Title (Swahili)</label>
                    <input
                      type="text"
                      value={advTitleSw}
                      onChange={(e) => setAdvTitleSw(e.target.value)}
                      placeholder="e.g. Tahadhari ya Mbegu Feki za Mahindi"
                      className="w-full p-2.5 bg-white border border-gray-300 rounded-lg text-sm"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-1">Title (English)</label>
                    <input
                      type="text"
                      value={advTitleEn}
                      onChange={(e) => setAdvTitleEn(e.target.value)}
                      placeholder="e.g. Counterfeit Maize Seed Alert"
                      className="w-full p-2.5 bg-white border border-gray-300 rounded-lg text-sm"
                      required
                    />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-1">Body (Swahili)</label>
                    <textarea
                      value={advBodySw}
                      onChange={(e) => setAdvBodySw(e.target.value)}
                      rows={3}
                      placeholder="Maelezo kwa Swahili..."
                      className="w-full p-2.5 bg-white border border-gray-300 rounded-lg text-sm"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-1">Body (English)</label>
                    <textarea
                      value={advBodyEn}
                      onChange={(e) => setAdvBodyEn(e.target.value)}
                      rows={3}
                      placeholder="Description in English..."
                      className="w-full p-2.5 bg-white border border-gray-300 rounded-lg text-sm"
                      required
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  className="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-lg flex items-center gap-2"
                >
                  <Radio className="w-4 h-4" />
                  Dispatch Advisory Across All Channels
                </button>
              </form>
            </div>
          )}

          {activeTab === 'dealers' && (
            <div className="space-y-4">
              <h3 className="text-lg font-bold text-gray-900">Agrodealer KYC & Licence Management (A7)</h3>
              <p className="text-sm text-gray-500">
                Trust levels: <code>MKULIMA_VERIFIED</code> and <code>REGULATOR_RECORD_MATCHED</code>. Expiry job runs daily.
              </p>
              <div className="p-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                <Store className="w-12 h-12 text-purple-600 mx-auto mb-3" />
                <p className="font-semibold text-gray-700">No Agrodealers Registered Yet</p>
                <p className="text-xs text-gray-500 mt-1">Sellers of regulated inputs submit KYC documents for review here.</p>
              </div>
            </div>
          )}

          {activeTab === 'sources' && (
            <div className="space-y-4">
              <h3 className="text-lg font-bold text-gray-900">Regulatory Data Source Adapters (A2 / A3)</h3>
              <p className="text-sm text-gray-500">
                Swappable adapter layer: TOSCI, TPHPA (Pesticides), TBS, TFRA, MAFC. Supports manual import, public dataset, and official API modes.
              </p>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                {[
                  { name: 'TPHPA — Pesticides Authority', acronym: 'TPHPA', mode: 'manual_import', status: 'Active (CSV Mode)' },
                  { name: 'TOSCI — Seed Certification', acronym: 'TOSCI', mode: 'manual_import', status: 'Active (CSV Mode)' },
                  { name: 'TFRA — Fertilizer Authority', acronym: 'TFRA', mode: 'manual_import', status: 'Active (CSV Mode)' },
                  { name: 'TBS — Bureau of Standards', acronym: 'TBS', mode: 'manual_import', status: 'Active (CSV Mode)' },
                ].map((src, i) => (
                  <div key={i} className="p-4 bg-gray-50 rounded-xl border border-gray-200 flex justify-between items-center">
                    <div>
                      <h4 className="font-bold text-gray-900 text-sm">{src.name}</h4>
                      <span className="text-xs text-gray-500">Backing Mode: {src.mode}</span>
                    </div>
                    <span className="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-semibold">
                      {src.status}
                    </span>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
