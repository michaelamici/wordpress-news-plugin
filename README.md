# New Baltimore Gazette News Plugin

## 🎯 Goal
Transform WordPress into a modern, online-only newspaper platform for the New Baltimore Gazette. This plugin adds core newsroom concepts (Sections, Fronts, Articles, Placements, Breaking Alerts) with a Gutenberg-first editorial experience.

## 🚀 Status
**v1.1.0 - Gutenberg-first Article Settings** 🎉  
*Article Settings now live in a Gutenberg sidebar; legacy PHP meta box is hidden by default and can be re-enabled via `news_show_legacy_article_meta_box` filter or disabling `Enable Blocks` setting.*

## ✨ Core Features
- **News Management**: Custom post type `news` with editorial meta fields
- **Section Organization**: Hierarchical taxonomy `news_section` for content organization
- **Fronts System**: Configurable home and section fronts with placement slots
- **Gutenberg Blocks**: Breaking news indicator and placement blocks
- **REST API**: AI-friendly JSON endpoints for all content types
- **Admin Dashboard**: News statistics and management interface
- **Security**: Capability management and input sanitization
- **Performance**: Caching and query optimization

## 🏗️ Architecture

### Core Concepts
- **Section**: Hierarchical container (e.g., World, Sports). Sections can nest.
- **Article**: News post assigned to one or more Sections.
- **Front**: A curated, configurable landing page for a Section.
- **Placement**: Optional advertisement or promo slots rendered within a Front layout.

### Data Model
- **Article**: Post type `news` with meta fields (dek, byline, location, flags)
- **Section**: Taxonomy `news_section` (hierarchical) with term meta
- **Front**: Configurable regions (hero, rails, sidebars) with queries
- **Placement**: Named ad/promo slots mapped to regions

### WordPress Integration
- **Content Types**: CPT `news`, Taxonomy `news_section`
- **REST API**: Expose meta via `register_post_meta()` with `show_in_rest => true`; REST now filters/returns individual meta keys (`_news_featured`, `_news_breaking`, etc.)
- **Security**: Custom caps, nonces, sanitization, escaping
- **Performance**: Transients/object cache, efficient queries
- **Assets**: Conditional enqueue, minimal structural CSS

## 📋 Quick Start
1. **Install**: Upload to `/wp-content/plugins/news/`
2. **Activate**: Enable through WordPress admin
3. **Configure**: Set up sections and fronts
4. **Create**: Start publishing news content!

## 🛠️ Development

### Requirements
- WordPress: 6.5+ (Gutenberg block editor, block themes only)
- PHP: 8.1+
- Database: MySQL/MariaDB as supported by WordPress core

### File Structure
```
news/
├── news.php                 # Main plugin file
├── src/
│   ├── NewsPlugin.php       # Core plugin class
│   ├── PostTypes.php        # News post type and taxonomy
│   ├── Admin.php           # Admin interface
│   ├── Blocks.php          # Gutenberg blocks
│   ├── RestApi.php         # REST API endpoints
│   └── Assets/            # CSS/JS assets
├── blocks/                 # Block definitions
└── README.md              # This file
```

### Key Classes
- `NewsPlugin`: Main plugin initialization and hooks
- `PostTypes`: News post type and section taxonomy registration
- `Admin`: Admin dashboard and management interface
- `Blocks`: Gutenberg blocks for breaking news and placements
- `RestApi`: REST API endpoints for content access

## 🔧 Configuration

### Article Settings (Gutenberg)
- Open a `news` article in the block editor; use the “Article Settings” panel in the document sidebar.
- Fields: Featured, Breaking, Exclusive, Sponsored, Live, Last Updated, Byline.
- Toggle the legacy PHP meta box via:
```php
add_filter('news_show_legacy_article_meta_box', '__return_true');
```

### Breaking Alerts
Manage breaking news alerts through the editor panel or REST API.

### Placements
Register placement slots via filters and render with actions:
```php
// Register placements
add_filter('news_register_placements', function($placements) {
    $placements['hero-top'] = [
        'name' => 'Hero Top',
        'description' => 'Top of hero section',
        'region' => 'hero'
    ];
    return $placements;
});

// Render placement
do_action('news_render_slot', 'hero-top', $context);
```

### Fronts
Configure front regions and queries through the admin interface or programmatically.

## 🎯 Success Metrics
- Core functionality working without critical bugs
- All REST API endpoints responding correctly
- Gutenberg blocks functional
- Admin interface complete
- Security hardening implemented
- Performance optimization in place

## 📄 License
Proprietary. All rights reserved.

---

**🚀 Ready for production use!**