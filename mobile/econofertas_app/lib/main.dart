import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';

const configuredApiBaseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: '',
);

String get apiBaseUrl => configuredApiBaseUrl.isNotEmpty
    ? configuredApiBaseUrl
    : Platform.isIOS
    ? 'http://127.0.0.1:8000/api'
    : 'http://10.0.2.2:8000/api';

void main() => runApp(const EconofertasApp());

class EconofertasApp extends StatelessWidget {
  const EconofertasApp({super.key});

  @override
  Widget build(BuildContext context) {
    const green = Color(0xFF12643F);
    return MaterialApp(
      title: 'Econofertas',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: green,
          primary: green,
          surface: const Color(0xFFF7F9F7),
        ),
        scaffoldBackgroundColor: const Color(0xFFF7F9F7),
        appBarTheme: const AppBarTheme(
          backgroundColor: Color(0xFF0A3D27),
          foregroundColor: Colors.white,
          elevation: 0,
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(6),
            borderSide: BorderSide.none,
          ),
        ),
        cardTheme: const CardThemeData(
          color: Colors.white,
          elevation: 0,
          margin: EdgeInsets.zero,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.all(Radius.circular(6)),
            side: BorderSide(color: Color(0xFFDFE5E1)),
          ),
        ),
      ),
      home: const HomeScreen(),
    );
  }
}

class Supermarket {
  const Supermarket({
    required this.id,
    required this.name,
    required this.slug,
    required this.description,
    required this.color,
    required this.flyersCount,
  });

  final int id;
  final String name;
  final String slug;
  final String description;
  final Color color;
  final int flyersCount;

  factory Supermarket.fromJson(Map<String, dynamic> json) => Supermarket(
    id: json['id'] as int,
    name: json['name'] as String,
    slug: json['slug'] as String,
    description: json['description'] as String? ?? '',
    color: _parseColor(json['color'] as String? ?? '#12643F'),
    flyersCount: json['flyers_count'] as int? ?? 0,
  );
}

class Flyer {
  const Flyer({
    required this.id,
    required this.title,
    required this.endsAt,
    required this.pdfUrl,
  });

  final int id;
  final String title;
  final DateTime? endsAt;
  final String pdfUrl;

  factory Flyer.fromJson(Map<String, dynamic> json) => Flyer(
    id: json['id'] as int,
    title: json['title'] as String,
    endsAt: json['ends_at'] == null
        ? null
        : DateTime.tryParse(json['ends_at'] as String),
    pdfUrl: json['pdf_url'] as String,
  );
}

class Offer {
  const Offer({required this.name, required this.market, required this.price, required this.imageUrl, required this.sourceUrl, required this.isCheapest});
  final String name;
  final String market;
  final double? price;
  final String? imageUrl;
  final String sourceUrl;
  final bool isCheapest;

  factory Offer.fromJson(Map<String, dynamic> json) => Offer(
    name: json['name'] as String,
    market: (json['supermarket'] as Map<String, dynamic>)['name'] as String,
    price: (json['price'] as num?)?.toDouble(),
    imageUrl: json['image_url'] as String?,
    sourceUrl: json['source_url'] as String,
    isCheapest: json['is_cheapest'] as bool? ?? false,
  );
}

Color _parseColor(String value) {
  final hex = value.replaceFirst('#', '');
  return Color(int.parse('FF$hex', radix: 16));
}

class ApiService {
  static Future<List<Supermarket>> getSupermarkets() async {
    final response = await http
        .get(Uri.parse('$apiBaseUrl/supermercados'))
        .timeout(const Duration(seconds: 12));
    if (response.statusCode != 200) {
      throw Exception('Falha ao carregar lojas');
    }
    final body = jsonDecode(response.body) as Map<String, dynamic>;
    return (body['data'] as List)
        .map((item) => Supermarket.fromJson(item as Map<String, dynamic>))
        .toList();
  }

  static Future<List<Flyer>> getFlyers(String slug) async {
    final response = await http
        .get(Uri.parse('$apiBaseUrl/supermercados/$slug'))
        .timeout(const Duration(seconds: 12));
    if (response.statusCode != 200) {
      throw Exception('Falha ao carregar folhetos');
    }
    final body = jsonDecode(response.body) as Map<String, dynamic>;
    final data = body['data'] as Map<String, dynamic>;
    return (data['flyers'] as List)
        .map((item) => Flyer.fromJson(item as Map<String, dynamic>))
        .toList();
  }

