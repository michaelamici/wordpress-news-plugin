# Roadmap to Nov 1 (Beta)

## Milestones (Step-based)
- Step 1 — Foundation: Data model scaffolding (CPT `news`, taxonomy `news_section`), options for fronts, BreakingAlert storage, placements registry spec.
- Step 2 — Fronts MVP: HomeFront + SectionFront with hero/rails SSR, caching, and REST (`/news/v1/front/<slug>`, `/news/v1/breaking`).
- Step 3 — Editor Surfaces: Gutenberg panels/blocks for front config and placements; minimal structural CSS; theme override hooks verified.
- Step 4 — Hardening: Validation, capabilities, nonces; performance passes; JSON-LD; optional content migration script.

## Constraints
- Gutenberg-only, block themes, WP 6.5+, PHP 8.1+.
- Hook/Filter-first rendering; templates only when unavoidable.
- Structural CSS only; theme can override/replace via filters.

## Risks & Mitigations
- Scope creep: lock MVP (no custom workflows); defer advanced modules.
- Theme integration: provide stable hooks and `locate_template(...)` fallbacks early.
- Performance: transients per region; invalidate on content/term changes; preload thumbnails/meta.

## Success Criteria
- Editors can publish Articles and assign Sections.
- Home/Section fronts render with hero+rails; ad slots render via hooks.
- No PHP fatals; REST and editor panels work; page TTFB acceptable.

## Exit Gates
- Step 1: CPT/taxonomy register, basic meta registered with REST exposure, options saved/loaded, placements registry callable. ✅
- Step 2: Fronts JSON endpoint returns regions with items, SSR templates render via hooks, caching keys configured. ✅
- Step 3: Editor panels load without console errors; block attributes persist; theme overrides demonstrated. ✅
- Step 4: Security checks pass (nonces/caps), Lighthouse basic performance acceptable, smoke tests green. ✅

## COMPLETED - Nov 1 Beta Ready! 🎉

### What's Been Delivered
- ✅ **Data Model**: CPT `news`, taxonomy `news_section`, meta fields with REST exposure
- ✅ **Fronts System**: HomeFront + SectionFront with hero/rails regions, caching, SSR templates
- ✅ **REST API**: `/news/v1/front/<slug>`, `/news/v1/breaking`, `/news/v1/fronts`
- ✅ **Gutenberg Blocks**: Front configuration and placement blocks with Inspector controls
- ✅ **Editor Experience**: News article panels, meta fields, section assignment
- ✅ **Admin Interface**: Dashboard, fronts management, breaking alerts, placements
- ✅ **Security**: Capability mapping, nonces, validation, sanitization
- ✅ **Performance**: Caching, query optimization, asset optimization
- ✅ **Sample Content**: 5 news articles across 4 sections showcasing all features

### Next Steps (Post-Beta)
- **Step 5**: Advanced Features
  - Breaking news ticker widget
  - Advanced placement targeting (device, time, user)
  - Analytics integration
  - Content scheduling and embargo system
  - Multi-author bylines and contributor management

- **Step 6**: Performance & Scale
  - Redis/Memcached integration
  - CDN optimization for assets
  - Database query optimization
  - Load testing and performance monitoring

- **Step 7**: Editorial Workflow
  - Content approval workflow
  - Editorial calendar integration
  - Social media auto-posting
  - SEO optimization tools

- **Step 8**: Advanced Fronts
  - Drag-and-drop front builder
  - A/B testing for front layouts
  - Personalization based on user behavior
  - Mobile-specific front configurations
