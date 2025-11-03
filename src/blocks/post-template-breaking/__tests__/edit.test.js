/**
 * Unit tests for Breaking News Template block edit component.
 */

import { render } from '@testing-library/react';
import Edit from '../edit';

// Mock WordPress dependencies
jest.mock( '@wordpress/data', () => ( {
	useSelect: jest.fn( () => ( {
		getEntityRecords: jest.fn( () => [] ),
		getEditedEntityRecord: jest.fn( () => ( {} ) ),
	} ) ),
} ) );

jest.mock( '@wordpress/block-editor', () => ( {
	BlockControls: ( { children } ) => <div data-testid="block-controls">{ children }</div>,
	BlockContextProvider: ( { children } ) => <div data-testid="block-context">{ children }</div>,
	useBlockProps: jest.fn( () => ( {} ) ),
	useInnerBlocksProps: jest.fn( () => ( {} ) ),
	__experimentalUseBlockPreview: jest.fn( () => ( {
		renderedBlocks: [],
	} ) ),
	store: jest.fn( () => ( {} ) ),
} ) );

jest.mock( '@wordpress/components', () => ( {
	Spinner: () => <div data-testid="spinner">Loading...</div>,
	ToolbarGroup: ( { children } ) => <div data-testid="toolbar-group">{ children }</div>,
} ) );

describe( 'BreakingNewsTemplate Edit', () => {
	const defaultProps = {
		clientId: 'test-client-id',
		attributes: {},
		setAttributes: jest.fn(),
		context: {
			queryId: 1,
			query: {},
			displayLayout: {
				type: 'list',
			},
		},
	};

	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'should render the component', () => {
		const { container } = render( <Edit { ...defaultProps } /> );
		expect( container ).toBeInTheDocument();
	} );

	it( 'should render block controls', () => {
		const { getByTestId } = render( <Edit { ...defaultProps } /> );
		expect( getByTestId( 'block-controls' ) ).toBeInTheDocument();
	} );
} );

