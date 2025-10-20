#!/bin/bash

# News Plugin Version Management Script
# Usage: ./scripts/version.sh [patch|minor|major|set <version>]

set -e

# Get current version from plugin file
CURRENT_VERSION=$(grep "Version:" news.php | sed 's/.*Version: //' | sed 's/ .*//')
echo "Current version: $CURRENT_VERSION"

# Parse version components
IFS='.' read -ra VERSION_PARTS <<< "$CURRENT_VERSION"
MAJOR=${VERSION_PARTS[0]}
MINOR=${VERSION_PARTS[1]}
PATCH=${VERSION_PARTS[2]}

# Function to update version in files
update_version() {
    local new_version=$1
    echo "Updating to version: $new_version"
    
    # Update plugin header
    sed -i "s/Version: [0-9]\+\.[0-9]\+\.[0-9]\+/Version: $new_version/" news.php
    
    # Update plugin constant
    sed -i "s/NEWS_PLUGIN_VERSION', '[0-9]\+\.[0-9]\+\.[0-9]\+/NEWS_PLUGIN_VERSION', '$new_version/" news.php
    
    # Update composer.json
    sed -i "s/\"version\": \"[0-9]\+\.[0-9]\+\.[0-9]\+\"/\"version\": \"$new_version\"/" composer.json
    
    echo "✅ Version updated to $new_version"
}

# Function to create git tag
create_tag() {
    local version=$1
    local tag_message="v$version - News Plugin Release"
    
    echo "Creating git tag: v$version"
    git add .
    git commit -m "chore: Bump version to $version" || true
    git tag -a "v$version" -m "$tag_message"
    
    echo "✅ Git tag v$version created"
    echo "Run 'git push --tags' to push the tag to remote"
}

# Main version logic
case "$1" in
    "patch")
        NEW_PATCH=$((PATCH + 1))
        NEW_VERSION="$MAJOR.$MINOR.$NEW_PATCH"
        update_version $NEW_VERSION
        create_tag $NEW_VERSION
        ;;
    "minor")
        NEW_MINOR=$((MINOR + 1))
        NEW_VERSION="$MAJOR.$NEW_MINOR.0"
        update_version $NEW_VERSION
        create_tag $NEW_VERSION
        ;;
    "major")
        NEW_MAJOR=$((MAJOR + 1))
        NEW_VERSION="$NEW_MAJOR.0.0"
        update_version $NEW_VERSION
        create_tag $NEW_VERSION
        ;;
    "set")
        if [ -z "$2" ]; then
            echo "Error: Version required for 'set' command"
            echo "Usage: ./scripts/version.sh set 1.2.3"
            exit 1
        fi
        NEW_VERSION="$2"
        update_version $NEW_VERSION
        create_tag $NEW_VERSION
        ;;
    *)
        echo "Usage: $0 [patch|minor|major|set <version>]"
        echo ""
        echo "Commands:"
        echo "  patch    - Increment patch version (0.1.0 -> 0.1.1)"
        echo "  minor    - Increment minor version (0.1.0 -> 0.2.0)"
        echo "  major    - Increment major version (0.1.0 -> 1.0.0)"
        echo "  set      - Set specific version (e.g., set 1.2.3)"
        echo ""
        echo "Current version: $CURRENT_VERSION"
        exit 1
        ;;
esac

echo ""
echo "🎉 Version management complete!"
echo "Next steps:"
echo "1. Review changes: git diff"
echo "2. Push changes: git push"
echo "3. Push tags: git push --tags"
echo "4. Create release notes for v$NEW_VERSION"