  static Future<List<Offer>> getOffers([String query = '']) async {
    final uri = Uri.parse('$apiBaseUrl/ofertas').replace(queryParameters: query.trim().isEmpty ? null : {'q': query.trim()});
    final response = await http.get(uri).timeout(const Duration(seconds: 15));
    if (response.statusCode != 200) throw Exception('Falha ao carregar ofertas');
    final body = jsonDecode(response.body) as Map<String, dynamic>;
    return (body['data'] as List).map((item) => Offer.fromJson(item as Map<String, dynamic>)).toList();
  }
}

class FavoriteStore {
  static const key = 'favorite_supermarkets';

  static Future<Set<String>> load() async {
    final prefs = await SharedPreferences.getInstance();
    return (prefs.getStringList(key) ?? []).toSet();
  }

  static Future<void> save(Set<String> values) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList(key, values.toList());
  }
}

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final searchController = TextEditingController();
  List<Supermarket> supermarkets = [];
  Set<String> favorites = {};
  bool loading = true;
  bool onlyFavorites = false;
  String? error;

  @override
  void initState() {
    super.initState();
    _load();
    searchController.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    searchController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final results = await Future.wait([
        ApiService.getSupermarkets(),
        FavoriteStore.load(),
      ]);
      if (!mounted) {
        return;
      }
      setState(() {
        supermarkets = results[0] as List<Supermarket>;
        favorites = results[1] as Set<String>;
      });
    } catch (_) {
      if (mounted) {
        setState(() => error = 'Não foi possível conectar ao Econofertas.');
      }
    } finally {
      if (mounted) {
        setState(() => loading = false);
      }
    }
  }

  Future<void> _toggleFavorite(String slug) async {
    setState(
      () => favorites.contains(slug)
          ? favorites.remove(slug)
          : favorites.add(slug),
    );
    await FavoriteStore.save(favorites);
  }

  List<Supermarket> get filtered {
    final query = searchController.text.trim().toLowerCase();
    return supermarkets.where((market) {
      final matchesSearch = market.name.toLowerCase().contains(query);
      return matchesSearch &&
          (!onlyFavorites || favorites.contains(market.slug));
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        titleSpacing: 20,
        title: const Row(
          children: [
            _LogoMark(size: 34),
            SizedBox(width: 10),
            Text('Econofertas', style: TextStyle(fontWeight: FontWeight.w800)),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Ofertas por produto',
            icon: const Icon(Icons.local_offer_outlined),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const OffersScreen())),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverToBoxAdapter(child: _buildHeader()),
            if (loading)
              const SliverFillRemaining(
                child: Center(child: CircularProgressIndicator()),
              )
            else if (error != null)
              SliverFillRemaining(
                child: _ErrorState(message: error!, onRetry: _load),
              )
            else if (filtered.isEmpty)
              const SliverFillRemaining(child: _EmptyState())
            else
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 4, 16, 32),
                sliver: SliverList.separated(
                  itemCount: filtered.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 10),
                  itemBuilder: (context, index) {
                    final market = filtered[index];
                    return MarketTile(
                      market: market,
                      favorite: favorites.contains(market.slug),
                      onFavorite: () => _toggleFavorite(market.slug),
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => FlyersScreen(market: market),
                        ),
                      ),
                    );
                  },
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() => Container(
    color: const Color(0xFF12643F),
    padding: const EdgeInsets.fromLTRB(16, 16, 16, 22),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'ECONOMIZE NA PRÓXIMA COMPRA',
          style: TextStyle(
            color: Color(0xFFDFF06C),
            fontSize: 11,
            fontWeight: FontWeight.w800,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'Encontre ofertas perto de você',
          style: TextStyle(
            color: Colors.white,
            fontSize: 27,
            fontWeight: FontWeight.w800,
            height: 1.1,
          ),
        ),
        const SizedBox(height: 18),
        TextField(
          controller: searchController,
          textInputAction: TextInputAction.search,
          decoration: InputDecoration(
            hintText: 'Buscar supermercado',
            prefixIcon: const Icon(Icons.search),
            suffixIcon: searchController.text.isEmpty
                ? null
                : IconButton(
                    tooltip: 'Limpar busca',
                    onPressed: searchController.clear,
                    icon: const Icon(Icons.close),
                  ),
          ),
        ),
        const SizedBox(height: 12),
        SegmentedButton<bool>(
          segments: const [
            ButtonSegment(
              value: false,
              label: Text('Todos'),
              icon: Icon(Icons.storefront_outlined),
            ),
            ButtonSegment(
              value: true,
              label: Text('Favoritos'),
              icon: Icon(Icons.favorite_outline),
            ),
          ],
          selected: {onlyFavorites},
          onSelectionChanged: (value) =>
              setState(() => onlyFavorites = value.first),
          style: ButtonStyle(
            backgroundColor: WidgetStateProperty.resolveWith(
              (states) => states.contains(WidgetState.selected)
                  ? const Color(0xFFDFF06C)
                  : Colors.white,
            ),
          ),
        ),
      ],
    ),
  );
}

