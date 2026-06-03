import 'package:flutter/material.dart';

class ForumScreen extends StatelessWidget {
  const ForumScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.forum, size: 80, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text(
            'Jukwaa la Wakulima',
            style: Theme.of(context).textTheme.headlineSmall,
          ),
          const SizedBox(height: 8),
          Text(
            'Inakuja hivi karibuni',
            style: TextStyle(color: Colors.grey[600]),
          ),
        ],
      ),
    );
  }
}
