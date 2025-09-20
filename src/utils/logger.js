/**
 * Production-safe logger utility
 * Only logs in development mode, silent in production
 */

const isDev = import.meta.env.MODE === 'development';

const logger = {
  /**
   * Development-only info logging
   * @param {string} message - Log message
   * @param {...any} args - Additional arguments
   */
  info: (message, ...args) => {
    if (isDev) {
      console.log(`ℹ️ ${message}`, ...args);
    }
  },

  /**
   * Development-only success logging
   * @param {string} message - Log message
   * @param {...any} args - Additional arguments
   */
  success: (message, ...args) => {
    if (isDev) {
      console.log(`✅ ${message}`, ...args);
    }
  },

  /**
   * Development-only warning logging
   * @param {string} message - Log message
   * @param {...any} args - Additional arguments
   */
  warn: (message, ...args) => {
    if (isDev) {
      console.warn(`⚠️ ${message}`, ...args);
    }
  },

  /**
   * Error logging (always enabled, but with conditional details)
   * @param {string} message - Error message
   * @param {...any} args - Additional arguments
   */
  error: (message, ...args) => {
    if (isDev) {
      console.error(`❌ ${message}`, ...args);
    } else {
      // In production, log minimal error info
      console.error('Application error occurred');
    }
  },

  /**
   * Debug logging (development only)
   * @param {string} message - Debug message
   * @param {...any} args - Additional arguments
   */
  debug: (message, ...args) => {
    if (isDev) {
      console.log(`🔍 ${message}`, ...args);
    }
  },

  /**
   * Process logging (development only)
   * @param {string} message - Process message
   * @param {...any} args - Additional arguments
   */
  process: (message, ...args) => {
    if (isDev) {
      console.log(`🚀 ${message}`, ...args);
    }
  },

  /**
   * Data logging (development only)
   * @param {string} message - Data message
   * @param {...any} args - Additional arguments
   */
  data: (message, ...args) => {
    if (isDev) {
      console.log(`📊 ${message}`, ...args);
    }
  }
};

export default logger;