import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/strings.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../widgets/mk_empty_state.dart';

/// Mkulima AI — AI chatbot & farm advisor (multi-turn, Swahili-first).
class MkulimaBotScreen extends StatefulWidget {
  const MkulimaBotScreen({super.key});

  @override
  State<MkulimaBotScreen> createState() => _MkulimaBotScreenState();
}

class _ChatMessage {
  final String role; // 'user' | 'model'
  final String content;
  const _ChatMessage(this.role, this.content);
}

class _MkulimaBotScreenState extends State<MkulimaBotScreen> {
  final _controller = TextEditingController();
  final _scrollController = ScrollController();
  final List<_ChatMessage> _messages = [];
  String? _conversationUuid;
  bool _isSending = false;

  static const _suggestedPrompts = [
    'Nipande mahindi lini msimu huu?',
    'Mbolea gani bora kwa mpunga?',
    'Jinsi ya kudhibiti viwavijeshi',
    'Bei ya soko ya mahindi ikoje?',
  ];

  @override
  void dispose() {
    _controller.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _send([String? preset]) async {
    final text = (preset ?? _controller.text).trim();
    if (text.isEmpty || _isSending) return;

    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (!auth.isAuthenticated) {
      final ok = await AuthProvider.requireAuth(context,
          action: 'kuzungumza na Mkulima AI');
      if (!ok || !mounted) return;
    }

    setState(() {
      _messages.add(_ChatMessage('user', text));
      _isSending = true;
    });
    _controller.clear();
    _scrollToBottom();

    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final response = await api.botChat(
        message: text,
        conversationUuid: _conversationUuid,
      );
      setState(() {
        _conversationUuid = response['conversation_uuid'] as String?;
        _messages.add(_ChatMessage('model', response['reply'] ?? ''));
        _isSending = false;
      });
    } catch (e) {
      setState(() {
        _messages.add(const _ChatMessage(
          'model',
          MkStrings.botUnavailable,
        ));
        _isSending = false;
      });
    }
    _scrollToBottom();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOut,
        );
      }
    });
  }

  void _newConversation() {
    setState(() {
      _messages.clear();
      _conversationUuid = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(MkStrings.botTitle),
        actions: [
          if (_messages.isNotEmpty)
            IconButton(
              tooltip: MkStrings.botNewChat,
              icon: const Icon(Icons.add_comment_outlined),
              onPressed: _newConversation,
            ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: _messages.isEmpty ? _buildWelcome() : _buildMessages(),
          ),
          if (_isSending)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 4),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  SizedBox(
                    width: 14,
                    height: 14,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  ),
                  SizedBox(width: 8),
                  Text(MkStrings.botThinking,
                      style: TextStyle(fontSize: 12, color: Colors.grey)),
                ],
              ),
            ),
          _buildComposer(),
        ],
      ),
    );
  }

  Widget _buildWelcome() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          const SizedBox(height: 24),
          const MkEmptyState(
            icon: Icons.psychology_outlined,
            title: MkStrings.botTitle,
            subtitle: MkStrings.botWelcome,
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            alignment: WrapAlignment.center,
            children: _suggestedPrompts
                .map((p) => ActionChip(
                      label: Text(p, style: const TextStyle(fontSize: 12)),
                      onPressed: () => _send(p),
                    ))
                .toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildMessages() {
    return ListView.builder(
      controller: _scrollController,
      padding: const EdgeInsets.all(16),
      itemCount: _messages.length,
      itemBuilder: (context, index) {
        final msg = _messages[index];
        final isUser = msg.role == 'user';
        return Align(
          alignment: isUser ? Alignment.centerRight : Alignment.centerLeft,
          child: Container(
            margin: const EdgeInsets.only(bottom: 8),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            constraints: BoxConstraints(
              maxWidth: MediaQuery.of(context).size.width * 0.8,
            ),
            decoration: BoxDecoration(
              color: isUser ? MkColors.primary : Colors.white,
              borderRadius: BorderRadius.only(
                topLeft: const Radius.circular(MkRadii.card),
                topRight: const Radius.circular(MkRadii.card),
                bottomLeft: Radius.circular(isUser ? MkRadii.card : 2),
                bottomRight: Radius.circular(isUser ? 2 : MkRadii.card),
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.06),
                  blurRadius: 4,
                ),
              ],
            ),
            child: Text(
              msg.content,
              style: TextStyle(
                color: isUser ? Colors.white : Colors.black87,
                height: 1.35,
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildComposer() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 4),
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            Expanded(
              child: TextField(
                controller: _controller,
                minLines: 1,
                maxLines: 4,
                textInputAction: TextInputAction.send,
                onSubmitted: (_) => _send(),
                decoration: const InputDecoration(
                  hintText: MkStrings.botHint,
                  border: OutlineInputBorder(),
                  contentPadding:
                      EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                ),
              ),
            ),
            const SizedBox(width: 8),
            IconButton.filled(
              style: IconButton.styleFrom(backgroundColor: MkColors.primary),
              icon: const Icon(Icons.send, color: Colors.white),
              onPressed: _isSending ? null : _send,
            ),
          ],
        ),
      ),
    );
  }
}
