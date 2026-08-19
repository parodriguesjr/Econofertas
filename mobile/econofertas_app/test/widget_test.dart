import 'package:econofertas_app/main.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('exibe a identidade do Econofertas', (tester) async {
    await tester.pumpWidget(const EconofertasApp());
    expect(find.text('Econofertas'), findsOneWidget);
    expect(find.text('Encontre ofertas perto de você'), findsOneWidget);
  });
}
