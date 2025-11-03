/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';

/**
 * Hook that provides props to ToolsPanel dropdown menu.
 *
 * This is a compatibility hook for ToolsPanel components.
 * In newer WordPress versions, this may be provided by @wordpress/components.
 *
 * @return {Object} Props for ToolsPanel dropdown menu.
 */
export const useToolsPanelDropdownMenuProps = () => {
	return useMemo( () => ( {} ), [] );
};

