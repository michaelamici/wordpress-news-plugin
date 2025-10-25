# Block Context Fix - Gutenberg Editor Preview

## Problem
The Gutenberg editor was showing placeholder text like "Post Author" instead of actual post data (author name, title, excerpt, etc.), even though the frontend displayed correctly. This meant the WordPress block context system wasn't properly providing `postId` and `postType` to core blocks.

## Root Cause
The issue had multiple components:

1. **Missing Attributes**: The `news/front-layout` block declared `providesContext` but didn't have the corresponding attributes (`postId`, `postType`, `position`) in its `attributes` section of `block.json`.

2. **React Context vs WordPress Block Context**: Both blocks were using React's `createContext()` and `Context.Provider`, but WordPress core blocks (like `core/post-author`, `core/post-title`, etc.) don't read from React Context. They only read from WordPress's native block context system managed through `providesContext` in `block.json`.

3. **Context Not Synced to Attributes**: The hero template block received context from its parent but wasn't syncing it to its own attributes, so `providesContext` couldn't pass it to its children (the core blocks).

## Solution

### 1. Added Missing Attributes (news-article-layout/block.json)
Added `postId`, `postType`, and `position` to the attributes section so that `providesContext` can expose them as context.

### 2. Provide Standard WordPress Context Keys
Updated `providesContext` in both blocks to include:
- Custom keys: `news/postId`, `news/postType`, `news/position`
- Standard keys: `postId`, `postType` (what core blocks expect)

### 3. Removed React Context Providers
Removed all uses of React's `createContext()` and `Context.Provider` since they don't work with WordPress core blocks. WordPress blocks use their own context system based on `block.json` configuration.

### 4. Sync Context to Attributes in Hero Template
Added logic to sync received context to the block's own attributes:

```javascript
// Sync context to attributes so providesContext can pass them to children
if (postId && (attributes.postId !== postId || attributes.postType !== postType)) {
    setAttributes({
        postId: postId,
        postType: postType,
        position: position
    });
}
```

This ensures that when the hero template block receives context from its parent, it immediately sets its own attributes, which are then provided as context to its children through the `providesContext` mechanism.

## How WordPress Block Context Works

1. **Attributes Declaration**: A block must declare attributes in `block.json`
2. **providesContext**: Maps attribute names to context keys that child blocks can access
3. **usesContext**: Child blocks declare which context keys they need
4. **Automatic Flow**: WordPress automatically provides attribute values as context to children

### Context Flow in Our Blocks

```
Front Layout Block
├── Has attributes: postId, postType, position
├── providesContext: { "postId": "postId", "postType": "postType", ... }
└── Sets attributes when hero post is available

    ↓ Context flows automatically

Hero Template Block
├── usesContext: ["postId", "postType", ...]
├── Receives context and syncs to its own attributes
├── providesContext: { "postId": "postId", "postType": "postType", ... }
└── Passes context to its children

    ↓ Context flows automatically

Core Blocks (post-title, post-author, etc.)
├── usesContext: ["postId", "postType"]
└── Receive context and fetch/display actual post data
```

## Key Takeaways

1. **WordPress block context ≠ React context**: Core blocks only work with WordPress's `providesContext` system
2. **Attributes are required**: `providesContext` only works if attributes are declared in `block.json`
3. **Sync context to attributes**: When a block receives context and needs to pass it further, sync it to attributes via `setAttributes()`
4. **Use standard keys**: Core blocks expect standard context keys like `postId` and `postType`, not custom namespaced ones

## Result

With these changes:
- The Gutenberg editor now displays actual post data (author names, titles, excerpts, etc.)
- Core blocks receive proper context and can fetch post data
- The editor preview matches the frontend display
- The solution follows WordPress's native block context system

