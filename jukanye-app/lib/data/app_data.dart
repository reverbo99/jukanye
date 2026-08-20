class MenuEntry {
  const MenuEntry({
    required this.label,
    required this.route,
    required this.icon,
    this.children = const [],
  });

  final String label;
  final String route;
  final String icon;
  final List<MenuEntry> children;

  bool get hasChildren => children.isNotEmpty;
}

class AppData {
  static const donationAmounts = [2000, 5000, 10000, 20000, 50000];

  /// Main navigation tree — main items with optional sub-menus.
  static const menuTree = [
    MenuEntry(label: 'Home', route: 'home', icon: 'home'),
    MenuEntry(
      label: 'About Us',
      route: 'about',
      icon: 'info_outline',
      children: [
        MenuEntry(label: 'Friends', route: 'friends', icon: 'groups'),
        MenuEntry(label: 'Sponsors', route: 'sponsors', icon: 'handshake'),
        MenuEntry(label: 'Festival Map', route: 'map', icon: 'map'),
        MenuEntry(label: 'Contact', route: 'contact', icon: 'mail_outline'),
      ],
    ),
    MenuEntry(
      label: 'Programme',
      route: 'programme',
      icon: 'event_note',
      children: [
        MenuEntry(label: 'Speakers', route: 'speakers', icon: 'record_voice_over'),
        MenuEntry(label: 'Artists', route: 'artists', icon: 'music_note'),
        MenuEntry(label: 'Heroes', route: 'heroes', icon: 'military_tech'),
        MenuEntry(label: 'Exhibitions', route: 'exhibitions', icon: 'museum'),
        MenuEntry(label: 'Tourism', route: 'tourism', icon: 'travel_explore'),
        MenuEntry(label: 'Merchandise', route: 'shop', icon: 'shopping_bag'),
        MenuEntry(label: 'Awards', route: 'awards', icon: 'emoji_events'),
      ],
    ),
    MenuEntry(label: 'Download', route: 'download', icon: 'download'),
    MenuEntry(label: 'News', route: 'news', icon: 'newspaper'),
    MenuEntry(label: 'Vote', route: 'vote', icon: 'how_to_vote'),
    MenuEntry(label: 'Admin', route: 'admin', icon: 'admin_panel_settings'),
  ];

  static const actionButtons = [
    ('Register', 'Jiandikishe', 'register', AppActionVariant.register),
    ('Buy Tickets', 'Tiketi', 'tickets', AppActionVariant.tickets),
    ('Donors', 'Wafadhili', 'donate', AppActionVariant.donate),
  ];
}

enum AppActionVariant { register, tickets, donate }
