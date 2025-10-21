module.exports = {
    testMatch: [
        '<rootDir>/tests/unit/**/*.test.js',
        '<rootDir>/tests/unit/**/*.test.ts',
    ],
    testEnvironment: 'node',
    transform: {
        '^.+\\.(js|jsx|ts|tsx)$': 'babel-jest',
    },
    collectCoverageFrom: [
        'src/**/*.{js,jsx,ts,tsx}',
        '!src/**/*.d.ts',
        '!src/**/index.{js,jsx,ts,tsx}',
    ],
    coverageDirectory: 'coverage',
    coverageReporters: ['text', 'lcov', 'html'],
    testTimeout: 30000,
    verbose: true,
};