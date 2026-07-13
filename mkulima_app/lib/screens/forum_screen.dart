import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/strings.dart';
import '../core/theme.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import '../widgets/mk_empty_state.dart';
import '../widgets/mk_thread_tile.dart';
import 'login_modal.dart';

class ForumScreen extends StatefulWidget {
  const ForumScreen({super.key});

  @override
  State<ForumScreen> createState() => _ForumScreenState();
}

class _ForumScreenState extends State<ForumScreen> {
  List<dynamic> _categories = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  Future<void> _loadCategories() async {
    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final categories = await api.getForumCategories();
      if (!mounted) return;
      setState(() {
        _categories = categories;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_categories.isEmpty) {
      return const MkEmptyState(
        icon: Icons.forum_outlined,
        title: MkStrings.emptyList,
      );
    }

    return Column(
      children: [
        if (!auth.isAuthenticated)
          Container(
            color: Colors.blue[50],
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                Icon(Icons.info_outline, color: Colors.blue[700], size: 20),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Ingia ili uweze kuandika na kujibu mijadala',
                    style: TextStyle(color: Colors.blue[700], fontSize: 13),
                  ),
                ),
                TextButton(
                  onPressed: () => LoginModal.show(context),
                  child: const Text('Ingia'),
                ),
              ],
            ),
          ),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: _categories.length,
            itemBuilder: (context, index) {
              final cat = _categories[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: MkColors.primary,
                    child: Icon(
                      _getIcon(cat['icon'] ?? 'forum'),
                      color: Colors.white,
                    ),
                  ),
                  title: Text(
                    cat['name'] ?? 'Category',
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                  subtitle: Text(
                    cat['description'] ?? '',
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(
                        builder: (_) => ThreadsScreen(
                          categoryId: cat['id']?.toString() ?? '',
                          categoryName: cat['name'] ?? 'Category',
                        ),
                      ),
                    );
                  },
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  IconData _getIcon(String icon) {
    switch (icon) {
      case 'seed': return Icons.grass;
      case 'flask': return Icons.science;
      case 'spray': return Icons.sanitizer;
      case 'wrench': return Icons.build;
      case 'bone': return Icons.pets;
      case 'droplet': return Icons.water_drop;
      default: return Icons.forum;
    }
  }
}

class ThreadsScreen extends StatefulWidget {
  final String categoryId;
  final String categoryName;

  const ThreadsScreen({
    super.key,
    required this.categoryId,
    required this.categoryName,
  });

  @override
  State<ThreadsScreen> createState() => _ThreadsScreenState();
}

class _ThreadsScreenState extends State<ThreadsScreen> {
  List<dynamic> _threads = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadThreads();
  }

  Future<void> _loadThreads() async {
    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final threads = await api.getThreads(widget.categoryId);
      if (!mounted) return;
      setState(() {
        _threads = threads;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
    }
  }

  void _showCreateThreadDialog() {
    final titleController = TextEditingController();
    final bodyController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Andika Mada Mpya'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: titleController,
              decoration: const InputDecoration(
                labelText: 'Kichwa cha Mada',
                hintText: 'Mfano: Ushauri wa kilimo cha mahindi',
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: bodyController,
              maxLines: 4,
              decoration: const InputDecoration(
                labelText: 'Maelezo',
                hintText: 'Andika maelezo yako hapa...',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Ghairi'),
          ),
          ElevatedButton(
            onPressed: () async {
              final title = titleController.text.trim();
              final body = bodyController.text.trim();

              if (title.isEmpty || body.isEmpty) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Jaza kichwa na maelezo')),
                );
                return;
              }

              final messenger = ScaffoldMessenger.of(this.context);
              final api = Provider.of<ApiService>(context, listen: false);
              Navigator.of(context).pop();

              try {
                await api.createThread({
                  'category_id': widget.categoryId,
                  'title': title,
                  'body': body,
                });

                _loadThreads();

                messenger.showSnackBar(
                  const SnackBar(content: Text('Mada imetumwa kikamilifu')),
                );
              } catch (e) {
                messenger.showSnackBar(
                  SnackBar(content: Text('Kosa: $e')),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: MkColors.primary,
              foregroundColor: Colors.white,
            ),
            child: const Text('Tuma'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.categoryName),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _threads.isEmpty
              ? const MkEmptyState(
                  icon: Icons.chat_bubble_outline,
                  title: MkStrings.emptyList,
                )
              : RefreshIndicator(
                  onRefresh: _loadThreads,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _threads.length,
                    itemBuilder: (context, index) {
                      final thread = _threads[index];
                      return MkThreadTile(
                        thread: thread,
                        onTap: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => ThreadDetailScreen(
                                threadId: thread['uuid'] ?? '',
                                threadTitle: thread['title'] ?? 'Thread',
                              ),
                            ),
                          );
                        },
                      );
                    },
                  ),
                ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final ok = await AuthProvider.requireAuth(context,
              action: 'kuandika mada mpya');
          if (ok && context.mounted) {
            _showCreateThreadDialog();
          }
        },
        backgroundColor: MkColors.primary,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }
}

