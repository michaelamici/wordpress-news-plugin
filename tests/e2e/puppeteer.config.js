/**
 * Puppeteer configuration for News Plugin e2e tests
 * Based on WordPress Gutenberg testing practices
 */

module.exports = {
    // Test environment configuration
    testEnvironment: 'jsdom',
    
    // Test file patterns
    testMatch: [
        '<rootDir>/tests/e2e/**/*.test.js',
        '<rootDir>/tests/e2e/**/*.test.ts',
    ],
    
    // Setup files
    setupFilesAfterEnv: [
        '<rootDir>/tests/e2e/setup.js',
    ],
    
    // Transform configuration
    transform: {
        '^.+\\.(js|jsx|ts|tsx)$': 'babel-jest',
    },
    
    // Module name mapping
    moduleNameMapping: {
        '^@/(.*)$': '<rootDir>/src/$1',
    },
    
    // Coverage configuration
    collectCoverageFrom: [
        'src/**/*.{js,jsx,ts,tsx}',
        '!src/**/*.d.ts',
        '!src/**/index.{js,jsx,ts,tsx}',
    ],
    
    coverageDirectory: 'coverage',
    coverageReporters: ['text', 'lcov', 'html'],
    
    // Test timeout
    testTimeout: 30000,
    
    // Global setup
    globalSetup: '<rootDir>/tests/e2e/global-setup.js',
    globalTeardown: '<rootDir>/tests/e2e/global-teardown.js',
    
    // Verbose output
    verbose: true,
    
    // Clear mocks between tests
    clearMocks: true,
    
    // Reset modules between tests
    resetModules: true,
    
    // Restore mocks after each test
    restoreMocks: true,
};
