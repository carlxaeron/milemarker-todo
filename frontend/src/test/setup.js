import { config } from '@vue/test-utils'
import '@testing-library/jest-dom'

// Global test configuration
config.global.stubs = {
  // Stub any global components or directives if needed
}

// Note: axios mocking is handled in individual test files
// to avoid hoisting issues with vi.mock