class ThreadDetailScreen extends StatefulWidget {
  final String threadId;
  final String threadTitle;

  const ThreadDetailScreen({
    super.key,
    required this.threadId,
    required this.threadTitle,
  });

  @override
  State<ThreadDetailScreen> createState() => _ThreadDetailScreenState();
}

class _ThreadDetailScreenState extends State<ThreadDetailScreen> {
  Map<String, dynamic>? _thread;
  List<dynamic> _replies = [];
  bool _isLoading = true;
  final _replyController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadThread();
  }

  Future<void> _loadThread() async {
    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final thread = await api.getThread(widget.threadId);
      if (!mounted) return;
      setState(() {
        _thread = thread;
        _replies = thread['replies'] ?? [];
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
    }
  }

  Future<void> _postReply() async {
    final body = _replyController.text.trim();
    if (body.isEmpty) return;

    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (!auth.isAuthenticated) {
      final ok = await AuthProvider.requireAuth(context, action: 'kujibu mada');
      if (!ok || !mounted) return;
    }

    try {
      final api = Provider.of<ApiService>(context, listen: false);
      await api.createReply(widget.threadId, body);
      _replyController.clear();
      _loadThread();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Jibu limetumwa')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Kosa: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.threadTitle, maxLines: 1, overflow: TextOverflow.ellipsis),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Expanded(
                  child: RefreshIndicator(
                    onRefresh: _loadThread,
                    child: ListView(
                      padding: const EdgeInsets.all(16),
                      children: [
                        if (_thread != null) ...[
                          Card(
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    _thread!['title'] ?? '',
                                    style: const TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  Text(_thread!['body'] ?? ''),
                                  const SizedBox(height: 8),
                                  Text(
                                    'Na: ${_thread!['user']?['name'] ?? 'Unknown'}',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Colors.grey[600],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 16),
                          const Text(
                            'Majibu',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 8),
                        ],
                        if (_replies.isEmpty)
                          const Padding(
                            padding: EdgeInsets.all(16),
                            child: Center(child: Text('Hakuna majibu bado')),
                          )
                        else
                          ..._replies.map((reply) => Card(
                            margin: const EdgeInsets.only(bottom: 8),
                            child: Padding(
                              padding: const EdgeInsets.all(12),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(reply['body'] ?? ''),
                                  const SizedBox(height: 4),
                                  Text(
                                    'Na: ${reply['user']?['name'] ?? 'Unknown'}',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Colors.grey[600],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          )),
                      ],
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.1),
                        blurRadius: 4,
                      ),
                    ],
                  ),
                  child: SafeArea(
                    child: Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _replyController,
                            decoration: const InputDecoration(
                              hintText: 'Andika jibu lako...',
                              border: OutlineInputBorder(),
                              contentPadding: EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 8,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        IconButton(
                          icon: const Icon(Icons.send, color: MkColors.primary),
                          onPressed: _postReply,
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
    );
  }
}
