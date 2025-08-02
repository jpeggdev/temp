const DEFAULT_RETRY_COUNT = 3;
const DEFAULT_RETRY_DELAY = 1000; // 1 second
const MAX_RETRY_DELAY = 5000; // 5 seconds

interface RetryConfig {
  maxRetries?: number;
  initialDelay?: number;
  maxDelay?: number;
  shouldRetry?: (error: unknown) => boolean;
}

interface ApiError extends Error {
  status: number;
}

export async function withRetry<T>(
  operation: () => Promise<T>,
  config: RetryConfig = {},
): Promise<T> {
  const {
    maxRetries = DEFAULT_RETRY_COUNT,
    initialDelay = DEFAULT_RETRY_DELAY,
    maxDelay = MAX_RETRY_DELAY,
    shouldRetry = (error: unknown) => {
      // By default, retry on network errors and 5xx server errors
      if (error instanceof Error) {
        if ("status" in error) {
          const status = (error as ApiError).status;
          return status >= 500 && status < 600;
        }
        return error.name === "NetworkError" || error.name === "TypeError";
      }
      return false;
    },
  } = config;

  let lastError: unknown;
  let delay = initialDelay;

  for (let attempt = 0; attempt < maxRetries; attempt++) {
    try {
      return await operation();
    } catch (error) {
      lastError = error;

      if (attempt === maxRetries - 1 || !shouldRetry(error)) {
        throw error;
      }

      // Exponential backoff with jitter
      const jitter = Math.random() * 200;
      await new Promise((resolve) =>
        setTimeout(resolve, Math.min(delay + jitter, maxDelay)),
      );
      delay *= 2;
    }
  }

  throw lastError;
}