class MarketTile extends StatelessWidget {
  const MarketTile({
    super.key,
    required this.market,
    required this.favorite,
    required this.onFavorite,
    required this.onTap,
  });
  final Supermarket market;
  final bool favorite;
  final VoidCallback onFavorite;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => Card(
    child: InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(6),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          children: [
            Container(
              width: 54,
              height: 54,
              alignment: Alignment.center,
              color: market.color.withValues(alpha: .12),
              child: Text(
                market.name.characters.first,
                style: TextStyle(
                  color: market.color,
                  fontSize: 25,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    market.name,
                    style: const TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 5),
                  Text(
                    '${market.flyersCount} ${market.flyersCount == 1 ? 'folheto disponível' : 'folhetos disponíveis'}',
                    style: const TextStyle(
                      color: Color(0xFF657069),
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
            IconButton(
              tooltip: favorite
                  ? 'Remover dos favoritos'
                  : 'Adicionar aos favoritos',
              onPressed: onFavorite,
              icon: Icon(
                favorite ? Icons.favorite : Icons.favorite_border,
                color: favorite ? const Color(0xFFD7432F) : null,
              ),
            ),
            const Icon(Icons.chevron_right),
          ],
        ),
      ),
    ),
  );
}

class FlyersScreen extends StatefulWidget {
  const FlyersScreen({super.key, required this.market});
  final Supermarket market;

  @override
  State<FlyersScreen> createState() => _FlyersScreenState();
}

class _FlyersScreenState extends State<FlyersScreen> {
  late Future<List<Flyer>> flyers;

  @override
  void initState() {
    super.initState();
    flyers = ApiService.getFlyers(widget.market.slug);
  }

