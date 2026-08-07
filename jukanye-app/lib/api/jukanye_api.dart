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
