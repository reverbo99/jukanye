import 'package:flutter_test/flutter_test.dart';
import 'package:jukanye/main.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    await themeController.load();
  });

  testWidgets('Jukanye app boots to splash', (WidgetTester tester) async {
    await tester.pumpWidget(const JukanyeApp());
    await tester.pump();

    expect(find.text('JUKANYE'), findsOneWidget);
    expect(find.text('BUY TICKETS'), findsOneWidget);
    expect(find.text('DONATE NOW'), findsOneWidget);
  });
}
