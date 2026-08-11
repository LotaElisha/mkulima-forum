import React, { useState, useEffect } from 'react';
import { 
  Globe, MessageSquare, Share2, Plus, Edit2, Trash2, QrCode, 
  CheckCircle, ExternalLink, Activity, PhoneCall, Layers 
} from 'lucide-react';

export default function CommunityHub() {
  const [channels, setChannels] = useState([]);
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editingChannel, setEditingChannel] = useState(null);

  // Form State
  const [name, setName] = useState('');
  const [platform, setPlatform] = useState('whatsapp_channel');
  const [channelType, setChannelType] = useState('WHATSAPP_CHANNEL');
  const [url, setUrl] = useState('');
  const [phoneNumber, setPhoneNumber] = useState('');
  const [descSw, setDescSw] = useState('');
  const [descEn, setDescEn] = useState('');
  const [isOfficial, setIsOfficial] = useState(true);
  const [isFeatured, setIsFeatured] = useState(false);
  const [isAlertChannel, setIsAlertChannel] = useState(false);

  useEffect(() => {
    fetchChannels();
  }, []);

  const fetchChannels = async () => {
    try {
      const res = await fetch('/api/admin/community/channels', {
        headers: { }
      });
      if (res.ok) {
        const data = await res.json();
        setChannels(data.data || []);
        setStats(data.stats || null);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  const handleSaveChannel = async (e) => {
    e.preventDefault();
    const endpoint = editingChannel
      ? `/api/admin/community/channels/${editingChannel.id}`
      : '/api/admin/community/channels';
    const method = editingChannel ? 'PUT' : 'POST';

    try {
      const res = await fetch(endpoint, {
        method,
        headers: {
          'Content-Type': 'application/json',

        },
        body: JSON.stringify({
          name,
          platform,
          channel_type: channelType,
          url,
          phone_number: phoneNumber,
          description_sw: descSw,
          description_en: descEn,
          is_official: isOfficial,
          is_featured: isFeatured,
          is_alert_channel: isAlertChannel,
        })
      });

      if (res.ok) {
        setShowModal(false);
        fetchChannels();
        resetForm();
      }
    } catch (e) {
      alert('Error saving channel');
    }
  };

  const handleDelete = async (id) => {
    if (!confirm('Are you sure you want to delete this channel?')) return;
    try {
      await fetch(`/api/admin/community/channels/${id}`, {
        method: 'DELETE',
        headers: { }
      });
      fetchChannels();
    } catch (e) {
      alert('Error deleting channel');
    }
  };

  const resetForm = () => {
    setName(''); setUrl(''); setPhoneNumber(''); setDescSw(''); setDescEn('');
    setEditingChannel(null);
  };

  const openEdit = (c) => {
    setEditingChannel(c);
    setName(c.name);
    setPlatform(c.platform);
    setChannelType(c.channel_type);
    setUrl(c.url || '');
    setPhoneNumber(c.phone_number || '');
    setDescSw(c.description?.sw || '');
    setDescEn(c.description?.en || '');
    setIsOfficial(c.is_official);
    setIsFeatured(c.is_featured);
    setIsAlertChannel(c.is_alert_channel);
    setShowModal(true);
  };

  return (
    <div className="p-6 max-w-7xl mx-auto space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div className="flex items-center gap-3">
          <div className="p-3 bg-blue-50 text-blue-700 rounded-xl">
            <Globe className="w-8 h-8" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Mkulima Community Hub</h1>
            <p className="text-sm text-gray-500">Central Social, WhatsApp, Telegram & Community Channel Infrastructure</p>
          </div>
        </div>
        <button
          onClick={() => { resetForm(); setShowModal(true); }}
          className="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl flex items-center gap-2"
        >
          <Plus className="w-4 h-4" />
          Add Channel / Link
        </button>
      </div>

      {/* Stats Summary Strip */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div className="flex items-center justify-between text-gray-500 text-sm font-medium">
            <span>Total Active Channels</span>
            <Layers className="w-5 h-5 text-blue-600" />
          </div>
          <p className="text-2xl font-bold text-gray-900 mt-2">{stats?.active_channels ?? channels.length}</p>
          <span className="text-xs text-gray-400">Official + Community run</span>
        </div>

        <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div className="flex items-center justify-between text-gray-500 text-sm font-medium">
            <span>Total Join Clicks</span>
            <ExternalLink className="w-5 h-5 text-emerald-600" />
          </div>
          <p className="text-2xl font-bold text-gray-900 mt-2">{stats?.total_join_clicks ?? 0}</p>
          <span className="text-xs text-gray-400">Tracked via Event Bus (Rule 7)</span>
        </div>

        <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div className="flex items-center justify-between text-gray-500 text-sm font-medium">
            <span>WhatsApp Contact Clicks</span>
            <PhoneCall className="w-5 h-5 text-emerald-500" />
          </div>
          <p className="text-2xl font-bold text-gray-900 mt-2">{stats?.total_whatsapp_clicks ?? 0}</p>
          <span className="text-xs text-gray-400">Click-to-chat engagements</span>
        </div>

        <div className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div className="flex items-center justify-between text-gray-500 text-sm font-medium">
            <span>Official Channels</span>
            <CheckCircle className="w-5 h-5 text-blue-600" />
          </div>
          <p className="text-2xl font-bold text-gray-900 mt-2">{stats?.official_channels ?? 0}</p>
          <span className="text-xs text-gray-400">Mkulima Forum Official badge</span>
        </div>
      </div>

      {/* Channel Table */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-6">
        <h3 className="text-lg font-bold text-gray-900 mb-4">Official & Community Links Registry</h3>
        {channels.length === 0 ? (
          <div className="p-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
            <Share2 className="w-12 h-12 text-gray-400 mx-auto mb-3" />
            <p className="font-semibold text-gray-700">No Community Channels Configured</p>
            <p className="text-xs text-gray-500 mt-1">Add WhatsApp Channels, WhatsApp Groups, YouTube or social profiles above.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  <th className="py-3 px-4">Platform</th>
                  <th className="py-3 px-4">Name</th>
                  <th className="py-3 px-4">Type</th>
                  <th className="py-3 px-4">Status</th>
                  <th className="py-3 px-4">Alert Target</th>
                  <th className="py-3 px-4">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100 text-sm">
                {channels.map((c) => (
                  <tr key={c.id} className="hover:bg-gray-50/50">
                    <td className="py-3 px-4 font-semibold text-gray-900 capitalize">{c.platform.replace('_', ' ')}</td>
                    <td className="py-3 px-4">
                      <div className="font-medium text-gray-900">{c.name}</div>
                      <div className="text-xs text-gray-400">{c.url || c.click_to_chat_url}</div>
                    </td>
                    <td className="py-3 px-4 text-xs font-mono text-gray-600">{c.channel_type}</td>
                    <td className="py-3 px-4">
                      {c.is_official ? (
                        <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Official</span>
                      ) : (
                        <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Community</span>
                      )}
                    </td>
                    <td className="py-3 px-4">
                      {c.is_alert_channel ? (
                        <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Alert Channel</span>
                      ) : (
                        <span className="text-xs text-gray-400">—</span>
                      )}
                    </td>
                    <td className="py-3 px-4 flex items-center gap-2">
                      <button onClick={() => openEdit(c)} className="p-1.5 hover:bg-gray-100 rounded text-gray-600">
                        <Edit2 className="w-4 h-4" />
                      </button>
                      <button onClick={() => handleDelete(c.id)} className="p-1.5 hover:bg-red-50 rounded text-red-600">
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-xl w-full p-6 space-y-4 shadow-xl">
            <h3 className="text-lg font-bold text-gray-900">{editingChannel ? 'Edit Channel' : 'Add Community Channel'}</h3>
            <form onSubmit={handleSaveChannel} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1">Channel Name</label>
                <input
                  type="text"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="e.g. Mkulima Forum Official Channel"
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Platform</label>
                  <select
                    value={platform}
                    onChange={(e) => setPlatform(e.target.value)}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm"
                  >
                    <option value="whatsapp_channel">WhatsApp Channel</option>
                    <option value="whatsapp_group">WhatsApp Group</option>
                    <option value="whatsapp_community">WhatsApp Community</option>
                    <option value="whatsapp">WhatsApp Business</option>
                    <option value="telegram">Telegram</option>
                    <option value="youtube">YouTube</option>
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                    <option value="x_twitter">X / Twitter</option>
                    <option value="tiktok">TikTok</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">Channel Type</label>
                  <select
                    value={channelType}
                    onChange={(e) => setChannelType(e.target.value)}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm"
                  >
                    <option value="WHATSAPP_CHANNEL">WHATSAPP_CHANNEL</option>
                    <option value="WHATSAPP_GROUP">WHATSAPP_GROUP</option>
                    <option value="WHATSAPP_COMMUNITY">WHATSAPP_COMMUNITY</option>
                    <option value="WHATSAPP_BUSINESS">WHATSAPP_BUSINESS</option>
                    <option value="SOCIAL">SOCIAL</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1">Target URL / Invite Link</label>
                <input
                  type="url"
                  value={url}
                  onChange={(e) => setUrl(e.target.value)}
                  placeholder="https://whatsapp.com/channel/..."
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm"
                />
              </div>

              {channelType === 'WHATSAPP_BUSINESS' && (
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">WhatsApp Phone Number</label>
                  <input
                    type="text"
                    value={phoneNumber}
                    onChange={(e) => setPhoneNumber(e.target.value)}
                    placeholder="+255 700 000 000"
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm"
                  />
                </div>
              )}

              <div className="flex gap-4 items-center pt-2">
                <label className="flex items-center gap-2 text-xs font-medium text-gray-700">
                  <input type="checkbox" checked={isOfficial} onChange={(e) => setIsOfficial(e.target.checked)} />
                  Official Mkulima Forum Badge
                </label>
                <label className="flex items-center gap-2 text-xs font-medium text-gray-700">
                  <input type="checkbox" checked={isAlertChannel} onChange={(e) => setIsAlertChannel(e.target.checked)} />
                  Advisory Alert Target (Part C)
                </label>
              </div>

              <div className="flex justify-end gap-2 pt-4 border-t border-gray-100">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-sm font-medium"
                >
                  Save Channel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
