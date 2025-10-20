# New Baltimore Gazette – News Plugin

## 🎯 Goal
Adapt WordPress into a modern, online-only newspaper platform for the New Baltimore Gazette. This plugin adds core newsroom concepts (Sections, Fronts, Articles, Placements, Breaking Alerts) and a Gutenberg-first editorial experience.

## 🚀 Status
**v0.1.0 - Beta Launch Ready!** 🎉  
*November 1st, 2024*

## ✨ Highlights
- **Gutenberg-only editing** with block themes and hook/filter-first rendering
- **News Management**: Custom post type `news` with full meta fields
- **Section Organization**: Hierarchical taxonomy `news_section` with term meta
- **Fronts System**: Configurable home and section fronts with placement slots
- **REST API**: AI-friendly JSON endpoints for all content types
- **Security**: Capability management and input sanitization
- **Performance**: Caching and query optimization
- **Testing**: Comprehensive test framework

## 📋 Quick Start
1. **Install**: Upload to `/wp-content/plugins/news/`
2. **Activate**: Enable through WordPress admin
3. **Configure**: Set up fronts and sections
4. **Create**: Start publishing news content!

## 🏷️ Version History
- **v0.1.0** (Current): Beta launch with core features
- **v0.2.0** (Dec 2024): Editorial workflow features
- **v0.3.0** (Jan 2025): Performance and scale
- **v0.4.0** (Mar 2025): Advanced fronts
- **v0.5.0** (Apr 2025): Analytics and insights
- **v1.0.0** (Jun 2025): Production excellence

## 🛠️ Development

### Version Management
```bash
# Increment patch version (0.1.0 -> 0.1.1)
./scripts/version.sh patch

# Increment minor version (0.1.0 -> 0.2.0)
./scripts/version.sh minor

# Increment major version (0.1.0 -> 1.0.0)
./scripts/version.sh major

# Set specific version
./scripts/version.sh set 1.2.3
```

### Testing
```bash
# Run all tests
composer test

# Run specific test suites
composer test-unit
composer test-integration
composer test-smoke
```

## 📚 Documentation
- [Architecture](ARCHITECTURE.md) - System design, data model, and WP mapping
- [Roadmap](ROADMAP.md) - Version-based development strategy
- [Releases](RELEASES.md) - Release notes and changelog
- [Contributing](CONTRIBUTING.md) - Development guidelines

## 🎯 Getting This Thing Off the Ground!

This plugin is designed to get The New Baltimore Gazette off the ground with:
- **Modern WordPress development** (Gutenberg-only, PHP 8.1+)
- **Newspaper-specific features** (sections, fronts, placements)
- **Performance optimization** (caching, query optimization)
- **Security hardening** (capability checks, sanitization)
- **Comprehensive testing** (unit, integration, smoke tests)

**Ready for November 1st beta launch! 🚀**

## 📄 License
Proprietary. All rights reserved. See `LICENSE`.