  Future<void> _reload() async {
    setState(() => flyers = ApiService.getFlyers(widget.market.slug));
    await flyers;
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Folhetos')),
    body: RefreshIndicator(
      onRefresh: _reload,
      child: FutureBuilder<List<Flyer>>(
        future: flyers,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
            return _ErrorState(
              message: 'Não foi possível carregar os folhetos.',
              onRetry: _reload,
            );
          }
          final items = snapshot.data ?? [];
          return ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.only(bottom: 30),
            children: [
              Container(
                color: widget.market.color,
                padding: const EdgeInsets.fromLTRB(20, 24, 20, 28),
                child: Row(
                  children: [
                    Container(
                      width: 62,
                      height: 62,
                      alignment: Alignment.center,
                      color: Colors.white,
                      child: Text(
                        widget.market.name.characters.first,
                        style: TextStyle(
                          color: widget.market.color,
                          fontSize: 30,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'OFERTAS DE',
                            style: TextStyle(
                              color: Colors.white70,
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            widget.market.name,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 25,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 24, 16, 12),
                child: Text(
                  '${items.length} ${items.length == 1 ? 'folheto disponível' : 'folhetos disponíveis'}',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
              if (items.isEmpty)
                const Padding(
                  padding: EdgeInsets.only(top: 50),
                  child: _EmptyState(
                    message: 'Nenhum folheto ativo nesta loja.',
                  ),
                )
              else
                ...items.map(
                  (flyer) => Padding(
                    padding: const EdgeInsets.fromLTRB(16, 0, 16, 10),
                    child: FlyerTile(flyer: flyer, color: widget.market.color),
                  ),
                ),
            ],
          );
        },
      ),
    ),
  );
}

class FlyerTile extends StatelessWidget {
  const FlyerTile({super.key, required this.flyer, required this.color});
  final Flyer flyer;
  final Color color;

  String get validity {
    final date = flyer.endsAt;
    if (date == null) {
      return 'Consulte as condições no folheto';
    }
    return 'Válido até ${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
  }

  Future<void> _open(BuildContext context) async {
    final uri = Uri.parse(flyer.pdfUrl);
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication) &&
        context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Não foi possível abrir este PDF.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) => Card(
    child: Padding(
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          Container(
            width: 50,
            height: 64,
            color: color,
            alignment: Alignment.center,
            child: const Icon(
              Icons.picture_as_pdf_outlined,
              color: Colors.white,
              size: 28,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  flyer.title,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  validity,
                  style: const TextStyle(
                    color: Color(0xFF657069),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          IconButton.filled(
            tooltip: 'Abrir folheto',
            onPressed: () => _open(context),
            icon: const Icon(Icons.open_in_new),
          ),
        ],
      ),
    ),
  );
}

class OffersScreen extends StatefulWidget {
  const OffersScreen({super.key});
  @override
  State<OffersScreen> createState() => _OffersScreenState();
}

class _OffersScreenState extends State<OffersScreen> {
  final controller = TextEditingController();
  late Future<List<Offer>> offers = ApiService.getOffers();

  void search() => setState(() => offers = ApiService.getOffers(controller.text));
  @override
  void dispose() { controller.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Ofertas em Nova Odessa')),
    body: Column(children: [
      Padding(padding: const EdgeInsets.all(16), child: TextField(
        controller: controller,
        textInputAction: TextInputAction.search,
        onSubmitted: (_) => search(),
        decoration: InputDecoration(hintText: 'Buscar produto', prefixIcon: const Icon(Icons.search), suffixIcon: IconButton(tooltip: 'Buscar', onPressed: search, icon: const Icon(Icons.arrow_forward))),
      )),
      Expanded(child: FutureBuilder<List<Offer>>(future: offers, builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
        if (snapshot.hasError) return _ErrorState(message: 'Não foi possível carregar as ofertas.', onRetry: () async => search());
        final items = snapshot.data ?? [];
        if (items.isEmpty) return const _EmptyState(message: 'Nenhuma oferta encontrada.');
        return RefreshIndicator(onRefresh: () async => search(), child: GridView.builder(
          padding: const EdgeInsets.fromLTRB(12, 0, 12, 30),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 2, childAspectRatio: .67, crossAxisSpacing: 10, mainAxisSpacing: 10),
          itemCount: items.length,
          itemBuilder: (_, index) => OfferTile(offer: items[index]),
        ));
      })),
    ]),
  );
}

class OfferTile extends StatelessWidget {
  const OfferTile({super.key, required this.offer});
  final Offer offer;
  @override
  Widget build(BuildContext context) => Card(child: InkWell(
    onTap: () => launchUrl(Uri.parse(offer.sourceUrl), mode: LaunchMode.externalApplication),
    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Expanded(child: Container(color: Colors.white, width: double.infinity, child: offer.imageUrl == null ? const Icon(Icons.local_offer_outlined, size: 48) : Image.network(offer.imageUrl!, fit: BoxFit.contain, errorBuilder: (_, _, _) => const Icon(Icons.broken_image_outlined)))),
      Padding(padding: const EdgeInsets.all(10), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(offer.market, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(color: Color(0xFF12643F), fontSize: 11, fontWeight: FontWeight.w800)),
        const SizedBox(height: 5),
        SizedBox(height: 36, child: Text(offer.name, maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700))),
        const SizedBox(height: 7),
        Text(offer.price == null ? 'Ver encarte' : 'R\$ ${offer.price!.toStringAsFixed(2).replaceAll('.', ',')}', style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900, color: Color(0xFF12643F))),
        if (offer.isCheapest) const Text('Menor preço', style: TextStyle(fontSize: 11, color: Color(0xFFD7432F), fontWeight: FontWeight.w800)),
      ])),
    ]),
  ));
}

class _LogoMark extends StatelessWidget {
  const _LogoMark({required this.size});
  final double size;
  @override
  Widget build(BuildContext context) => Container(
    width: size,
    height: size,
    alignment: Alignment.center,
    color: const Color(0xFFDFF06C),
    child: const Text(
      'E',
      style: TextStyle(
        color: Color(0xFF0A3D27),
        fontWeight: FontWeight.w900,
        fontSize: 20,
      ),
    ),
  );
}

class _ErrorState extends StatelessWidget {
  const _ErrorState({required this.message, required this.onRetry});
  final String message;
  final Future<void> Function() onRetry;
  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(28),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(
            Icons.cloud_off_outlined,
            size: 48,
            color: Color(0xFF657069),
          ),
          const SizedBox(height: 14),
          Text(message, textAlign: TextAlign.center),
          const SizedBox(height: 16),
          FilledButton.icon(
            onPressed: onRetry,
            icon: const Icon(Icons.refresh),
            label: const Text('Tentar novamente'),
          ),
        ],
      ),
    ),
  );
}

class _EmptyState extends StatelessWidget {
  const _EmptyState({this.message = 'Nenhum supermercado encontrado.'});
  final String message;
  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(28),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.search_off, size: 48, color: Color(0xFF657069)),
          const SizedBox(height: 14),
          Text(message, textAlign: TextAlign.center),
        ],
      ),
    ),
  );
}
