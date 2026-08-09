import '../models/award_category.dart';
import '../models/festival_settings.dart';
import '../models/home_section.dart';
import '../models/map_place.dart';
import '../models/nominee.dart';
import '../models/person.dart';
import '../models/post.dart';
import '../models/product.dart';
import '../models/schedule_item.dart';
import '../models/sponsor.dart';
import '../models/team_member.dart';
import '../models/ticket_tier.dart';
import '../models/tour.dart';
import 'api_client.dart';
import 'api_exception.dart';

class JukanyeApi {
  JukanyeApi({ApiClient? client}) : _client = client ?? ApiClient();

  final ApiClient _client;

  static final JukanyeApi instance = JukanyeApi();

  ApiClient get client => _client;

  void setAuthToken(String? token) => _client.setBearerToken(token);

  Future<List<Post>> fetchPosts({int perPage = 15, int page = 1}) async {
    final json = await _client.getJson(
      '/posts',
      query: {
        'per_page': '$perPage',
        'page': '$page',
      },
    );
    return _mapList(json, Post.fromJson);
  }

  Future<Post> fetchPost(String slug) async {
    final json = await _client.getJson('/posts/$slug');
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException('Post payload missing');
    }
    return Post.fromJson(data);
  }

  Future<List<AwardCategory>> fetchAwardCategories() async {
    final json = await _client.getJson('/award-categories');
    return _mapList(json, AwardCategory.fromJson);
  }

  Future<List<Nominee>> fetchNominees({String? categorySlug}) async {
    final json = await _client.getJson(
      '/nominees',
      query: categorySlug == null || categorySlug.isEmpty
          ? null
          : {'category': categorySlug},
    );
    return _mapList(json, Nominee.fromJson);
  }

  Future<List<ScheduleItem>> fetchSchedule() async {
    final json = await _client.getJson('/schedule');
    return _mapList(json, ScheduleItem.fromJson);
  }

  Future<FestivalSettings> fetchSettings() async {
    final json = await _client.getJson('/settings');
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException('Settings payload missing');
    }
    return FestivalSettings.fromJson(data);
  }

  Future<List<Sponsor>> fetchSponsors() async {
    final json = await _client.getJson('/sponsors');
    return _mapList(json, Sponsor.fromJson);
  }

  Future<List<TeamMember>> fetchTeam() async {
    final json = await _client.getJson('/team');
    return _mapList(json, TeamMember.fromJson);
  }

  Future<List<Product>> fetchProducts() async {
    final json = await _client.getJson('/products');
    return _mapList(json, Product.fromJson);
  }

  Future<List<HomeSection>> fetchHomeSections({String? type}) async {
    final json = await _client.getJson(
      '/home-sections',
      query: type == null || type.isEmpty ? null : {'type': type},
    );
    return _mapList(json, HomeSection.fromJson);
  }

  Future<List<Person>> fetchPeople({String? type}) async {
    final json = await _client.getJson(
      '/people',
      query: type == null || type.isEmpty ? null : {'type': type},
    );
    return _mapList(json, Person.fromJson);
  }

  Future<Person> fetchPerson(int id) async {
    final json = await _client.getJson('/people/$id');
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException('Person payload missing');
    }
    return Person.fromJson(data);
  }

  Future<List<Tour>> fetchTours() async {
    final json = await _client.getJson('/tours');
    return _mapList(json, Tour.fromJson);
  }

  Future<List<TicketTier>> fetchTicketTiers() async {
    final json = await _client.getJson('/ticket-tiers');
    return _mapList(json, TicketTier.fromJson);
  }

  Future<List<MapPlace>> fetchMapPlaces() async {
    final json = await _client.getJson('/map-places');
    return _mapList(json, MapPlace.fromJson);
  }

  // ── Auth ──────────────────────────────────────────────────────────

  Future<({String token, Map<String, dynamic> user})> login({
    required String email,
    required String password,
  }) async {
    final json = await _client.postJson('/auth/login', body: {
      'email': email,
      'password': password,
    });
    return _parseAuthPayload(json);
  }

  Future<({String token, Map<String, dynamic> user})> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
  }) async {
    final json = await _client.postJson('/auth/register', body: {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      if (phone != null && phone.trim().isNotEmpty) 'phone': phone.trim(),
    });
    return _parseAuthPayload(json);
  }

  Future<Map<String, dynamic>> fetchMe() async {
    final json = await _client.getJson('/auth/me');
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException('User payload missing');
    }
    return data;
  }

  Future<void> logout() async {
    try {
      await _client.postJson('/auth/logout');
    } on ApiException {
      // Clear local session even if the token is already invalid.
    } finally {
      setAuthToken(null);
    }
  }

  // ── Payments ──────────────────────────────────────────────────────

  Future<Map<String, dynamic>> initiatePayment({
    required String type,
    int? amount,
    int? ticketTierId,
    String? customerName,
    String? customerEmail,
    String? customerPhone,
    String? method,
  }) async {
    final json = await _client.postJson('/payments/initiate', body: {
      'type': type,
      'amount': ?amount,
      'ticket_tier_id': ?ticketTierId,
      if (customerName != null && customerName.trim().isNotEmpty)
        'customer_name': customerName.trim(),
      if (customerEmail != null && customerEmail.trim().isNotEmpty)
        'customer_email': customerEmail.trim(),
      if (customerPhone != null && customerPhone.trim().isNotEmpty)
        'customer_phone': customerPhone.trim(),
      if (method != null && method.trim().isNotEmpty) 'method': method.trim(),
    });
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException('Payment payload missing');
    }
    return data;
  }

  Future<Map<String, dynamic>> fetchPayment(String reference) async {
    final json = await _client.getJson('/payments/$reference');
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException('Payment payload missing');
    }
    return data;
  }

  Future<Map<String, dynamic>> verifyPayment({
    required String reference,
    String? transactionId,
  }) async {
    final json = await _client.postJson('/payments/verify', body: {
      'reference': reference,
      if (transactionId != null && transactionId.trim().isNotEmpty)
        'transaction_id': transactionId.trim(),
    });
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException('Payment payload missing');
    }
    return data;
  }

  Future<List<Map<String, dynamic>>> fetchMyTickets() async {
    final json = await _client.getJson('/me/tickets');
    return _mapRawList(json);
  }

  Future<List<Map<String, dynamic>>> fetchMyDonations() async {
    final json = await _client.getJson('/me/donations');
    return _mapRawList(json);
  }

  // ── Forms ─────────────────────────────────────────────────────────

  Future<Map<String, dynamic>> submitForm({
    required String form,
    String? email,
    String? name,
    String? phone,
    String? message,
    String? city,
    String? organization,
    String? country,
    Map<String, dynamic>? payload,
  }) async {
    final json = await _client.postJson('/submissions', body: {
      'form': form,
      if (email != null && email.trim().isNotEmpty) 'email': email.trim(),
      if (name != null && name.trim().isNotEmpty) 'name': name.trim(),
      if (phone != null && phone.trim().isNotEmpty) 'phone': phone.trim(),
      if (message != null && message.trim().isNotEmpty) 'message': message.trim(),
      if (city != null && city.trim().isNotEmpty) 'city': city.trim(),
      if (organization != null && organization.trim().isNotEmpty)
        'organization': organization.trim(),
      if (country != null && country.trim().isNotEmpty) 'country': country.trim(),
      'payload': ?payload,
    });
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException('Submission payload missing');
    }
    return data;
  }

  ({String token, Map<String, dynamic> user}) _parseAuthPayload(
    Map<String, dynamic> json,
  ) {
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException('Auth payload missing');
    }
    final token = data['token'] as String?;
    final user = data['user'];
    if (token == null || token.isEmpty) {
      throw const ApiException('Auth token missing');
    }
    if (user is! Map<String, dynamic>) {
      throw const ApiException('Auth user missing');
    }
    setAuthToken(token);
    return (token: token, user: user);
  }

  List<Map<String, dynamic>> _mapRawList(Map<String, dynamic> json) {
    final data = json['data'];
    if (data is! List) return const [];
    return data
        .whereType<Map<String, dynamic>>()
        .map(Map<String, dynamic>.from)
        .toList(growable: false);
  }

  List<T> _mapList<T>(
    Map<String, dynamic> json,
    T Function(Map<String, dynamic>) map,
  ) {
    final data = json['data'];
    if (data is! List) return const [];
    return data
        .whereType<Map<String, dynamic>>()
        .map(map)
        .toList(growable: false);
  }
}
