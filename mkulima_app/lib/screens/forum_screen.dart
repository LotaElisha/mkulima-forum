import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';

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
      setState(() {
        _categories = categories;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_categories.isEmpty) {
      return const Center(child: Text('Hakuna mijadala kwa sasa'));
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _categories.length,
      itemBuilder: (context, index) {
        final cat = _categories[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: const Color(0xFF2E7D32),
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
                    categoryId: cat['id'].toString(),
                    categoryName: cat['name'] ?? 'Category',
                  ),
                ),
              );
            },
          ),
        );
      },
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
      setState(() {
        _threads = threads;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.categoryName),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _threads.isEmpty
              ? const Center(child: Text('Hakuna mada kwa sasa'))
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _threads.length,
                  itemBuilder: (context, index) {
                    final thread = _threads[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      child: ListTile(
                        title: Text(
                          thread['title'] ?? 'Thread',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              thread['body'] ?? '',
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                Icon(Icons.comment, size: 14, color: Colors.grey[600]),
                                Text(' ${thread['reply_count'] ?? 0}',
                                    style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                                const SizedBox(width: 12),
                                Icon(Icons.remove_red_eye, size: 14, color: Colors.grey[600]),
                                Text(' ${thread['view_count'] ?? 0}',
                                    style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                              ],
                            ),
                          ],
                        ),
                        isThreeLine: true,
                      ),
                    );
                  },
                ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // Create thread dialog
        },
        backgroundColor: const Color(0xFF2E7D32),
        child: const Icon(Icons.add),
      ),
    );
  }
}
