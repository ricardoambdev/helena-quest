import 'package:flutter_test/flutter_test.dart';
import 'package:helena_quest_app/config/theme.dart';

void main() {
  testWidgets('App theme loads correctly', (WidgetTester tester) async {
    final theme = AppTheme.light;
    expect(theme.primaryColor.toARGB32(), 0xFFFF6600);
  });
}
